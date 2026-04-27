<?php

namespace App\Libraries\Restserver\Traits;

trait RestParser
{
    protected function _detect_method()
    {
        $method = $this->request->getMethod(true);
        if (!in_array(strtolower($method), $this->allowed_http_methods)) {
            $method = 'get';
        }
        $this->request->method = strtolower($method);
    }

    protected function _detect_output_format()
    {
        $format = $this->request->getGet('format');
        if ($format && array_key_exists($format, $this->_supported_formats)) {
            return $format;
        }
        return $this->config->restDefaultFormat ?? 'json';
    }

    protected function _detect_lang()
    {
        return $this->request->getServer('HTTP_ACCEPT_LANGUAGE') ?? 'en';
    }

    protected function _parse_request()
    {
        $method = $this->request->getMethod(true);

        $this->_get_args     = $this->request->getGet() ?? [];
        $this->_post_args    = $this->request->getPost() ?? [];
        $this->_query_args   = $this->_get_args;

        if ($method === 'PUT' || $method === 'PATCH' || $method === 'DELETE') {
            $inputStream = $this->request->getBody();
            $parsed      = json_decode($inputStream, true);
            if ($method === 'PUT') {
                $this->_put_args = $parsed ?? [];
            } elseif ($method === 'PATCH') {
                $this->_patch_args = $parsed ?? [];
            } elseif ($method === 'DELETE') {
                $this->_delete_args = $parsed ?? [];
            }
        }

        $this->_args = array_merge(
            $this->_get_args,
            $this->_post_args,
            $this->_put_args,
            $this->_delete_args,
            $this->_patch_args
        );
    }
}
