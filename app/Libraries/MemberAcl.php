<?php

namespace App\Libraries;

class MemberAcl
{
    /** TTL for cache entries (seconds) */
    private const TTL = 3600;
    /** Safe separator for cache keys (CI4 reserves {}()/\@:) */
    private const SEP = '_';

    /** @var array<string, array{perm:string,inheritted:bool,value:bool,Name:string,id:int}> */
    protected array $perms = [];   // permissions (keyed by permKey)
    protected int   $userID = 0;   // current user id
    /** @var int[] */
    protected array $userRoles = [];   // role IDs for current user
    /** @var array<int, array{id:int,Name:string,Key:string}> */
    protected array $allPerms = [];    // perm dictionary by id

    protected \CodeIgniter\Database\BaseConnection $db;
    protected \CodeIgniter\Cache\CacheInterface   $cache;
    protected \CodeIgniter\Session\Session        $session;

    public function __construct(?int $userID = null)
    {
        $this->db      = db_connect();
        $this->cache   = \Config\Services::cache();
        $this->session = \Config\Services::session();

        $this->userID  = $userID ?? (int) ($this->session->get('member_userid') ?? 0);

        $this->userRoles = $this->getUserRoles();
        $this->allPerms  = $this->getAllPerms('full');
        $this->buildACL();
    }

    /** Build a SAFE cache key */
    private static function key(string $base, string $suffix = ''): string
    {
        // Only use letters, numbers, _ and -
        $base   = preg_replace('/[^A-Za-z0-9_\-]/', '', $base) ?? '';
        $suffix = $suffix === '' ? '' : self::SEP . preg_replace('/[^A-Za-z0-9_\-]/', '', (string) $suffix);
        return $base . $suffix;
    }

    /** Build effective permissions: roles → user overrides, cached */
    public function buildACL(): void
    {
        $cacheKey = self::key('uperms', (string) $this->userID);

        $perms = $this->cache->get($cacheKey);
        if (is_array($perms)) {
            $this->perms = $perms;
            return;
        }

        $perms = [];

        if (! empty($this->userRoles)) {
            $perms = $this->mergeRolePermSetsUnion($perms, $this->getRolePerms($this->userRoles));
        }

        // User-specific overrides win over inherited role permissions.
        $perms = $this->mergePermSets($perms, $this->getUserPerms($this->userID));

        $this->perms = $perms;
        $this->cache->save($cacheKey, $perms, self::TTL);
    }

    /** Merge B into A (B overrides A) */
    private function mergePermSets(array $a, array $b): array
    {
        foreach ($b as $k => $v) {
            $a[$k] = $v;
        }
        return $a;
    }

    /**
     * Union role permissions: granted in ANY role stays granted.
     */
    private function mergeRolePermSetsUnion(array $a, array $b): array
    {
        foreach ($b as $k => $v) {
            if (! isset($a[$k])) {
                $a[$k] = $v;
                continue;
            }

            if (! empty($v['value'])) {
                $a[$k]['value'] = true;
            }
        }

        return $a;
    }

    /** Helpers to read perm metadata */
    public function getPermKeyFromID(int $permID): string|false
    {
        return $this->allPerms[$permID]['Key'] ?? false;
    }

    public function getPermNameFromID(int $permID): string|false
    {
        return $this->allPerms[$permID]['Name'] ?? false;
    }

    public function getPermFromID(int $permID): array|false
    {
        return $this->allPerms[$permID] ?? false;
    }

    public function getRoleNameFromID(int $roleID): string|false
    {
        $allroles = $this->getAllRoles('full');
        return $allroles[$roleID]['Name'] ?? false;
    }

    /** Class/section mapping */
    public function getUserClassSections(?int $userID = null): array
    {
        $userID = $userID ?? $this->userID;
        if (!$userID) return [];

        $builder = $this->db->table('user_roles ur')
            ->select(
                'cs.cls_sec_id,
                 cs.section_id,
                 c.class_id,
                 c.class_name,
                 s.section_name,
                 CONCAT(c.class_name, " - ", s.section_name) AS sectionclassname'
            )
            ->join('roles r', 'r.id = ur.roleID')
            ->join('role_classes rc', 'rc.role_id = r.id')
            ->join('class_section cs', 'cs.cls_sec_id = rc.class_section_id')
            ->join('classes c', 'c.class_id = cs.class_id')
            ->join('sections s', 's.section_id = cs.section_id')
            ->where('ur.userID', (int) $userID)
            ->where('cs.status', 1);

        return $builder->get()->getResultArray();
    }

