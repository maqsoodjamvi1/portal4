<?php
namespace App\Controllers\Frontend;




class Campuses extends BaseController {

	public function index()
	{	
		$this->load->view('campuses');
	}
}
