<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class ProfileCampus extends BaseController
{
    protected $db;
    protected $session;
    protected $validation;

    public function __construct()
    {
        $this->db         = \Config\Database::connect();
        $this->session    = \Config\Services::session();
        $this->validation = \Config\Services::validation();
    }


public function uploadSignaturePage()
{
    $campusId = (int) $this->session->get('member_campusid');
    $db = \Config\Database::connect();
    
    $campus = $db->table('campus')
        ->where('campus_id', $campusId)
        ->get()
        ->getRowArray();
    
    return view('admin/upload_signature', ['campus' => $campus]);
}
public function index()
{
    $campus_id = $this->session->get('member_campusid'); 
    
    $info = $this->db->table('campus')->where('campus_id', $campus_id)->get()->getRow();
    
    $languages  = $this->db->table('languages')->get()->getResult();
    $currencies = $this->db->table('currencies')->get()->getResult();
    
    // Get or generate QR code for campus
    $campusQR = $this->db->table('campus_qr_codes')
        ->where('campus_id', $campus_id)
        ->get()
        ->getRow();
    
    $qr_image_base64 = null;
    
    // If QR code exists, generate image
    if ($campusQR && !empty($campusQR->qr_code)) {
        try {
            if (class_exists('\\Endroid\\QrCode\\QrCode')) {
                $qrCode = new \Endroid\QrCode\QrCode($campusQR->qr_code);
                if (method_exists($qrCode, 'setSize')) {
                    $qrCode->setSize(250);
                }
                $writer = new \Endroid\QrCode\Writer\PngWriter();
                $result = $writer->write($qrCode);
                $qr_image_base64 = 'data:image/png;base64,' . base64_encode($result->getString());
            }
        } catch (\Exception $e) {
            log_message('error', 'QR generation error: ' . $e->getMessage());
            $qr_error = $e->getMessage();
        }
    }
    
    return view('admin/profile_campus', [
        'info'           => $info,
        'languages'      => $languages,
        'currencies'     => $currencies,
        'campusQR'       => $campusQR,
        'qr_image_base64' => $qr_image_base64,
        'campus_name'    => $info->campus_name ?? '',
        'id'             => $campus_id,
    ]);
}

public function generate_qr()
{
    $campus_id = $this->request->getPost('campus_id');
    
    if (!$campus_id) {
        return $this->response->setJSON([
            'success' => false,
            'msg' => 'Campus ID is required'
        ]);
    }
    
    $db = \Config\Database::connect();
    
    // Generate unique QR code
    $qr_string = 'CAMPUS_' . $campus_id . '_' . md5(uniqid() . time());
    
    // Check if QR exists
    $existing = $db->table('campus_qr_codes')
        ->where('campus_id', $campus_id)
        ->get()
        ->getRow();
    
    if ($existing) {
        // Update existing
        $db->table('campus_qr_codes')
            ->where('campus_id', $campus_id)
            ->update([
                'qr_code' => $qr_string,
                'generated_at' => date('Y-m-d H:i:s'),
                'is_active' => 1
            ]);
    } else {
        // Insert new
        $db->table('campus_qr_codes')->insert([
            'campus_id' => $campus_id,
            'qr_code' => $qr_string,
            'generated_at' => date('Y-m-d H:i:s'),
            'is_active' => 1
        ]);
    }
    
    return $this->response->setJSON([
        'success' => true,
        'msg' => 'QR Code generated successfully',
        'qr_code' => $qr_string
    ]);
}
    public function save()
    {
        $request = service('request');
        $user_id = $this->session->get('member_userid');
        $id      = (int) $request->getPost('id');

        $rules = [
            'mobile_no'   => [
                'label'  => 'Mobile Number',
                'rules'  => 'required|regex_match[/^\+[0-9]{6,15}$/]',
                'errors' => ['regex_match' => 'Mobile number must be in international format (+CountryCode Number)']
            ],
            'landline'    => [
                'label'  => 'Landline',
                'rules'  => 'permit_empty|regex_match[/^\+?[0-9\s\-\(\)]{6,20}$/]',
                'errors' => ['regex_match' => 'Invalid landline format. Use +CountryCode Area Number']
            ],
            'campus_name' => 'required|max_length[150]',
            'location'    => 'permit_empty|max_length[500]',
            'bank_acc'    => 'permit_empty|max_length[30]',
        ];

        if (!$this->validate($rules)) {
            json_response([
                'success' => false,
                'msg'     => validation_list_errors(),
                'errors'  => $this->validator->getErrors()
            ]);
        }

        $date = date('Y-m-d H:i:s');

        $data = [
            'campus_name'      => trim($request->getPost('campus_name')),
            'short_name'       => trim($request->getPost('short_name')),
            'landline'         => trim($request->getPost('landline')),
            'mobile_no'        => trim($request->getPost('mobile_no')),
            'location'         => trim($request->getPost('location')),
            'bank_name'        => trim($request->getPost('bank_name')),
            'bank_address'     => trim($request->getPost('bank_address')),
            'bank_code'        => trim($request->getPost('bank_code')),
            'bank_acc'         => trim($request->getPost('bank_acc')),
            'chalan_h_msg'     => trim($request->getPost('chalan_h_msg')),
            'chalan_f_msg'     => trim($request->getPost('chalan_f_msg')),
            'late_fee_fine'    => trim($request->getPost('late_fee_fine')),
            'fine_type'        => trim($request->getPost('fine_type')),
            'fee_issue_date'   => trim($request->getPost('fee_issue_date')),
            'fee_due_date'     => trim($request->getPost('fee_due_date')),
            'default_language' => $request->getPost('default_language'),
            'currency_code'    => $request->getPost('currency_code'),
            'updated_date'     => $date,
            'user_id'          => $user_id,
        ];

        foreach ([
            's_flag'   => 'school',
            'hfz_flag' => 'hifz',
        ] as $flag => $inputName) {
            $data[$flag] = $request->getPost($inputName) ? 1 : 0;
        }
        $this->db->transStart();
        $this->db->table('campus')->where('campus_id', $id)->update($data);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            json_response(['success' => false, 'msg' => 'Update failed']);
        }

        json_response(['success' => true, 'msg' => 'Campus updated successfully']);
    }

    public function update_password()
    {
        $request = service('request');

        $rules = ['password' => 'required'];
        if (!$this->validate($rules)) {
            json_response(['success' => false, 'msg' => validation_list_errors()]);
        }

        $user_id = (int) $request->getPost('user_id');
        $data    = ['password' => password_hash(trim($request->getPost('password')), PASSWORD_BCRYPT)];

        $this->db->table('users')->where('id', $user_id)->update($data);

        json_response(['success' => true, 'msg' => 'Change Password Success']);
    }
}
