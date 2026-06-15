<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\AdminControllerPermissions;

/**
 * Enforces admin session + RBAC for all /admin routes.
 */
class AdminPermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $path = trim($request->getUri()->getPath(), '/');

        if (! str_starts_with($path, 'admin')) {
            return null;
        }

        if ($this->isLoginPath($path)) {
            return null;
        }

        $router     = service('router');
        $controller = $router->controllerName() ?? '';

        if ($controller === 'AdminDispatcher' || $controller === '\\App\\Controllers\\AdminDispatcher') {
            return $this->guardLegacyDispatcher($request);
        }

        if ($controller !== '' && str_contains($controller, '\\Admin\\')) {
            return $this->guardController(class_basename($controller), $request);
        }

        if ($controller !== '' && str_contains($controller, 'Controllers\\Admin\\')) {
            return $this->guardController(class_basename($controller), $request);
        }

        // admin/* routes resolved without namespace in router name
        if ($controller !== '' && ! str_contains($controller, '\\')) {
            return $this->guardController($controller, $request);
        }

        return $this->guardAuthenticated($request);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    private function isLoginPath(string $path): bool
    {
        return (bool) preg_match('#^admin/login(/|$)#', $path);
    }

    private function guardLegacyDispatcher(RequestInterface $request): ?ResponseInterface
    {
        $deny = $this->guardAuthenticated($request);
        if ($deny !== null) {
            return $deny;
        }

        $legacyName = (string) ($request->getGet('c') ?? '');
        if ($legacyName === '') {
            return null;
        }

        $class = AdminControllerPermissions::resolveLegacyControllerClass($legacyName);
        if ($class === null) {
            log_message('warning', 'AdminPermissionFilter: unmapped legacy controller {c}', ['c' => $legacyName]);

            return redirect()->to(base_url('admin/dashboard'))
                ->with('error', 'This admin action is not available.');
        }

        $method = strtolower((string) ($request->getGet('m') ?? 'index'));

        return $this->guardController(class_basename($class), $request, $method);
    }

    private function guardController(string $shortName, RequestInterface $request, ?string $method = null): ?ResponseInterface
    {
        if (AdminControllerPermissions::isPublic($shortName)) {
            return null;
        }

        $deny = $this->guardAuthenticated($request);
        if ($deny !== null) {
            return $deny;
        }

        if (AdminControllerPermissions::isSessionOnly($shortName)) {
            return null;
        }

        if ($method === null) {
            $router = service('router');
            $method = strtolower((string) ($router->methodName() ?? 'index'));
        }

        $perms = AdminControllerPermissions::resolveKeys($shortName, $method);
        if ($perms === []) {
            return $this->guardQuizzesElearningRole($shortName, $method, $request);
        }

        $json = $this->wantsJson($request);
        check_any_permission($perms, $json);

        return $this->guardQuizzesElearningRole($shortName, $method, $request);
    }

    /**
     * Block Super Admin–only areas (Quizzes tooling, Billing & Admin) for other roles.
     */
    private function guardQuizzesElearningRole(string $shortName, string $method, RequestInterface $request): ?ResponseInterface
    {
        if (! AdminControllerPermissions::isSuperAdminOnlyController($shortName, $method)) {
            return null;
        }

        helper('role');

        if (userIsSuperAdmin()) {
            return null;
        }

        $menuKey = AdminControllerPermissions::menuKeyForSuperAdminController($shortName);
        if ($menuKey !== null && $menuKey !== ''
            && \App\Libraries\RoleMenuAccess::isMenuGrantedByAnyRole($menuKey)) {
            return null;
        }

        if (userCanAccessDirectorQuizzesSubmenu()
            && \App\Libraries\RoleMenuAccess::isMenuGrantedByAnyRole('quizzes')) {
            return null;
        }

        if ($shortName === 'VocabBank' && in_array($method, AdminControllerPermissions::$vocabBankDirectorMethods, true)) {
            if (userHasRoleName('Principal')) {
                return null;
            }
        }

        $message = 'This area is only available to Super Admin.';

        if ($this->wantsJson($request)) {
            return service('response')
                ->setStatusCode(403)
                ->setJSON([
                    'success' => false,
                    'msg'     => $message,
                ]);
        }

        return redirect()->to(base_url('admin/dashboard'))
            ->with('error', $message);
    }

    private function guardAuthenticated(RequestInterface $request): ?ResponseInterface
    {
        $session = session();
        if ($session->get('member_userid') || $session->get('IsAuthorized')) {
            return null;
        }

        helper('server');

        return admin_auth_failure_response($request);
    }

    private function wantsJson(RequestInterface $request): bool
    {
        helper('server');

        return admin_request_wants_json($request);
    }
}
