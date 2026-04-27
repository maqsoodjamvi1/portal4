<?php

namespace App\Libraries\Restserver\Traits;

use CodeIgniter\HTTP\IncomingRequest;
use Config\Services;

trait RestSecurity
{
    protected function _check_blacklist_auth()
    {
        $blacklist = explode(',', $this->config->restIpBlacklist);
        if (in_array($this->request->getIPAddress(), $blacklist)) {
            return $this->failUnauthorized('IP blacklisted');
        }
    }

    protected function _check_whitelist_auth()
    {
        $whitelist = explode(',', $this->config->restIpWhitelist);
        $ip = $this->request->getIPAddress();
        $whitelist[] = '127.0.0.1';
        $whitelist[] = '0.0.0.0';

        if (!in_array($ip, $whitelist)) {
            return $this->failUnauthorized('IP not whitelisted');
        }
    }

    protected function _force_login(string $nonce = '')
    {
        $rest_auth = $this->config->restAuth;
        $realm = $this->config->restRealm;

        if ($rest_auth === 'basic') {
            header('WWW-Authenticate: Basic realm="' . $realm . '"');
        } elseif ($rest_auth === 'digest') {
            header('WWW-Authenticate: Digest realm="' . $realm . '", qop="auth", nonce="' . $nonce . '", opaque="' . md5($realm) . '"');
        }

        $this->is_valid_request = false;
        return $this->failUnauthorized('Unauthorized access');
    }

    protected function _check_login($username = null, $password = false)
    {
        $valid_logins = $this->config->restValidLogins;
        $auth_source = $this->config->authSource;
        $rest_auth = $this->config->restAuth;

        if (!$username) return false;

        if (!$auth_source && $rest_auth === 'digest') {
            return md5("$username:{$this->config->restRealm}:{$valid_logins[$username]}");
        }

        if ($password === false) return false;

        if ($auth_source === 'ldap') {
            return $this->_perform_ldap_auth($username, $password);
        }

        if ($auth_source === 'library') {
            return $this->_perform_library_auth($username, $password);
        }

        return isset($valid_logins[$username]) && $valid_logins[$username] === $password;
    }
}
