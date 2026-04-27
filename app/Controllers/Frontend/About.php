<?php
namespace App\Controllers\Frontend;




class About  extends BaseController {

	public function index()
	{	

		$this->load->view('about');
	}
}
