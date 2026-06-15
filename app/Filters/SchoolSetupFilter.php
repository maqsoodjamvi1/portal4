<?php



namespace App\Filters;



use App\Libraries\SchoolSetupProgress;

use CodeIgniter\Filters\FilterInterface;

use CodeIgniter\HTTP\RequestInterface;

use CodeIgniter\HTTP\ResponseInterface;



/**

 * Redirect school admins to basic setup until calendar, academic, and fee config are complete.

 */

class SchoolSetupFilter implements FilterInterface

{

    /** @var list<string> */

    private array $alwaysAllowedPrefixes = [

        'admin/getting-started',

        'admin/profile-system',

        'admin/profile_system',

    ];



    /** @var list<string> */

    private array $regTextAllowedPrefixes = [

        'admin/profile-system',

        'admin/profile_system',

    ];



    /** @var list<string> */

    private array $calendarPrefixes = [

        'admin/academic-calendar',

    ];



    /** @var list<string> */

    private array $academicPrefixes = [

        'admin/academic-setup',

        'admin/academic-wizard-bootstrap',

    ];



    /** @var list<string> */

    private array $feePrefixes = [

        'admin/fee_setup',

        'admin/fee_type',

        'admin/fee_amount',

        'admin/fee_plan_months',

    ];



    public function before(RequestInterface $request, $arguments = null)

    {

        $path = $this->normalizePath($request);



        if (! str_starts_with($path, 'admin')) {

            return null;

        }



        if ($this->pathMatchesAny($path, ['admin/login', 'admin/logout'])) {

            return null;

        }



        $session = session();

        if (! $session->get('member_userid') && ! $session->get('IsAuthorized')) {

            return null;

        }



        helper('server');

        $schoolinfo = function_exists('getSchoolInfo') ? getSchoolInfo() : null;

        if (! $schoolinfo || empty($schoolinfo->system_id)) {

            return null;

        }



        $systemId = (int) $schoolinfo->system_id;

        $campusId = (int) ($session->get('member_campusid') ?? 0);

        if ($campusId <= 0 && ! empty($schoolinfo->campus_id)) {

            $campusId = (int) $schoolinfo->campus_id;

        }



        $userId = (int) $session->get('member_userid');



        if (SchoolSetupProgress::isTeacher($userId, $campusId)) {

            return null;

        }



        $regText = trim((string) ($session->get('member_reg_text') ?? $schoolinfo->reg_text ?? ''));

        if ($regText === '') {

            if ($this->pathMatchesAny($path, $this->regTextAllowedPrefixes)) {

                return null;

            }



            return $this->redirect($request, base_url('admin/profile-system'), lang('SchoolSetup.redirect_reg_text'));

        }



        if (SchoolSetupProgress::isComplete($systemId, $campusId)) {

            return null;

        }



        $status  = SchoolSetupProgress::getStatus($systemId, $campusId);
        $checks  = SchoolSetupProgress::getChecks($systemId, $campusId);
        $allowed = $this->buildAllowedPrefixes($checks);



        if ($this->pathMatchesAny($path, $allowed)) {

            return null;

        }



        $nextUrl = $status['next_step_url'] ?? base_url('admin/getting-started');

        $message = $this->resolveBlockedMessage($path, $status, $checks);



        return $this->redirect($request, $nextUrl, $message);

    }



    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)

    {

        return null;

    }



    /**

     * @param array<string,bool|string> $checks

     * @return list<string>

     */

    private function buildAllowedPrefixes(array $checks): array

    {

        $allowed = $this->alwaysAllowedPrefixes;



        $allowed = array_merge($allowed, $this->calendarPrefixes);



        if (! empty($checks['calendar'])) {

            $allowed = array_merge($allowed, $this->academicPrefixes);

        }



        if (! empty($checks['academic'])) {

            $allowed = array_merge($allowed, $this->feePrefixes);

        }



        return $allowed;

    }



    /**

     * @param array<string,mixed> $status

     * @param array<string,bool|string> $checks

     */

    private function resolveBlockedMessage(string $path, array $status, array $checks): string

    {

        if ($this->pathMatchesAny($path, $this->academicPrefixes) && empty($checks['calendar'])) {

            return lang('SchoolSetup.redirect_step_locked', [lang('SchoolSetup.step_calendar_title')]);

        }



        if ($this->pathMatchesAny($path, $this->feePrefixes) && empty($checks['academic'])) {

            return lang('SchoolSetup.redirect_step_locked', [lang('SchoolSetup.step_academic_title')]);

        }



        return lang('SchoolSetup.redirect_setup_required');

    }



    private function normalizePath(RequestInterface $request): string

    {

        $path = trim($request->getUri()->getPath(), '/');

        if (str_starts_with($path, 'index.php/')) {

            $path = substr($path, strlen('index.php/'));

        }



        return $path;

    }



    /**

     * @param list<string> $prefixes

     */

    private function pathMatchesAny(string $path, array $prefixes): bool

    {

        foreach ($prefixes as $prefix) {

            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {

                return true;

            }

        }



        return false;

    }



    private function redirect(RequestInterface $request, string $url, string $message): ResponseInterface

    {

        if ($this->wantsJson($request)) {

            return service('response')

                ->setStatusCode(403)

                ->setJSON([

                    'success'  => false,

                    'code'     => 'setup_required',

                    'msg'      => $message,

                    'redirect' => $url,

                ]);

        }



        return redirect()->to($url)->with('setup_required', $message);

    }



    private function wantsJson(RequestInterface $request): bool

    {

        if ($request->isAJAX()) {

            return true;

        }



        $accept = $request->getHeaderLine('Accept');



        return str_contains($accept, 'json') || str_contains($accept, 'application/json');

    }

}
