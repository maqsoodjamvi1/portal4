<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Legacy quiz-ai UI — redirects to Quiz Builder.
 */
class QuizAi extends BaseController
{
    private function redirectToQuizBuilder()
    {
        return redirect()->to(base_url('admin/quiz'));
    }

    public function index()
    {
        return $this->redirectToQuizBuilder();
    }

    public function generate()
    {
        return $this->response->setJSON([
            'error' => 'This endpoint is retired. Use admin/quiz instead.',
        ]);
    }

    public function save()
    {
        return $this->redirectToQuizBuilder();
    }
}
