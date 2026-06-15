<?php



namespace App\Controllers\Admin;



use App\Controllers\BaseController;

use Aws\Exception\AwsException;



class EmployeeFaceAttendance extends BaseController

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

        $this->rekognition = new \App\Libraries\RekognitionService();

        $this->awsConfig = config('Aws');



        helper(['form']);

        check_any_permission(['admin-employee-face-attendance', 'admin-employee-face-management']);

    }



    public function index()

    {

        return view('admin/employee_face_attendance');

    }



    public function management()

    {

        return view('admin/employee_face_management');

    }



    public function getEmployees()

    {

        $campusId = (int) $this->session->get('member_campusid');



        $employees = $this->db->table('users')

            ->select('id, first_name, last_name')

            ->where('campus_id', $campusId)

            ->where('status', 1)

            ->orderBy('first_name', 'ASC')

            ->orderBy('last_name', 'ASC')

            ->get()

            ->getResultArray();



        return $this->response->setJSON([

            'success' => true,

            'data' => $employees,

        ]);

    }



    public function enroll()

    {

        if (!$this->request->isAJAX()) {

            return json_response(['success' => false, 'msg' => 'Invalid request']);

        }



        $file = $this->request->getFile('image');

        $empId = (int) $this->request->getPost('emp_id');

        $campusId = (int) $this->session->get('member_campusid');



        if (!$file || !$file->isValid() || $empId < 1) {

            return json_response(['success' => false, 'msg' => 'Missing data']);

        }



        $employee = $this->db->table('users')

            ->where('id', $empId)

            ->where('campus_id', $campusId)

            ->where('status', 1)

            ->get()

            ->getRow();



        if (!$employee) {

            return json_response(['success' => false, 'msg' => 'Employee not found for this campus.']);

        }



        $imageBytes = file_get_contents($file->getTempName());

        $maxBytes = $this->awsConfig->rekognitionMaxImageBytes;

        if ($maxBytes > 0 && strlen($imageBytes) > $maxBytes) {

            return json_response(['success' => false, 'msg' => 'Image too large; use a smaller photo.']);

        }



        try {

            $result = $this->rekognition->indexFace($imageBytes, $empId, $campusId, 'employee');

        } catch (AwsException $e) {

            log_message('error', 'Rekognition employee indexFace: ' . $e->getAwsErrorMessage());



            return json_response(['success' => false, 'msg' => 'Face service error. Try again or check AWS settings.']);

        }



        if (empty($result['FaceRecords'])) {

            return json_response(['success' => false, 'msg' => 'No usable face detected. Face the camera with good light.']);

        }



        $faceId = $result['FaceRecords'][0]['Face']['FaceId'];



        $uploadDir = ROOTPATH . 'public/uploads/faces/employees';

        if (!is_dir($uploadDir)) {

            mkdir($uploadDir, 0755, true);

        }



        $fileName = 'uploads/faces/employees/' . time() . '_' . $empId . '.jpg';

        $file->move($uploadDir, basename($fileName));



        $this->db->table('employee_faces')->insert([

            'emp_id' => $empId,

            'face_id' => $faceId,

            'campus_id' => $campusId,

            'image_path' => $fileName,

            'created_date' => date('Y-m-d H:i:s'),

            'user_id' => $this->session->get('member_userid'),

        ]);



        return json_response(['success' => true, 'msg' => 'Employee face enrolled']);

    }



    public function mark()

    {

        if (!$this->request->isAJAX()) {

            return json_response(['success' => false, 'msg' => 'Invalid request']);

        }



        $file = $this->request->getFile('image');

        $campusId = (int) $this->session->get('member_campusid');



        if (!$file || !$file->isValid()) {

            return json_response(['success' => false, 'msg' => 'Image required']);

        }



        $imageBytes = file_get_contents($file->getTempName());

        $maxBytes = $this->awsConfig->rekognitionMaxImageBytes;

        if ($maxBytes > 0 && strlen($imageBytes) > $maxBytes) {

            return json_response(['success' => false, 'msg' => 'Image too large.']);

        }



        try {

            $result = $this->rekognition->searchFace($imageBytes, $campusId, 'employee');

        } catch (AwsException $e) {

            log_message('error', 'Rekognition employee searchFace: ' . $e->getAwsErrorMessage());



            return json_response(['success' => false, 'msg' => 'Face service unavailable. Try again shortly.']);

        }



        if (empty($result['FaceMatches'])) {

            return json_response(['success' => false, 'msg' => 'Face not recognized. Enroll the employee first and ensure good lighting.']);

        }



        $match = $result['FaceMatches'][0];

        $similarity = (float) ($match['Similarity'] ?? 0);

        if ($similarity < $this->awsConfig->rekognitionMinSimilarityPercent) {

            return json_response(['success' => false, 'msg' => 'Low confidence match. Move closer to the camera.']);

        }



        $empId = (int) ($match['Face']['ExternalImageId'] ?? 0);

        if ($empId < 1) {

            return json_response(['success' => false, 'msg' => 'Invalid match data']);

        }



        $employee = $this->db->table('users')

            ->select('id, first_name, last_name')

            ->where('id', $empId)

            ->where('campus_id', $campusId)

            ->where('status', 1)

            ->get()

            ->getRow();



        if (!$employee) {

            return json_response(['success' => false, 'msg' => 'Employee not found for this campus.']);

        }



        $displayName = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''));

        $today = date('Y-m-d');

        $day = date('l', strtotime($today));

        $nowTime = date('H:i');

        $nowDatetime = date('Y-m-d H:i:s');



        $timing = $this->db->table('emp_timings')

            ->where('user_id', $empId)

            ->where('dayname', $day)

            ->get()

            ->getRow();



        if (!$timing) {

            return json_response([

                'success' => false,

                'msg' => 'No work timing set for ' . $displayName . ' on ' . $day . '. Configure employee timing first.',

            ]);

        }



        $scheduledIn = $this->normalizeTime($timing->checkin ?? '');

        $scheduledOut = $this->normalizeTime($timing->checkout ?? '');



        $attendance = $this->db->table('attendance_employee')

            ->where('emp_id', $empId)

            ->where('date', $today)

            ->get()

            ->getRow();



        if (!$attendance) {

            $status = 'P';

            $lcDuration = 0;



            if ($scheduledIn !== '' && $nowTime > $scheduledIn) {

                $status = 'LC';

                $lcDuration = max(0, (int) round((strtotime($nowTime) - strtotime($scheduledIn)) / 60));

            }



            $this->db->table('attendance_employee')->insert([

                'emp_id' => $empId,

                'date' => $today,

                'checkin' => $nowTime,

                'checkout' => '',

                'status' => $status,

                'lc_duration' => $lcDuration,

                'el_duration' => 0,

                'check_in_method' => 'face',

                'created_date' => $nowDatetime,

                'user_id' => $this->session->get('member_userid'),

            ]);



            $statusLabel = $status === 'LC' ? 'Late check-in' : 'Check-in';



            return json_response([

                'success' => true,

                'msg' => $statusLabel . ': ' . $displayName . ' at ' . date('h:i A', strtotime($nowTime)),

                'action' => 'checkin',

                'emp_id' => $empId,

                'employee_name' => $displayName,

                'status' => $status,

                'similarity' => round($similarity, 2),

            ]);

        }



        if (!empty($attendance->checkout)) {

            $inLabel = $this->formatTimeForDisplay($attendance->checkin ?? '');

            $outLabel = $this->formatTimeForDisplay($attendance->checkout ?? '');



            return json_response([

                'success' => false,

                'msg' => 'Already checked in and out today: ' . $displayName

                    . ' (' . $inLabel . ' – ' . $outLabel . ')',

            ]);

        }



        $status = (string) ($attendance->status ?? 'P');

        $elDuration = (int) ($attendance->el_duration ?? 0);



        if ($scheduledOut !== '' && $nowTime < $scheduledOut) {

            $status = 'EL';

            $elDuration = max(0, (int) round((strtotime($scheduledOut) - strtotime($nowTime)) / 60));

        }



        $this->db->table('attendance_employee')

            ->where('attendance_id', $attendance->attendance_id)

            ->update([

                'checkout' => $nowTime,

                'status' => $status,

                'el_duration' => $elDuration,

                'check_out_method' => 'face',

                'updated_date' => $nowDatetime,

            ]);



        $statusLabel = $status === 'EL' ? 'Early check-out' : 'Check-out';



        return json_response([

            'success' => true,

            'msg' => $statusLabel . ': ' . $displayName . ' at ' . date('h:i A', strtotime($nowTime)),

            'action' => 'checkout',

            'emp_id' => $empId,

            'employee_name' => $displayName,

            'status' => $status,

            'similarity' => round($similarity, 2),

        ]);

    }



    public function data()
    {
        $campusId = (int) $this->session->get('member_campusid');

        if (! $this->db->tableExists('employee_faces')) {
            log_message('error', 'employee_faces table missing — run: php spark migrate');

            return $this->response->setJSON([
                'data'  => [],
                'error' => 'Face enrollment database is not set up. Please run database migrations (employee_faces table).',
            ]);
        }

        try {
            $rows = $this->db->table('employee_faces ef')
                ->select('ef.emp_id, ef.face_id, ef.image_path, u.first_name, u.last_name')
                ->join('users u', 'u.id = ef.emp_id', 'left')
                ->where('ef.campus_id', $campusId)
                ->orderBy('ef.emp_id', 'ASC')
                ->get()
                ->getResult();
        } catch (\Throwable $e) {
            log_message('error', 'EmployeeFaceAttendance::data: ' . $e->getMessage());

            return $this->response->setJSON([
                'data'  => [],
                'error' => 'Could not load enrolled faces. Check server logs.',
            ]);
        }

        $data = [];
        $i = 1;

        foreach ($rows as $row) {
            $name = trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? ''));
            $data[] = [
                'sno'           => $i++,
                'emp_id'        => $row->emp_id,
                'employee_name' => $name !== '' ? $name : ('ID ' . $row->emp_id),
                'image'         => ! empty($row->image_path) ? base_url($row->image_path) : '',
                'face_id'       => $row->face_id,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }



    public function delete()

    {

        $faceId = $this->request->getPost('face_id');

        $campusId = (int) $this->session->get('member_campusid');



        if (!$faceId) {

            return json_response(['success' => false, 'msg' => 'Face ID required']);

        }



        try {

            $this->rekognition->deleteFace($faceId, $campusId, 'employee');

        } catch (AwsException $e) {

            log_message('error', 'Rekognition employee deleteFace: ' . $e->getAwsErrorMessage());



            return json_response(['success' => false, 'msg' => 'Could not remove face from AWS.']);

        }



        $this->db->table('employee_faces')->where('face_id', $faceId)->delete();



        return json_response(['success' => true, 'msg' => 'Deleted']);

    }



    private function normalizeTime(?string $time): string

    {

        if ($time === null || $time === '') {

            return '';

        }



        $time = trim($time);

        if (preg_match('/^\d{1,2}:\d{2}$/', $time)) {

            return $time;

        }



        $ts = strtotime($time);



        return $ts ? date('H:i', $ts) : $time;

    }



    private function formatTimeForDisplay(?string $time): string

    {

        $normalized = $this->normalizeTime($time);

        if ($normalized === '') {

            return '—';

        }



        $ts = strtotime($normalized);



        return $ts ? date('h:i A', $ts) : $normalized;

    }

}
