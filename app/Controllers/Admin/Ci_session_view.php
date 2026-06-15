<?php
namespace App\Controllers\Admin;


/**
 * Academic Session Manage
 *
 * @author		Maqsood Ahmed
 * @copyright	Copyright (c) 2016~2099 TIME Soft Soltions
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */


class Ci_session_view extends MY_Controller {

	function __construct(){
		parent::__construct();
		check_permission('admin-ci-session_view');
	}

	/**
	 * Index Page for this controller.
	 */
	public function index()
	{
		$this->load->view('ci_session_view', $this->template_data);
	}

	function data(){
		$response = new stdClass;
		$response->draw = $this->input->post('draw');

		$rows = $this->loadCiSessionRows();
		$response->recordsTotal    = count($rows);
		$response->recordsFiltered = $response->recordsTotal;

		$start  = (int) $this->input->post('start');
		$length = (int) $this->input->post('length');
		$slice  = $length > 0 ? array_slice($rows, $start, $length) : $rows;

		$response->data = [];
		foreach ($slice as $row) {
			$response->data[] = $this->formatCiSessionRow($row);
		}

		$this->output->set_output(json_encode($response));
	}

	/**
	 * @return list<array{id:string|int,ip_address:string,timestamp:int,data:string}>
	 */
	private function loadCiSessionRows(): array
	{
		if ($this->ciSessionsTableExists()) {
			return $this->loadCiSessionsFromDatabase();
		}

		return $this->loadCiSessionsFromFiles();
	}

	private function ciSessionsTableExists(): bool
	{
		try {
			return \Config\Database::connect()->tableExists('ci_sessions');
		} catch (\Throwable $e) {
			return false;
		}
	}

	/**
	 * @return list<array{id:string|int,ip_address:string,timestamp:int,data:string}>
	 */
	private function loadCiSessionsFromDatabase(): array
	{
		$this->db->select('A.*');
		$this->db->from('ci_sessions A');
		$this->db->order_by('A.id', 'desc');
		$results = $this->db->get()->result();

		$rows = [];
		foreach ($results as $row) {
			$rows[] = [
				'id'         => $row->id,
				'ip_address' => (string) ($row->ip_address ?? ''),
				'timestamp'  => (int) ($row->timestamp ?? 0),
				'data'       => (string) ($row->data ?? ''),
			];
		}

		return $rows;
	}

	/**
	 * CI4 default: sessions stored as files under writable/session.
	 *
	 * @return list<array{id:string|int,ip_address:string,timestamp:int,data:string}>
	 */
	private function loadCiSessionsFromFiles(): array
	{
		$sessionConfig = config('Session');
		$path          = rtrim((string) ($sessionConfig->savePath ?? ''), '/\\');
		if ($path === '') {
			$path = rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'session';
		}

		if (! is_dir($path)) {
			return [];
		}

		$rows = [];
		foreach (glob($path . DIRECTORY_SEPARATOR . 'ci_session*') ?: [] as $file) {
			if (! is_file($file)) {
				continue;
			}

			$content = @file_get_contents($file);
			if ($content === false) {
				continue;
			}

			$rows[] = [
				'id'         => substr(basename($file), strlen('ci_session')),
				'ip_address' => '',
				'timestamp'  => (int) filemtime($file),
				'data'       => $content,
			];
		}

		usort($rows, static fn (array $a, array $b): int => $b['timestamp'] <=> $a['timestamp']);

		return $rows;
	}

	/**
	 * @param array{id:string|int,ip_address:string,timestamp:int,data:string} $row
	 * @return array{id:string|int,ip_address:string,timestamp:string,campusid:string,userid:string}
	 */
	private function formatCiSessionRow(array $row): array
	{
		$return_data = $this->parseCiSessionPayload($row['data']);

		$campusid = $return_data['member_campusid'] ?? '';
		$userid   = $return_data['member_userid'] ?? '';

		$campus = '';
		if ($campusid !== '' && $campusid !== null) {
			$this->db->where('campus_id', $campusid);
			$campusinfo = $this->db->get('campus')->row();
			if ($campusinfo) {
				$campus = $campusinfo->campus_name;
			}
		}

		$user = '';
		if ($userid !== '' && $userid !== null) {
			$this->db->where('id', $userid);
			$userinfo = $this->db->get('users')->row();
			if ($userinfo) {
				$user = trim(($userinfo->first_name ?? '') . ' ' . ($userinfo->last_name ?? ''));
			}
		}

		$timestamp = '';
		if (! empty($row['timestamp'])) {
			$timestamp = date('d-m-Y h:i:sa', (int) $row['timestamp']);
		}

		return [
			'id'         => $row['id'],
			'ip_address' => $row['ip_address'],
			'timestamp'  => $timestamp,
			'campusid'   => $campus,
			'userid'     => $user,
		];
	}

