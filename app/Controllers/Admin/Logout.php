<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\MemberCurrentUser;

/**
 * Logout
 *
 * @author		Maqsood Jamvi
 * @copyright	Copyright (c) 2025 
 * @email		maqsoodjamvi@gmail.com
 * @filesource
 */

class Logout extends BaseController {
	 
	function index(){
		
		//$this->load->library(array('Member_current_user'));
		//$this->load->helper(array('server'));
		helper(['form']);

        $member = new MemberCurrentUser();

		//$user = Member_Current_user::user();
		MemberCurrentUser::logout();
		// other  
		return redirect()->to('/admin/login');

		//redirect('/admin/login');
	}	

}
// end this file