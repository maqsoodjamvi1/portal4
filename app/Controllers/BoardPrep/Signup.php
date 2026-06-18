<?php

namespace App\Controllers\BoardPrep;

use App\Libraries\BoardPrepPlatformService;
use App\Libraries\BoardPrepProvisioningService;

class Signup extends BoardPrepBaseController
{
    public function index()
    {
        if (board_prep_auth()) {
            return redirect()->to(board_prep_url('dashboard'));
        }

        $platform = new BoardPrepPlatformService();

        return view('board_prep/signup', [
            'productName' => board_prep_product_name(),
            'gradeLabels' => $this->boardPrepConfig()->gradeLabels,
            'boards'      => $platform->listBoardPublishers(),
            'errors'      => session()->getFlashdata('errors') ?? [],
            'error'       => session()->getFlashdata('error'),
        ]);
    }

    public function submit()
    {
        if (! $this->request->is('post')) {
            return redirect()->to(board_prep_url('signup'));
        }

        if (board_prep_auth()) {
            return redirect()->to(board_prep_url('dashboard'));
        }

        if (! $this->checkSignupRateLimit()) {
            return redirect()->back()->withInput()->with('error', 'Too many signup attempts. Please wait an hour and try again.');
        }

        if (! $this->verifyCaptcha()) {
            return redirect()->back()->withInput()->with('error', 'Incorrect or expired security code. Enter the code from the image.');
        }

        $validation = service('validation');
        $validation->setRules([
            'display_name'         => 'required|min_length[2]|max_length[100]',
            'username'             => 'required|min_length[3]|max_length[32]|regex_match[/^[a-zA-Z0-9._-]+$/]',
            'password'             => 'required|min_length[8]|max_length[64]',
            'repassword'           => 'required|matches[password]',
            'father_name'          => 'required|min_length[2]|max_length[100]',
            'grade_level'          => 'required|in_list[ssc1,ssc2,hssc1,hssc2]',
            'board_publisher_id'   => 'required|integer',
        ], [
            'display_name' => ['required' => 'Your name is required.'],
            'username'     => ['required' => 'Username is required.'],
            'password'     => [
                'required'   => 'Password is required.',
                'min_length' => 'Password must be at least 8 characters.',
            ],
            'repassword'   => ['matches' => 'Passwords do not match.'],
            'father_name'  => ['required' => 'Father name is required.'],
            'grade_level'  => ['required' => 'Please select your class.'],
            'board_publisher_id' => ['required' => 'Please select your board.'],
        ]);

        if (! $validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $service = new BoardPrepProvisioningService();
        $result  = $service->provision([
            'display_name'       => $this->request->getPost('display_name'),
            'username'           => strtolower(trim((string) $this->request->getPost('username'))),
            'password'           => (string) $this->request->getPost('password'),
            'father_name'        => $this->request->getPost('father_name'),
            'grade_level'        => $this->request->getPost('grade_level'),
            'board_publisher_id' => (int) $this->request->getPost('board_publisher_id'),
        ]);

        if (! ($result['success'] ?? false)) {
            return redirect()->back()->withInput()->with('error', $result['msg'] ?? 'Signup failed.');
        }

        $user = $this->db->table('board_prep_users bpu')
            ->select('bpu.*, bp.name AS board_name')
            ->join('qb_board_publishers bp', 'bp.id = bpu.board_publisher_id', 'left')
            ->where('bpu.id', (int) $result['user_id'])
            ->get()
            ->getRow();

        if ($user) {
            $service->establishSession($user);
        }

        return redirect()->to(board_prep_url('dashboard'))
            ->with('success', 'Welcome! Your account is ready. Start practicing quizzes for your board exam.');
    }
}
