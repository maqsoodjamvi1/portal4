<?php
namespace App\Controllers\Admin;

/**
 * Bill types are no longer managed separately — redirect to Bill Amount setup.
 */
class Bill_type extends MY_Controller {
	function __construct(){
		parent::__construct();
	}

	private function redirectToBillAmount(): void
	{
		redirect()->to(base_url('admin/bill_amount/add'))->send();
		exit;
	}

	public function index()
	{
		$this->redirectToBillAmount();
	}

	function add()
	{
		$this->redirectToBillAmount();
	}

	function edit()
	{
		$this->redirectToBillAmount();
	}

	function data()
	{
		$this->redirectToBillAmount();
	}

	function save()
	{
		json_response(['success' => false, 'msg' => 'Bill types are no longer used. Set amounts on Bill Amount instead.']);
	}
}
