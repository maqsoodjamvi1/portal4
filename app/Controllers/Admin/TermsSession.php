<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class TermsSession extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        helper(['form']);
        $this->db = \Config\Database::connect();
        $this->session = session();
        check_permission('admin-terms-sessions');
    }

    public function index()
    {
        return view('admin/terms_session', [
            'member_termsessionid' => session()->get('member_termsessionid')
        ]);
    }

    public function data()
    {
        $schoolinfo = getSchoolInfo();
        $request = $this->request;
        $response = new \stdClass();
        $keyword = $request->getPost('search')['value'] ?? '';

        // Total count
        $builder = $this->db->table('terms_session')->where('system_id', $schoolinfo->system_id);
        if ($keyword) $builder->like('name', $keyword);
        $response->recordsTotal = $builder->countAllResults(false);

        // Data
        $builder->select('*')->orderBy('term_id', 'desc')
            ->limit($request->getPost('length'), $request->getPost('start'));
        $results = $builder->get()->getResult();

        $response->recordsFiltered = $response->recordsTotal;
        $response->data = [];

        foreach ($results as $row) {
            $term = $this->db->table('terms')->where('term_id', $row->term_id)->get()->getRow();
            $session = $this->db->table('academic_session')->where('session_id', $row->session_id)->get()->getRow();

            $response->data[] = [
                'id' => $row->term_session_id,
                'term_name' => $term->name ?? '',
                'session' => $session->session_name ?? '',
                'start_date' => dateFormat($row->start_date),
                'end_date' => dateFormat($row->end_date),
            ];
        }

        return $this->response->setJSON($response);
    }

