<?php



namespace App\Controllers\Admin;



use App\Controllers\BaseController;

use App\Libraries\AdminLegacyDispatch;

use CodeIgniter\HTTP\ResponseInterface;



class Fallback extends BaseController

{

    public function index()

    {

        $session = session();

        if (! $session->get('IsAuthorized') && ! $session->get('member_userid')) {

            return redirect()->to(base_url('admin/login'));

        }



        $segment = (string) (service('uri')->getSegment(2) ?? '');

        $method  = (string) (service('uri')->getSegment(3) ?? 'index');

        if ($method === '') {

            $method = 'index';

        }



        $response = AdminLegacyDispatch::run($segment, $method);



        if ($response === null) {

            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(

                'Admin page not found: ' . ($segment !== '' ? $segment : '(empty)')

            );

        }



        if ($response instanceof ResponseInterface) {

            return $response;

        }



        if (is_string($response)) {

            return $response;

        }



        return '';

    }

}
