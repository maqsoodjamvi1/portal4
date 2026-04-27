<?php
namespace App\Models\Frontend;

use CodeIgniter\Model;


 
/* * ***
 * Version: V1.0.1
 *
 * Description of Auth model
 *
 * @author The Prep School Team
 *
 * @email  info@theprepschool.com.pk
 *
 * *** */
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
 
class Careers_model extends Model {
 
    // Declaration of a variables
    private $_adStudentID;   
    private $_campus;
    private $_adClass;
    private $_gender;
    private $_name;
    private $_fName;
    private $_CNIC;
    private $_stdCNIC;
    private $_email;
    private $_password;
    private $_cell;
    private $_cell2;
    private $_landline;
    private $_address;
    private $_dob;
    private $_verificationCode;
    private $_timeStamp;
    private $_status;
 
    //Declaration of a methods
    public function setadStudentID($adStudentID) {
        $this->_adStudentID = $adStudentID;
    }

    public function setCampus($campus) {
        $this->_campus = $campus;
    }

    public function setadClass($adClass) {
        $this->_adClass = $adClass;
    }

     public function setGender($gender) {
        $this->_gender = $gender;
    }
 
    public function setCNIC($CNIC) {
        $this->_CNIC = $CNIC;
    }

    public function setStdCNIC($stdCNIC) {
        $this->_stdCNIC = $stdCNIC;
    }

    public function setName($name) {
        $this->_name = $name;
    }
 
    public function setFName($fName) {
        $this->_fName = $fName;
    }
 
    public function setEmail($email) {
        $this->_email = $email;
    }

    public function setContactNo($cell) {
        $this->_cell = $cell;
    }

    public function setContactNo2($cell2) {
        $this->_cell2 = $cell2;
    }

    public function setLandLine($landline) {
        $this->_landline = $landline;
    }
 
    public function setPassword($password) {
        $this->_password = $password;
    }
 
    public function setAddress($address) {
        $this->_address = $address;
    }
 
    public function setDOB($dob) {
        $this->_dob = $dob;
    }
 
    public function setVerificationCode($verificationCode) {
        $this->_verificationCode = $verificationCode;
    }
 
    public function setTimeStamp($timeStamp) {
        $this->_timeStamp = $timeStamp;
    }
 
    public function setStatus($status) {
        $this->_status = $status;
    }
 
    //create new user
    public function create() {

        $this->db->where('class_id', $this->_adClass);
        $classInfo = $this->db->get('classes')->row();

        $this->db->where('campus_id', $this->_campus);
        $campusInfo = $this->db->get('campus')->row();

        $this->db->where('father_cnicnew', $this->_CNIC);
        $parentInfo = $this->db->get('parents')->row();

        $admissionslotInfo = $this->db->query('SELECT * from admission_slot_panels where campus_id='.$this->_campus.' and status=1  and class_id='.$this->_adClass.' and capacity > 0 order by panel_id,slot_id ASC')->row();

        $admissionPhaseInfo = $this->db->query('SELECT * from admission_phases where campus_id='.$this->_campus.' and class_id='.$this->_adClass.' and status=1')->row();
        
        $this->db->where('campus_id', $this->_campus);
        $this->db->where('class_id', $this->_adClass);
        $this->db->order_by('ad_student_id', 'desc');
        $last_row = $this->db->get('admission_students')->row();
        //print_r($last_row);
        if($last_row){

            $regArr = explode('-' , $last_row->reg_no);
           
            $last_id = (int)trim($regArr[4]) + 1;

        }else{
            $last_id = 101;
        }

        $regG = '';
        if($this->_gender == 'b'){
            $regG = 'B';
        }

        if($this->_gender == 'g'){
            $regG = 'G';
        }

        $reg_no = "23-".$campusInfo->short_name."-".$classInfo->class_short_name."-".$regG."-".$last_id;
        
        $hash = $this->hash($this->_password); 
        if($parentInfo){
            $parent_id = $parentInfo->parent_id;
        }else{
            $dataParent = array(
                'father_cnicnew' => $this->_CNIC,
                'f_name' => $this->_fName,
                'father_email' => $this->_email,
                'password' => $hash,
                'pwd' => $this->_password,
                'father_contact' => $this->_cell,
                'mother_contact' => $this->_cell2,
                'emergency_contact' => $this->_landline,
                'address_line1' => $this->_address,
                'created_date' => $this->_timeStamp,
                'updated_date' => $this->_timeStamp,
                'status' => $this->_status
            );

            $this->db->insert('parents', $dataParent);
            $parent_id = $this->db->insert_id();
        }

        $dataStudent = array(
            'parent_id' => $parent_id,
            'student_cnic' => $this->_stdCNIC,
            'first_name' => $this->_name,
            'reg_no' => $reg_no,
            'date_of_birth' => $this->_dob,
            'gender' => $this->_gender,
            'campus_id' => $this->_campus,
            'created_date' => $this->_timeStamp,
            'updated_date' => $this->_timeStamp,
            'status' => $this->_status
        );
       
        $this->db->insert('admission_students', $dataStudent);
       
        if (!empty($this->db->insert_id()) && $this->db->insert_id() > 0) {
             
            if($admissionPhaseInfo && $admissionslotInfo){
                if($admissionPhaseInfo->phase_id && $admissionslotInfo->slot_panel_id){
                    $admissionRegistration = array(
                        'ad_std_id' => $this->db->insert_id(),
                        'campus_id' => $this->_campus,
                        'class_id' =>  $this->_adClass,
                        'phase_id' => $admissionPhaseInfo->phase_id,
                        'slot_panel_id' => $admissionslotInfo->slot_panel_id,
                    );
                    $this->db->insert('admission_registration', $admissionRegistration);

                    $admissionslotInfoData = array(
                        'capacity' => ($admissionslotInfo->capacity -1),
                    );

                    $this->db->where('slot_panel_id', $admissionslotInfo->slot_panel_id);
                    $msg = $this->db->update('admission_slot_panels', $admissionslotInfoData);
                }
            }
            $authArray = array(
                        'user_id' => $parent_id,
                        //'user_name' => $row->user_name,
                        'email' => $this->_email
                    );
            $this->session->set_userdata('ci_session_key_generate', TRUE);
            $this->session->set_userdata('ci_seesion_key', $authArray);
            return TRUE;
        } else {
            return FALSE;
        }
    }
 
