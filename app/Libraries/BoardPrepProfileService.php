<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;

class BoardPrepProfileService
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * @return object|null
     */
    public function loadForUser(int $userId)
    {
        if ($userId <= 0 || ! $this->db->tableExists('board_prep_users')) {
            return null;
        }

        $user = $this->db->table('board_prep_users bpu')
            ->select('bpu.*, bp.name AS board_name')
            ->join('qb_board_publishers bp', 'bp.id = bpu.board_publisher_id', 'left')
            ->where('bpu.id', $userId)
            ->get()
            ->getRow();

        if (! $user) {
            return null;
        }

        $studentId = (int) ($user->linked_student_id ?? 0);
        if ($studentId <= 0) {
            return $user;
        }

        $student = $this->db->table('students s')
            ->select('s.date_of_birth, s.profile_photo, s.previous_school, s.ps_city, s.parent_id')
            ->where('s.student_id', $studentId)
            ->get()
            ->getRow();

        $parent = null;
        if ($student && (int) ($student->parent_id ?? 0) > 0) {
            $parent = $this->db->table('parents')
                ->select('f_name, father_contact, city')
                ->where('parent_id', (int) $student->parent_id)
                ->get()
                ->getRow();
        }

        if (! $this->hasColumn('phone') && $parent) {
            $user->phone = (string) ($parent->father_contact ?? '');
        } elseif ($this->hasColumn('phone') && ($user->phone ?? '') === '' && $parent) {
            $user->phone = (string) ($parent->father_contact ?? '');
        }

        if (! $this->hasColumn('city')) {
            $user->city = (string) ($parent->city ?? $student->ps_city ?? '');
        } elseif (($user->city ?? '') === '') {
            $user->city = (string) ($parent->city ?? $student->ps_city ?? '');
        }

        if (! $this->hasColumn('school_name')) {
            $user->school_name = (string) ($student->previous_school ?? '');
        } elseif (($user->school_name ?? '') === '') {
            $user->school_name = (string) ($student->previous_school ?? '');
        }

        if (! $this->hasColumn('date_of_birth')) {
            $user->date_of_birth = $student->date_of_birth ?? null;
        } elseif (($user->date_of_birth ?? '') === '' || $user->date_of_birth === null) {
            $user->date_of_birth = $student->date_of_birth ?? null;
        }

        if (! $this->hasColumn('profile_photo')) {
            $user->profile_photo = (string) ($student->profile_photo ?? '');
        } elseif (($user->profile_photo ?? '') === '') {
            $user->profile_photo = (string) ($student->profile_photo ?? '');
        }

        if (($user->father_name ?? '') === '' && $parent) {
            $user->father_name = (string) ($parent->f_name ?? '');
        }

        return $user;
    }

    /**
     * @return array{success:bool,msg?:string,profile?:object}
     */
    public function update(int $userId, array $input, ?UploadedFile $photoFile = null): array
    {
        $profile = $this->loadForUser($userId);
        if (! $profile) {
            return ['success' => false, 'msg' => 'Profile not found.'];
        }

        $fatherName = trim((string) ($input['father_name'] ?? ''));
        if ($fatherName === '') {
            return ['success' => false, 'msg' => 'Father name is required.'];
        }

        $phone       = trim((string) ($input['phone'] ?? ''));
        $city        = trim((string) ($input['city'] ?? ''));
        $schoolName  = trim((string) ($input['school_name'] ?? ''));
        $province    = trim((string) ($input['province'] ?? ''));
        $country     = trim((string) ($input['country'] ?? ''));
        $dateOfBirth = trim((string) ($input['date_of_birth'] ?? ''));

        if ($dateOfBirth !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateOfBirth)) {
            return ['success' => false, 'msg' => 'Please enter a valid date of birth.'];
        }

        $profilePhoto = (string) ($profile->profile_photo ?? '');
        if ($photoFile !== null && $photoFile->isValid() && ! $photoFile->hasMoved()) {
            $uploadDir = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
            $stored    = (new SecureUploadService())->storeImage($photoFile, $uploadDir);
            if ($stored === null) {
                return ['success' => false, 'msg' => 'Photo upload failed. Use JPG/PNG under 2 MB.'];
            }
            $profilePhoto = $stored;
        }

        $now = date('Y-m-d H:i:s');

        $userData = ['father_name' => $fatherName, 'updated_at' => $now];
        if ($this->hasColumn('phone')) {
            $userData['phone'] = $phone;
        }
        if ($this->hasColumn('city')) {
            $userData['city'] = $city;
        }
        if ($this->hasColumn('school_name')) {
            $userData['school_name'] = $schoolName;
        }
        if ($this->hasColumn('province')) {
            $userData['province'] = $province;
        }
        if ($this->hasColumn('country')) {
            $userData['country'] = $country;
        }
        if ($this->hasColumn('date_of_birth')) {
            $userData['date_of_birth'] = $dateOfBirth !== '' ? $dateOfBirth : null;
        }
        if ($this->hasColumn('profile_photo')) {
            $userData['profile_photo'] = $profilePhoto;
        }

        $this->db->transStart();

        $this->db->table('board_prep_users')
            ->where('id', $userId)
            ->update($userData);

        $this->syncLinkedRecords($profile, $fatherName, $phone, $city, $schoolName, $dateOfBirth, $profilePhoto, $now);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return ['success' => false, 'msg' => 'Could not save profile. Please try again.'];
        }

        $updated = $this->loadForUser($userId);

        return ['success' => true, 'profile' => $updated];
    }

    private function syncLinkedRecords(
        object $profile,
        string $fatherName,
        string $phone,
        string $city,
        string $schoolName,
        string $dateOfBirth,
        string $profilePhoto,
        string $now
    ): void {
        $studentId = (int) ($profile->linked_student_id ?? 0);
        if ($studentId <= 0) {
            return;
        }

        $studentRow = $this->db->table('students')
            ->select('parent_id')
            ->where('student_id', $studentId)
            ->get()
            ->getRow();

        $studentUpdate = $this->filterTableFields('students', [
            'date_of_birth'   => $dateOfBirth !== '' ? $dateOfBirth : null,
            'profile_photo'   => $profilePhoto,
            'previous_school' => $schoolName,
            'ps_city'         => $city,
            'updated_date'    => $now,
        ]);

        if ($studentUpdate !== []) {
            $this->db->table('students')
                ->where('student_id', $studentId)
                ->update($studentUpdate);
        }

        $parentId = (int) ($studentRow->parent_id ?? 0);
        if ($parentId <= 0) {
            return;
        }

        $parentUpdate = $this->filterTableFields('parents', [
            'f_name'         => $fatherName,
            'father_contact' => $phone,
            'city'           => $city,
            'updated_date'   => $now,
        ]);

        if ($parentUpdate !== []) {
            $this->db->table('parents')
                ->where('parent_id', $parentId)
                ->update($parentUpdate);
        }
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function filterTableFields(string $table, array $data): array
    {
        $filtered = [];
        foreach ($data as $column => $value) {
            if ($this->fieldExists($table, $column)) {
                $filtered[$column] = $value;
            }
        }

        return $filtered;
    }

    private function hasColumn(string $column): bool
    {
        return $this->fieldExists('board_prep_users', $column);
    }

    private function fieldExists(string $table, string $column): bool
    {
        try {
            return $this->db->fieldExists($column, $table);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
