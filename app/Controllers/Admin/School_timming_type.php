<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * School timing types are no longer managed separately — redirect to School Timing.
 */
class School_timming_type extends BaseController
{
    public function __construct()
    {
        helper(['form', 'url']);
    }

    private function redirectToSchoolTiming(): void
    {
        redirect()->to(base_url('admin/school_timing/add'))->send();
        exit;
    }

    public function index()
    {
        $this->redirectToSchoolTiming();
    }

    public function add()
    {
        $this->redirectToSchoolTiming();
    }

    public function edit()
    {
        $this->redirectToSchoolTiming();
    }

    public function data()
    {
        $this->redirectToSchoolTiming();
    }

    public function save()
    {
        return $this->response->setJSON([
            'success' => false,
            'msg'     => 'School timing types are no longer used. Set timings on School Timing instead.',
        ]);
    }

    public function getDateRange()
    {
        $this->redirectToSchoolTiming();
    }

    public function delete()
    {
        $this->redirectToSchoolTiming();
    }
}
