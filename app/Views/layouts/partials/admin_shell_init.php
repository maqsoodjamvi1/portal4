<?php
/**
 * Shared variables for admin shell (context bar + app bar).
 * Expects: $schoolinfo, $campuses, $academic_sessions, $curr_campus_id, $curr_session_id, $user
 */

$session = \Config\Services::session();
helper(['campus', 'permission']);

$curr_campus_id  = (int) ($curr_campus_id ?? $session->get('member_campusid') ?? 0);
$curr_session_id = (int) ($curr_session_id ?? $session->get('member_sessionid') ?? 0);
$campuses          = $campuses ?? [];
$academic_sessions = $academic_sessions ?? [];
$schoolinfo        = $schoolinfo ?? null;
$user              = $user ?? (function_exists('getLoginUser') ? getLoginUser() : null);

$host = $_SERVER['HTTP_HOST'] ?? '';
$isTrialHost = ($host === 'trial.timesoftsol.com');
$isDemoHost  = ($host === 'demo.timesoftsol.com');
$hasAppBanner = $isTrialHost || $isDemoHost;

// Resolve primary role for campus menu visibility
$db = \Config\Database::connect();
$role_name_info = null;
$userid = (int) $session->get('member_userid');

if ($userid > 0) {
    $currentCampusBill = $db->table('campus_bills')
        ->where('campus_id', $curr_campus_id)
        ->orderBy('bill_id', 'DESC')
        ->get()
        ->getRow();
    $plan_id = (int) ($currentCampusBill->plan_id ?? 0);

    $userRoleRows = $db->table('user_roles')
        ->select('roleID')
        ->where('userID', $userid)
        ->orderBy('addDate', 'ASC')
        ->get()
        ->getResultArray();

    $storedRoleIds = array_values(array_filter(array_map(static fn ($r) => (int) ($r['roleID'] ?? 0), $userRoleRows)));

    if (!empty($storedRoleIds)) {
        $roleRows = [];
        if ($plan_id > 0) {
            $roleRows = $db->table('roles r')
                ->select('r.id, r.role_name_id, rn.rolename, rn.parent_id')
                ->join('role_name rn', 'rn.role_name_id = r.role_name_id', 'inner')
                ->where('r.plan_id', $plan_id)
                ->whereIn('r.id', $storedRoleIds)
                ->get()
                ->getResult();

            if (empty($roleRows)) {
                $roleRows = $db->table('roles r')
                    ->select('r.id, r.role_name_id, rn.rolename, rn.parent_id')
                    ->join('role_name rn', 'rn.role_name_id = r.role_name_id', 'inner')
                    ->where('r.plan_id', $plan_id)
                    ->whereIn('r.role_name_id', $storedRoleIds)
                    ->get()
                    ->getResult();
            }
        }

        if (!empty($roleRows)) {
            $roleNameRows = $db->table('role_name')
                ->select('role_name_id, parent_id, rolename')
                ->get()
                ->getResultArray();

            $roleNameMap = [];
            foreach ($roleNameRows as $rr) {
                $rid = (int) ($rr['role_name_id'] ?? 0);
                if ($rid > 0) {
                    $roleNameMap[$rid] = [
                        'parent_id' => (int) ($rr['parent_id'] ?? 0),
                        'rolename'  => (string) ($rr['rolename'] ?? ''),
                    ];
                }
            }

            $depthOf = static function (int $roleNameId) use (&$depthOf, $roleNameMap): int {
                $depth = 0;
                $cursor = $roleNameId;
                $guard = 0;
                while (isset($roleNameMap[$cursor]) && (int) $roleNameMap[$cursor]['parent_id'] > 0 && $guard < 50) {
                    $cursor = (int) $roleNameMap[$cursor]['parent_id'];
                    $depth++;
                    $guard++;
                }
                return $depth;
            };

            usort($roleRows, static function ($a, $b) use ($depthOf) {
                $da  = $depthOf((int) ($a->role_name_id ?? 0));
                $dbb = $depthOf((int) ($b->role_name_id ?? 0));
                if ($da === $dbb) {
                    return ((int) ($a->role_name_id ?? 0)) <=> ((int) ($b->role_name_id ?? 0));
                }
                return $da <=> $dbb;
            });

            $allRoleNames = array_values(array_filter(array_map(
                static fn ($row) => trim((string) ($row->rolename ?? '')),
                $roleRows
            )));

            $role_name_info = (object) [
                'role_name_id' => (int) ($roleRows[0]->role_name_id ?? 0),
                'rolename'     => $allRoleNames !== []
                    ? implode(', ', $allRoleNames)
                    : (string) ($roleRows[0]->rolename ?? ''),
                'all_rolenames' => $allRoleNames,
            ];
        }
    }
}

helper('role');
$isDirectorSystemRole = function_exists('userHasAnyRoleNameLike')
    ? userHasAnyRoleNameLike('director system')
    : (strpos(strtolower(trim((string) ($role_name_info->rolename ?? ''))), 'director system') !== false);
$canAccessCampusMenu  = (hasPermission('admin-campus') || $isDirectorSystemRole);
$canSelectSession     = hasPermission('admin-view-global-session');
$showCampusSelector   = $canAccessCampusMenu && !empty($schoolinfo) && (int) ($schoolinfo->system_id ?? 0) !== 60;

$expiryInfo = ($curr_campus_id > 0) ? getCampusExpiryInfo($curr_campus_id) : null;

// Labels for mobile workspace summary
$activeCampusLabel = '';
if ($showCampusSelector && !empty($campuses)) {
    foreach ($campuses as $campus) {
        if ((int) $campus->campus_id === $curr_campus_id) {
            $activeCampusLabel = (string) $campus->campus_name;
            break;
        }
    }
}

$activeSessionLabel = '';
if ($canSelectSession && !empty($academic_sessions)) {
    foreach ($academic_sessions as $academic_session) {
        if ((int) $academic_session->session_id === $curr_session_id) {
            $activeSessionLabel = (string) $academic_session->session_name;
            break;
        }
    }
}

$workspaceSummaryParts = array_filter([$activeCampusLabel, $activeSessionLabel]);
$workspaceSummary = !empty($workspaceSummaryParts)
    ? implode(' · ', $workspaceSummaryParts)
    : 'Workspace';
