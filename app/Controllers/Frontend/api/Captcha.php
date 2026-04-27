<?php
namespace App\Controllers\Frontend;

 
/**
 * Captcha
 *
 * @author		Chaegumi
 * @copyright	Copyright (c) 2013 chaegumi
 * @email		chaegumi@qq.com
 * @filesource
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 验证码
 */
class Captcha extends BaseController {
	function __construct(){
		parent::__construct();
	}
	public function index()
	{
		include_once FCPATH . 'resource/securimage/securimage.php';
		$img = new Securimage();
		$img->show();
	}
	
}

/* End of file stat.php */
/* Location: ./application/modules/api/stat.php */