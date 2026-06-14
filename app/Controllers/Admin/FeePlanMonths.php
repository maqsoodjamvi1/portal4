<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use stdClass;

class FeePlanMonths extends BaseController
{
    protected $db;
    protected $session;

    public function __construct(bool $skipPermissionCheck = false)
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        if (!$skipPermissionCheck) {
            check_permission('admin-fee-plan-months');
        }
    }

    /**
     * Data for fee setup plan-months grid (plans × months).
     */
    public function getSetupGridData(): array
    {
        $campusid = $this->session->get('member_campusid');
        $months = $this->months();
        $fee_plan_info = $this->db->table('fee_plans')
            ->orderBy('plan_id', 'ASC')
            ->get()
            ->getResult();

        $plans = [];
        $totalActive = 0;

        foreach ($fee_plan_info as $fee_plan) {
            $monthStates = [];
            $activeCount = 0;

            foreach ($months as $month) {
                $row = $this->db->table('fee_plan_months')
                    ->where('campus_id', $campusid)
                    ->where('month', $month)
                    ->where('fee_plan_id', $fee_plan->plan_id)
                    ->get()
                    ->getRow();

                $checked = $row && (int) $row->status === 1;
                $monthStates[$month] = $checked;
                if ($checked) {
                    $activeCount++;
                    $totalActive++;
                }
            }

            $plans[] = [
                'plan_id'      => (int) $fee_plan->plan_id,
                'plan_name'    => $fee_plan->plan_name,
                'months'       => $monthStates,
                'active_count' => $activeCount,
            ];
        }

        return [
            'billing_months' => $months,
            'fee_plans'      => $plans,
            'plan_count'     => count($plans),
            'active_slots'   => $totalActive,
        ];
    }

    public function index()
    {
        return view('admin/fee_plan_months/index', $this->getSetupGridData());
    }

    public function data(): ResponseInterface
    {
        $request = $this->request->getPost();
        $draw = $request['draw'] ?? 1;
        $length = $request['length'] ?? 10;
        $start = $request['start'] ?? 0;
        $campus_id = $this->session->get('member_campusid');
        $keyword = $request['search']['value'] ?? '';

        // Count
        $builder = $this->db->table('fee_plan_months A')
            ->selectCount('A.plan_month_id', 'ccount')
            ->where('A.campus_id', $campus_id)
            ->where('A.status', 1);

        $q = $builder->get()->getRow();
        $recordsTotal = $q->ccount ?? 0;

        // Results
        $builder = $this->db->table('fee_plan_months A')
            ->select('A.*')
            ->where('A.campus_id', $campus_id)
            ->where('A.status', 1)
            ->orderBy('A.plan_month_id', 'desc')
            ->limit($length, $start);

        $results = $builder->get()->getResult();

        $data = [];
        foreach ($results as $row) {
            $feeplansinfo = $this->db->table('fee_plans')
                ->where('plan_id', $row->fee_plan_id)
                ->get()
                ->getRow();

            $data[] = [
                'id' => $row->plan_month_id,
                'plan_name' => $feeplansinfo->plan_name ?? '',
                'month' => $row->month,
            ];
        }

        $response = [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $data
        ];

        return $this->response->setJSON($response);
    }

    private function months($month_format="M")
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = date($month_format, mktime(0,0,0,$i,1,date("Y")));
        }
        return $months;
    }

    public function data2(): ResponseInterface
    {
        $campusid = $this->session->get('member_campusid');
        $schoolinfo = getSchoolInfo();
        $months = $this->months();
        $fee_plan_info = $this->db->table('fee_plans')->get()->getResult();

        $data = '<p>Select checkbox to save Fee Months</p>';
        $data .= '<table class="table"><tr><th></th>';
        foreach ($months as $month) {
            $data .= '<th><input type="hidden" name="section_id[]"  value="'.$month.'"  />'.$month.'</th>';
        }
        $data .= '</tr>';

        foreach ($fee_plan_info as $fee_plan) {
            $data .= '<tr><td><input type="hidden" name="class_id[]"  value="'.$fee_plan->plan_id.'"  />'.$fee_plan->plan_name.'</td>';
            foreach ($months as $month) {
                $fee_plan_months = $this->db->table('fee_plan_months')
                    ->where('campus_id', $campusid)
                    ->where('month', $month)
                    ->where('fee_plan_id', $fee_plan->plan_id)
                    ->get()
                    ->getRow();

                $checked = ($fee_plan_months && $fee_plan_months->status == 1) ? ' checked' : '';
                $data .= '<td><input type="checkbox" class="setClassSub setlock_'.$fee_plan->plan_id.'" name="'.$month.'_'.$fee_plan->plan_id.'_class_section[]" value="'.$month.'_'.$fee_plan->plan_id.'"'.$checked.' /></td>';
            }
            $data .= '</tr>';
        }

        $data .= '</table>
        <script type="text/javascript">
        $(function(){
            $(".setClassSub").on("change",function() {
                var status = this.checked ? 1 : 0;
                var plan_month_id = $(this).val();
                $.ajax({
                    type: "POST",
                    url: "/admin/fee_plan_months/updateFeePlanMonth",
                    data: {plan_month_id: plan_month_id, status: status},
                    dataType: "json",
                    success: function(res){
                        toastr.success(res.msg);
                    }
                });
            });
        });
        </script>';

        return $this->response->setJSON(['html' => $data]);
    }

    public function updateFeePlanMonth(): ResponseInterface
    {
        $campusid = $this->session->get('member_campusid');
        $status = $this->request->getPost('status');
        $plan_month_id = $this->request->getPost('plan_month_id');
        $planMonthArr = explode('_', $plan_month_id);

        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d H:i:s');
        $month = $planMonthArr[0];
        $plan_id = $planMonthArr[1];

        $feemonthplan = $this->db->table('fee_plan_months')
            ->where('fee_plan_id', $plan_id)
            ->where('campus_id', $campusid)
            ->where('month', $month)
            ->get()->getRow();

        if ($feemonthplan) {
            $data = [
                'user_id' => $user_id,
                'updated_date' => $date,
                'status' => $status
            ];
            $this->db->table('fee_plan_months')
                ->where('fee_plan_id', $plan_id)
                ->where('campus_id', $campusid)
                ->where('month', $month)
                ->update($data);
        } elseif ((int) $status === 1) {
            $data = [
                'fee_plan_id'  => $plan_id,
                'month'        => $month,
                'campus_id'    => $campusid,
                'user_id'      => $user_id,
                'created_date' => $date,
                'status'       => 1,
            ];
            $this->db->table('fee_plan_months')->insert($data);
        }

        return $this->response->setJSON([
            'success' => true,
            'msg'     => ((int) $status === 1) ? 'Month enabled for plan' : 'Month disabled for plan',
        ]);
    }

    public function add()
    {
        check_permission('admin-add-fee-plan-months');

        return redirect()->to(base_url('admin/fee_plan_months'));
    }

    public function edit()
    {
        check_permission('admin-edit-fee-plan-months');
        $id = intval($this->request->getGet('id'));
        $campusid = $this->session->get('member_campusid');
        $info = $this->db->table('section_subjects')
            ->where('cs_id', $id)
            ->get()->getRow();

        $classsectioninfo = $this->db->table('class_section')
            ->where('campus_id', $campusid)
            ->get()->getResult();

        $sectionsclassinfo = [];
        foreach ($classsectioninfo as $section) {
            $classinfo = $this->db->table('classes')
                ->where('class_id', $section->class_id)
                ->get()->getRow();
            $sectioninfo = $this->db->table('sections')
                ->where('section_id', $section->section_id)
                ->get()->getRow();

            $sectionsclassinfo[] = [
                'section_id' => $section->cls_sec_id,
                'sectionclassname' => ($classinfo->class_name ?? '') . " (" . ($sectioninfo->section_name ?? '') . ")"
            ];
        }
        $subjectinfo = $this->db->table('allsubject')->get()->getResult();

        return view('admin/fee_plan_months_edit', [
            'info' => $info,
            'sectionsclassinfo' => $sectionsclassinfo,
            'subjectinfo' => $subjectinfo
        ]);
    }

    public function save(): ResponseInterface
    {
        $id = intval($this->request->getPost('id'));
        $campus_id = $this->session->get('member_campusid');
        $user_id = $this->session->get('member_userid');
        $date = date('Y-m-d');
        $schoolinfo = getSchoolInfo();
        $section_ids = $this->request->getPost('section_id');
        $class_ids = $this->request->getPost('class_id');
        $cls_sec_ids = $this->request->getPost('cls_sec_id');

        check_permission('admin-add-fee-plan-months');
        $this->db->transStart();
        $this->db->transComplete();

        $subjects_info = $this->db->table('allsubject')
            ->where('system_id', $schoolinfo->system_id)
            ->get()->getRow();

        if (empty($subjects_info->sid)) {
            return $this->response->setJSON(['subject_id' => false, 'msg' => 'Fee Plan Months Success']);
        } else {
            return $this->response->setJSON(['success' => true, 'msg' => 'Add Fee Plan Months Success']);
        }
    }
}
