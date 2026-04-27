<?php
namespace App\Controllers\Frontend;




class Main extends BaseController {

	public function index()
	{	
		$this->load->view('main');
	}
}
