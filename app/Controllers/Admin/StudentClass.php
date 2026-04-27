<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use Config\Services;
use stdClass;

class StudentClass extends BaseController
{
    use ResponseTrait;

    protected $db;
    protected $session;
    protected $request;

    public function __construct()
    {
        helper(['form', 'url']);
        $this->db = db_connect();
        $this->session = Services::session();
        $this->request = Services::request();

        
    }

    public function index()
    {
        check_permission('admin-student-class');
        return view('admin/student_class', []);
    }

    public function data()
    {
        $campus_id = (int) $this->session->get('member_campusid');
        $draw = $this->request->getPost('draw');
        $start = $this->request->getPost('start');
        $length = $this->request->getPost('length');
        $keyword = $this->request->getPost('search')['value'] ?? '';

        $builder = $this->db->table('student_class A');
        $builder->select('COUNT(A.sc_id) as ccount');
        $builder->join('class_section CS', 'A.cls_sec_id = CS.cls_sec_id');
        $builder->where('CS.campus_id', $campus_id);
        $total = $builder->get()->getRow()->ccount;

        $builder = $this->db->table('student_class A');
        $builder->select('A.*');
        $builder->join('class_section CS', 'A.cls_sec_id = CS.cls_sec_id');
        $builder->where('CS.campus_id', $campus_id);
        $builder->orderBy('A.session_id', 'desc');
        $builder->limit($length, $start);
        $results = $builder->get()->getResult();

        $data = [];
        foreach ($results as $row) {
            $student = $this->db->table('students')->getWhere(['student_id' => $row->student_id])->getRow();
            if (!$student) continue;

            $classSec = $this->db->table('class_section')->getWhere(['cls_sec_id' => $row->cls_sec_id])->getRow();
            $class = $this->db->table('classes')->getWhere(['class_id' => $classSec->class_id ?? 0])->getRow();
            $sectionName = getClassSection($row->cls_sec_id)['sectionclassname'] ?? '';

            $feeType = $this->db->table('fee_type')->where('is_monthly_fee', 1)->get()->getRow();
            $monthlyFee = 0;
            if ($feeType) {
                $fee = $this->db->table('fee_amount')
                    ->where([
                        'fee_type_id' => $feeType->fee_type_id,
                        'session_id' => $row->session_id,
                        'class_id' => $classSec->class_id ?? 0,
                        'campus_id' => $campus_id,
                    ])
                    ->get()
                    ->getRow();
                $monthlyFee = $fee ? $fee->amount - $student->discounted_amount : 0;
            }

            $session = $this->db->table('academic_session')->getWhere(['session_id' => $row->session_id])->getRow();

            $imgPath = FCPATH . 'uploads/' . $student->profile_photo;
            $profilePhoto = ($student->profile_photo && file_exists($imgPath)) ?
                "<img style='width:50px;height:50px;border-radius:30px;display:block;margin:0 auto;' src='" . base_url("uploads/" . $student->profile_photo) . "' >"
                : "<i style='font-size:40px;text-align:center;display:block;' class='fa fa-user'></i>";

            $data[] = [
                'id' => $row->sc_id,
                'student' => $student->first_name . ' ' . $student->last_name,
                'reg_no' => $student->reg_no,
                'section' => $sectionName,
                'class' => $class->class_name ?? '',
                'session' => $session->session_name ?? '',
                'monthly_fee' => $monthlyFee,
                'profile_photo' => $profilePhoto,
            ];
        }

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data,
        ]);
    }

     public function add()
    {
        check_permission('admin-add-student-class');

        $data['infostudents'] = $this->db->table('students')->get()->getResult();
        $data['sectionsclassinfo'] = userClassSections();

        $schoolinfo = getSchoolInfo();
        $data['academic_sessioninfo'] = $this->db->table('academic_session')
            ->where('system_id', $schoolinfo->system_id)
            ->get()->getResult();

        $data['sectionsinfo'] = $this->db->table('sections')->get()->getResult();

        return view('admin/student_class_edit', $data);
    }

    public function edit()
    {
        check_permission('admin-edit-student-class');
        $id = (int) $this->request->getGet('id');

        $data['info'] = $this->db->table('student_class')->where('sc_id', $id)->get()->getRow();
        $data['infostudents'] = $this->db->table('students')->get()->getResult();
        $data['classesinfo'] = $this->db->table('classes')->get()->getResult();
        $data['sectionsinfo'] = $this->db->table('sections')->get()->getResult();
        $data['academic_sessioninfo'] = $this->db->table('academic_session')->get()->getResult();

        return view('admin/student_class_edit', $data);
    }

    public function save()
    {
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d H:i:s');
        $id = (int) $this->request->getPost('id');
        $campus_id = $this->request->getPost('campus_id');
        $student_id = $this->request->getPost('student_id');
        $passed = $this->request->getPost('passed');
        $section_id = $this->request->getPost('section_id');

        if ($id === 0) {
            check_permission('admin-add-student-class');
            $this->db->transBegin();

            foreach ($student_id as $key => $studentID) {
                $passedStd = isset($passed[$key]) ? $passed[$key] : 0;
                $sectionID = $section_id;

                if ($passedStd == $studentID) {
                    $this->db->table('student_class')
                        ->where('student_id', $studentID)
                        ->where('cls_sec_id', $this->request->getPost('current_class_id'))
                        ->where('session_id', $this->request->getPost('session_id'))
                        ->update([
                            'status' => 0,
                            'updated_date' => $date,
                            'user_id' => $user_id
                        ]);

                    $this->db->table('student_class')->insert([
                        'student_id' => $studentID,
                        'cls_sec_id' => $this->request->getPost('next_class_id'),
                        'session_id' => $this->request->getPost('next_session_id'),
                        'status' => 1,
                        'created_date' => $date,
                        'user_id' => $user_id
                    ]);
                } else {
                    $this->db->table('student_class')->insert([
                        'student_id' => $studentID,
                        'cls_sec_id' => $this->request->getPost('current_class_id'),
                        'section_id' => $sectionID,
                        'session_id' => $this->request->getPost('next_session_id'),
                        'status' => 1,
                        'created_date' => $date,
                        'user_id' => $user_id
                    ]);
                }
            }

            $this->db->transComplete();
            return $this->response->setJSON(['success' => true, 'msg' => 'Add Student Class Success']);
        }
    }

   public function fetchStudents()
{
    try {
        check_permission('admin-student-class');

        $campus_id = (int) $this->session->get('member_campusid');

        // Accept multiple param names (new + old) so the view and any legacy code both work
        $from_session_id = (int) (
            $this->request->getVar('from_session_id')
            ?? $this->request->getVar('running_session')
            ?? $this->request->getVar('session_id')
        );

        $from_cls_sec_id = (int) (
            $this->request->getVar('from_cls_sec_id')
            ?? $this->request->getVar('running_class')
            ?? $this->request->getVar('current_class_id')
        );

        $to_session_id = (int) (
            $this->request->getVar('to_session_id')
            ?? $this->request->getVar('new_session')
            ?? $this->request->getVar('next_session_id')
        );

        $to_cls_sec_id = (int) (
            $this->request->getVar('to_cls_sec_id')
            ?? $this->request->getVar('new_class')
            ?? $this->request->getVar('next_class_id')
        );

        if (!$from_session_id || !$from_cls_sec_id || !$to_session_id || !$to_cls_sec_id) {
            return $this->failValidationErrors('Missing required parameters.');
        }

        // LEFT (students in running/current class-session)
        $fromRows = $this->db->table('student_class sc')
            ->select('sc.student_id, s.reg_no, s.first_name, s.last_name, s.profile_photo')
            ->join('students s', 's.student_id = sc.student_id')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
            ->where([
                'sc.session_id' => $from_session_id,
                'sc.cls_sec_id' => $from_cls_sec_id,
                'sc.status'     => 1,
                'cs.campus_id'  => $campus_id,
            ])
            ->orderBy('s.first_name', 'ASC')
            ->get()->getResult();

        // RIGHT (already in new/target class-session)
        $toRows = $this->db->table('student_class sc')
            ->select('sc.student_id, s.reg_no, s.first_name, s.last_name, s.profile_photo')
            ->join('students s', 's.student_id = sc.student_id')
            ->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id')
            ->where([
                'sc.session_id' => $to_session_id,
                'sc.cls_sec_id' => $to_cls_sec_id,
                'sc.status'     => 1,
                'cs.campus_id'  => $campus_id,
            ])
            ->orderBy('s.first_name', 'ASC')
            ->get()->getResult();

        $map = function ($r) {
            $photo = '';
            if (!empty($r->profile_photo)) {
                $path = FCPATH . 'uploads/' . $r->profile_photo;
                $photo = is_file($path) ? base_url('uploads/' . $r->profile_photo) : '';
            }
            return [
                'id'      => (int) $r->student_id, // NOTE: view expects "id"
                'reg_no'  => (string) $r->reg_no,
                'name'    => trim(($r->first_name ?? '') . ' ' . ($r->last_name ?? '')),
                'photo'   => $photo,
            ];
        };

        // Return keys that your current view code uses: "students" (left) + "promoted" (right)
        return $this->respond([
            'success'  => true,
            'students' => array_map($map, $fromRows),
            'promoted' => array_map($map, $toRows),
        ]);

    } catch (\Throwable $e) {
        return $this->failServerError('Failed to fetch students.');
    }
}

    /**
     * POST admin/student_class/move
     * Body:
     *  - student_id
     *  - from_session_id, from_cls_sec_id
     *  - to_session_id,   to_cls_sec_id
     */
    public function move()
    {
        try {
            check_permission('admin-student-class');

            $student_id      = (int) $this->request->getPost('student_id');
            $from_session_id = (int) $this->request->getPost('from_session_id');
            $from_cls_sec_id = (int) $this->request->getPost('from_cls_sec_id');
            $to_session_id   = (int) $this->request->getPost('to_session_id');
            $to_cls_sec_id   = (int) $this->request->getPost('to_cls_sec_id');

            if (!$student_id || !$from_session_id || !$from_cls_sec_id || !$to_session_id || !$to_cls_sec_id) {
                return $this->failValidationErrors('Missing required parameters.');
            }

            $result = $this->promoteOne($student_id, $from_session_id, $from_cls_sec_id, $to_session_id, $to_cls_sec_id);
            if ($result['success']) {
                return $this->respond($result);
            }
            return $this->fail($result['message'] ?? 'Unable to move student.');
        } catch (Exception $e) {
            return $this->failServerError('Unexpected error during move.');
        }
    }

    /**
     * POST admin/student_class/move-bulk
     * Body:
     *  - student_ids[] (array of ints)
     *  - from_session_id, from_cls_sec_id
     *  - to_session_id,   to_cls_sec_id
     */
    public function moveBulk()
    {
        try {
            check_permission('admin-student-class');

            $student_ids     = (array) $this->request->getPost('student_ids');
            $from_session_id = (int) $this->request->getPost('from_session_id');
            $from_cls_sec_id = (int) $this->request->getPost('from_cls_sec_id');
            $to_session_id   = (int) $this->request->getPost('to_session_id');
            $to_cls_sec_id   = (int) $this->request->getPost('to_cls_sec_id');

            if (empty($student_ids) || !$from_session_id || !$from_cls_sec_id || !$to_session_id || !$to_cls_sec_id) {
                return $this->failValidationErrors('Missing required parameters.');
            }

            $ok = 0; $fail = 0; $messages = [];
            foreach ($student_ids as $sid) {
                $sid = (int) $sid;
                if (!$sid) continue;
                $res = $this->promoteOne($sid, $from_session_id, $from_cls_sec_id, $to_session_id, $to_cls_sec_id);
                if ($res['success']) { $ok++; } else { $fail++; $messages[] = "ID {$sid}: " . ($res['message'] ?? 'fail'); }
            }

            return $this->respond([
                'success'  => true,
                'moved'    => $ok,
                'skipped'  => $fail,
                'messages' => $messages,
            ]);
        } catch (Exception $e) {
            return $this->failServerError('Unexpected error during bulk move.');
        }
    }

    /* =========================
     * Internals
     * ========================= */

    /**
     * Promote / move one student atomically.
     * - Deactivate FROM (if exists/active)
     * - Ensure TO active row exists (create or activate)
     * - Campus-safety check through class_section join
     */
    protected function promoteOne(int $student_id, int $from_session_id, int $from_cls_sec_id, int $to_session_id, int $to_cls_sec_id): array
    {
        $campus_id = (int) $this->session->get('member_campusid');
        $user_id   = (int) $this->session->get('member_userid');
        $now       = date('Y-m-d H:i:s');

        // Basic guardrails: ensure both class_sections belong to this campus
        $fromOk = $this->db->table('class_section')->where(['cls_sec_id' => $from_cls_sec_id, 'campus_id' => $campus_id])->countAllResults();
        $toOk   = $this->db->table('class_section')->where(['cls_sec_id' => $to_cls_sec_id,   'campus_id' => $campus_id])->countAllResults();
        if (!$fromOk || !$toOk) {
            return ['success' => false, 'message' => 'Invalid class/section for this campus.'];
        }

        $this->db->transBegin();

        try {
            // Is student currently active in FROM?
            $fromRow = $this->db->table('student_class')
                ->where([
                    'student_id' => $student_id,
                    'session_id' => $from_session_id,
                    'cls_sec_id' => $from_cls_sec_id,
                    'status'     => 1,
                ])->get()->getRow();

            // Already active in TO?
            $toRowActive = $this->db->table('student_class')
                ->where([
                    'student_id' => $student_id,
                    'session_id' => $to_session_id,
                    'cls_sec_id' => $to_cls_sec_id,
                    'status'     => 1,
                ])->get()->getRow();

            if ($toRowActive) {
                // Nothing to do—already there.
                $this->db->transCommit();
                return ['success' => true, 'message' => 'Student already in target class/session.'];
            }

            // Deactivate FROM (if active)
            if ($fromRow) {
                $this->db->table('student_class')
                    ->where('sc_id', $fromRow->sc_id)
                    ->update([
                        'status'       => 0,
                        'updated_date' => $now,
                        'user_id'      => $user_id,
                    ]);
            }

            // If a TO row exists but inactive, activate it; else insert a new one.
            $toRowAny = $this->db->table('student_class')
                ->where([
                    'student_id' => $student_id,
                    'session_id' => $to_session_id,
                    'cls_sec_id' => $to_cls_sec_id,
                ])->get()->getRow();

            if ($toRowAny) {
                $this->db->table('student_class')
                    ->where('sc_id', $toRowAny->sc_id)
                    ->update([
                        'status'       => 1,
                        'updated_date' => $now,
                        'user_id'      => $user_id,
                    ]);
            } else {
                $this->db->table('student_class')->insert([
                    'student_id'   => $student_id,
                    'session_id'   => $to_session_id,
                    'cls_sec_id'   => $to_cls_sec_id,
                    'status'       => 1,
                    'created_date' => $now,
                    'user_id'      => $user_id,
                ]);
            }

            $this->db->transCommit();
            return ['success' => true, 'message' => 'Student moved.'];
        } catch (Exception $e) {
            $this->db->transRollback();
            return ['success' => false, 'message' => 'Transaction failed.'];
        }
    }
}
