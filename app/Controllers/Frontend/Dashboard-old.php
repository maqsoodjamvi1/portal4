<?php
namespace App\Controllers\Frontend;




class Dashboard extends MY_Controller {

	public function index()
	{	
		$this->page_construct('welcome_message');

	}
}
