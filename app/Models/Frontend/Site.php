<?php
namespace App\Models\Frontend;

use CodeIgniter\Model;


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @package Razorpay :  CodeIgniter Site
 *
 * @author TechArise Team
 *
 * @email  info@techarise.com
 *   
 * Description of Site Controller
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Site extends Model {
    private $_name;
    private $_email;
    private $_contactNo;
    private $_comment;
    private $_timeStamp;

    public function setName($name) 
    {
        $this->_name = $name;
    }
    
    public function setEmail($email)
    {
        $this->_email = $email;
    }
    public function setContactNo($contactNo) 
    {
        $this->_contactNo = $contactNo;
    }
    public function setComment($comment)
    {
        $this->_comment = $comment;
    }
    public function setTimeStamp($timeStamp) 
    {
        $this->_timeStamp = $timeStamp;
    }

    // save value in database
    public function create()
    {
        $data = array(
            'name' => $this->_name,
            'email' => $this->_email,
            'contact_no' => $this->_contactNo,
            'comment' => $this->_comment,
            'created_date' => $this->_timeStamp,
        );
        $this->db->insert('contact', $data);
        return $this->db->insert_id();
    }

    // email validation
    public function validateEmail($email)
    {
        return preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)?TRUE:FALSE;
    }

    // mobile validation
    public function validateMobile($mobile)
    {
        return preg_match('/^[0-9]{10}+$/', $mobile)?TRUE:FALSE;
    }

}
