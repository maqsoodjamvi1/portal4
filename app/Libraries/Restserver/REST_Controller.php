<?php

namespace App\Libraries\Restserver;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;
use Config\Services;
use Config\Format;
use stdClass;
use Exception;

abstract class REST_Controller extends Controller
{
    use Traits\RestConstants;
    use Traits\RestProperties;
    use Traits\RestSecurity;
    use Traits\RestParser;
    use Traits\RestMethods;

    public function __construct()
    {
        parent::__construct();

        $this->request = service('request');
        $this->response = service('response');
        $this->config = config('Rest');

        $this->_start_rtime = microtime(true);

        $this->rest = new stdClass();
        $this->rest->db = \Config\Database::connect();

        $this->input = Services::request();
        $this->output = $this->response;
        $this->lang = Services::language();
        $this->session = session();

        $this->_enable_xss = config('App')->CSRFProtection;

        $this->format = new \App\Libraries\Restserver\Format();

        $this->_detect_method();
        $this->_parse_request();

        $this->responseFormat = $this->_detect_output_format();
        $this->responseLang = $this->_detect_lang();

        if ($this->request->getMethod() !== 'options') {
            $this->_validate_request();
        }
    }

    public function _remap($object_called, ...$arguments)
    {
        $method = strtolower($this->request->getMethod());
        $controllerMethod = $object_called . '_' . $method;

        if (!method_exists($this, $controllerMethod)) {
            $controllerMethod = 'index_' . $method;
            array_unshift($arguments, $object_called);
        }

        if (!method_exists($this, $controllerMethod)) {
            return $this->respond(['status' => false, 'message' => 'Unknown method'], self::HTTP_METHOD_NOT_ALLOWED);
        }

        return call_user_func_array([$this, $controllerMethod], $arguments);
    }

    protected function _validate_request()
    {
        // Simplified logic example: API key, IP filter, auth, etc.
        if (isset($this->config->restEnableKeys) && $this->config->restEnableKeys) {
            if (!$this->_detect_api_key()) {
                return $this->failForbidden('Invalid API key.');
            }
        }
    }

    public function response($data = null, int $status = 200)
    {
        if ($this->responseFormat === 'array') {
            return $this->respond($data, $status);
        }

        if (method_exists($this->format, 'to_' . $this->responseFormat)) {
            $formatter = 'to_' . $this->responseFormat;
            $output = $this->format->{$formatter}($data);
        } else {
            $output = json_encode($data);
        }

        return $this->response->setStatusCode($status)->setBody($output)->setHeader('Content-Type', $this->_supported_formats[$this->responseFormat]);
    }

    public function set_response($data = null, int $status = 200)
    {
        return $this->response($data, $status);
    }
}

// Traits would go into separate files under App\Libraries\Restserver\Traits\ to manage modularity.
// Traits include: RestConstants, RestProperties, RestSecurity, RestParser, RestMethods
// You can copy your original logic from CI3 and place it inside respective trait files with CI4 syntax (e.g., service(), config(), etc.)
