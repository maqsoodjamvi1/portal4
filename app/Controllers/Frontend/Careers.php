<?php
namespace App\Controllers\Frontend;



/* * ***
 * Version: V1.0.1
 *
 * Description of Employee Controller
 *
 * @author The Prep School Team
 *
 * @email  info@theprepschool.com.pk
 *
 * *** */
ob_start();
//
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Careers extends MY_Controller {

    public function __construct() {
        parent::__construct();
        //load model
        $this->load->model('Careers_model', 'auth');
        $this->load->model('Mail', 'mail');
        $this->load->library('form_validation');
    }

    // user profile
    // public function index() {
    //     if ($this->session->userdata('ci_session_key_generate') == FALSE) {
    //         redirect('signin'); // the user is not logged in, redirect them!
    //     } else {
    //         $data = array();
    //         $data['metaDescription'] = 'User Profile';
    //         $data['metaKeywords'] = 'User Profile';
    //         $data['title'] = "User Profile";
    //         $data['breadcrumbs'] = array('Profile' => '#');
    //         $sessionArray = $this->session->userdata('ci_seesion_key');
    //         $this->auth->setadStudentID($sessionArray['user_id']);
    //         $data['userInfo'] = $this->auth->getUserDetails();
    //         $this->page_construct('employee/index', $data);
    //     }
    // }

    // registration method
    public function account() {
        $data = array();
        $current_date = date('Y-m-d');
        $campus_info = $this->db->query(
            'SELECT * FROM campus WHERE system_id = 1 AND campus_id IN (
                SELECT campus_id FROM admission_phases WHERE status = 1 AND start_date <= ?
            )',
            [$current_date]
        )->getResult();
        $dataCampus = array();    
        foreach($campus_info as $campus_value){

                $dataCampus[] = array(
                    'campus_id' => $campus_value->campus_id,
                    'campus_name' => $campus_value->campus_name,
                    'location' => $campus_value->location
                );
            
        }
       
        
        $data['campus_data'] = $dataCampus; 
        $this->page_construct('employee/account', $data);
    }

    // registration method
    public function registration() {
        $data = array();
        $current_date = date('Y-m-d');
        // $campus_info = $this->db->query("SELECT * from campus where system_id=1 and campus_id IN (select campus_id from admission_phases where status=1 and start_date <= '".$current_date."')")->result();
        $dataCampus = array();    
        // foreach($campus_info as $campus_value){

        //     $dataCampus[] = array(
        //         'campus_id' => $campus_value->campus_id,
        //         'campus_name' => $campus_value->campus_name,
        //         'location' => $campus_value->location
        //     );
            
        // }
       $terms_session = $this->db->query(
            'SELECT * FROM terms_session WHERE system_id = 1 AND ? BETWEEN start_date AND end_date',
            [$current_date]
        )->getRow();

        $session_info = $this->db->query(
            'SELECT * FROM academic_session WHERE system_id = 1 AND ? BETWEEN start_date AND end_date',
            [$current_date]
        )->getRow();

        if (empty($session_info)) {
            $session_info = $this->db->query('SELECT * FROM academic_session WHERE system_id = 1')->getRow();
        }
        
        if($session_info){
            $sessionid = $session_info->session_id;
        }else{
            $sessionid = 0;
        }
        
        $data['sessionid'] = $sessionid; 

        //$data['campus_data'] = $dataCampus; 
        $this->page_construct('employee/register', $data);
    }

 

    // action create user method
    public function actionCreate() {
          exit;
      
            $position = 'm';//$this->input->post('position');
            $session_id = $this->input->post('session_id');
            $name = $this->input->post('name');
            $cnic = $this->input->post('cnic');

            $dob = $this->input->post('dob');
            $fname = $this->input->post('fname');
            $cell = $this->input->post('cell');
            $email = $this->input->post('email');
            $gender = $this->input->post('gender');
            
            $address = $this->input->post('address');
            $qualification = $this->input->post('qualification');
            $rowscount = $this->input->post('rowscount');
            
            if($gender == 'm') {
                    echo json_encode(array('error' => TRUE, 'msg' => 'Only Females are allowed'));
                    exit;
            }
           // print_r($_POST);
          //exit;
           
            header('Content-Type: application/json');
            $config['upload_path']   = './recruitment_doc/';
            $config['allowed_types'] ="gif|jpg|jpeg|png|iso|dmg|zip|rar|doc|docx|xls|xlsx|ppt|pptx|csv|ods|odt|odp|pdf|rtf|sxc|sxi|txt|exe|avi|mpeg|mp3|mp4|3gp";  
            $config['max_size']   = 1024;
            $this->load->library('upload', $config);
          

            // $this->upload->initialize($config);
            // $this->upload->do_upload('image');  // File Name
            // $image = $this->upload->data(); 

            // $imageName = $image['file_name']; 
          
            $timeStamp = time();
            $status = 1;
            $result = $this->db->table('recruitment')->like('post', $position)->get();
            $g = $result->num_rows();
            $id1 = str_pad($g+1, 4, '0', STR_PAD_LEFT);
            //$id1=$g+0001;
            $reg_no = 'TPS-M-'.$id1;
            //exit;
            $divider = 20; 
           
            if($g < 20){
              $room = '1';
            }else if($g < 40){
              $room = '2';
            }else if($g < 60){
              $room = '3';
            }else if($g < 80){
              $room = '4';
            }else if($g < 100){
              $room = '5';
            }else if($g < 120){
              $room = '6';
            }else if($g < 140){
              $room = '7';
            }else if($g < 160){
              $room = '8';
            }else if($g < 180){
              $room = '9';
            }else if($g < 200){
              $room = '10';
            }else if($g < 220){
              $room = '11';
            }else if($g < 240){
              $room = '12';
            }else if($g < 260){
              $room = '13';
            }else if($g < 280){
              $room = '14';
            }else if($g < 300){
              $room = '15';                      
            }else if($g < 320){
              $room = '16';
            }else if($g < 340){
              $room = '17';
            }else if($g < 360){
              $room = '18';
            }else if($g < 380){
              $room = '19';
            }else if($g < 400){
              $room = '20';
            }else if($g < 420){
              $room = '21';
            }else if($g < 440){
              $room = '22';
            }else if($g < 460){
              $room = '23';
            }else if($g < 480){
              $room = '24';
            }else if($g < 500){
              $room = '25';
            }else if($g < 520){
              $room = '26';
            }else if($g < 540){
              $room = '27';
            }else if($g < 560){
              $room = '28';
            }else if($g < 580){
              $room = '29';
            }else if($g < 600){
              $room = '30';
            }else if($g < 620){
              $room = '31'; 
            }else if($g < 640){
              $room = '32';
            }else if($g < 660){
              $room = '33';
            }else if($g < 680){
              $room = '34';
            }else if($g < 700){
              $room = '35';
            }else if($g < 720){
              $room = '36';
            }else if($g < 740){
              $room = '37';
            }else if($g < 760){
              $room = '38';
            }else if($g < 780){
              $room = '39';
            }else if($g < 800){
              $room = '40';
            }else if($g < 820){
              $room = '41';
            }else if($g < 840){
              $room = '42';
            }else if($g < 860){
              $room = '43';
            }else if($g < 880){
              $room = '44';
            }else if($g < 900){
              $room = '45';
            }else if($g < 920){
              $room = '46';
            }else if($g < 940){
              $room = '47';
            }else if($g < 960){
              $room = '48';
            }else if($g < 980){
              $room = '49';
            }else if($g < 1000){
              $room = '50';
            }else if($g < 1020){
              $room = '51';
            }else if($g < 1040){
              $room = '52';
            }else if($g < 1060){
              $room = '53';
            }else if($g < 1080){
              $room = '54';
            }else if($g < 1100){
              $room = '55';
            }else if($g < 1120){
              $room = '56';
            }else if($g < 1140){
              $room = '57';
            }else if($g < 1160){
              $room = '58';
            }else if($g < 1180){
              $room = '59';
            }else if($g < 1200){
              $room = '60';                          
            }else if($g < 1220){
              $room = '61';
            }else if($g < 1240){
              $room = '62';
            }else if($g < 1260){
              $room = '63';
            }else if($g < 1280){
              $room = '64';
            }else if($g < 1300){
              $room = '65';
            }else if($g < 1320){
              $room = '66';
            }else if($g < 1340){
              $room = '67';
            }else if($g < 1360){
              $room = '68';
            }else if($g < 1380){
              $room = '69';
            }else if($g < 1400){
              $room = '70';
            }else if($g < 1420){
              $room = '71';
            }else if($g < 1440){
              $room = '72';  
            }else if($g < 1460){
              $room = '73';    
            }else if($g < 1480){
              $room = '74';
            }else if($g < 1500){
              $room = '75';    
            }else if($g < 1515){
              $room = '76';
            }

            // else if($g < 1540){
            //   $room = '77';    
            // }else if($g < 1560){
            //   $room = '78';                
            // }
 
            // else if($g < 1420){
            //   $room = '11';
            // }else if($g < 1440){
            //   $room = '12';
            // }else if($g < 1460){
            //   $room = '13';
            // }else if($g < 1480){
            //   $room = '14';
            // }else if($g < 1500){
            //   $room = '15';                      
            // }else if($g < 1520){
            //   $room = '16';
            // }else if($g < 1540){
            //   $room = '17';
            // }else if($g < 1560){
            //   $room = '18';
            // }else if($g < 1580){
            //   $room = '19';
            // }else if($g < 1600){
            //   $room = '20';
            // }else if($g < 1620){
            //   $room = '21';
            // }else if($g < 1640){
            //   $room = '22';
            // }else if($g < 1660){
            //   $room = '23';
            // }else if($g < 1680){
            //   $room = '24';
            // }else if($g < 1700){
            //   $room = '25';
            // }else if($g < 1720){
            //   $room = '26';
            // }else if($g < 1740){
            //   $room = '27';
            // }else if($g < 1760){
            //   $room = '28';
            // }else if($g < 1780){
            //   $room = '29';
            // }else if($g < 1800){
            //   $room = '30';
            // }else if($g < 1820){
            //   $room = '31'; 
            // }else if($g < 1840){
            //   $room = '32';
            // }else if($g < 1860){
            //   $room = '33';
            // }else if($g < 1880){
            //   $room = '34';
            // }else if($g < 1900){
            //   $room = '35';
            // }else if($g < 1920){
            //   $room = '36';
            // }else if($g < 1940){
            //   $room = '37';
            // }else if($g < 1960){
            //   $room = '38';
            // }else if($g < 1980){
            //   $room = '39';
            // }else if($g < 2000){
            //   $room = '40';
            // }else if($g < 2020){
            //   $room = '41';
            // }else if($g < 2040){
            //   $room = '42';
            // }else if($g < 2060){
            //   $room = '43';
            // }else if($g < 2080){
            //   $room = '44';
            // }else if($g < 2100){
            //   $room = '45';
            // }else if($g < 2120){
            //   $room = '46';
            // }else if($g < 2140){
            //   $room = '47';
            // }else if($g < 2160){
            //   $room = '48';
            // }else if($g < 2180){
            //   $room = '49';
            // }else if($g < 2200){
            //   $room = '50';
            // }else if($g < 2220){
            //   $room = '51';
            // }else if($g < 2240){
            //   $room = '52';
            // }else if($g < 2260){
            //   $room = '53';
            // }else if($g < 2280){
            //   $room = '54';
            // }else if($g < 2300){
            //   $room = '55';
            // }else if($g < 2320){
            //   $room = '56';
            // }else if($g < 2340){
            //   $room = '57';
            // }else if($g < 2360){
            //   $room = '58';
            // }else if($g < 2380){
            //   $room = '59';
            // }else if($g < 2400){
            //   $room = '60'; 
            // }

           
            $this->db->trans_begin();

            // header('Content-Type: application/json');
            // $config['upload_path']   = './recruitment_doc/';
            // $config['allowed_types'] ="gif|jpg|jpeg|png|iso|dmg|zip|rar|doc|docx|xls|xlsx|ppt|pptx|csv|ods|ogv|odt|odp|pdf|rtf|sxc|sxi|txt|exe|avi|mpeg|mp3|mp4|3gp";  
            // $config['max_size']   = 1024;
            // $this->load->library('upload', $config); 


                $data = array(
                    'post' => trim('m'),
                    'session_id' => trim($this->input->post('session_id')),
                    'name'=> $name,
                    'cnic' => trim($this->input->post('cnic')),
                    'reg_no' => trim($reg_no),
                    'dob' => trim($this->input->post('dob')),
                    'room' => trim($room),
                    'fname' => trim($this->input->post('fname')),
                    'cell' => trim($this->input->post('cell')),
                    'address' => trim($this->input->post('address')),
                    'email' => trim($this->input->post('email')),
                    'mphil' => trim($this->input->post('mphil')),
                    'tefl' => trim($this->input->post('tefl')),
                    'epm' => trim($this->input->post('epm')),
                    'med' => trim($this->input->post('med')),
                    'md' => trim($this->input->post('md'))
                    //'user_id' => $user_id,
                    //'recruitment' => $date
                );

                $this->db->insert('recruitment', $data);
                $new_user_id = $this->db->insert_id();
            
            if($new_user_id){
              
                foreach($qualification as $key => $value){
                    
                    $subject = $this->input->post('subject['.$value.']');
                  
                    $institution = $this->input->post('institution['.$value.']');
                    $board = $this->input->post('board['.$value.']');
                    $session = $this->input->post('session['.$value.']');
                    $percentage = $this->input->post('percentage['.$value.']');
                  
                    $this->upload->initialize($config);
                    $this->upload->do_upload('certificate_'.$value);  // File Name
                    $certificateFile = $this->upload->data(); 
                    $certificate_name = $certificateFile['file_name']; 

                    $edudata = array(
                    'emp_id' => trim($new_user_id),
                    'qualification' => trim($value),
                    'subject'=> $subject,
                    'institution' => trim($institution),
                    'uni_board' => trim($board),
                    'session' => trim($session),
                    'percentage' => trim($percentage),
                    'attachment' => trim($certificate_name)
                    );
                    
                    $this->db->insert('emp_education', $edudata);
                
                }

                
                for($i=0; $i < count($rowscount); $i++){

                    $institution = $this->input->post('institution'.$i);
                    $assignment = $this->input->post('assignment'.$i);
                    $from_date = $this->input->post('from_date'.$i);
                    $to_date = $this->input->post('to_date'.$i);
                   // $experience_letter = $this->input->post('experience_letter'+$i);
                    
                    // $this->upload->initialize($config2);
                    // $this->upload->do_upload('experience_letter'.$i);  // File Name
                    // $experience_letter = $this->upload->data(); 
                    // $experience_letter_name = $experience_letter['file_name'];  

                     $this->upload->initialize($config);
                     $this->upload->do_upload('experience_letter'.$i);  // File Name
                     $experienceletter = $this->upload->data(); 
                     $experience_letter_name = $experienceletter['file_name'];  
                   
                    $dataexp = array(
                    'emp_id' => trim($new_user_id),
                    'institution' => trim($institution),
                    'assignment'=> $assignment,
                    'from_date' => trim($from_date),
                    'to_date' => trim($to_date),
                    'attachment' => trim($experience_letter_name)
                    );

                    $this->db->insert('emp_experience', $dataexp);
                
                }        
                

            }
            
            $this->db->trans_complete();
            
            $chk = TRUE;
            
                if ($chk === TRUE) {
                    echo json_encode(array('success' => TRUE, 'msg' => 'Registered successfully','id' => $new_user_id));
                    //header("Location: ".base_url()."/careers/appointment_slip?id=".$new_user_id);
                    //redirect('index.php/signin');
                    //redirect('index.php/signin', 'refresh');
                } else {
                    echo 'Error';
                }
            // } else {
                
            // }
    }

    public function appointment_slip(){
         $this->page_construct('employee/slip');
    }

    public function re_print(){ 
       
         $this->page_construct('employee/regen');
    }
    // action login method
    function doLogin() {
        // Check form  validation
        $this->load->library('form_validation');
        $this->form_validation->set_rules('user_name', 'CNIC', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            //Field validation failed.  User redirected to login page
            $this->login;
        } else {
            $sessArray = array();
            //Field validation succeeded.  Validate against database
            $username = $this->input->post('user_name');
            $password = $this->input->post('password');

            $this->auth->setCNIC($username);
            $this->auth->setPassword($password);
            //query the database
            $result = $this->auth->login();

            if (!empty($result) && count($result) > 0) {
                foreach ($result as $row) {
                    $authArray = array(
                        'user_id' => $row->user_id,
                        'user_name' => $row->user_name,
                        'email' => $row->email
                    );
                    
                    $this->session->set_userdata('ci_session_key_generate', TRUE);
                    $this->session->set_userdata('ci_seesion_key', $authArray);
                }
                redirect('appointment_slip');
            } else {
                redirect('signin?msg=1');
            }
        }
    }

    public function actionChangePwd() {
        $this->form_validation->set_rules('change_pwd_password', 'Password', 'trim|required|min_length[8]');
        $this->form_validation->set_rules('change_pwd_confirm_password', 'Password Confirmation', 'trim|required|matches[change_pwd_password]');
        if ($this->form_validation->run() == FALSE) {
            $this->changepwd();
        } else {
            $change_pwd_password = $this->input->post('change_pwd_password');
            $sessionArray = $this->session->userdata('ci_seesion_key');
            $this->auth->setUserID($sessionArray['user_id']);
            $this->auth->setPassword($change_pwd_password);
            $status = $this->auth->changePassword();
            if ($status == TRUE) {
                redirect('profile');
            }
        }
    }

    //action forgot password method
    public function actionForgotPassword() {
        $this->form_validation->set_rules('forgot_email', 'Your Email', 'trim|required|valid_email');
        if ($this->form_validation->run() == FALSE) {
            //Field validation failed.  User redirected to Forgot Password page
            $this->forgotpassword();
        } else {
            $login = site_url() . 'signin';
            $email = $this->input->post('forgot_email');
            $this->auth->setEmail($email);
            $pass = $this->generateRandomPassword(8);
            $this->auth->setPassword($pass);
            $status = $this->auth->updateForgotPassword();
            if ($status == TRUE) {
                $this->load->library('encryption');
                $mailData = array('topMsg' => 'Hi', 'bodyMsg' => 'Your password has been reset successfully!.', 'thanksMsg' => SITE_DELIMETER_MSG, 'delimeter' => SITE_DELIMETER, 'loginLink' => $login, 'pwd' => $pass, 'username' => $email);
                $this->mail->setMailTo($email);
                $this->mail->setMailFrom(MAIL_FROM);
                $this->mail->setMailSubject('Forgot Password!');
                $this->mail->setMailContent($mailData);
                $this->mail->setTemplateName('sendpwd');
                $this->mail->setTemplatePath('mailTemplate/');
                $chkStatus = $this->mail->sendMail(MAILING_SERVICE_PROVIDER);
                if ($chkStatus === TRUE) {
                    redirect('forgotpwd?msg=2');
                } else {
                    redirect('forgotpwd?msg=1');
                }
            } else {
                redirect('forgotpwd?msg=1');
            }
        }
    }

    //generate random password
    public function generateRandomPassword($length = 10) {
        $alphabets = range('a', 'z');
        $numbers = range('0', '9');
        $final_array = array_merge($alphabets, $numbers);
        $password = '';
        while ($length--) {
            $key = array_rand($final_array);
            $password .= $final_array[$key];
        }
        return $password;
    }

    //logout method
    public function logout() {
        $this->session->unset_userdata('ci_seesion_key');
        $this->session->unset_userdata('ci_session_key_generate');
        $this->session->sess_destroy();
        $this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
        $this->output->set_header("Pragma: no-cache");
        redirect('signin');
    }

}

?>