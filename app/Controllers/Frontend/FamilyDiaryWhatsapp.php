<?php
namespace App\Controllers\Frontend;


use App\Controllers\BaseController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;


class FamilyDiaryWhatsapp extends \App\Controllers\BaseController
{
    protected $db;

    // Load helpers automatically
    protected $helpers = ['url', 'form'];

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);

        // DB connect (if your BaseController didn't already)
        $this->db = $this->db ?? \Config\Database::connect();

        // Permission gate (same helper you used in CI3)
        if (function_exists('check_permission')) {
            check_permission('admin-classdairy');
        }
    }

    /**
     * Page: shows the Family Diary WhatsApp table & filters
     */
    public function index()
    {
        // Same role logic you had
        $currentrole = function_exists('currentUserRoles') ? currentUserRoles() : [];
        if (is_array($currentrole) && in_array(5, $currentrole, true)) {
            $sectionsclassinfo = function_exists('teacherSubjectSections') ? teacherSubjectSections() : [];
        } else {
            $sectionsclassinfo = function_exists('userClassSections') ? userClassSections() : [];
        }

        return view('Frontend/family_diary_whatsapp', [
            'sectionsclassinfo' => $sectionsclassinfo,
        ]);
    }

    /**
     * DataTables JSON endpoint (POST)
     */
    public function data()
    {
        if ($this->request->getMethod(true) !== 'POST') {
            return $this->response->setStatusCode(405)->setJSON([
                'error' => 'Method Not Allowed'
            ]);
        }

        $draw      = (int) $this->request->getPost('draw');
        $length    = (int) $this->request->getPost('length') ?: 10;
        $start     = (int) $this->request->getPost('start')  ?: 0;
        $searchArr = (array) ($this->request->getPost('search') ?? []);
        $keyword   = trim((string) ($searchArr['value'] ?? ''));

        $campusid  = (int) (session('member_campusid')  ?? 0);
        $sessionid = (int) (session('member_sessionid') ?? 0);

        // For building parent URLs
        $schoolinfo = function_exists('getSchoolInfo') ? getSchoolInfo() : null;
        $domain     = $schoolinfo->domain ?? 'school';

        // ---------- COUNT ----------
        $countBuilder = $this->db->table('parents A')
            ->select('COUNT(A.parent_id) AS ccount', false)
            ->where('A.campus_id', $campusid)
            ->where("A.parent_id IN (SELECT parent_id FROM students WHERE status = 1)", null, false);

        if ($keyword !== '') {
            $countBuilder->where('A.f_name', $keyword);
        }

        $countRow = $countBuilder->get()->getRow();
        $recordsTotal = (int) ($countRow->ccount ?? 0);

        // ---------- DATA ----------
        $dataBuilder = $this->db->table('parents A')
            ->select('A.*')
            ->where('A.campus_id', $campusid)
            ->where("A.parent_id IN (SELECT parent_id FROM students WHERE status = 1)", null, false);

        if ($keyword !== '') {
            $dataBuilder->where('A.f_name', $keyword);
        }

        $results = $dataBuilder
            ->orderBy('A.parent_id', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResult();

        $data = [];
        $nCount = $start + 1;

        foreach ($results as $row) {
            // Get all active students under this parent
            $students = $this->db->table('students')
                ->select('first_name,last_name')
                ->where('status', 1)
                ->where('parent_id', (int) $row->parent_id)
                ->get()->getResult();

            $names = [];
            foreach ($students as $st) {
                $names[] = trim(($st->first_name ?? '') . ' ' . ($st->last_name ?? ''));
            }
            $strStudents = implode(', ', array_filter($names));

            $f_name           = (string) ($row->f_name ?? '');
            $father_contact   = (string) ($row->father_contact ?? '');
            $mother_contact   = (string) ($row->mother_contact ?? '');
            $whatsapp_raw     = (string) ($row->whatsapp ?? '');
            $whatsapp_contact = preg_replace('/\D+/', '', $whatsapp_raw); // keep digits only for wa.me

            // Link to family diary detail (original pattern kept)
            $detailUrl = 'https://' . $domain . '.timesoftsol.com/students_diary_detail/?parent_id='
                       . (int)$row->parent_id . '&campus_id=' . $campusid;

            $waLink = $whatsapp_contact
                ? '<a href="https://wa.me/' . $whatsapp_contact . '?text=' . rawurlencode($detailUrl) . '" target="_blank" rel="noopener">'
                    . esc($whatsapp_raw) .
                  '</a>'
                : '-';

            $data[] = [
                'id'         => $nCount,
                'f_name'     => esc($f_name) . '<br><small>' . esc($strStudents) . '</small>',
                'f_contacts' => esc($father_contact),
                'w_contacts' => $waLink,
                'm_contacts' => esc($mother_contact),
            ];

            $nCount++;
        }

        // DataTables JSON
        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsTotal, // same as total (we only applied simple filter)
            'data'            => $data,
        ]);
    }
}
