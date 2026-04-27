<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\RekognitionService;

class FaceAttendance extends BaseController
{
    protected $db;
    protected $session;
    protected $rekognition;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->rekognition = new RekognitionService();

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

        if (!$file || !$studentId) {
            return json_response(['success' => false, 'msg' => 'Missing data']);
        }

        $imageBytes = file_get_contents($file->getTempName());

        $result = $this->rekognition->indexFace($imageBytes, $studentId, $campusId);

        if (empty($result['FaceRecords'])) {
            return json_response(['success' => false, 'msg' => 'No face detected']);
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
        $campusId = $this->session->get('member_campusid');

        if (!$file) {
            return json_response(['success' => false, 'msg' => 'Image required']);
        }

        $imageBytes = file_get_contents($file->getTempName());

        $result = $this->rekognition->searchFace($imageBytes, $campusId);

        if (empty($result['FaceMatches'])) {
            return json_response(['success' => false, 'msg' => 'Face not recognized']);
        }

        $studentId = $result['FaceMatches'][0]['Face']['ExternalImageId'];

        $today = date('Y-m-d');

        $exists = $this->db->table('attendance')
            ->where('student_id', $studentId)
            ->where('date', $today)
            ->get()->getRow();

        if (!$exists) {
            $this->db->table('attendance')->insert([
                'student_id' => $studentId,
                'date' => $today,
                'checkin' => date('H:i:s'),
                'status' => 'present',
                'created_date' => date('Y-m-d H:i:s'),
                'user_id' => $this->session->get('member_userid')
            ]);
        }

        return json_response(['success' => true, 'msg' => 'Attendance marked']);
    }

    // ?? LIST FACES
    public function data()
    {
        $campusId = $this->session->get('member_campusid');

        $rows = $this->db->table('student_faces')
            ->where('campus_id', $campusId)
            ->get()->getResult();

        $data = [];
        $i = 1;

        foreach ($rows as $row) {
            $data[] = [
                'sno' => $i++,
                'student_id' => $row->student_id,
                'image' => base_url($row->image_path),
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

        $this->rekognition->deleteFace($faceId, $campusId);

        $this->db->table('student_faces')->where('face_id', $faceId)->delete();

        return json_response(['success' => true, 'msg' => 'Deleted']);
    }
}