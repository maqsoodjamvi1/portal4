<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Libraries\MemberCurrentUser;

class BaseController extends Controller
{
    protected $request;
    protected $helpers = ['url', 'form', 'session','server'];
    protected $db;
    protected $useLayout = true;
    protected $cache;
    protected $template_data = [];
    protected $user;
    protected $memberCurrentUser;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->initializeLanguage();

        $locale = session('language') ?: (service('response')->getCookie('lang')?->getValue() ?: config('App')->defaultLocale);
        $request->setLocale($locale);

        $this->cache = \Config\Services::cache();
        $this->db = \Config\Database::connect();
        $this->session = \Config\Services::session();
        $this->email = \Config\Services::email();
        $this->pager = \Config\Services::pager();
        $this->validation = \Config\Services::validation();
        helper('auth');
        // Load helpers globally
        helper(['url', 'file', 'form', 'date','server']);
        

        $this->template_data['cdn_server'] = base_url();

        // Make schoolinfo available globally
        
        if ($this->useLayout) {
            $this->view = \Config\Services::renderer();
            $this->view->setVar('schoolinfo', getSchoolInfo());
            $this->view->setVar('user', getLoginUser());
            $this->view->setVar('headerData', $this->headerData());
        }

    }


    protected function initializeLanguage()
    {
        $supported_languages = ['en', 'ur', 'ar'];
        $default_language = 'en';
        
        // Get language from session, cookie, or browser
        $language = session('language');
        
        if (!$language && $this->request->getCookie('preferred_language')) {
            $language = $this->request->getCookie('preferred_language');
        }
        
        if (!$language) {
            // Detect browser language
            $browser_lang = $this->request->getLocale();
            $browser_lang = substr($browser_lang, 0, 2);
            if (in_array($browser_lang, $supported_languages)) {
                $language = $browser_lang;
            }
        }
        
        // Set default if not valid
        if (!$language || !in_array($language, $supported_languages)) {
            $language = $default_language;
        }
        
        // Set session and service
        session()->set('language', $language);
        service('request')->setLocale($language);
    }
    function index(){
        $this->useLayout = false; // ❌ disables layout loading
        return view('admin/login');
    }

    public function headerData()
    {
        $db = \Config\Database::connect();
        $session = \Config\Services::session();

        $schoolinfo = getSchoolInfo();
        $curr_session_id = $session->get('member_sessionid');
        $curr_campus_id = $session->get('member_campusid');

        $academic_sessions = [];
        $campuses = [];

        if ($schoolinfo && $schoolinfo->system_id) {
            $academic_sessions = $db->table('academic_session')
                ->where('system_id', $schoolinfo->system_id)
                ->get()
                ->getResult();

            $campuses = $db->table('campus')
                ->where('system_id', $schoolinfo->system_id)
                ->get()
                ->getResult();
        }

        return view('layouts/header', [ 
            'schoolinfo' => $schoolinfo,
            'curr_session_id' => $curr_session_id,
            'curr_campus_id' => $curr_campus_id,
            'academic_sessions' => $academic_sessions,
            'campuses' => $campuses
        ]);
    }


}
