<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class StudentIdCard extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        helper(['form', 'url', 'text']);
        $this->db = \Config\Database::connect();
        $this->session = session();
        check_permission('admin-student-id-cards');
    }

    public function index()
    {
        $campus_id = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        $schoolinfo = getSchoolInfo();

        $currentrole = currentUserRoles();

        if (in_array(5, $currentrole)) {
            $sectionsclassinfo = teacherSubjectSections();
        } else {
            $sectionsclassinfo = userClassSections();
        }

        $data = [
            'sectionsclassinfo' => $sectionsclassinfo,
        ];

        return view('admin/student_id_card', $data);
    }

    public function data_vertical()
    {
        $cls_sec_id = $this->request->getPost('cls_sec_id');
        $statusFilter = $this->request->getPost('status');
        $schoolinfo = getSchoolInfo();
        $campus_id = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');

        // Get current academic session dates
        $sessionDates = $this->db->table('academic_session')
            ->select('start_date, end_date')
            ->where('session_id', $sessionid)
            ->get()
            ->getRow();

        $builder = $this->db->table('student_class sc');
        $builder->select('sc.*, s.first_name, s.last_name, s.reg_no, s.date_of_birth, 
                          s.profile_photo, s.parent_id, s.date_of_admission, s.status,
                          p.f_name, p.father_contact, p.mother_contact, p.emergency_contact, p.address_line1,
                          cs.class_id, cs.section_id, c.class_name, sec.section_name');
        $builder->join('students s', 's.student_id = sc.student_id');
        $builder->join('parents p', 'p.parent_id = s.parent_id', 'left');
        $builder->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id');
        $builder->join('classes c', 'c.class_id = cs.class_id');
        $builder->join('sections sec', 'sec.section_id = cs.section_id');
        $builder->where('s.campus_id', $campus_id);
        $builder->where('sc.session_id', $sessionid);

        // Apply class filter
        if ($cls_sec_id) {
            $builder->where('sc.cls_sec_id', $cls_sec_id);
        }

        // Apply status filter
        switch ($statusFilter) {
            case 'active':
                $builder->where('s.status', 1);
                break;
            case 'new':
                $builder->where('s.status', 1);
                if ($sessionDates) {
                    $builder->where('s.date_of_admission >=', $sessionDates->start_date);
                    $builder->where('s.date_of_admission <=', $sessionDates->end_date);
                }
                break;
            case 'all':
                // No additional filter
                break;
            default:
                $builder->where('s.status', 1);
        }

        $builder->orderBy('sc.cls_sec_id', 'asc');
        $student_data = $builder->get()->getResult();

        $campus_info = $this->db->table('campus')
            ->select('campus_name, landline')
            ->where('campus_id', $campus_id)
            ->get()
            ->getRow();

        $strResultCard = '
<style>
/* Card container */
.id-card {
  display: flex;
  flex-direction: column;   /* header | content | footer */
  overflow: hidden;
}

/* Header */
.id-card .card-header.school {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 10px;
}

/* Content: photo left, details right */
.id-card .card-content {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 8px 10px;
  flex: 1 1 auto;           /* grows but does not push footer */
  min-height: 110px;
}

/* Photo fixed size */
.photo-container { flex: 0 0 auto; }
.student-photo,
.photo-placeholder {
  width: 80px;
  height: 100px;
  object-fit: cover;
  border-radius: 6px;
  border: 1px solid #e8ecf3;
  background: #f9fbff;
}
.photo-placeholder {
  display: flex; align-items: center; justify-content: center;
}
.photo-placeholder i { font-size: 22px; color: #a0aec0; }

/* Details: compact 6 rows */
.details-container {
  display: grid;
  grid-auto-rows: minmax(16px, auto);  /* ↓ row height */
  row-gap: 2px;                        /* ↓ space between rows */
  width: 100%;
  align-content: start;
}

/* ID row */
.student-id {
  font-size: 11px;
  color: #4a5568;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

/* Row layout: icon + text */
.detail-row {
  display: grid;
  grid-template-columns: 16px 1fr; /* compact icon/text ratio */
  align-items: center;
  column-gap: 5px;
  min-height: 16px;
  line-height: 1.1;
}

/* Icons smaller */
.icon-badge {
  width: 16px; height: 16px;
  border-radius: 4px;
  background: #edf2f7;
  color: #2b6cb0;
  font-size: 10px;
  display: flex; align-items: center; justify-content: center;
}

/* Text compact */
.detail-value {
  font-size: 11.5px;
  color: #2d3748;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

/* Allow name + father to wrap to 2 lines if long */
.detail-value.name,
.detail-value.father {
  white-space: normal;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Footer always pinned */
.id-card .card-footer {
  flex: 0 0 auto;
  text-align: center;
  font-size: 11px;
  padding: 4px 6px;
  border-top: 1px solid #e8ecf3;
  background: #f9fafb;
}

.smart-btn {
  font-size: 13px;       /* smaller text */
  padding: 4px 8px;      /* tighter button height */
  border-radius: 4px;    /* more compact rounded corners */
}
.smart-btn i { font-size: 12px; } /* smaller icon */
</style>
<div class="id-card-container clearfix">';
        $i = 1;

        foreach ($student_data as $student) {
            // Determine student status
            $status = 'Active';
            $statusClass = 'badge-success';
            if ($student->status != 1) {
                $status = 'Inactive';
                $statusClass = 'badge-danger';
            } elseif ($sessionDates && 
                     $student->date_of_admission >= $sessionDates->start_date && 
                     $student->date_of_admission <= $sessionDates->end_date) {
                $status = 'New';
                $statusClass = 'badge-info';
            }
            
            // Calculate font sizes based on name lengths
           // Calculate font sizes
            $studentName = trim($student->first_name . ' ' . $student->last_name);
            $studentNameClass = $this->getFontSizeClass(strlen($studentName));
            
            $fatherName = trim($student->f_name);
            $fatherNameClass = $this->getFontSizeClass(strlen($fatherName));
            
            // Contact number with reduced font size
            $contactNumber = $student->father_contact ?? '';
            

            $profile_photo = !empty($student->profile_photo)
                ? '<img class="student-photo" src="' . base_url('uploads/' . $student->profile_photo) . '">'
                : '<div class="photo-placeholder"><i class="fas fa-user"></i></div>';

            $date_of_birth = isset($student->date_of_birth) ? date_create($student->date_of_birth) : null;
            $date_of_birthFormated = $date_of_birth ? date_format($date_of_birth, "d M Y") : '';
            $class_section = ($student->class_name ?? '') . ' - ' . ($student->section_name ?? '');

             $strResultCard .= '<div class="id-card">
                
                
                <div class="card-header school">
                    <div class="school-logo">
                        <img src="' . base_url('system-logo/' . $schoolinfo->logo) . '" alt="School Logo">
                    </div>
                    ' . $schoolinfo->system_name . '
                </div>
                
                <div class="card-content">
                    <div class="photo-container">
                        ' . $profile_photo . '
                    </div>
                    
                    <div class="details-container">
                        <div class="student-id">
                            ID: ' . ($student->reg_no ?? '') . '
                        </div>
                        
                        <div class="detail-row">
                            <div class="icon-badge" title="Student Name">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="detail-value ' . $studentNameClass . '">' 
                                . $studentName . 
                            '</div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="icon-badge" title="Father\'s Name">
                                <i class="fas fa-user-friends"></i>
                            </div>
                            <div class="detail-value ' . $fatherNameClass . '">' . $fatherName . '</div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="icon-badge contact-badge" title="Contact Number">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="detail-value font-size-13">' . $contactNumber . '</div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="icon-badge class-badge" title="Class & Section">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="detail-value">' . $class_section . '</div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="icon-badge dob-badge" title="Date of Birth">
                                <i class="fas fa-birthday-cake"></i>
                            </div>
                            <div class="detail-value">' . $date_of_birthFormated . '</div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    ' . ($campus_info->campus_name ?? '') . ' | ' . ($campus_info->landline ?? '') . '
                </div>
            </div>';
        }
        $strResultCard .= '</div>';

        return $this->response->setContentType('text/html')->setBody($strResultCard);
    }
    
    /**
     * Get font size class based on name length
     */
    private function getFontSizeClass($length)
    {
        if ($length > 21) {
            return 'font-size-11';
        } elseif ($length > 20) {
            return 'font-size-13';
        } elseif ($length > 15) {
            return 'font-size-13';
        } else {
            return 'font-size-13';
        }
    }
}


    // public function data()
    // {
    //     $cls_sec_id = $this->request->getPost('cls_sec_id');
    //     $schoolinfo = getSchoolInfo();
    //     $campus_id = $this->session->get('member_campusid');
    //     $sessionid = $this->session->get('member_sessionid');

    //     // Main query
    //     if ($cls_sec_id) {
    //         $student_class = $this->db->query(
    //             'SELECT * FROM student_class WHERE student_id IN(SELECT student_id FROM students WHERE status=1 AND campus_id=?) AND session_id = ? AND cls_sec_id = ? ORDER BY cls_sec_id ASC',
    //             [$campus_id, $sessionid, $cls_sec_id]
    //         )->getResult();
    //     } else {
    //         $student_class = $this->db->query(
    //             'SELECT * FROM student_class WHERE student_id IN(SELECT student_id FROM students WHERE status=1 AND campus_id=?) AND session_id = ? ORDER BY cls_sec_id ASC',
    //             [$campus_id, $sessionid]
    //         )->getResult();
    //     }

    //     $strResultCard = '<div class="row"><page>';
    //     $i = 1;

    //     foreach ($student_class as $studentinfo) {
    //         $student_info = $this->db->table('students')
    //             ->where('student_id', $studentinfo->student_id)
    //             ->get()
    //             ->getRow();

    //         $campus_info = $this->db->table('campus')
    //             ->where('campus_id', $campus_id)
    //             ->get()
    //             ->getRow();

    //         if ($student_info) {
    //             $parent_info = $this->db->table('parents')
    //                 ->where('parent_id', $student_info->parent_id)
    //                 ->get()
    //                 ->getRow();
    //             $f_name = $father_contact = $mother_contact = $emergency_contact = $address = '';
    //             if ($parent_info) {
    //                 $f_name = $parent_info->f_name;
    //                 $father_contact = $parent_info->father_contact;
    //                 $address = $parent_info->address_line1;
    //                 $mother_contact = $parent_info->mother_contact;
    //                 $emergency_contact = $parent_info->emergency_contact;
    //             }

    //             $class_info = getClassSection($studentinfo->cls_sec_id);
    //         }

    //         $strResultCard .= '<div class="card-block" style="border: 2px dashed #000;">
    //             <div class="card-top">
    //                 <div class="card-logo">
    //                     <img src="' . base_url('system-logo/' . $schoolinfo->logo) . '" alt="">
    //                 </div>
    //                 <div class="card-school">
    //                     <h2>' . $schoolinfo->system_name . '</h2>
    //                     <p>' . ($campus_info->campus_name ?? '') . '</p>
    //                     <p>' . ($campus_info->landline ?? '') . '</p>
    //                 </div>
    //             </div>
    //             <div class="std-id">
    //                 <h3><span>' . ($student_info->reg_no ?? '') . '</span></h3>
    //             </div>
    //             <div class="card-main">
    //                 <div class="card-photo">';
    //         if (!empty($student_info->profile_photo)) {
    //             $strResultCard .= '<img style="width: 94px;margin-top: -20px;border-radius: 8px;" src="' . base_url('uploads/' . $student_info->profile_photo) . '">';
    //         } else {
    //             $strResultCard .= '<i style="font-size: 94px;margin-top: -20px;margin-bottom:13px;text-align: center;display: block;" class="fa fa-user"></i>';
    //         }

    //         $date_of_birth = isset($student_info->date_of_birth) ? date_create($student_info->date_of_birth) : null;
    //         $date_of_birthFormated = $date_of_birth ? date_format($date_of_birth, "F j, Y") : '';

    //         $strResultCard .= '</div>
    //                 <div class="card-info">
    //                     <p><span class="card-title">Name</span><span class="card-value">: ' . ($student_info->first_name ?? '') . ' ' . ($student_info->last_name ?? '') . '</span></p>
    //                     <p><span class="card-title">Father Name</span><span class="card-value">: ' . $f_name . '</span></p>
    //                     <p><span class="card-title">Father Contact</span><span class="card-value">: ' . $father_contact . '</span></p>
    //                     <p><span class="card-title">Class</span><span class="card-value">: ' . ($class_info['sectionclassname'] ?? '') . '</span></p>
    //                     <p><span class="card-title">Date of Birth</span><span class="card-value">: ' . $date_of_birthFormated . '</span></p>
    //                 </div>
    //             </div>
    //             <div class="card-bottom">
    //                 <p>Student ID Card</p>
    //             </div>
    //         </div>';

    //         if ($i == 4) {
    //             $strResultCard .= '</page><p style="clear:both;page-break-before: always;">&nbsp;</p><page style="margin-top:10px;">';
    //             $i = 0;
    //         }
    //         $i++;
    //     }
    //     $strResultCard .= '</div>';
    //     return $this->response->setContentType('text/html')->setBody($strResultCard);
    // }

    // public function vertical()
    // {
    //     $campus_id = $this->session->get('member_campusid');
    //     $sessionid = $this->session->get('member_sessionid');
    //     $schoolinfo = getSchoolInfo();

    //     $test_series = $this->db->table('test_series')
    //         ->where(['session_id' => $sessionid, 'campus_id' => $campus_id])
    //         ->get()
    //         ->getResult();

    //     $currentrole = currentUserRoles();

    //     if (in_array(5, $currentrole)) {
    //         $sectionsclassinfo = teacherSubjectSections();
    //     } else {
    //         $sectionsclassinfo = userClassSections();
    //     }

    //     $data = [
    //         'sectionsclassinfo' => $sectionsclassinfo,
    //         'test_series'       => $test_series
    //     ];

    //     return view('student_id_card', $data);
    // }