public function data2()
{
    $session_id = $this->request->getPost('session_id');
    if (!$session_id) {
        return $this->response->setBody('<div class="alert alert-warning mb-0">No session selected.</div>');
    }

    $schoolinfo = getSchoolInfo();

    // Session
    $academic_session = $this->db->table('academic_session')
        ->where('session_id', $session_id)
        ->where('system_id', $schoolinfo->system_id)
        ->get()->getRow();

    if (!$academic_session) {
        return $this->response->setBody('<div class="alert alert-danger mb-0">Selected session not found.</div>');
    }

    // Terms
    $termsinfo = $this->db->table('terms')
        ->where('system_id', $schoolinfo->system_id)
        ->orderBy('term_id', 'ASC')
        ->get()->getResult();

    $termCountRow = $this->db->query(
        'SELECT COUNT(term_id) AS totalCount FROM terms WHERE system_id = ' . (int) $schoolinfo->system_id
    )->getRow();
    $termCount = (int) ($termCountRow->totalCount ?? 0);

    // Helpers
    $fmtDMY = function (?string $ymd) {
        if (empty($ymd)) return '';
        $d = \DateTime::createFromFormat('Y-m-d', $ymd);
        return $d ? $d->format('d-m-Y') : '';
    };
    $dayName = function (?string $ymd) {
        if (empty($ymd)) return '—';
        $d = \DateTime::createFromFormat('Y-m-d', $ymd);
        return $d ? $d->format('l') : '—';
    };
    $weeksInclusive = function (?string $sYmd, ?string $eYmd) {
        if (!$sYmd || !$eYmd) return 0;
        $s = \DateTime::createFromFormat('Y-m-d', $sYmd);
        $e = \DateTime::createFromFormat('Y-m-d', $eYmd);
        if (!$s || !$e) return 0;
        $diff = (int) $e->diff($s)->format('%a') + 1; // inclusive
        return $diff > 0 ? (int) ceil($diff / 7) : 0;
    };

    $sessStartYmd = $academic_session->start_date;
    $sessEndYmd   = $academic_session->end_date;

    $data  = '';
    $data .= '<div class="table-responsive"><table class="table table-sm align-middle mb-0">
                <thead>
                  <tr>
                    <th style="min-width:180px;">Term (Code)</th>
                    <th style="min-width:220px;">Start Date</th>
                    <th style="min-width:220px;">End Date</th>
                    <th style="width:100px;">Weeks</th>
                  </tr>
                </thead>
                <tbody>';

    $i = 1;
    foreach ($termsinfo as $term) {

        // Existing term_session for this session/term
        $term_session = $this->db->table('terms_session')
            ->where('session_id', $session_id)
            ->where('term_id', $term->term_id)
            ->get()->getRow();

        $term_start_ymd = '';
        $term_end_ymd   = '';
        $term_session_id = 0;

        if ($term_session) {
            $term_start_ymd  = $term_session->start_date;
            $term_end_ymd    = $term_session->end_date;
            $term_session_id = (int) $term_session->term_session_id;
        } else {
            // Prefill first start and last end from session bounds
            if ($i === 1 && !empty($sessStartYmd)) {
                $term_start_ymd = $sessStartYmd;
            }
            if ($termCount === $i && !empty($sessEndYmd)) {
                $term_end_ymd = $sessEndYmd;
            }
        }

        $term_start_dmy = $fmtDMY($term_start_ymd);
        $term_end_dmy   = $fmtDMY($term_end_ymd);
        $initWeeks      = $weeksInclusive($term_start_ymd, $term_end_ymd);

        $data .= '<tr class="term-row"
                      data-term-id="' . (int) $term->term_id . '"
                      data-name="' . htmlspecialchars($term->name, ENT_QUOTES, 'UTF-8') . '"
                      data-start="' . htmlspecialchars($term_start_ymd ?? '', ENT_QUOTES, 'UTF-8') . '"
                      data-end="'   . htmlspecialchars($term_end_ymd ?? '', ENT_QUOTES, 'UTF-8') . '">
                    <td>
                      <strong class="term-name">' . htmlspecialchars($term->name, ENT_QUOTES, 'UTF-8') . '</strong>
                      <span class="badge badge-info ml-1 term-code">' . (int) $term->term_id . '</span>
                      <input type="hidden" name="rowscount[]" value="1" />
                      <input type="hidden" name="term_id[]" value="' . (int) $term->term_id . '">
                      <input type="hidden" name="term_session_id[]" value="' . (int) $term_session_id . '">
                    </td>

                    <td>
                      <div class="form-group mb-0">
                        <div class="input-group input-group-sm date">
                          <div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-calendar"></i></div></div>
                          <input type="text" class="form-control term-start datepicker" readonly
                                 id="startdatepicker' . $i . '" data-idx="' . $i . '"
                                 name="start_date[]" value="' . htmlspecialchars($term_start_dmy, ENT_QUOTES, 'UTF-8') . '">
                          <div class="input-group-append">
                            <span class="input-group-text bg-white">
                              <span id="startDay' . $i . '" class="badge badge-light border">' . ($term_start_ymd ? $dayName($term_start_ymd) : '—') . '</span>
                            </span>
                          </div>
                        </div>
                      </div>
                    </td>

                    <td>
                      <div class="form-group mb-0">
                        <div class="input-group input-group-sm date">
                          <div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-calendar"></i></div></div>
                          <input type="text" class="form-control term-end datepicker" ' .
                            ((($termCount === $i) && ($term_end_dmy !== '' || !empty($sessEndYmd))) ? 'readonly ' : '') . '
                            id="enddatepicker' . $i . '" data-idx="' . $i . '"
                            name="end_date[]" value="' . htmlspecialchars($term_end_dmy, ENT_QUOTES, 'UTF-8') . '">
                          <div class="input-group-append">
                            <span class="input-group-text bg-white">
                              <span id="endDay' . $i . '" class="badge badge-light border">' . ($term_end_ymd ? $dayName($term_end_ymd) : '—') . '</span>
                            </span>
                          </div>
                        </div>
                      </div>
                    </td>

                    <td>
                      <span id="weeks' . $i . '" class="badge badge-secondary">' . (int) $initWeeks . '</span>
                    </td>
                  </tr>';

        $i++;
    }

    $data .= '  </tbody></table></div>';

    // Inline JS initialiser
    $data .= '<script>
      (function($){
        var dayNames=["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
        var sessMin=new Date("' . htmlspecialchars($sessStartYmd, ENT_QUOTES, 'UTF-8') . '");
        var sessMax=new Date("' . htmlspecialchars($sessEndYmd, ENT_QUOTES, 'UTF-8') . '");

        function parseDMY(s){
          if(!s) return null;
          var a=s.split("-");
          if(a.length!==3) return null;
          var d=parseInt(a[0],10), m=parseInt(a[1],10)-1, y=parseInt(a[2],10);
          // handle 2-digit years gracefully
          if (y < 100) { y = y + (y >= 70 ? 1900 : 2000); }
          var dt=new Date(y,m,d);
          return isNaN(dt)?null:dt;
        }
        function weeksInclusive(s,e){
          if(!s||!e) return 0;
          var MS=86400000;
          var diff=Math.floor((e - s)/MS)+1; // inclusive
          return diff>0?Math.ceil(diff/7):0;
        }
        function updateBadgesAndWeeks(idx){
          var sStr=$("#startdatepicker"+idx).val();
          var eStr=$("#enddatepicker"+idx).val();
          var s=parseDMY(sStr), e=parseDMY(eStr);
          $("#startDay"+idx).text(s?dayNames[s.getDay()]:"—");
          $("#endDay"+idx).text(e?dayNames[e.getDay()]:"—");
          $("#weeks"+idx).text(weeksInclusive(s,e));
        }

        // Start datepicker: align to Monday
        $(".term-start").each(function(){
          var $inp=$(this), idx=$inp.data("idx");
          $inp.datepicker({
            dateFormat:"dd-mm-yy",
            showWeek:true, firstDay:1,
            minDate:sessMin, maxDate:sessMax,
            onSelect:function(dateText){
              var d=$.datepicker.parseDate("dd-mm-yy", dateText);
              var dow=d.getDay();
              if(dow===0){ d.setDate(d.getDate()-6); }   // Sun -> previous Mon
              else if(dow!==1){ d.setDate(d.getDate()-(dow-1)); } // Tue..Sat -> Mon
              $(this).val($.datepicker.formatDate("dd-mm-yy", d));
              $("#startDay"+idx).text(dayNames[d.getDay()]);
              updateBadgesAndWeeks(idx);
            }
          });
        });

        // End datepicker: align to Sunday; set next start = +1 day
        $(".term-end").each(function(){
          var $inp=$(this), idx=$inp.data("idx");
          $inp.datepicker({
            dateFormat:"dd-mm-yy",
            showWeek:true, firstDay:1,
            minDate:sessMin, maxDate:sessMax,
            onSelect:function(dateText){
              var d=$.datepicker.parseDate("dd-mm-yy", dateText);
              var dow=d.getDay();
              if(dow===0){ /* already Sunday */ }
              else if(dow===1){ d.setDate(d.getDate()-1); }       // Mon -> prev Sun
              else { d.setDate(d.getDate()+(7-dow)); }            // Tue..Sat -> next Sun
              $(this).val($.datepicker.formatDate("dd-mm-yy", d));
              $("#endDay"+idx).text(dayNames[d.getDay()]);
              updateBadgesAndWeeks(idx);

              // Next term start = next day
              var nextIdx = idx + 1;
              var $nextStart=$("#startdatepicker"+nextIdx);
              if($nextStart.length){
                var next=new Date(d.getFullYear(), d.getMonth(), d.getDate()+1);
                if(next<=sessMax){
                  $nextStart.val($.datepicker.formatDate("dd-mm-yy", next));
                  $("#startDay"+nextIdx).text(dayNames[next.getDay()]);
                  updateBadgesAndWeeks(nextIdx);
                }
              }
            }
          });
        });

        // Initial compute for all rows
        $("[id^=startdatepicker][data-idx], [id^=enddatepicker][data-idx]").each(function(){
          var idx=$(this).data("idx");
          if(idx){ updateBadgesAndWeeks(idx); }
        });

      })(jQuery);
    </script>';

    return $this->response->setBody($data);
}
public function save()
{
    check_permission('admin-add-terms-session');

    $schoolinfo = getSchoolInfo();
    $user_id    = $this->session->get('member_userid');
    $now        = date('Y-m-d H:i:s');

    $session_id       = $this->request->getPost('session_id');
    $term_ids         = (array) $this->request->getPost('term_id');         // term_id[]
    $term_start_dates = (array) $this->request->getPost('start_date');      // start_date[]
    $term_end_dates   = (array) $this->request->getPost('end_date');        // end_date[]
    $term_session_ids = (array) $this->request->getPost('term_session_id'); // term_session_id[]

    // Decide response type
    $wantsJson = $this->request->isAJAX()
        || stripos($this->request->getHeaderLine('Accept'), 'application/json') !== false;

    // Basic guards
    if (empty($session_id)) {
        $msg = 'Session is required.';
        if ($wantsJson) return $this->response->setJSON(['success' => false, 'msg' => $msg]);
        session()->setFlashdata('error', $msg);
        return redirect()->back()->withInput();
    }
    if (empty($term_ids)) {
        $msg = 'No term rows were submitted.';
        if ($wantsJson) return $this->response->setJSON(['success' => false, 'msg' => $msg]);
        session()->setFlashdata('error', $msg);
        return redirect()->back()->withInput();
    }

    // Flexible date parser (dd-mm-yy, dd-mm-YYYY, dd/mm/yy, YYYY-mm-dd, etc.)
    $toYmd = static function($v) {
        if (!$v) return null;
        $vNorm = str_replace('/', '-', trim((string)$v));
        foreach (['Y-m-d','d-m-Y','d-m-y','j-n-Y','j-n-y'] as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $vNorm);
            if ($dt && $dt->format($fmt) === $vNorm) return $dt->format('Y-m-d');
        }
        try { $dt = new \DateTime($vNorm); return $dt->format('Y-m-d'); } catch (\Throwable $e) { return null; }
    };

    $rowCount = min(count($term_ids), count($term_start_dates), count($term_end_dates));
    if ($rowCount === 0) {
        $msg = 'Start/end dates are missing.';
        if ($wantsJson) return $this->response->setJSON(['success' => false, 'msg' => $msg]);
        session()->setFlashdata('error', $msg);
        return redirect()->back()->withInput();
    }

    $inserted = 0;
    $updated  = 0;

    $this->db->transStart();
    try {
        $builder = $this->db->table('terms_session');

        for ($i = 0; $i < $rowCount; $i++) {
            $termId = $term_ids[$i] ?? null;
            if (empty($termId)) continue;

            $start = $toYmd($term_start_dates[$i] ?? null);
            $end   = $toYmd($term_end_dates[$i] ?? null);
            if (!$start || !$end) continue;
            if ($start > $end) { [$start, $end] = [$end, $start]; }

            $data = [
                'system_id'  => $schoolinfo->system_id,
                'session_id' => (int)$session_id,
                'term_id'    => (int)$termId,
                'start_date' => $start,
                'end_date'   => $end,
                'status'     => 1,                 // keep safe for NOT NULL status
                'user_id'    => $user_id,
            ];

            $existingId = $term_session_ids[$i] ?? null;
            if (!empty($existingId)) {
                $data['updated_date'] = $now;
                $builder->where('term_session_id', (int)$existingId)->update($data);
                $updated += ($this->db->affectedRows() >= 0) ? 1 : 0;
            } else {
                $data['created_date'] = $now;
                $builder->insert($data);
                $inserted += ($this->db->affectedRows() > 0) ? 1 : 0;
            }
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            $msg = 'Database error while saving terms.';
            if ($wantsJson) return $this->response->setJSON(['success' => false, 'msg' => $msg]);
            session()->setFlashdata('error', $msg);
            return redirect()->back()->withInput();
        }

        if ($inserted === 0 && $updated === 0) {
            $msg = 'Nothing was saved. Please check dates/rows and try again.';
            if ($wantsJson) return $this->response->setJSON(['success' => false, 'msg' => $msg]);
            session()->setFlashdata('warning', $msg);
            return redirect()->back()->withInput();
        }

        // SUCCESS
        $friendly = 'Terms saved successfully.';       // 👈 user-friendly message
        $detail   = "Inserted: {$inserted}, Updated: {$updated}."; // diagnostic if you ever want it

        if ($wantsJson) {
            // Keep JSON for AJAX callers
            return $this->response->setJSON(['success' => true, 'msg' => $friendly, 'detail' => $detail]);
        }

        // Non-AJAX → flash + redirect
        session()->setFlashdata('success', $friendly);
        return redirect()->to(base_url('admin/terms_session'));

    } catch (\Throwable $e) {
        $this->db->transRollback();
        $msg = 'Unexpected error: '.$e->getMessage();
        if ($wantsJson) return $this->response->setJSON(['success' => false, 'msg' => $msg]);
        session()->setFlashdata('error', $msg);
        return redirect()->back()->withInput();
    }
}


    public function add()
    {
        check_permission('admin-add-terms-session');
        $schoolinfo = getSchoolInfo();

        $termsinfo = $this->db->table('terms')->where('system_id', $schoolinfo->system_id)->get()->getResult();
        $academic_session = $this->db->table('academic_session')->where('system_id', $schoolinfo->system_id)->get()->getResult();
        $terms_session_info = $this->db->table('terms_session')->where('system_id', $schoolinfo->system_id)->get()->getRow();

        
        return view('admin/terms_session_edit', [
            'academic_session' => $academic_session,
            'termsinfo' => $termsinfo,
            'terms_session_info' => $terms_session_info,
            'session_id' => session()->get('member_sessionid'),
            'id' => $id ?? null,
        ]);

    }


    public function edit()
    {
        check_permission('admin-edit-terms-session');
        $id = (int) $this->request->getGet('id');
        $schoolinfo = getSchoolInfo();

        $info = $this->db->table('terms_session')->where('term_session_id', $id)->get()->getRow();
        $terms = $this->db->table('terms')->where('system_id', $schoolinfo->system_id)->get()->getResult();
        $sessions = $this->db->table('academic_session')->where('system_id', $schoolinfo->system_id)->get()->getResult();

        return view('admin/terms_session_edit', [
            'info' => $info,
            'termsinfo' => $terms,
            'academic_session' => $sessions
        ]);
    }

   

   
}
