<?php

namespace App\Libraries;

use Config\BoardPrep;

class BoardPrepProvisioningService
{
    protected $db;

    protected BoardPrep $config;

    protected BoardPrepPlatformService $platform;

    public function __construct(?BoardPrep $config = null)
    {
        $this->db       = \Config\Database::connect();
        $this->config   = $config ?? config('BoardPrep');
        $this->platform = new BoardPrepPlatformService($this->config);
    }

    /**
     * @param array{display_name?:string,username?:string,father_name?:string,password?:string,grade_level?:string,board_publisher_id?:int} $input
     * @return array{success:bool,msg?:string,user_id?:int,username?:string,linked_student_id?:int}
     */
    public function provision(array $input): array
    {
        $displayName = trim((string) ($input['display_name'] ?? ''));
        $username    = strtolower(trim((string) ($input['username'] ?? '')));
        $fatherName  = trim((string) ($input['father_name'] ?? ''));
        $password    = (string) ($input['password'] ?? '');
        $gradeLevel  = trim((string) ($input['grade_level'] ?? ''));
        $boardId     = (int) ($input['board_publisher_id'] ?? 0);

        if ($displayName === '' || $username === '' || $fatherName === '' || $password === '') {
            return ['success' => false, 'msg' => 'All required fields must be filled.'];
        }

        if (! preg_match('/^[a-z0-9._-]{3,32}$/', $username)) {
            return ['success' => false, 'msg' => 'Username must be 3–32 characters (letters, numbers, dot, dash, underscore).'];
        }

        if (! isset($this->config->gradeLabels[$gradeLevel])) {
            return ['success' => false, 'msg' => 'Please select a valid class.'];
        }

        if ($boardId <= 0) {
            return ['success' => false, 'msg' => 'Please select your board.'];
        }

        $boardExists = $this->db->table('qb_board_publishers')
            ->where('id', $boardId)
            ->where('status', 1)
            ->countAllResults() > 0;
        if (! $boardExists) {
            return ['success' => false, 'msg' => 'Selected board is not valid.'];
        }

        if ($this->usernameExists($username)) {
            return ['success' => false, 'msg' => 'This username is already taken. Please choose another.'];
        }

        try {
            $platform = $this->platform->ensurePlatform();
        } catch (\Throwable $e) {
            log_message('error', 'BoardPrepProvisioningService platform: ' . $e->getMessage());

            return ['success' => false, 'msg' => 'Platform setup failed. Please contact support.'];
        }

        $clsSecId  = (int) ($platform['grade_cls_sec'][$gradeLevel] ?? 0);
        $campusId  = (int) $platform['campus_id'];
        $sessionId = (int) $platform['session_id'];

        if ($clsSecId <= 0 || $campusId <= 0 || $sessionId <= 0) {
            return ['success' => false, 'msg' => 'Class setup is incomplete. Please contact support.'];
        }

        $classRow = $this->db->table('class_section cs')
            ->select('cs.class_id')
            ->where('cs.cls_sec_id', $clsSecId)
            ->get()
            ->getRow();
        $classId = (int) ($classRow->class_id ?? 0);

        $now = date('Y-m-d H:i:s');

        $this->db->transStart();

        $this->db->table('board_prep_users')->insert([
            'username'             => $username,
            'password_hash'        => password_hash($password, PASSWORD_DEFAULT),
            'display_name'         => $displayName,
            'father_name'          => $fatherName,
            'grade_level'          => $gradeLevel,
            'board_publisher_id'   => $boardId,
            'status'               => 'active',
            'created_at'           => $now,
            'updated_at'           => $now,
        ]);
        $userId = (int) $this->db->insertID();

        if ($userId <= 0) {
            $this->db->transRollback();

            return ['success' => false, 'msg' => 'Could not create account. Please try again.'];
        }

        $parentId = $this->createStubParent($userId, $username, $fatherName, $campusId, $now);
        if ($parentId <= 0) {
            $this->db->transRollback();

            return $this->provisionFailure('parent');
        }

        $studentId = $this->createShadowStudent(
            $userId,
            $username,
            $displayName,
            $parentId,
            $campusId,
            $sessionId,
            $classId,
            $clsSecId,
            $now
        );
        if ($studentId <= 0) {
            $this->db->transRollback();

            return $this->provisionFailure('student');
        }

        $this->db->table('board_prep_users')
            ->where('id', $userId)
            ->update([
                'linked_student_id' => $studentId,
                'updated_at'        => $now,
            ]);

        $this->insertStudentClassRow($studentId, $sessionId, $clsSecId, $classId, $now);

        $this->db->transComplete();

        if ($this->db->transStatus() === false || $studentId <= 0) {
            return $this->provisionFailure('transaction');
        }

        return [
            'success'           => true,
            'user_id'           => $userId,
            'username'          => $username,
            'linked_student_id' => $studentId,
        ];
    }

    public function usernameExists(string $username): bool
    {
        if (! $this->db->tableExists('board_prep_users')) {
            return false;
        }

        return $this->db->table('board_prep_users')
            ->where('username', strtolower($username))
            ->countAllResults() > 0;
    }

