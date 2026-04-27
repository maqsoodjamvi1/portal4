<?php
namespace App\Models\Admin;

use CodeIgniter\Model;




class Students_model extends Model {

	var $table = 'students';
	var $column_order = array(null, 'first_name','last_name','father_contact','address_line1','city'); 
	var $column_search = array('student_id','parent_id','status'); 
	var $order = array('student_id' => 'asc'); // default order 

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	private function _get_datatables_query()
	{
		$campusid = $this->session->userdata('member_campusid');
		$sessionid = $this->session->userdata('member_sessionid');

		//add custom filter here
		if($this->input->post('status'))
		{
			$this->db->where('status', $this->input->post('status'));
		}
		//print_r($_GET);


		if($this->input->get('status'))
		{
			$this->db->where('status', $this->input->get('status'));
		}
		
		if($this->input->post('student_id'))
		{	//echo "Student";
			$this->db->where('student_id', $this->input->post('student_id'));
		}
		
		if($this->input->post('cls_sec_id'))
		{		
			$this->db->where(' student_id IN(select student_id from student_class where session_id='.$sessionid.' AND cls_sec_id='.$this->input->post('cls_sec_id').')');
		}
		if($this->input->post('parent_id'))
		{	//echo "Parent";	
			$this->db->where('parent_id', $this->input->post('parent_id'));
		}
		$this->db->where('campus_id', $campusid);
		$this->db->from($this->table); 

		$i = 0;
	
		foreach ($this->column_search as $item) // loop column 
		{

			if($_POST['search']['value']) // if datatable send POST for search
			{
				
				if($i===0) // first loop
				{
					$this->db->group_start();
					$this->db->like($item, $_POST['search']['value']);
				}
				else
				{
					$this->db->or_like($item, $_POST['search']['value']);
				}

				if(count($this->column_search) - 1 == $i) //last loop
					$this->db->group_end(); //close bracket
			}
			$i++;
		}
		
		if(isset($_POST['order'])) // here order processing
		{
			$this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		} 
		else if(isset($this->order))
		{
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
		//echo $this->db->last_query();
		
	}

	public function get_datatables()
	{
		$this->_get_datatables_query();
		if($_POST['length'] != -1)
		$this->db->limit($_POST['length'], $_POST['start']);
		$query = $this->db->get();
		return $query->result();
	}

	public function count_filtered()
	{
		$this->_get_datatables_query();
		$query = $this->db->get();
		return $query->num_rows();
	}

	public function count_all()
	{
		$campusid = $this->session->userdata('member_campusid');
		
		$this->db->where('campus_id', $campusid);
		$this->db->from($this->table);
		return $this->db->count_all_results();
	}

	public function get_list_parents()
	{
		$this->db->select('parents');
		$this->db->from($this->table);
		$this->db->order_by('country','asc');
		$query = $this->db->get();
		$result = $query->result();

		$countries = array();
		foreach ($result as $row) 
		{
			$countries[] = $row->country;
		}
		return $countries;
	}

}