    // login method and password verify
    function login() {
        $this->db->select('parent_id as user_id, father_cnicnew, father_email, password');
        $this->db->from('parents');
        $this->db->where('father_email', $this->_email);
        //$this->db->where('verification_code', 1);
        $this->db->where('status', 1);
        //{OR}
        $this->db->or_where('father_cnicnew', $this->_CNIC);
        //$this->db->where('verification_code', 1);
        $this->db->where('status', 1);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            $result = $query->result();
            foreach ($result as $row) {
                if ($this->verifyHash($this->_password, $row->password) == TRUE) {
                    return $result;
                } else {
                    return FALSE;
                }
            }
        } else {
            return FALSE;
        }
    }
 
    //update user
    public function update() {
        $data = array(
            'first_name' => $this->_firstName,
            'last_name' => $this->_lastName,
            'contact_no' => $this->_contactNo,
            'address' => $this->_address,
            'dob' => $this->_dob,
            'modified_date' => $this->_timeStamp,
        );
        $this->db->where('id', $this->_userID);
        $msg = $this->db->update('users', $data);
        if ($msg == 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
 
    //change password
    public function changePassword() {
        $hash = $this->hash($this->_password);
        $data = array(
            'password' => $hash,
        );
        $this->db->where('id', $this->_adStudentID);
        $msg = $this->db->update('users', $data);
        if ($msg == 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
 
    // get User Detail
    public function getUserDetails() {
        $this->db->select(array('m.parent_id as user_id', 'CONCAT(m.f_name) as full_name', 'm.father_email', 'm.father_contact', 'm.address_line1'));
        $this->db->from('parents as m');
        $this->db->where('m.parent_id', $this->_adStudentID);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->row_array();
        } else {
            return FALSE;
        }
    }
 
    // update Forgot Password
    public function updateForgotPassword() {
        $hash = $this->hash($this->_password);
        $data = array(
            'password' => $hash,
        );
        $this->db->where('email', $this->_email);
        $msg = $this->db->update('users', $data);
        if ($msg > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
 
    // get Email Address
    public function activate() {
        $data = array(
            'status' => 1,
            'verification_code' => 1,
        );
        $this->db->where('verification_code', $this->_verificationCode);
        $msg = $this->db->update('users', $data);
        if ($msg == 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
 
    // password hash
    public function hash($password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        return $hash;
    }
 
    // password verify
    public function verifyHash($password, $vpassword) {
        if (password_verify($password, $vpassword)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
 
}
?>