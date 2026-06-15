<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Section incharge is managed on Subject Teachers — this module only redirects.
 */
class TeacherSection extends BaseController
{
    public function __construct()
    {
        helper(['form', 'url']);
    }

    private function redirectToTeacherSubjects(): void
    {
        redirect()->to(base_url('admin/teacher_subjects/add'))->send();
        exit;
    }

    public function index()
    {
        $this->redirectToTeacherSubjects();
    }

    public function add()
    {
        $this->redirectToTeacherSubjects();
    }

    public function edit($id = null)
    {
        $this->redirectToTeacherSubjects();
    }

    public function data()
    {
        $this->redirectToTeacherSubjects();
    }

    public function save()
    {
        return $this->response->setJSON([
            'success' => false,
            'msg'     => 'Section incharge is managed on Subject Teachers.',
        ]);
    }

    public function selectteachersection()
    {
        $this->redirectToTeacherSubjects();
    }

    public function delete($id = null)
    {
        $this->redirectToTeacherSubjects();
    }
}
