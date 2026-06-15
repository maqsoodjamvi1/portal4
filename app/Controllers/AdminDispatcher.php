<?php

namespace App\Controllers;

use CodeIgniter\Controller;

/**
 * @deprecated Legacy ?c=Controller&m=action routing. Prefer explicit routes in Config/Routes.php.
 */
class AdminDispatcher extends Controller
{
    public function route()
    {
        $request = service('request');
        $session = session();

        $controllerName = $request->getGet('c');
        $methodName = $request->getGet('m') ?? 'index';

        log_message('notice', 'Deprecated AdminDispatcher: c={c} m={m}', [
            'c' => (string) $controllerName,
            'm' => (string) $methodName,
        ]);

        $isAuthed = (bool) ($session->get('IsAuthorized') || $session->get('member_userid'));

        if (! $isAuthed) {
            helper('server');

            return admin_auth_failure_response($request);
        }

        // Collect optional params: param1, param2, ...
        $params = [];
        foreach ($request->getGet() as $key => $value) {
            if (preg_match('/^param\d+$/', $key)) {
                $params[] = $value;
            }
        }

        if (! $controllerName) {
            return redirect()->to(base_url('admin/dashboard'));
        }

        $response = \App\Libraries\AdminLegacyDispatch::run($controllerName, $methodName, $request);

        if ($response === null) {
            return $this->response
                ->setStatusCode(404)
                ->setBody("Controller not found for: {$controllerName}");
        }

        if ($response instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $response;
        }

        if (is_string($response) && ! $request->isAJAX()) {
            return view('admin/layout', ['content' => $response]);
        }

        if (! empty($params)) {
            log_message('warning', 'AdminDispatcher: paramN not supported for {c}', ['c' => $controllerName]);
        }

        return $response;
    }
}
