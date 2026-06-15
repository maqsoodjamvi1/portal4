<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\StudentsModel;
use CodeIgniter\HTTP\ResponseInterface;

class Students_bulk_contacts extends BaseController
{
    protected $db;
    protected $session;
    protected $students;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url']);
        check_permission('admin-students');

        $this->students = new StudentsModel(); // update to your actual model if needed
    }

    public function index()
    {
        $campus_id = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $schoolinfo = getSchoolInfo();
        $currentrole = currentUserRoles();

        $sectionsclassinfo = in_array(5, $currentrole) ? teacherSubjectSections() : $this->userClassSections();

        $campus_info = $this->db->table('campus')->where('campus_id', $campus_id)->get()->getRow();

        return view('admin/students_bulk_contacts', [
            'sectionsclassinfo' => $sectionsclassinfo,
            'campus_info'       => $campus_info,
        ]);
    }


protected function userClassSections()
{
    $db = \Config\Database::connect();
    $campus_id = $this->session->get('member_campusid');

    return $db->table('class_section cs')
        ->select('cs.cls_sec_id, cs.section_id, CONCAT(c.class_name, " (", s.section_name, ")") as sectionclassname')
        ->join('classes c', 'c.class_id = cs.class_id')
        ->join('sections s', 's.section_id = cs.section_id')
        ->where('cs.status', 1)
        ->where('cs.campus_id', $campus_id)
        ->get()
        ->getResultArray(); // Must return array, not stdClass
}