    /** Fetch role IDs of current user (plan-aware), cached */
    public function getUserRoles(): array
    {
        $userId = (int) $this->userID;
        if ( $userId <= 0 ) return [];

        $cacheKey = self::key('user_roles', (string) $userId);
        try {
            $cached = $this->cache->get($cacheKey);
        } catch (\Throwable $e) {
            $this->cache->delete($cacheKey);
            $cached = null;
        }
        if (is_array($cached)) {
            return $cached;
        }

        // users row
        $user = $this->db->table('users')->where('id', $userId)->get()->getRow();
        if (!$user) return [];

        // campus_bills (active)
        $campusBill = $this->db->table('campus_bills')
            ->where('campus_id', (int) $user->campus_id)
            ->where('status', 1)
            ->limit(1)->get()->getRow();
        if (!$campusBill) return [];

        helper('role');
        $planId = getRolePlanId();

        // Primary mapping: user_roles.roleID stores roles.id
        $primary = $this->db->table('user_roles ur')
            ->distinct()
            ->select('r.id')
            ->join('roles r', 'r.id = ur.roleID AND r.plan_id = ' . $planId, 'inner')
            ->where('ur.userID', $userId)
            ->get()
            ->getResultArray();

        // Legacy mapping: user_roles.roleID stores roles.role_name_id
        $legacy = $this->db->table('user_roles ur')
            ->distinct()
            ->select('r.id')
            ->join('roles r', 'r.role_name_id = ur.roleID AND r.plan_id = ' . $planId, 'inner')
            ->where('ur.userID', $userId)
            ->get()
            ->getResultArray();

        // Merge both mappings because some users have mixed historical data.
        $resp = [];
        foreach (array_merge($primary, $legacy) as $row) {
            $rid = (int) ($row['id'] ?? 0);
            if ($rid > 0) {
                $resp[$rid] = $rid;
            }
        }

        // Remap legacy plan 1/2 role ids to the active plan (e.g. CEO + Teacher on plan 3).
        $rawRows = $this->db->table('user_roles')
            ->select('roleID')
            ->where('userID', $userId)
            ->get()
            ->getResultArray();

        $rawIds = [];
        foreach ($rawRows as $row) {
            $rid = (int) ($row['roleID'] ?? 0);
            if ($rid > 0) {
                $rawIds[] = $rid;
            }
        }

        if ($rawIds !== []) {
            foreach (normalizeRoleIdsForPlan($rawIds, $planId) as $rid) {
                $resp[(int) $rid] = (int) $rid;
            }
        }

        $resp = array_values($resp);

        $this->cache->save($cacheKey, $resp, self::TTL);
        return $resp;
    }

    /** All roles, cached per format */
    public function getAllRoles(string $format = 'ids'): array
    {
        $format = strtolower($format) === 'full' ? 'full' : 'ids';
        $cacheKey = self::key('allroles', $format);

        $cached = $this->cache->get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $rows = $this->db->table('roles')->orderBy('roleName', 'ASC')->get()->getResultArray();
        $resp = [];

        foreach ($rows as $row) {
            $id = (int) $row['id'];
            if ($format === 'full') {
                $resp[$id] = ['id' => $id, 'Name' => $row['roleName']];
            } else {
                $resp[] = $id;
            }
        }

        $this->cache->save($cacheKey, $resp, self::TTL);
        return $resp;
    }

    /** All perms, cached per format */
    public function getAllPerms(string $format = 'ids'): array
    {
        $format = strtolower($format) === 'full' ? 'full' : 'ids';
        $cacheKey = self::key('allperms', $format);

        $cached = $this->cache->get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $rows = $this->db->table('permissions')->orderBy('permName', 'ASC')->get()->getResultArray();
        $resp = [];

        foreach ($rows as $row) {
            $id  = (int) $row['id'];
            $key = (string) $row['permKey'];
            $nm  = (string) $row['permName'];

            if ($format === 'full') {
                $resp[$id] = ['id' => $id, 'Name' => $nm, 'Key' => $key];
            } else {
                $resp[] = $id;
            }
        }

        $this->cache->save($cacheKey, $resp, self::TTL);
        return $resp;
    }

