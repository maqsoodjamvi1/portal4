<?php
namespace App\Controllers\Frontend;




class Admissions extends BaseController {

	public function index()
	{	
		$this->load->view('admissions');
	}
}