	/**
	 * @return array<string, mixed>
	 */
	private function parseCiSessionPayload(string $sessionData): array
	{
		if ($sessionData === '') {
			return [];
		}

		if (str_contains($sessionData, '|')) {
			$return_data = [];
			$offset      = 0;
			$length      = strlen($sessionData);

			while ($offset < $length) {
				$pipePos = strpos($sessionData, '|', $offset);
				if ($pipePos === false) {
					break;
				}

				$varname    = substr($sessionData, $offset, $pipePos - $offset);
				$offset     = $pipePos + 1;
				$serialized = substr($sessionData, $offset);
				$data       = @unserialize($serialized);

				if ($data === false && $serialized !== serialize(false)) {
					break;
				}

				$return_data[$varname] = $data;
				$offset += strlen(serialize($data));
			}

			return $return_data;
		}

		$json = json_decode($sessionData, true);

		return is_array($json) ? $json : [];
	}

	function add(){
		check_permission('admin-add-academic-session');
		$schoolinfo = getSchoolInfo();
		$academic_session = $this->db->query('SELECT * from academic_session where  system_id='.$schoolinfo->system_id.'  order by session_id desc')->row();
		
		$this->template_data['academic_session'] = $academic_session;
		
		$this->load->view('academic_session_edit', $this->template_data);
	}

	function edit(){
		check_permission('admin-edit-academic-session');
		$session_id = intval($this->input->get('id'));
		$this->db->where('session_id', $session_id);
		$info = $this->db->get('academic_session')->row();
		$this->template_data['info'] = $info;
		$this->load->view('academic_session_edit', $this->template_data);
	}



	function save(){
		$id = intval($this->input->post('id'));
		$user_id = $this->session->userdata['member_userid'];
		$date = date('Y-m-d H:i:s');
		$schoolinfo = getSchoolInfo();
		
		$start_date = date($this->input->post('start_date'));
		$end_date = date($this->input->post('end_date'));

		if($end_date < $start_date)
		{
		    json_response(array('error' => FALSE, 'msg' => 'End date should be greater'));
		    exit; 
		}
		$this->form_validation->set_rules('session_name', 'Session Name', 'trim|required');
		if($this->form_validation->run() === FALSE){
			json_response(array('success' => FALSE, 'msg' => validation_errors()));
		}else{
			if($id === 0){
				check_permission('admin-add-academic-session');
				$this->db->trans_begin();
				
				$this->db->where('system_id', $schoolinfo->system_id);
				$academic_session_info = $this->db->get('academic_session')->row();

				if(empty($academic_session_info)){
					
					$data = array(
					'session_name' => trim($this->input->post('session_name')),
					'start_date' => trim($this->input->post('start_date')),
					'end_date' => trim($this->input->post('end_date')),
					'system_id' => $schoolinfo->system_id,
					'user_id' => $user_id,
					'created_date' => $date 
				);
				
				$this->db->insert('academic_session', $data);
				$new_session_id = $this->db->insert_id();

				$sess_data = [
				 'member_sessionid'	=> $new_session_id,
				 ];
	
				$this->session->set_userdata($sess_data);	
				}else{
					
				$data = array(
					'session_name' => trim($this->input->post('session_name')),
					'start_date' => trim($this->input->post('start_date')),
					'end_date' => trim($this->input->post('end_date')),
					'system_id' => $schoolinfo->system_id,
					'user_id' => $user_id,
					'created_date' => $date
				);
				
				$this->db->insert('academic_session', $data);
				$new_session_id = $this->db->insert_id();
			}

				$this->db->trans_complete();

				$this->db->where('system_id', $schoolinfo->system_id);
				$terms_info = $this->db->get('terms')->row();
				
				if(empty($terms_info->term_id)){
					$this->output->set_output(json_encode(array('term_id' => FALSE, 'msg' => 'Session Success')));
				}else {
					json_response(array('success' => TRUE, 'msg' => 'Add Academic Session Success'));
				}
				
			}else{
			    check_permission('admin-edit-academic-session');
				$this->db->trans_begin();
				$data = array(
					'session_name' => trim($this->input->post('session_name')),
					'start_date' => trim($this->input->post('start_date')),
					'end_date' => trim($this->input->post('end_date')),
					'user_id' => $user_id,
					'updated_date' => $date
				);
				$this->db->where('session_id', $id);
				$this->db->update('academic_session', $data);
				// User Roles
				$this->db->trans_complete();
				json_response(array('success' => TRUE, 'msg' => 'Edit Academic Session Success'));
			}

		}
	}

	function delete(){
		check_permission('admin-del-academic-session');
		$id = intval($this->input->get('id'));

		$this->db->trans_begin();

		// delete class
		$this->db->where('id', $id);
		$this->db->delete('academic_session');

		$this->db->trans_complete();
		json_response(array('success' => TRUE, 'msg' => 'Delete Academic Session Success'));
	}


}
// end this file
