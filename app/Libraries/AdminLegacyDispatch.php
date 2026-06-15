<?php

namespace App\Libraries;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\AdminControllerPermissions;

/**
 * Resolves and runs legacy Admin controllers (snake_case URLs and MY_Controller).
 */
class AdminLegacyDispatch
{
    /**
     * Map hyphenated admin paths to CI4 controllers (same as Fallback).
     *
     * @var array<string, array{0: class-string, 1: string}>
     */
    public static array $hyphenatedMap = [
        'question-paper'   => [\App\Controllers\Admin\QuestionPaper::class, 'index'],
        'question-bank-ai' => [\App\Controllers\Admin\QuestionBankAi::class, 'index'],
        'math-crossword'   => [\App\Controllers\Admin\MathCrossword::class, 'index'],
        'math-worksheet'   => [\App\Controllers\Admin\MathWorksheet::class, 'index'],
        'word-search'      => [\App\Controllers\Admin\WordSearch::class, 'index'],
    ];

    public static function resolveControllerClass(string $segment): ?string
    {
        $segment = trim($segment);
        if ($segment === '') {
            return null;
        }

        return AdminControllerPermissions::resolveLegacyControllerClass($segment);
    }

    /**
     * @return ResponseInterface|string|null
     */
    public static function run(
        string $controllerSegment,
        string $method = 'index',
        ?RequestInterface $request = null
    ) {
        $request = $request ?? service('request');
        $method  = trim($method) !== '' ? trim($method) : 'index';

        if (isset(self::$hyphenatedMap[$controllerSegment])) {
            [$class, $mappedMethod] = self::$hyphenatedMap[$controllerSegment];
            if (class_exists($class)) {
                return self::invokeCi4Controller($class, $mappedMethod, $request);
            }
        }

        $class = self::resolveControllerClass($controllerSegment);
        if ($class === null) {
            return null;
        }

        if (is_subclass_of($class, \App\Controllers\Admin\MY_Controller::class)) {
            return self::invokeLegacyController($class, $method, $request);
        }

        return self::invokeCi4Controller($class, $method, $request);
    }

    /**
     * @param class-string $class
     * @return ResponseInterface|string|null
     */
    private static function invokeCi4Controller(string $class, string $method, RequestInterface $request)
    {
        $shortName = class_basename($class);
        self::enforcePermissions($shortName, $method, $request);

        $controller = new $class();
        $controller->initController(
            $request,
            service('response'),
            service('logger')
        );

        if (! method_exists($controller, $method)) {
            $method = 'index';
        }

        if (! method_exists($controller, $method)) {
            return null;
        }

        $response = $controller->{$method}();

        return self::normalizeResponse($response, $class, $request);
    }

    /**
     * @param class-string $class
     * @return ResponseInterface|string|null
     */
    private static function invokeLegacyController(string $class, string $method, RequestInterface $request)
    {
        $shortName = class_basename($class);
        self::enforcePermissions($shortName, $method, $request);

        $controller = new $class();
        $controller->initController(
            $request,
            service('response'),
            service('logger')
        );

        if (! method_exists($controller, $method)) {
            $method = 'index';
        }

        if (! method_exists($controller, $method)) {
            return null;
        }

        ob_start();
        $returned = $controller->{$method}();
        $buffered = ob_get_clean();

        if ($returned instanceof ResponseInterface) {
            return $returned;
        }

        if (is_string($returned) && $returned !== '') {
            return $returned;
        }

        if ($buffered !== '') {
            return $buffered;
        }

        return null;
    }

    private static function enforcePermissions(string $shortName, string $method, RequestInterface $request): void
    {
        if (AdminControllerPermissions::isPublic($shortName)
            || AdminControllerPermissions::isSessionOnly($shortName)) {
            return;
        }

        $perms = AdminControllerPermissions::resolveKeys($shortName, $method);
        if ($perms !== []) {
            $json = $request->isAJAX()
                || str_contains($request->getHeaderLine('Accept'), 'json');
            check_any_permission($perms, $json);
        }

        if (AdminControllerPermissions::isSuperAdminOnlyController($shortName, $method)) {
            helper('role');
            if (! userIsSuperAdmin()) {
                $menuKey = AdminControllerPermissions::menuKeyForSuperAdminController($shortName);
                if ($menuKey !== null && $menuKey !== ''
                    && \App\Libraries\RoleMenuAccess::isMenuGrantedByAnyRole($menuKey)) {
                    return;
                }

                if ($shortName === 'VocabBank'
                    && in_array(strtolower($method), AdminControllerPermissions::$vocabBankDirectorMethods, true)
                    && userHasRoleName('Principal')) {
                    return;
                }

                if ($request->isAJAX()) {
                    json_response([
                        'success' => false,
                        'msg'     => 'This area is only available to Super Admin.',
                    ], '', 403);
                }

                redirect()->to(base_url('admin/dashboard'))
                    ->with('error', 'This area is only available to Super Admin.')
                    ->send();
                exit;
            }
        }
    }

    /**
     * @param mixed $response
     * @param class-string $class
     * @return ResponseInterface|string|null
     */
    private static function normalizeResponse($response, string $class, RequestInterface $request)
    {
        if ($response instanceof ResponseInterface) {
            return $response;
        }

        if (is_string($response) && $response !== '' && ! $request->isAJAX()) {
            if (str_starts_with($class, 'App\\Controllers\\Admin\\')) {
                // Modern admin views extend layouts/admin_template and render a full page.
                if (str_contains($response, 'content-wrapper') || str_contains($response, '<!DOCTYPE')) {
                    return $response;
                }

                return view('admin/layout', ['content' => $response]);
            }

            return $response;
        }

        return $response;
    }

}
