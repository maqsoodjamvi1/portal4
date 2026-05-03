<?php

// function check_permission($permission)
// {
//     $role = session()->get('role');
//     return in_array($role, ['admin', 'teacher']);
// }

// function getSchoolInfo()
// {
//     return (object)[
//         'system_id' => session()->get('school_id') ?? 1,
//         'school_name' => 'Default School'
//     ];
// }

if (!function_exists('getSchoolInfo')) {
    function getSchoolInfo()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('system');
        return $builder->get()->getRow();
    }
}

/**
 * Font size for a one-line school title: full size up to $refChars (UTF-8),
 * then scale down proportionally so long names still fit on one line.
 *
 * @param string $name      School display name
 * @param int    $refChars  Character count at which base size applies (default 22)
 * @param float  $baseSize  Font size for names up to $refChars (pt or px in caller)
 * @param float  $minSize   Floor so text stays readable
 */
if (!function_exists('school_name_fit_font_size')) {
    function school_name_fit_font_size(string $name, int $refChars = 22, float $baseSize = 11.0, float $minSize = 6.5): float
    {
        $name = trim($name);
        $len  = mb_strlen($name, 'UTF-8');
        if ($len <= 0 || $len <= $refChars) {
            return $baseSize;
        }

        return max($minSize, round($baseSize * ($refChars / $len), 2));
    }
}

/**
 * Class label next to student name on fee challans: prefer DB short name, else compact "Grade N" → "N".
 */
if (! function_exists('fee_chalan_class_badge_text')) {
    function fee_chalan_class_badge_text(?string $classShortName, ?string $classFullName): string
    {
        $src = trim((string) $classShortName);
        if ($src === '') {
            $src = trim((string) $classFullName);
        }
        if ($src === '') {
            return '';
        }
        $out = preg_replace('/^Grade\s+/iu', '', $src);

        return $out !== '' ? $out : $src;
    }
}
?>