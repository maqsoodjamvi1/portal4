<?php



namespace App\Controllers\Admin;



use App\Controllers\BaseController;

use App\Libraries\QbBoardPublisherService;

use CodeIgniter\HTTP\ResponseInterface;



class QbBoardPublishers extends BaseController

{

    protected $db;

    protected $session;

    protected QbBoardPublisherService $boardService;



    public function __construct()

    {

        $this->db           = db_connect();

        $this->session      = session();

        $this->boardService = new QbBoardPublisherService($this->db);

    }



    public function index()

    {

        $rows = $this->boardService->listGlobal(false);



        return view('admin/board_publishers', [

            'rows' => $rows,

        ]);

    }



    public function listJson(): ResponseInterface

    {

        return $this->response->setJSON([

            'status' => 'ok',

            'rows'   => $this->boardService->listGlobal(true),

        ]);

    }



    public function save(): ResponseInterface

    {

        if (! $this->request->isAJAX()) {

            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'msg' => 'Invalid request']);

        }



        $payload = $this->request->getPost();

        if (! is_array($payload) || $payload === []) {

            $payload = $this->request->getJSON(true);

        }

        if (! is_array($payload)) {

            $payload = [];

        }



        $id         = (int) ($payload['id'] ?? 0);

        $removeLogo = (int) ($payload['remove_logo'] ?? 0) === 1;

        $logoFile   = $this->request->getFile('logo');



        $result = $this->boardService->saveEntry(

            $payload,

            $id > 0 ? $id : null,

            $logoFile,

            $removeLogo

        );



        if (! ($result['ok'] ?? false)) {

            return $this->response->setJSON([

                'status' => 'error',

                'msg'    => $result['msg'] ?? 'Could not save.',

            ]);

        }



        return $this->response->setJSON([

            'status'    => 'ok',

            'msg'       => $id > 0 ? 'Updated successfully.' : 'Added successfully.',

            'id'        => (int) ($result['id'] ?? 0),

            'csrf_hash' => csrf_hash(),

        ]);

    }



    public function delete(int $id = 0): ResponseInterface

    {

        if (! $this->request->isAJAX()) {

            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'msg' => 'Invalid request']);

        }



        $id = $id > 0 ? $id : (int) $this->request->getPost('id');

        if ($id <= 0) {

            return $this->response->setJSON(['status' => 'error', 'msg' => 'Invalid ID.']);

        }



        $ok = $this->boardService->deleteEntry($id);



        return $this->response->setJSON([

            'status'    => $ok ? 'ok' : 'error',

            'msg'       => $ok ? 'Deleted successfully.' : 'Could not delete.',

            'csrf_hash' => csrf_hash(),

        ]);

    }

}
