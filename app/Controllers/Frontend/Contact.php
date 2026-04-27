<?php
namespace App\Controllers\Frontend;




class Contact extends BaseController {

	public function index()
	{	
		$this->load->view('contact');
	}
}
