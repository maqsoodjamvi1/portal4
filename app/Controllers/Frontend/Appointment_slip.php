<?php
namespace App\Controllers\Frontend;




class Appointment_slip  extends MY_Controller {

	public function __construct() {
        parent::__construct();
        //load model
        $this->load->model('Auth_model', 'auth');
        $this->load->model('Mail', 'mail');
        $this->load->library('form_validation');
    }

	public function index()
	{	
		if ($this->session->userdata('ci_session_key_generate') == FALSE) {
            redirect('signin'); // the user is not logged in, redirect them!
        } else {
        	$sessionArray = $this->session->userdata('ci_seesion_key');
        	$slotPanelsInfo = '';
        	$adPhaseInfo = '';
        	$campusInfo = '';
        	$adSlotsInfo = '';
        	$adpanelsInfo = '';
        	$this->db->where('parent_id', $sessionArray['user_id']);
        	$parentInfo = $this->db->get('parents')->row();

        	$this->db->where('parent_id', $parentInfo->parent_id);	
        	$adStudentsInfo = $this->db->get('admission_students')->result();
        foreach ($adStudentsInfo as $key => $adStudentInfo) {
        	
        	$this->db->where('ad_std_id', $adStudentInfo->ad_student_id);	
        	$this->db->where('status', 1);	
        	$adRegistrationInfo = $this->db->get('admission_registration')->row();

        	
        	if($adRegistrationInfo){
	        	$this->db->where('slot_panel_id', $adRegistrationInfo->slot_panel_id);	
	        	$slotPanelsInfo = $this->db->get('admission_slot_panels')->row();

	        	$this->db->where('phase_id', $adRegistrationInfo->phase_id);	
	        	$adPhaseInfo = $this->db->get('admission_phases')->row();

	        	$this->db->where('campus_id', $adRegistrationInfo->campus_id);	
	        	$campusInfo = $this->db->get('campus')->row();
        	}

        	if($slotPanelsInfo){
        		$this->db->where('slot_id', $slotPanelsInfo->slot_id);	
        		$adSlotsInfo = $this->db->get('admission_slots')->row();	

        		$this->db->where('panel_id', $slotPanelsInfo->panel_id);	
        		$adpanelsInfo = $this->db->get('admission_panels')->row();
        	}
        	

        	$data['parentInfo'] = $parentInfo;
        	$data['studentInfo'][] = $adStudentInfo;
        	$data['campusInfo'] = $campusInfo;
        	$data['adSlotsInfo'][] = $adSlotsInfo;
        	$data['adRegistrationsInfo'][] = $adRegistrationInfo;
        	$data['adPhaseInfo'][] = $adPhaseInfo;
        	$data['adpanelsInfo'][] = $adpanelsInfo;
        }
		
		$this->page_construct('appointment_slip',$data);
		// code...
        	
		}
	}
			
}