    /**
     * @return array{success:bool,msg?:string,user?:object}
     */
    public function authenticate(string $username, string $password): array
    {
        $username = strtolower(trim($username));
        if ($username === '' || $password === '') {
            return ['success' => false, 'msg' => 'Username and password are required.'];
        }

        $user = $this->db->table('board_prep_users bpu')
            ->select('bpu.*, bp.name AS board_name')
            ->join('qb_board_publishers bp', 'bp.id = bpu.board_publisher_id', 'left')
            ->where('bpu.username', $username)
            ->get()
            ->getRow();

        if (! $user || ($user->status ?? '') !== 'active') {
            return ['success' => false, 'msg' => 'Invalid username or password.'];
        }

        if (! password_verify($password, (string) $user->password_hash)) {
            return ['success' => false, 'msg' => 'Invalid username or password.'];
        }

        return ['success' => true, 'user' => $user];
    }

    public function establishSession(object $user): void
    {
        $linkedStudentId = (int) ($user->linked_student_id ?? 0);

        session()->set([
            'board_prep_auth' => [
                'logged_in'          => true,
                'user_id'            => (int) $user->id,
                'username'           => (string) $user->username,
                'display_name'       => (string) $user->display_name,
                'father_name'        => (string) $user->father_name,
                'grade_level'        => (string) $user->grade_level,
                'board_publisher_id' => (int) $user->board_publisher_id,
                'board_name'         => (string) ($user->board_name ?? ''),
                'linked_student_id'  => $linkedStudentId,
            ],
            'auth' => [
                'logged_in' => true,
                'role'      => 'student',
            ],
            'student_id' => $linkedStudentId,
        ]);
    }

    private function createStubParent(int $userId, string $username, string $fatherName, int $campusId, string $now): int
    {
        $cnic = 'BP-' . $userId . '-' . substr(md5($username), 0, 8);

        $existing = $this->db->table('parents')
            ->where('father_cnic', $cnic)
            ->where('campus_id', $campusId)
            ->get()
            ->getRow();
        if ($existing) {
            return (int) $existing->parent_id;
        }

        $parentData = $this->filterTableFields('parents', [
            'father_cnic'    => $cnic,
            'f_name'         => $fatherName,
            'religion'       => 'Islam',
            'father_contact' => '',
            'whatsapp'       => '',
            'father_email'   => '',
            'm_name'         => '',
            'address_line1'  => '',
            'city'           => '',
            'password'       => password_hash(bin2hex(random_bytes(8)), PASSWORD_BCRYPT),
            'campus_id'      => $campusId,
            'created_date'   => $now,
            'updated_date'   => $now,
            'user_id'        => 0,
        ]);

        $this->db->table('parents')->insert($parentData);

        return (int) $this->db->insertID();
    }

    private function createShadowStudent(
        int $userId,
        string $username,
        string $displayName,
        int $parentId,
        int $campusId,
        int $sessionId,
        int $classId,
        int $clsSecId,
        string $now
    ): int {
        $parts     = preg_split('/\s+/', $displayName, 2, PREG_SPLIT_NO_EMPTY);
        $firstName = $parts[0] ?? $displayName;
        $lastName  = $parts[1] ?? '';

        $regNo = 'BPREP-' . $userId;

        $studentData = $this->filterTableFields('students', [
            'reg_no'            => $regNo,
            'first_name'        => $firstName,
            'last_name'         => $lastName,
            'std_cnic'          => '',
            'parent_id'         => $parentId,
            'date_of_admission' => date('Y-m-d'),
            'date_of_birth'     => null,
            'gr_no'             => '',
            'gr_date'           => null,
            'class_id'          => $classId,
            'cls_sec_id'        => $clsSecId,
            'campus_id'         => $campusId,
            'session_id'        => $sessionId,
            'discounted_amount' => 0,
            'fee_plan'          => 1,
            'status'            => 1,
            's_flag'            => 1,
            'profile_photo'     => '',
            'std_type'          => 1,
            'account_type'      => 'board_prep',
            'board_prep_user_id'=> $userId,
            'created_date'      => $now,
            'updated_date'      => $now,
            'user_id'           => 0,
        ]);

        $this->db->table('students')->insert($studentData);

        return (int) $this->db->insertID();
    }

    private function insertStudentClassRow(int $studentId, int $sessionId, int $clsSecId, int $classId, string $now): void
    {
        $row = $this->filterTableFields('student_class', [
            'student_id'   => $studentId,
            'session_id'   => $sessionId,
            'cls_sec_id'   => $clsSecId,
            'class_id'     => $classId,
            'status'       => 1,
            'created_date' => $now,
            'updated_date' => $now,
            'user_id'      => 0,
        ]);

        $this->db->table('student_class')->insert($row);
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

    private function fieldExists(string $table, string $column): bool
    {
        try {
            return $this->db->fieldExists($column, $table);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @return array{success:false,msg:string}
     */
    private function provisionFailure(string $stage): array
    {
        $dbError = $this->db->error();
        $message = trim((string) ($dbError['message'] ?? ''));
        log_message('error', 'Board prep provision failed at {stage}: {msg}', [
            'stage' => $stage,
            'msg'   => $message !== '' ? $message : 'unknown',
        ]);

        return ['success' => false, 'msg' => 'Account could not be completed. Please try again.'];
    }
}
