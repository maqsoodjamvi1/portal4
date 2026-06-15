<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use stdClass;

class BulkSms extends BaseController
{
    protected $db;

    public function __construct()
    {
        helper(['form', 'url']);
        $this->db = \Config\Database::connect();
        if (function_exists('check_any_permission')) {
            check_any_permission(['admin-bulk-messages', 'admin-enquiry']);
        } else {
            check_permission('admin-enquiry');
        }
    }

    public function index()
    {
        return view('admin/bulk_sms');
    }

    public function data()
    {
        $response = new stdClass();
        $response->draw = $this->request->getPost('draw');
        $keyword = $this->request->getPost('search')['value'] ?? '';

        // Get count
        $builder = $this->db->table('admission_enquiry A')->selectCount('A.enquiry_id', 'ccount');
        if ($keyword) {
            $builder->where('A.name', $keyword);
        }
        $q = $builder->get()->getRow();
        $response->recordsTotal = $q->ccount ?? 0;

        // Fetch filtered data
        $builder = $this->db->table('admission_enquiry A')->select('A.*');
        if ($keyword) {
            $builder->where('A.name', $keyword);
        }
        $builder->orderBy('A.enquiry_id', 'desc');
        $builder->limit($this->request->getPost('length'), $this->request->getPost('start'));
        $results = $builder->get()->getResult();

        $response->recordsFiltered = $response->recordsTotal;
        $response->data = [];

        foreach ($results as $row) {
            $response->data[] = [
                'id'          => $row->enquiry_id,
                'name'        => $row->name,
                'contact'     => $row->contact,
                'email'       => $row->email,
                'address'     => $row->address,
                'description' => $row->description,
                'date'        => $row->date,
            ];
        }

        return $this->response->setJSON($response);
    }

    public function add()
    {
        check_permission('admin-add-enquiry');
        return view('admin/admission_enquiry_edit');
    }

    public function edit()
    {
        check_permission('admin-edit-enquiry');
        $id = intval($this->request->getGet('id'));

        $info = $this->db->table('admission_enquiry')->where('enquiry_id', $id)->get()->getRow();
        return view('admin/admission_enquiry_edit', ['info' => $info]);
    }

    public function save()
    {
        $campusId = session('member_campusid');
        $schoolInfo = getSchoolInfo();
        $message = $this->request->getPost('document');
        $date = date('Y-m-d H:i:s');

        $smsSettings = $this->db->table('sms_settings')
            ->where('system_id', $schoolInfo->system_id)
            ->get()
            ->getRow();

        if (!$smsSettings) {
            return $this->response->setJSON(['success' => false, 'msg' => 'SMS package not found']);
        }

        $maskingName = $smsSettings->masking_name;
        $apiSecret = $smsSettings->api_secret;
        $apiToken = $smsSettings->api_token;
        $url = "https://lifetimesms.com/plain";

        $file = $this->request->getFile('documentfile');
        if ($file && $file->isValid() && $file->getClientMimeType() === 'text/csv') {
            $csvData = $this->parseCSV($file->getTempName());

            foreach ($csvData as $row) {
                if (!isset($row['A'])) continue;

                $payload = [
                    'api_token'   => $apiToken,
                    'api_secret'  => $apiSecret,
                    'to'          => "92" . $row['A'],
                    'from'        => $maskingName,
                    'message'     => $message
                ];

                $this->sendSms($url, $payload);
            }

            return $this->response->setJSON(['success' => true, 'msg' => 'Bulk SMS sent successfully']);
        }

        return $this->response->setJSON(['success' => false, 'msg' => 'Invalid or missing CSV file']);
    }

    public function delete()
    {
        check_permission('admin-del-enquiry');
        $id = intval($this->request->getGet('id'));

        $this->db->transStart();
        $this->db->table('classes')->where('id', $id)->delete();
        $this->db->transComplete();

        return $this->response->setJSON(['success' => true, 'msg' => 'Deleted successfully']);
    }

    protected function parseCSV(string $filePath): array
    {
        $data = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            while (($row = fgetcsv($handle)) !== false) {
                $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
        return $data;
    }

    protected function sendSms(string $url, array $params): void
    {
        $client = \Config\Services::curlrequest();
        try {
            $client->post($url, [
                'form_params' => $params,
                'verify' => true
            ]);
        } catch (\Exception $e) {
            log_message('error', 'SMS send error: ' . $e->getMessage());
        }
    }
}
