<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class AdminDispatcher extends Controller
{
    public function route()
    {
        $request = service('request');

        $controllerName = $request->getGet('c');
        $methodName = $request->getGet('m') ?? 'index';

        // Collect optional params: param1, param2, ...
        $params = [];
        foreach ($request->getGet() as $key => $value) {
            if (preg_match('/^param\d+$/', $key)) {
                $params[] = $value;
            }
        }

        if (!$controllerName) {
            return redirect()->to('/admin');
        }

        $controllerClass = 'App\\Controllers\\Admin\\' . ucfirst($controllerName);

        if (!class_exists($controllerClass)) {
            return "?? Controller not found: {$controllerClass}";
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $methodName)) {
            return "?? Method '{$methodName}' not found in {$controllerClass}";
        }

        // Call the method
        $response = call_user_func_array([$controller, $methodName], $params);

        // Automatically wrap with layout if it's an Admin controller and not AJAX
        if (
            is_string($response) &&
            strpos(get_class($controller), 'App\\Controllers\\Admin\\') === 0 &&
            !$request->isAJAX()
        ) {
            return view('admin/layout', ['content' => $response]);
        }

        return $response;
    }
}
