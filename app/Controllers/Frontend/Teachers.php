<?php
namespace App\Controllers\Frontend;




class Teachers extends BaseController {

	public function index()
	{	
		$this->load->view('teachers');
	}
}
