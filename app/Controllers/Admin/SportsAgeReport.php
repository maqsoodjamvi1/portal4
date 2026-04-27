<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SportsAgeReport extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = db_connect();
        helper(['url']);
    }

    /**
     * Age-wise students report for whole campus.
     * - Uses date_of_birth_age when db_status = 1
     * - Uses date_of_birth when db_status = 0
     * - Includes students with zero participation (count = 0)
     */
    public function index()
    {
        $campusId  = (int) (session('member_campusid')  ?? 0);
        $sessionId = (int) (session('member_sessionid') ?? 0);

        $b = $this->db->table('students s');

        // Base student + DOB columns
        $b->select('
            s.student_id,
            s.first_name,
            s.last_name,
            s.profile_photo,
            s.date_of_birth,
            s.date_of_birth_age,
            s.db_status,
            s.house_id
        ');

        // Class
        $b->select('COALESCE(c.class_short_name, c.class_name) AS class_short', false);

        // House
        $b->select('h.house_name, h.color_code AS house_color');

        // Participation count in sports events (for selected session if available)
        $sub = '(SELECT COUNT(*) FROM sports_event_entries se 
                 WHERE se.student_id = s.student_id';
        if ($sessionId > 0) {
            $sub .= ' AND se.session_id = ' . $this->db->escape($sessionId);
        }
        $sub .= ') AS participation_count';

        $b->select($sub, false);

        // Joins
        $b->join('student_class sc', 'sc.student_id = s.student_id' . ($sessionId > 0 ? ' AND sc.session_id = ' . $this->db->escape($sessionId) : ''), 'left');
        $b->join('class_section cs', 'cs.cls_sec_id = sc.cls_sec_id', 'left');
        $b->join('classes c', 'c.class_id = cs.class_id', 'left');
        $b->join('sports_houses h', 'h.house_id = s.house_id', 'left');

        if ($campusId > 0) {
            $b->where('s.campus_id', $campusId);
        }

        $b->where('s.status', 1);
        $b->orderBy('s.gender', 'ASC');
        $b->orderBy('s.first_name', 'ASC');
        $rows = $b->get()->getResultArray();

        // Bucket students by age in YEARS
        $ageBuckets = [];

        foreach ($rows as $row) {
            $dob = null;

            if ((int) ($row['db_status'] ?? 0) === 1 && ! empty($row['date_of_birth_age']) && $row['date_of_birth_age'] !== '0000-00-00') {
                $dob = $row['date_of_birth_age'];
            } elseif (! empty($row['date_of_birth']) && $row['date_of_birth'] !== '0000-00-00') {
                $dob = $row['date_of_birth'];
            }

            $ageYears = $this->calculateAgeYears($dob);
            if ($ageYears <= 0) {
                // Skip invalid ages (no DOB), you can keep them in a "Unknown" bucket if needed
                continue;
            }

            $row['age_years'] = $ageYears;

            // For safety, normalise some fields
            $row['participation_count'] = (int) ($row['participation_count'] ?? 0);
            $row['class_name']          = $row['class_name'] ?? '';
            $row['house_name']          = $row['house_name'] ?? '';
            $row['color_code']          = $row['color_code'] ?? '#888';

            $ageBuckets[$ageYears][] = $row;
        }

        // Sort ages ascending
        ksort($ageBuckets);

        // Sort inside each age by house then name
        foreach ($ageBuckets as $age => &$list) {
            usort($list, function ($a, $b) {
                $h1 = $a['house_name'] ?? '';
                $h2 = $b['house_name'] ?? '';
                if ($h1 !== $h2) {
                    return strcmp($h1, $h2);
                }
                $n1 = ($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? '');
                $n2 = ($b['first_name'] ?? '') . ' ' . ($b['last_name'] ?? '');
                return strcmp($n1, $n2);
            });
        }
        unset($list);

       return view('admin/sports/age_report', [
    'byAge' => $ageBuckets,   // <-- FIXED
]);
    }

    /**
     * Calculate age in full years from a YYYY-mm-dd date.
     */



private function calculateAgeYears(?string $dob): int
{
    /**
     * NEW LOGIC:
     * When this function is called, $dob will actually receive
     * an ARRAY in this format:
     *
     * [
     *   'db_status' => 0|1,
     *   'date_of_birth' => 'YYYY-mm-dd',
     *   'date_of_birth_age' => 'YYYY-mm-dd'
     * ]
     *
     * To preserve function signature, we detect & extract values here.
     */

    if (is_array($dob)) {
        $dbStatus = (int)($dob['db_status'] ?? 0);

        // choose the correct DOB based on db_status
        if ($dbStatus === 1) {
            $dob = $dob['date_of_birth_age'] ?? null;
        } else {
            $dob = $dob['date_of_birth'] ?? null;
        }
    }

    // Now $dob is a single date string ("YYYY-mm-dd")
    if (! $dob || $dob === '0000-00-00') {
        return 0;
    }

    try {
        $d = new \DateTime($dob);
        $n = new \DateTime();

        $diff = $d->diff($n);

        $years  = (int) $diff->y;
        $months = (int) $diff->m;

        // Rounding rule:
        if ($months >= 6) {
            $years += 1;
        }

        return $years;

    } catch (\Throwable $e) {
        return 0;
    }
}

}