    /** Role-derived permissions (overridable by user perms) */
    public function getRolePerms(array|int $role): array
    {
        $roleIDs = is_array($role) ? array_map('intval', $role) : [(int) $role];
        $roleIDs = array_values(array_filter($roleIDs, static fn($v) => $v > 0));
        if (empty($roleIDs)) return [];

        $rows = $this->db->table('role_perms')
            ->whereIn('roleID', $roleIDs)
            ->orderBy('ID', 'ASC')
            ->get()->getResultArray();

        $perms = [];
        foreach ($rows as $row) {
            $permId = (int) $row['permID'];
            $perm   = $this->getPermFromID($permId);
            if (! $perm || empty($perm['Key'])) {
                continue;
            }

            $key     = strtolower($perm['Key']);
            $granted = (string) $row['value'] === '1';

            if (isset($perms[$key])) {
                if ($granted) {
                    $perms[$key]['value'] = true;
                }
                continue;
            }

            $perms[$key] = [
                'perm'       => $key,
                'inheritted' => true,
                'value'      => $granted,
                'Name'       => $perm['Name'],
                'id'         => $permId,
            ];
        }

        return $perms;
    }

    /** User-specific permissions (override), cached per user */
    public function getUserPerms(int $userID): array
    {
        if ($userID <= 0) return [];

        $cacheKey = self::key('userperms', (string) $userID);
        $cached   = $this->cache->get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $rows = $this->db->table('user_perms')
            ->where('userID', $userID)
            ->orderBy('addDate', 'ASC')
            ->get()->getResultArray();

        $perms = [];
        foreach ($rows as $row) {
            $permId = (int) $row['permID'];
            $perm   = $this->getPermFromID($permId);
            if (!$perm || empty($perm['Key'])) continue;

            $key = strtolower($perm['Key']);
            $perms[$key] = [
                'perm'       => $key,
                'inheritted' => false,
                'value'      => (string) $row['value'] === '1',
                'Name'       => $perm['Name'],
                'id'         => $permId,
            ];
        }

        $this->cache->save($cacheKey, $perms, self::TTL);
        return $perms;
    }

    /** Role check */
    public function userHasRole(int $roleID): bool
    {
        $roleID = (int) $roleID;
        foreach ($this->userRoles as $rid) {
            if ((int) $rid === $roleID) return true;
        }
        return false;
    }

    /** Permission check */
    public function hasPermission(string $permKey): bool
    {
        $k = strtolower($permKey);
        return isset($this->perms[$k]) && $this->perms[$k]['value'] === true;
    }

    /** Return as keyed array or flattened booleans */
    public function getPermArr(string $type = ''): array
    {
        if ($type === '') return $this->perms;

        $flat = [];
        foreach ($this->perms as $k => $p) {
            $flat[$k] = (bool) $p['value'];
        }
        return $flat;
    }

    public function getUsername(int $userID): ?string
    {
        $row = $this->db->table('users')->select('username')->where('id', (int) $userID)->get()->getRowArray();
        return $row['username'] ?? null;
    }

    /* ----------------- Cache invalidation helpers ----------------- */

    /** Clear all cached artifacts for this user (roles, perms, effective) */
    public function clearUserCaches(?int $userId = null): void
    {
        $uid = $userId ?? $this->userID;
        if ($uid <= 0) return;

        $this->cache->delete(self::key('user_roles', (string) $uid));
        $this->cache->delete(self::key('userperms', (string) $uid));
        $this->cache->delete(self::key('uperms', (string) $uid));
    }

    /** Clear shared dictionaries (roles/perms lists) */
    public function clearDictionaries(): void
    {
        $this->cache->delete(self::key('allroles', 'ids'));
        $this->cache->delete(self::key('allroles', 'full'));
        $this->cache->delete(self::key('allperms', 'ids'));
        $this->cache->delete(self::key('allperms', 'full'));
    }

    /** Static helper for seeders and admin permission edits. */
    public static function clearDictionaryCaches(): void
    {
        $cache = \Config\Services::cache();
        $sep   = self::SEP;

        foreach (['allroles', 'allperms'] as $base) {
            foreach (['ids', 'full'] as $format) {
                $cache->delete($base . $sep . $format);
            }
        }
    }
}
