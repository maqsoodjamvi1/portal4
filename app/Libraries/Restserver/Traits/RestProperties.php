<?php

namespace App\Libraries\Restserver\Traits;

trait RestProperties
{
    protected $rest_format = null;
    protected $methods = [];
    protected $allowed_http_methods = ['get', 'delete', 'post', 'put', 'options', 'patch', 'head'];

    protected $request;
    protected $response;
    protected $rest;

    protected $_get_args = [];
    protected $_post_args = [];
    protected $_put_args = [];
    protected $_delete_args = [];
    protected $_patch_args = [];
    protected $_head_args = [];
    protected $_options_args = [];
    protected $_query_args = [];
    protected $_args = [];

    protected $_insert_id = '';
    protected $_allow = true;
    protected $_user_ldap_dn = '';
    protected $_start_rtime;
    protected $_end_rtime;

    protected $_supported_formats = [
        'json' => 'application/json',
        'array' => 'application/json',
        'csv' => 'application/csv',
        'html' => 'text/html',
        'jsonp' => 'application/javascript',
        'php' => 'text/plain',
        'serialized' => 'application/vnd.php.serialized',
        'xml' => 'application/xml',
    ];

    protected $_apiuser;
    protected $check_cors = null;
    protected $_enable_xss = false;

    protected $is_valid_request = true;

    protected $responseFormat;
    protected $responseLang;
}
