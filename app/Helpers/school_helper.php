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
?>