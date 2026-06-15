<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Dompdf\Dompdf;
use Dompdf\Options;

if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
    require_once ROOTPATH . 'vendor/autoload.php';
}

class WeeklyPlanningReport extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = session();
        helper(['form', 'url']);
        check_permission('admin-weekly-planning');
    }

    public function index()
    {
        $schoolinfo = getSchoolInfo();
        $campusid = $this->session->get('member_campusid');
        $sessionid = $this->session->get('member_sessionid');
        
        // Get all sections for filter
        $sections = $this->db->table('class_section cs')
            ->select('cs.cls_sec_id, cs.class_id, c.class_name, s.section_name')
            ->join('classes c', 'c.class_id = cs.class_id')
            ->join('sections s', 's.section_id = cs.section_id')
            ->where('cs.campus_id', $campusid)
            ->where('cs.status', 1)
            ->orderBy('c.class_name', 'ASC')
            ->orderBy('s.section_name', 'ASC')
            ->get()
            ->getResult();
        
        // Get all subjects for filter
        $subjects = $this->db->table('allsubject')
            ->where('system_id', $schoolinfo->system_id)
            ->where('status', 1)
            ->orderBy('subject_name', 'ASC')
            ->get()
            ->getResult();
        
        // Get terms for current session
        $terms = $this->db->table('terms_session ts')
            ->select('ts.term_session_id, ts.term_id, ts.start_date, ts.end_date, t.name as term_name')
            ->join('terms t', 't.term_id = ts.term_id')
            ->where('ts.session_id', $sessionid)
            ->where('ts.status', 1)
            ->orderBy('ts.start_date', 'ASC')
            ->get()
            ->getResult();
        
        $data = [
            'sections' => $sections,
            'subjects' => $subjects,
            'terms' => $terms,
            'current_session_id' => $sessionid
        ];
        
        return view('admin/weekly_planning_report', $data);
    }

    public function getData()
{
    $term_session_id = $this->request->getPost('term_session_id');
    $section_id = $this->request->getPost('section_id');
    $subject_id = $this->request->getPost('subject_id');
    $week_id = $this->request->getPost('week_id');
    $campusid = $this->session->get('member_campusid');
    
    $response = [
        'success' => false,
        'data' => [],
        'html' => ''
    ];
    
    if (!$term_session_id) {
        $response['message'] = 'Please select a term';
        return $this->response->setJSON($response);
    }
    
    // Build query - remove section join to avoid duplication
    $builder = $this->db->table('weekly_planning wp')
        ->select('wp.*, tw.week_name, tw.start_date as week_start, tw.end_date as week_end,
                  s.subject_name, c.class_name')
        ->join('term_weeks tw', 'tw.term_weeks_id = wp.term_week_id')
        ->join('allsubject s', 's.sid = wp.subject_id')
        ->join('classes c', 'c.class_id = wp.class_id')
        ->where('wp.campus_id', $campusid)
        ->where('tw.term_session_id', $term_session_id);
    
    // Apply filters
    if ($section_id) {
        // If section is selected, get the class_id from section
        $classInfo = $this->db->table('class_section')
            ->select('class_id')
            ->where('cls_sec_id', $section_id)
            ->get()
            ->getRow();
        
        if ($classInfo) {
            $builder->where('wp.class_id', $classInfo->class_id);
        }
    }
    
    if ($subject_id) {
        $builder->where('wp.subject_id', $subject_id);
    }
    
    if ($week_id) {
        $builder->where('wp.term_week_id', $week_id);
    }
    
    // Only get records that have objectives (not null or empty)
    $builder->where('wp.objectives IS NOT NULL');
    $builder->where('wp.objectives !=', '');
    $builder->where('wp.objectives !=', '<p><br></p>');
    $builder->where('wp.objectives !=', '<p></p>');
    
    $builder->orderBy('c.class_name', 'ASC')
            ->orderBy('s.subject_name', 'ASC')
            ->orderBy('tw.start_date', 'ASC');
    
    $results = $builder->get()->getResult();
    
    // Group data by class and subject (not by section)
    $groupedData = [];
    foreach ($results as $item) {
        $classKey = $item->class_id;
        $subjectKey = $item->subject_id;
        
        if (!isset($groupedData[$classKey])) {
            $groupedData[$classKey] = [
                'class_id' => $item->class_id,
                'class_name' => $item->class_name,
                'subjects' => []
            ];
        }
        
        if (!isset($groupedData[$classKey]['subjects'][$subjectKey])) {
            $groupedData[$classKey]['subjects'][$subjectKey] = [
                'subject_id' => $item->subject_id,
                'subject_name' => $item->subject_name,
                'weeks' => []
            ];
        }
        
        // Check if week already exists for this subject to avoid duplicates
        $weekExists = false;
        foreach ($groupedData[$classKey]['subjects'][$subjectKey]['weeks'] as $existingWeek) {
            if ($existingWeek->term_week_id == $item->term_week_id) {
                $weekExists = true;
                break;
            }
        }
        
        if (!$weekExists) {
            $groupedData[$classKey]['subjects'][$subjectKey]['weeks'][] = $item;
        }
    }
    
    $response['success'] = true;
    $response['data'] = $groupedData;
    $response['html'] = $this->generateReportHTML($groupedData);
    
    return $this->response->setJSON($response);
}


    public function getWeeks()
    {
        $term_session_id = $this->request->getPost('term_session_id');
        
        $weeks = $this->db->table('term_weeks')
            ->select('term_weeks_id, week_name, start_date, end_date')
            ->where('term_session_id', $term_session_id)
            ->where('week_type_id', 1)
            ->orderBy('start_date', 'ASC')
            ->get()
            ->getResult();
        
        $html = '<option value="">All Weeks</option>';
        foreach ($weeks as $week) {
            $weekDisplay = $week->week_name . ' (' . date('d M', strtotime($week->start_date)) . ' - ' . date('d M', strtotime($week->end_date)) . ')';
            $html .= '<option value="' . $week->term_weeks_id . '">' . $weekDisplay . '</option>';
        }
        
        return $this->response->setBody($html);
    }
  
private function generateReportHTML($groupedData)
{
    if (empty($groupedData)) {
        return '<div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> No weekly planning records found for the selected filters.
                </div>';
    }
    
    $html = '<div class="report-container">';
    
    foreach ($groupedData as $classKey => $classData) {
        $html .= '<div class="card mb-4">';
        $html .= '<div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">';
        $html .= '<h4 class="mb-0"><i class="fas fa-graduation-cap me-2"></i> ' . htmlspecialchars($classData['class_name']) . '</h4>';
        $html .= '</div>';
        $html .= '<div class="card-body">';
        
        foreach ($classData['subjects'] as $subjectKey => $subjectData) {
            $html .= '<div class="subject-section mb-4">';
            $html .= '<h5 class="border-bottom pb-2" style="color: #4a5568;">';
            $html .= '<i class="fas fa-book text-primary me-2"></i> ' . htmlspecialchars($subjectData['subject_name']);
            $html .= '</h5>';
            $html .= '<div class="row">';
            
            foreach ($subjectData['weeks'] as $week) {
                $weekDisplay = $week->week_name;
                $weekDates = '<small class="text-muted">' . date('d M Y', strtotime($week->week_start)) . ' - ' . 
                            date('d M Y', strtotime($week->week_end)) . '</small>';
                
                $html .= '<div class="col-md-6 col-lg-4 mb-3">';
                $html .= '<div class="card h-100 week-card">';
                $html .= '<div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">';
                $html .= '<div class="d-flex justify-content-between align-items-center">';
                $html .= '<strong><i class="fas fa-calendar-week me-2"></i> ' . htmlspecialchars($weekDisplay) . '</strong>';
                $html .= '</div>';
                $html .= '<div class="mt-1" style="font-size: 11px; opacity: 0.9;">' . $weekDates . '</div>';
                $html .= '</div>';
                $html .= '<div class="card-body">';
                $html .= '<div class="objectives-content">';
                $html .= '<div class="objectives-text">' . $week->objectives . '</div>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '<div class="card-footer bg-light text-muted small">';
                $html .= '<i class="fas fa-clock me-1"></i> ' . $weekDisplay;
                $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';
            }
            
            $html .= '</div>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    return $html;
}

  public function exportPdf()
{
    // Load Composer autoloader
    $autoloadPath = ROOTPATH . 'vendor/autoload.php';
    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
    }
    
    $term_session_id = $this->request->getGet('term_session_id');
    $section_id = $this->request->getGet('section_id');
    $subject_id = $this->request->getGet('subject_id');
    $week_id = $this->request->getGet('week_id');
    
    // Get the data
    $data = $this->getReportData($term_session_id, $section_id, $subject_id, $week_id);
    
    if (empty($data)) {
        return redirect()->back()->with('error', 'No data available to export');
    }
    
    // Generate HTML for PDF with print-optimized styles
    $html = $this->generatePDFHTML($data, $term_session_id);
    
    // Check if Dompdf is loaded
    if (!class_exists('Dompdf\Dompdf')) {
        // Fallback: Show HTML for printing
        echo '<html><head><title>Weekly Planning Report</title>';
        echo '<style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .weeks-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
            .week-card { border: 1px solid #ddd; border-radius: 5px; overflow: hidden; }
            .week-header { background: #4472C4; color: white; padding: 8px; }
            .week-body { padding: 10px; }
            @media print { .no-print { display: none; } }
        </style>';
        echo '</head><body>';
        echo '<div class="no-print" style="text-align:center; margin-bottom:20px;">
                <button onclick="window.print();">Print Report</button>
                <button onclick="window.close();">Close</button>
              </div>';
        echo $html;
        echo '</body></html>';
        exit();
    }
    
    // Use Dompdf with print-optimized settings
    $options = new \Dompdf\Options();
    $options->set('defaultFont', 'DejaVu Sans'); // Better font for printing
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', false);
    $options->set('isPhpEnabled', false);
    $options->set('chroot', ROOTPATH);
    
    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    
    // Set margins (top, right, bottom, left) in mm
    $dompdf->setMargins(15, 15, 15, 15);
    
    $dompdf->render();
    
    $filename = 'weekly_planning_report_' . date('Y-m-d_His') . '.pdf';
    $dompdf->stream($filename, ['Attachment' => true]);
    exit();
}
   public function exportExcel()
{
    // Load Composer autoloader
    $autoloadPath = ROOTPATH . 'vendor/autoload.php';
    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
    }
    
    $term_session_id = $this->request->getGet('term_session_id');
    $section_id = $this->request->getGet('section_id');
    $subject_id = $this->request->getGet('subject_id');
    $week_id = $this->request->getGet('week_id');
    
    // Get the data
    $data = $this->getReportData($term_session_id, $section_id, $subject_id, $week_id);
    
    if (empty($data)) {
        return redirect()->back()->with('error', 'No data available to export');
    }
    
    // Check if PhpSpreadsheet is loaded
    if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
        // Fallback to CSV
        $filename = 'weekly_planning_report_' . date('Y-m-d_His') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Class', 'Subject', 'Week', 'Week Dates', 'Objectives']);
        
        foreach ($data as $classData) {
            foreach ($classData['subjects'] as $subjectData) {
                foreach ($subjectData['weeks'] as $week) {
                    fputcsv($output, [
                        $classData['class_name'],
                        $subjectData['subject_name'],
                        $week->week_name,
                        date('d M Y', strtotime($week->week_start)) . ' - ' . date('d M Y', strtotime($week->week_end)),
                        strip_tags($week->objectives)
                    ]);
                }
            }
        }
        fclose($output);
        exit();
    }
    
    // Use PhpSpreadsheet
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Weekly Planning Report');
    
    // Headers
    $headers = ['Class', 'Subject', 'Week', 'Week Dates', 'Objectives'];
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '1', $header);
        $col++;
    }
    
    // Style header
    $sheet->getStyle('A1:E1')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']]
    ]);
    
    // Add data
    $row = 2;
    foreach ($data as $classData) {
        foreach ($classData['subjects'] as $subjectData) {
            foreach ($subjectData['weeks'] as $week) {
                $sheet->setCellValue('A' . $row, $classData['class_name']);
                $sheet->setCellValue('B' . $row, $subjectData['subject_name']);
                $sheet->setCellValue('C' . $row, $week->week_name);
                $sheet->setCellValue('D' . $row, date('d M Y', strtotime($week->week_start)) . ' - ' . date('d M Y', strtotime($week->week_end)));
                $sheet->setCellValue('E' . $row, strip_tags($week->objectives));
                $row++;
            }
        }
    }
    
    // Auto-size columns
    foreach (range('A', 'E') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $sheet->getStyle('E2:E' . ($row - 1))->getAlignment()->setWrapText(true);
    
    // Output file
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $filename = 'weekly_planning_report_' . date('Y-m-d_His') . '.xlsx';
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer->save('php://output');
    exit();
}


    private function getReportData($term_session_id, $section_id, $subject_id, $week_id)
{
    $campusid = $this->session->get('member_campusid');
    
    $builder = $this->db->table('weekly_planning wp')
        ->select('wp.*, tw.week_name, tw.start_date as week_start, tw.end_date as week_end,
                  s.subject_name, c.class_name, c.class_id')
        ->join('term_weeks tw', 'tw.term_weeks_id = wp.term_week_id')
        ->join('allsubject s', 's.sid = wp.subject_id')
        ->join('classes c', 'c.class_id = wp.class_id')
        ->where('wp.campus_id', $campusid)
        ->where('tw.term_session_id', $term_session_id);
    
    if ($section_id) {
        $classInfo = $this->db->table('class_section')
            ->select('class_id')
            ->where('cls_sec_id', $section_id)
            ->get()
            ->getRow();
        
        if ($classInfo) {
            $builder->where('wp.class_id', $classInfo->class_id);
        }
    }
    
    if ($subject_id) {
        $builder->where('wp.subject_id', $subject_id);
    }
    
    if ($week_id) {
        $builder->where('wp.term_week_id', $week_id);
    }
    
    $builder->where('wp.objectives IS NOT NULL');
    $builder->where('wp.objectives !=', '');
    $builder->where('wp.objectives !=', '<p><br></p>');
    $builder->where('wp.objectives !=', '<p></p>');
    
    $builder->orderBy('c.class_name', 'ASC')
            ->orderBy('s.subject_name', 'ASC')
            ->orderBy('tw.start_date', 'ASC');
    
    $results = $builder->get()->getResult();
    
    // Group data by class and subject
    $groupedData = [];
    foreach ($results as $item) {
        $classKey = $item->class_id;
        $subjectKey = $item->subject_id;
        
        if (!isset($groupedData[$classKey])) {
            $groupedData[$classKey] = [
                'class_id' => $item->class_id,
                'class_name' => $item->class_name,
                'subjects' => []
            ];
        }
        
        if (!isset($groupedData[$classKey]['subjects'][$subjectKey])) {
            $groupedData[$classKey]['subjects'][$subjectKey] = [
                'subject_id' => $item->subject_id,
                'subject_name' => $item->subject_name,
                'weeks' => []
            ];
        }
        
        // Check for duplicate weeks
        $weekExists = false;
        foreach ($groupedData[$classKey]['subjects'][$subjectKey]['weeks'] as $existingWeek) {
            if ($existingWeek->term_week_id == $item->term_week_id) {
                $weekExists = true;
                break;
            }
        }
        
        if (!$weekExists) {
            $groupedData[$classKey]['subjects'][$subjectKey]['weeks'][] = $item;
        }
    }
    
    return $groupedData;
}

private function generatePDFHTML($data, $term_session_id)
{
    // Get term name
    $termInfo = $this->db->table('terms_session ts')
        ->select('t.name as term_name')
        ->join('terms t', 't.term_id = ts.term_id')
        ->where('ts.term_session_id', $term_session_id)
        ->get()
        ->getRow();
    
    $termName = $termInfo ? $termInfo->term_name : 'Selected Term';
    
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Weekly Planning Report</title>
        <style>
            @page {
                size: A4;
                margin: 1.5cm;
            }
            
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: "DejaVu Sans", "Helvetica", Arial, sans-serif;
                font-size: 10pt;
                line-height: 1.3;
                color: #333;
            }
            
            /* Header Styles */
            .report-header {
                text-align: center;
                margin-bottom: 25px;
                padding-bottom: 15px;
                border-bottom: 2px solid #4472C4;
            }
            
            .report-header h1 {
                color: #4472C4;
                font-size: 18pt;
                margin-bottom: 5px;
            }
            
            .report-header p {
                color: #666;
                font-size: 9pt;
                margin: 2px 0;
            }
            
            /* Class Section */
            .class-section {
                margin-bottom: 25px;
                page-break-inside: avoid;
            }
            
            .class-title {
                background: #4472C4;
                color: white;
                padding: 6px 12px;
                font-size: 12pt;
                font-weight: bold;
                margin-bottom: 12px;
            }
            
            /* Subject Card */
            .subject-card {
                margin-bottom: 20px;
                border: 1px solid #e0e0e0;
                border-radius: 5px;
                overflow: hidden;
                page-break-inside: avoid;
            }
            
            .subject-header {
                background: #f5f5f5;
                padding: 8px 12px;
                border-bottom: 2px solid #4472C4;
            }
            
            .subject-name {
                font-size: 11pt;
                font-weight: bold;
                color: #4472C4;
            }
            
            /* Top Level Planning */
            .top-level-planning {
                background: #f9f9f9;
                padding: 10px 12px;
                margin: 0;
                border-bottom: 1px dashed #ddd;
                font-size: 9pt;
            }
            
            .top-level-label {
                font-weight: bold;
                color: #4472C4;
                margin-right: 8px;
            }
            
            .top-level-content {
                color: #555;
                line-height: 1.4;
            }
            
            /* Table Styles */
            .weeks-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 9pt;
            }
            
            .weeks-table th {
                background: #e8e8e8;
                padding: 6px 8px;
                text-align: left;
                font-weight: bold;
                border: 1px solid #ddd;
                font-size: 9pt;
            }
            
            .weeks-table td {
                padding: 8px;
                border: 1px solid #ddd;
                vertical-align: top;
                background: white;
            }
            
            .week-name-cell {
                width: 15%;
                font-weight: bold;
                background: #fafafa;
                vertical-align: top;
            }
            
            .week-date-cell {
                width: 18%;
                color: #666;
                font-size: 8pt;
                background: #fafafa;
            }
            
            .objectives-cell {
                width: 67%;
            }
            
            .objectives-text {
                font-size: 9pt;
                line-height: 1.4;
            }
            
            .objectives-text ul,
            .objectives-text ol {
                padding-left: 20px;
                margin: 3px 0;
            }
            
            .objectives-text p {
                margin: 3px 0;
            }
            
            .no-objective {
                color: #999;
                font-style: italic;
            }
            
            /* Footer */
            .report-footer {
                text-align: center;
                margin-top: 25px;
                padding-top: 8px;
                border-top: 1px solid #ddd;
                font-size: 8pt;
                color: #888;
            }
            
            /* Print Optimization */
            @media print {
                body {
                    margin: 0;
                    padding: 0;
                }
                
                .subject-card {
                    break-inside: avoid;
                }
                
                .weeks-table tr {
                    break-inside: avoid;
                }
            }
        </style>
    </head>
    <body>
        <div class="report-header">
            <h1>Weekly Planning Report</h1>
            <p>Term: ' . htmlspecialchars($termName) . '</p>
            <p>Generated on: ' . date('d M Y, h:i A') . '</p>
        </div>';
    
    foreach ($data as $classData) {
        $html .= '<div class="class-section">';
        $html .= '<div class="class-title">' . htmlspecialchars($classData['class_name']) . '</div>';
        
        foreach ($classData['subjects'] as $subjectData) {
            $html .= '<div class="subject-card">';
            $html .= '<div class="subject-header">';
            $html .= '<span class="subject-name">' . htmlspecialchars($subjectData['subject_name']) . '</span>';
            $html .= '</div>';
            
            // Get Top Level Planning for this subject
            $topLevel = $this->getTopLevelPlanning($classData['class_id'], $subjectData['subject_id'], $term_session_id);
            if ($topLevel && !empty($topLevel->objective)) {
                $html .= '<div class="top-level-planning">';
                $html .= '<span class="top-level-label">📚 Top Level Planning:</span>';
                $html .= '<div class="top-level-content">' . $topLevel->objective . '</div>';
                $html .= '</div>';
            }
            
            // Weekly Objectives Table
            $html .= '<table class="weeks-table">';
            $html .= '<thead>';
            $html .= '<tr>';
            $html .= '<th width="15%">Week</th>';
            $html .= '<th width="18%">Week Dates</th>';
            $html .= '<th width="67%">Weekly Objectives / Lesson Plan</th>';
            $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
            
            foreach ($subjectData['weeks'] as $week) {
                $weekDates = date('d M Y', strtotime($week->week_start)) . ' - ' . date('d M Y', strtotime($week->week_end));
                
                $html .= '<tr>';
                $html .= '<td class="week-name-cell">' . htmlspecialchars($week->week_name) . '</td>';
                $html .= '<td class="week-date-cell">' . $weekDates . '</td>';
                $html .= '<td class="objectives-cell">';
                $html .= '<div class="objectives-text">';
                
                if ($week->objectives && trim($week->objectives) != '' && $week->objectives != '<p><br></p>') {
                    $html .= $week->objectives;
                } else {
                    $html .= '<span class="no-objective">— No objectives defined —</span>';
                }
                
                $html .= '</div>';
                $html .= '</td>';
                $html .= '</tr>';
            }
            
            $html .= '</tbody>';
            $html .= '</table>';
            $html .= '</div>'; // Close subject-card
        }
        
        $html .= '</div>'; // Close class-section
    }
    
    $html .= '<div class="report-footer">
                <p>This is a system generated report from School Management System</p>
              </div>
            </body>
            </html>';
    
    return $html;
}

private function getTopLevelPlanning($class_id, $subject_id, $term_session_id)
{
    $campusid = $this->session->get('member_campusid');
    
    $topLevel = $this->db->table('top_level_planning')
        ->select('objective, audio_url')
        ->where('class_id', $class_id)
        ->where('subject_id', $subject_id)
        ->where('term_session_id', $term_session_id)
        ->where('campus_id', $campusid)
        ->where('objective IS NOT NULL')
        ->where('objective !=', '')
        ->where('objective !=', '<p><br></p>')
        ->get()
        ->getRow();
    
    // If not found for this specific class, try without class_id
    if (!$topLevel) {
        $topLevel = $this->db->table('top_level_planning')
            ->select('objective, audio_url')
            ->where('subject_id', $subject_id)
            ->where('term_session_id', $term_session_id)
            ->where('campus_id', $campusid)
            ->where('objective IS NOT NULL')
            ->where('objective !=', '')
            ->where('objective !=', '<p><br></p>')
            ->orderBy('tlp_id', 'DESC')
            ->get()
            ->getRow();
    }
    
    return $topLevel;
}
}