public function data()
{
    $campusid  = (int) session('member_campusid');
    $sessionid = (int) session('member_sessionid');
    $clsSecId  = trim((string) $this->request->getPost('cls_sec_id')); // '' | 'all' | number

    $builder = $this->db->table('student_class sc')
        ->select('sc.student_id, sc.cls_sec_id')
        ->join('students s', 's.student_id = sc.student_id', 'inner')
        ->where('s.campus_id', $campusid)
        ->where('s.status', 1)
        ->where('sc.session_id', $sessionid);

    if ($clsSecId !== '' && strtolower($clsSecId) !== 'all' && ctype_digit($clsSecId)) {
        $builder->where('sc.cls_sec_id', (int) $clsSecId);
    }

    $student_class = $builder->orderBy('sc.cls_sec_id', 'ASC')->get()->getResult();

   $rows = '';
$sn = 1;

foreach ($student_class as $row) {
    $stu = $this->db->table('students')
        ->where([
            'student_id' => $row->student_id,
            'campus_id'  => $campusid,
            'status'     => 1
        ])->get()->getRow();

    if (!$stu) continue;

    $parent = $this->db->table('parents')->where('parent_id', $stu->parent_id)->get()->getRow();

    $f_name            = $parent->f_name            ?? '';
    $father_contact    = $parent->father_contact    ?? '';
    $mother_contact    = $parent->mother_contact    ?? '';
    $whatsapp_contact  = $parent->whatsapp          ?? '';
    $emergency_contact = $parent->emergency_contact ?? '';

    $father = esc($father_contact);
    $mother = esc($mother_contact);
    $wa     = esc($whatsapp_contact);
    $emerg  = esc($emergency_contact);

    $rows .=
    '<tr>'.
      // S.No (not student id)
      '<th>'.$sn.'<input type="hidden" class="parent-id" value="'.(int)$stu->parent_id.'"></th>'.

      // Name
      '<td>'.esc(trim($stu->first_name.' '.$stu->last_name).' c/o '.$f_name).'</td>'.

      // Parents (Father + Mother) — 2 rows in one cell, each row: icon + input (inline)
      '<td class="p-1 contact-cell">'.
        '<div class="contacts-grid">'.
          // Father
          '<div class="ico" data-bs-toggle="tooltip" title="Father contact"><i class="fas fa-user-tie"></i></div>'.
          '<input type="text" class="form-control form-control-sm father-contact" placeholder="Father" value="'.$father.'">'.

          // Mother
          '<div class="ico" data-bs-toggle="tooltip" title="Mother contact"><i class="fas fa-female"></i></div>'.
          '<input type="text" class="form-control form-control-sm mother-contact" placeholder="Mother" value="'.$mother.'">'.
        '</div>'.
      '</td>'.

      // Other (WhatsApp + Emergency)
      '<td class="p-1 contact-cell">'.
        '<div class="contacts-grid">'.
          // WhatsApp
          '<div class="ico" data-bs-toggle="tooltip" title="WhatsApp number"><i class="fab fa-whatsapp text-success"></i></div>'.
          '<input type="text" class="form-control form-control-sm whatsapp-contact" placeholder="WhatsApp" value="'.$wa.'">'.

          // Emergency
          '<div class="ico" data-bs-toggle="tooltip" title="Emergency contact"><i class="fas fa-phone-alt text-danger"></i></div>'.
          '<input type="text" class="form-control form-control-sm emergency-contact" placeholder="Emergency" value="'.$emerg.'">'.
        '</div>'.
      '</td>'.

      // Action
      '<td class="text-nowrap">'.
        '<button type="button" class="btn btn-primary btn-sm save-contacts" '.
        'data-parent-id="'.(int)$stu->parent_id.'" data-student-id="'.(int)$stu->student_id.'">Save</button>'.
      '</td>'.
    '</tr>';

    $sn++;
}

// Table + CSS
$html = '
<style>
  .contacts-table thead th { white-space: nowrap; }
  .contacts-table .contact-cell { min-width: 320px; }

  /* 2-column grid: [icon | input] repeated on two rows */
  .contacts-grid {
    display: grid;
    grid-template-columns: 28px 1fr;
    grid-auto-rows: minmax(28px, auto);
    grid-row-gap: 6px;
    align-items: center;
  }
  .contacts-grid .ico {
    text-align: center;
    width: 28px;
    line-height: 1;
    white-space: nowrap;
  }
  .contacts-grid input.form-control {
    width: 100%;
    min-width: 160px;
    padding: .25rem .5rem;
    font-size: .85rem;
    height: calc(1.5em + .5rem + 2px);
  }

  @media (max-width: 1400px) {
    .contacts-table .contact-cell { min-width: 280px; }
    .contacts-grid input.form-control { min-width: 140px; }
  }
  @media (max-width: 1200px) {
    .contacts-table .contact-cell { min-width: 240px; }
    .contacts-grid input.form-control { min-width: 120px; }
  }
</style>

<div class="table-responsive">
  <table class="table table-striped table-bordered table-hover contacts-table" style="font-size:10px;width:100%;">
    <thead>
      <tr>
        <th style="width:70px;">S.No</th>
        <th>Name</th>
        <th style="min-width:260px;">Parents</th>
        <th style="min-width:260px;">Other Contacts</th>
        <th style="width:80px;">Action</th>
      </tr>
    </thead>
    <tbody>'.$rows.'</tbody>
  </table>
</div>';

return $this->response->setBody($html);
}

    public function saveStudentContacts()
    {
        $user_id = $this->session->get('member_userid');
        $campusid = $this->session->get('member_campusid');
        $date = date('Y-m-d H:i:s');

        $parent_id = $this->request->getPost('parent_id');
        $father_contact = $this->request->getPost('father_contact');
        $mother_contact = $this->request->getPost('mother_contact');
        $whatsapp_contact = $this->request->getPost('whatsapp_contact');
        $emergency_contact = $this->request->getPost('emergency_contact');

        $data = [
            'father_contact' => trim($father_contact),
            'mother_contact' => trim($mother_contact),
            'whatsapp' => trim($whatsapp_contact),
            'emergency_contact' => trim($emergency_contact),
            'updated_date' => $date,
            'user_id' => $user_id
        ];

        $this->db->table('parents')->where('parent_id', $parent_id)->update($data);

        return $this->response->setJSON([
            'success' => true,
            'msg' => 'Edit Student Success'
        ]);
    }
}
