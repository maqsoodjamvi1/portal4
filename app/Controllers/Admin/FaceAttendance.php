<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\RekognitionService;
use Aws\Exception\AwsException;

class FaceAttendance extends BaseController
{
    protected $db;
    protected $session;
    protected $rekognition;

    /** @var \Config\Aws */
    protected $awsConfig;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->rekognition = new RekognitionService();
        $this->awsConfig = config('Aws');

        helper(['form']);
        check_permission('admin-emp-attendance-monthly-report');
    }

    public function index()
    {
        return view('admin/face_attendance');
    }

    public function management()
    {
        return view('admin/face_management');
    }


public function getStudents()
{
    $campus_id = session('member_campusid');
    $sessionid = session('member_sessionid');
    
    $students = $this->db->table('students s')
        ->select('s.student_id, s.reg_no, s.first_name, s.last_name')
        ->join('student_class sc', 'sc.student_id = s.student_id AND sc.session_id = ' . (int)$sessionid)
        ->where('s.campus_id', $campus_id)
        ->where('s.status', 1)
        ->orderBy('s.first_name', 'ASC')
        ->get()
        ->getResultArray();
    
    return $this->response->setJSON([
        'success' => true,
        'data' => $students
    ]);
}


    // ?? ENROLL FACE
    public function enroll()
    {
        if (!$this->request->isAJAX()) {
            return json_response(['success' => false, 'msg' => 'Invalid request']);
        }

        $file = $this->request->getFile('image');
        $studentId = (int)$this->request->getPost('student_id');
        $campusId = $this->session->get('member_campusid');

        if (!$file || !$file->isValid() || !$studentId) {
            return json_response(['success' => false, 'msg' => 'Missing data']);
        }

        $imageBytes = file_get_contents($file->getTempName());
        $maxBytes = $this->awsConfig->rekognitionMaxImageBytes;
        if ($maxBytes > 0 && strlen($imageBytes) > $maxBytes) {
            return json_response(['success' => false, 'msg' => 'Image too large; use a smaller photo.']);
        }

        try {
            $result = $this->rekognition->indexFace($imageBytes, $studentId, $campusId);
        } catch (AwsException $e) {
            log_message('error', 'Rekognition indexFace: ' . $e->getAwsErrorMessage());

            return json_response(['success' => false, 'msg' => 'Face service error. Try again or check AWS settings.']);
        }

        if (empty($result['FaceRecords'])) {
            return json_response(['success' => false, 'msg' => 'No usable face detected. Face the camera with good light.']);
        }

        $faceId = $result['FaceRecords'][0]['Face']['FaceId'];

        $fileName = 'uploads/faces/' . time() . '_' . $studentId . '.jpg';
        $file->move(ROOTPATH . 'public/uploads/faces', basename($fileName));

        $this->db->table('student_faces')->insert([
            'student_id' => $studentId,
            'face_id' => $faceId,
            'campus_id' => $campusId,
            'image_path' => $fileName,
            'created_date' => date('Y-m-d H:i:s'),
            'user_id' => $this->session->get('member_userid')
        ]);

        return json_response(['success' => true, 'msg' => 'Face enrolled']);
    }

    // ?? MARK ATTENDANCE
    public function mark()
    {
        if (!$this->request->isAJAX()) {
            return json_response(['success' => false, 'msg' => 'Invalid request']);
        }

        $file = $this->request->getFile('image');
        $campusId = (int)$this->session->get('member_campusid');

        if (!$file || !$file->isValid()) {
            return json_response(['success' => false, 'msg' => 'Image required']);
        }

        $imageBytes = file_get_contents($file->getTempName());
        $maxBytes = $this->awsConfig->rekognitionMaxImageBytes;
        if ($maxBytes > 0 && strlen($imageBytes) > $maxBytes) {
            return json_response(['success' => false, 'msg' => 'Image too large.']);
        }

        try {
            $result = $this->rekognition->searchFace($imageBytes, $campusId);
        } catch (AwsException $e) {
            log_message('error', 'Rekognition searchFace: ' . $e->getAwsErrorMessage());

            return json_response(['success' => false, 'msg' => 'Face service unavailable. Try again shortly.']);
        }

        if (empty($result['FaceMatches'])) {
            return json_response(['success' => false, 'msg' => 'Face not recognized. Ensure you are enrolled and lighting is good.']);
        }

        $match = $result['FaceMatches'][0];
        $similarity = (float)($match['Similarity'] ?? 0);
        if ($similarity < $this->awsConfig->rekognitionMinSimilarityPercent) {
            return json_response(['success' => false, 'msg' => 'Low confidence match. Move closer to the camera.']);
        }

        $studentId = (int)($match['Face']['ExternalImageId'] ?? 0);
        if ($studentId < 1) {
            return json_response(['success' => false, 'msg' => 'Invalid match data']);
        }

        $student = $this->db->table('students')
            ->select('student_id, first_name, last_name, reg_no')
            ->where('student_id', $studentId)
            ->where('campus_id', $campusId)
            ->where('status', 1)
            ->get()
            ->getRow();

        if (!$student) {
            return json_response(['success' => false, 'msg' => 'Student not found for this campus.']);
        }

        $displayName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));

        $today = date('Y-m-d');

        $exists = $this->db->table('attendance')
            ->where('student_id', $studentId)
            ->where('date', $today)
            ->get()->getRow();

        $alreadyPresent = $exists !== null;

        if (!$alreadyPresent) {
            $this->db->table('attendance')->insert([
                'student_id' => $studentId,
                'date' => $today,
                'checkin' => date('H:i:s'),
                'status' => 'present',
                'created_date' => date('Y-m-d H:i:s'),
                'user_id' => $this->session->get('member_userid')
            ]);
        }

        return json_response([
            'success' => true,
            'msg' => $alreadyPresent
                ? ('Already marked present today: ' . $displayName)
                : ('Attendance marked: ' . $displayName),
            'student_id' => $studentId,
            'student_name' => $displayName,
            'reg_no' => $student->reg_no ?? '',
            'similarity' => round($similarity, 2),
            'already_present' => $alreadyPresent,
        ]);
    }

    // ?? LIST FACES
    public function data()
    {
        $campusId = $this->session->get('member_campusid');

        $rows = $this->db->table('student_faces sf')
            ->select('sf.student_id, sf.face_id, sf.image_path, s.first_name, s.last_name, s.reg_no')
            ->join('students s', 's.student_id = sf.student_id', 'left')
            ->where('sf.campus_id', $campusId)
            ->orderBy('sf.student_id', 'ASC')
            ->get()
            ->getResult();

        $data = [];
        $i = 1;

        foreach ($rows as $row) {
            $name = trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? ''));
            $data[] = [
                'sno' => $i++,
                'student_id' => $row->student_id,
                'student_name' => $name !== '' ? $name : ('ID ' . $row->student_id),
                'reg_no' => $row->reg_no ?? '',
                'image' => !empty($row->image_path) ? base_url($row->image_path) : '',
                'face_id' => $row->face_id
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    // ?? DELETE FACE
    public function delete()
    {
        $faceId = $this->request->getPost('face_id');
        $campusId = $this->session->get('member_campusid');

        try {
            $this->rekognition->deleteFace($faceId, $campusId);
        } catch (AwsException $e) {
            log_message('error', 'Rekognition deleteFace: ' . $e->getAwsErrorMessage());

            return json_response(['success' => false, 'msg' => 'Could not remove face from AWS.']);
        }

        $this->db->table('student_faces')->where('face_id', $faceId)->delete();

        return json_response(['success' => true, 'msg' => 'Deleted']);
    }
}