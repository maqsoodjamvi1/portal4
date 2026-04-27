<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class BaseController extends Controller
{
    /** @var \CodeIgniter\HTTP\IncomingRequest */
    protected $request;

    protected $helpers      = ['campus',  'url', 'form', 'session', 'server', 'file', 'date', 'auth'];

      
    protected $db;
    protected $cache;
    protected $session;
    protected $email;
    protected $pager;
    protected $validation;
    protected $view;

    protected $useLayout    = true;
    protected $template_data = [];
    protected $user;
    protected $memberCurrentUser;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
         helper('campus');
        helper(['currency']);   // ✅ Added here so it's always available
        // Language
        $this->initializeLanguage();
        $locale = session('language') ?: (service('response')->getCookie('lang')?->getValue() ?: config('App')->defaultLocale);
        $request->setLocale($locale);

        // Core services
        $this->cache      = \Config\Services::cache();
        $this->db         = \Config\Database::connect();
        $this->session    = \Config\Services::session();
        $this->email      = \Config\Services::email();
        $this->pager      = \Config\Services::pager();
        $this->validation = \Config\Services::validation();

        // Helpers (already listed in $helpers, but safe to ensure)
        helper($this->helpers);

        $this->template_data['cdn_server'] = base_url();

        if ($this->useLayout) {
            $this->view = \Config\Services::renderer();
            $this->view->setVar('schoolinfo', getSchoolInfo());
            $this->view->setVar('user', getLoginUser());

            // Pre-render header (now includes badge counts, no raw SQL in the view)
            $this->view->setVar('headerData', $this->headerData());
        }
    }


/**
 * Get campus expiry information
 * @param int $campusId
 * @return array ['expiry_date', 'days_left', 'status', 'message', 'css_class']
 */
protected function getCampusExpiryInfo($campusId)
{
    $db = \Config\Database::connect();
    
    // Get the latest active bill (status = 1) for this campus
    $latestBill = $db->table('campus_bills')
        ->select('campus_expiry, bill_id, plan_id, bill_issue_date')
        ->where('campus_id', $campusId)
        ->where('status', 1)
        ->orderBy('bill_id', 'DESC')
        ->get()
        ->getRow();
    
    if (!$latestBill || empty($latestBill->campus_expiry)) {
        return [
            'expiry_date' => null,
            'days_left' => null,
            'status' => 'unknown',
            'message' => 'No active subscription',
            'css_class' => 'text-muted',
            'icon' => 'fa-question-circle',
            'badge_class' => 'bg-secondary'
        ];
    }
    
    $expiryDate = new \DateTime($latestBill->campus_expiry);
    $today = new \DateTime();
    $daysLeft = $today->diff($expiryDate)->days;
    
    // If expiry date is in the past, show negative
    if ($expiryDate < $today) {
        $daysLeft = -$daysLeft;
    }
    
    // Determine status based on days left
    if ($daysLeft < 0) {
        return [
            'expiry_date' => $latestBill->campus_expiry,
            'days_left' => $daysLeft,
            'status' => 'expired',
            'message' => 'EXPIRED!',
            'css_class' => 'text-danger font-weight-bold',
            'icon' => 'fa-exclamation-triangle',
            'badge_class' => 'bg-danger',
            'details' => 'Subscription expired ' . abs($daysLeft) . ' days ago'
        ];
    } elseif ($daysLeft <= 30) {
        return [
            'expiry_date' => $latestBill->campus_expiry,
            'days_left' => $daysLeft,
            'status' => 'critical',
            'message' => 'Expires in ' . $daysLeft . ' days!',
            'css_class' => 'text-danger font-weight-bold animated pulse',
            'icon' => 'fa-exclamation-circle',
            'badge_class' => 'bg-danger',
            'details' => 'Subscription expires in ' . $daysLeft . ' days. Please renew soon!'
        ];
    } elseif ($daysLeft <= 90) {
        return [
            'expiry_date' => $latestBill->campus_expiry,
            'days_left' => $daysLeft,
            'status' => 'warning',
            'message' => 'Expires in ' . $daysLeft . ' days',
            'css_class' => 'text-warning font-weight-bold',
            'icon' => 'fa-clock',
            'badge_class' => 'bg-warning',
            'details' => 'Subscription expires in ' . $daysLeft . ' days'
        ];
    } else {
        return [
            'expiry_date' => $latestBill->campus_expiry,
            'days_left' => $daysLeft,
            'status' => 'good',
            'message' => 'Expires: ' . date('d M Y', strtotime($latestBill->campus_expiry)),
            'css_class' => 'text-success',
            'icon' => 'fa-check-circle',
            'badge_class' => 'bg-success',
            'details' => 'Subscription active until ' . date('d M Y', strtotime($latestBill->campus_expiry))
        ];
    }
}


    protected function initializeLanguage(): void
    {
        $supported = ['en', 'ur', 'ar'];
        $language  = session('language');

        if (!$language && $this->request->getCookie('preferred_language')) {
            $language = $this->request->getCookie('preferred_language');
        }
        if (!$language) {
            $browser = substr($this->request->getLocale() ?? 'en', 0, 2);
            $language = in_array($browser, $supported, true) ? $browser : 'en';
        }
        if (!in_array($language, $supported, true)) {
            $language = 'en';
        }
        session()->set('language', $language);
        service('request')->setLocale($language);
    }

    public function index()
    {
        $this->useLayout = false;
        return view('admin/login');
    }

    /**
     * Build data for the global header and render it.
     * Passes outstanding counts so the view does not query directly.
     */
    public function headerData(): string
    {
        $db       = $this->db;
        $session  = $this->session;
        $school   = getSchoolInfo();

        $curr_session_id = $session->get('member_sessionid');
        $curr_campus_id  = $session->get('member_campusid');

        $academic_sessions = [];
        $campuses          = [];

        if ($school && $school->system_id) {
            $academic_sessions = $db->table('academic_session')
                ->where('system_id', $school->system_id)
                ->get()->getResult();

            $campuses = $db->table('campus')
                ->where('system_id', $school->system_id)
                ->get()->getResult();
        }

        // New: compute badges
        $unpaidBadge      = $this->countOutstandingForCurrentUser(); // unpaid + discounted
        $discountedBadge  = $this->countDiscountedForCurrentUser();  // only discounted

        return view('layouts/header', [
            'schoolinfo'        => $school,
            'curr_session_id'   => $curr_session_id,
            'curr_campus_id'    => $curr_campus_id,
            'academic_sessions' => $academic_sessions,
            'campuses'          => $campuses,
            'unpaidBadge'       => $unpaidBadge,
            'discountedBadge'   => $discountedBadge,
        ]);
    }

    /**
     * Count unpaid + discounted fee_chalan for the current parent/student scope.
     */
    protected function countOutstandingForCurrentUser(): int
    {
        $auth      = session('auth') ?? [];
        $parentId  = (int) ($auth['parent_id'] ?? 0);
        $studentId = (int) (session('active_student_id') ?? 0);

        if (!$parentId && !$studentId) {
            return 0;
        }

        $tbl = 'fee_chalan';
        if (!$this->tableHas($tbl, 'status')) {
            return 0;
        }

        $qb = $this->db->table($tbl)->select('COUNT(*) AS c')
            ->whereIn('status', ['unpaid', 'discounted']);

        if ($parentId && $this->tableHas($tbl, 'parent_id')) {
            $qb->where('parent_id', $parentId);
        } elseif ($studentId && $this->tableHas($tbl, 'student_id')) {
            $qb->where('student_id', $studentId);
        }

        $row = $qb->get()->getRowArray();
        return (int)($row['c'] ?? 0);
    }

    /**
     * Count discounted only (if you want a separate badge).
     */
    protected function countDiscountedForCurrentUser(): int
    {
        $auth      = session('auth') ?? [];
        $parentId  = (int) ($auth['parent_id'] ?? 0);
        $studentId = (int) (session('active_student_id') ?? 0);

        if (!$parentId && !$studentId) {
            return 0;
        }

        $tbl = 'fee_chalan';
        if (!$this->tableHas($tbl, 'status')) {
            return 0;
        }

        $qb = $this->db->table($tbl)->select('COUNT(*) AS c')
            ->where('status', 'discounted');

        if ($parentId && $this->tableHas($tbl, 'parent_id')) {
            $qb->where('parent_id', $parentId);
        } elseif ($studentId && $this->tableHas($tbl, 'student_id')) {
            $qb->where('student_id', $studentId);
        }

        $row = $qb->get()->getRowArray();
        return (int)($row['c'] ?? 0);
    }

    /**
     * Safe table/column existence check with cache.
     */
    protected function tableHas(string $table, string $column): bool
    {
        static $cache = [];
        $key = strtolower($table);

        try {
            if (!isset($cache[$key])) {
                $fields = $this->db->getFieldNames($table);
                $cache[$key] = array_map('strtolower', $fields);
            }
        } catch (\Throwable $e) {
            return false;
        }

        return in_array(strtolower($column), $cache[$key], true);
    }
}
