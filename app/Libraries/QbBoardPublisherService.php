<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

class QbBoardPublisherService
{
    /** Global boards/publishers shared across all schools and board prep portal. */
    public const GLOBAL_SYSTEM_ID = 0;

    protected BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function tablesReady(): bool
    {
        return $this->db->tableExists('qb_board_publishers')
            && $this->db->tableExists('qb_topic_board_publishers');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listForSystem(int $systemId, bool $activeOnly = true): array
    {
        unset($systemId);

        return $this->listGlobal($activeOnly);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listGlobal(bool $activeOnly = true): array
    {
        if (! $this->tablesReady()) {
            return [];
        }

        $select = 'id, name, short_code, sort_order, status';
        if ($this->db->fieldExists('logo', 'qb_board_publishers')) {
            $select .= ', logo';
        }

        $builder = $this->db->table('qb_board_publishers')
            ->select($select)
            ->where('system_id', self::GLOBAL_SYSTEM_ID);

        if ($activeOnly) {
            $builder->where('status', 1);
        }

        return $builder
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
    }

    public static function logoUrl(?string $logo): ?string
    {
        $logo = trim((string) $logo);
        if ($logo === '') {
            return null;
        }

        $basename = basename($logo);
        $paths    = [
            rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'board_logos' . DIRECTORY_SEPARATOR . $basename,
            rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $basename,
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                if (str_contains($path, 'board_logos')) {
                    return base_url('uploads/board_logos/' . $basename);
                }

                return base_url('uploads/' . $basename);
            }
        }

        return base_url('uploads/board_logos/' . $basename);
    }

    /**
     * @param array<string, mixed> $data
     * @return array{ok: bool, id?: int, msg?: string}
     */
    public function saveEntry(array $data, ?int $id = null, $logoFile = null, bool $removeLogo = false): array
    {
        if (! $this->tablesReady()) {
            return ['ok' => false, 'msg' => 'Boards/Publisher tables are not ready.'];
        }

        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            return ['ok' => false, 'msg' => 'Name is required.'];
        }

        $shortCode = trim((string) ($data['short_code'] ?? ''));
        $sortOrder = (int) ($data['sort_order'] ?? 0);
        $status    = (int) ($data['status'] ?? 1) === 1 ? 1 : 0;
        $now       = date('Y-m-d H:i:s');

        $payload = [
            'system_id'  => self::GLOBAL_SYSTEM_ID,
            'name'       => $name,
            'short_code' => $shortCode !== '' ? $shortCode : null,
            'sort_order' => $sortOrder,
            'status'     => $status,
        ];

        $existingLogo = null;
        if ($id !== null && $id > 0) {
            $existing = $this->db->table('qb_board_publishers')
                ->select('logo')
                ->where('id', $id)
                ->where('system_id', self::GLOBAL_SYSTEM_ID)
                ->get()
                ->getRow();
            if (! $existing) {
                return ['ok' => false, 'msg' => 'Entry not found.'];
            }
            $existingLogo = $existing->logo ?? null;
        }

        if ($this->db->fieldExists('logo', 'qb_board_publishers')) {
            if ($removeLogo) {
                $payload['logo'] = null;
            } elseif ($logoFile !== null && method_exists($logoFile, 'isValid') && $logoFile->isValid() && ! $logoFile->hasMoved()) {
                $uploadDir = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'board_logos' . DIRECTORY_SEPARATOR;
                $stored    = (new SecureUploadService())->storeImage($logoFile, $uploadDir);
                if ($stored === null) {
                    return ['ok' => false, 'msg' => 'Logo upload failed. Use JPG/PNG/WebP under 2 MB.'];
                }
                $payload['logo'] = $stored;
            } elseif ($id !== null && $id > 0) {
                $payload['logo'] = $existingLogo;
            }
        }

        if ($id !== null && $id > 0) {
            $this->db->table('qb_board_publishers')
                ->where('id', $id)
                ->where('system_id', self::GLOBAL_SYSTEM_ID)
                ->update($payload);

            return ['ok' => true, 'id' => $id];
        }

        if ($this->db->fieldExists('logo', 'qb_board_publishers') && ! array_key_exists('logo', $payload)) {
            $payload['logo'] = null;
        }

        $payload['created_at'] = $now;
        $this->db->table('qb_board_publishers')->insert($payload);

        return ['ok' => true, 'id' => (int) $this->db->insertID()];
    }

    /**
     * @return list<int>
     */
    public function getIdsForTopic(int $topicId): array
    {
        if (! $this->tablesReady() || $topicId <= 0) {
            return [];
        }

        $rows = $this->db->table('qb_topic_board_publishers')
            ->select('board_publisher_id')
            ->where('topic_id', $topicId)
            ->get()
            ->getResultArray();

        $ids = [];
        foreach ($rows as $row) {
            $id = (int) ($row['board_publisher_id'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    /**
     * @param list<int> $topicIds
     * @return array<int, list<int>>
     */
    public function getIdsMapForTopics(array $topicIds): array
    {
        $topicIds = array_values(array_unique(array_filter(array_map('intval', $topicIds))));
        if (! $this->tablesReady() || $topicIds === []) {
            return [];
        }

        $rows = $this->db->table('qb_topic_board_publishers')
            ->select('topic_id, board_publisher_id')
            ->whereIn('topic_id', $topicIds)
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($topicIds as $tid) {
            $map[$tid] = [];
        }

        foreach ($rows as $row) {
            $tid = (int) ($row['topic_id'] ?? 0);
            $bid = (int) ($row['board_publisher_id'] ?? 0);
            if ($tid > 0 && $bid > 0) {
                $map[$tid][] = $bid;
            }
        }

        return $map;
    }

    /**
     * @param list<int> $boardPublisherIds
     */
    public function syncTopicLinks(int $topicId, array $boardPublisherIds): void
    {
        if (! $this->tablesReady() || $topicId <= 0) {
            return;
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $boardPublisherIds))));

        $this->db->table('qb_topic_board_publishers')
            ->where('topic_id', $topicId)
            ->delete();

        if ($ids === []) {
            return;
        }

        $batch = [];
        foreach ($ids as $id) {
            $batch[] = [
                'topic_id'           => $topicId,
                'board_publisher_id' => $id,
            ];
        }

        $this->db->table('qb_topic_board_publishers')->insertBatch($batch);
    }

    /**
     * Topic matches when it has no tags (universal) or shares at least one selected tag.
     *
     * @param list<int> $topicIds
     * @param list<int> $boardPublisherIds
     * @return list<int>
     */
    public function filterTopicIdsByBoardPublishers(array $topicIds, array $boardPublisherIds): array
    {
        $topicIds = array_values(array_unique(array_filter(array_map('intval', $topicIds))));
        $boardPublisherIds = array_values(array_unique(array_filter(array_map('intval', $boardPublisherIds))));

        if ($topicIds === [] || $boardPublisherIds === [] || ! $this->tablesReady()) {
            return $topicIds;
        }

        $tagged = $this->db->table('qb_topic_board_publishers')
            ->select('topic_id')
            ->distinct()
            ->whereIn('topic_id', $topicIds)
            ->get()
            ->getResultArray();

        $taggedSet = [];
        foreach ($tagged as $row) {
            $taggedSet[(int) ($row['topic_id'] ?? 0)] = true;
        }

        $matched = $this->db->table('qb_topic_board_publishers')
            ->select('topic_id')
            ->distinct()
            ->whereIn('topic_id', $topicIds)
            ->whereIn('board_publisher_id', $boardPublisherIds)
            ->get()
            ->getResultArray();

        $matchedSet = [];
        foreach ($matched as $row) {
            $matchedSet[(int) ($row['topic_id'] ?? 0)] = true;
        }

        $out = [];
        foreach ($topicIds as $tid) {
            if (! isset($taggedSet[$tid]) || isset($matchedSet[$tid])) {
                $out[] = $tid;
            }
        }

        return $out;
    }

    /**
     * SQL subquery condition for topic visibility by board/publisher filter.
     *
     * @param list<int> $boardPublisherIds
     */
    public function topicMatchesBoardFilterSql(string $topicIdColumn, array $boardPublisherIds): ?string
    {
        $boardPublisherIds = array_values(array_unique(array_filter(array_map('intval', $boardPublisherIds))));
        if ($boardPublisherIds === [] || ! $this->tablesReady()) {
            return null;
        }

        $in = implode(',', $boardPublisherIds);

        return "(
            NOT EXISTS (
                SELECT 1 FROM qb_topic_board_publishers tbp0
                WHERE tbp0.topic_id = {$topicIdColumn}
            )
            OR EXISTS (
                SELECT 1 FROM qb_topic_board_publishers tbp1
                WHERE tbp1.topic_id = {$topicIdColumn}
                  AND tbp1.board_publisher_id IN ({$in})
            )
        )";
    }

    /**
     * @return array<int, list<array{id:int,name:string,short_code:?string}>>
     */
    public function getLabelsMapForTopics(array $topicIds): array
    {
        $topicIds = array_values(array_unique(array_filter(array_map('intval', $topicIds))));
        if (! $this->tablesReady() || $topicIds === []) {
            return [];
        }

        $rows = $this->db->table('qb_topic_board_publishers tbp')
            ->select('tbp.topic_id, bp.id, bp.name, bp.short_code')
            ->join('qb_board_publishers bp', 'bp.id = tbp.board_publisher_id', 'inner')
            ->whereIn('tbp.topic_id', $topicIds)
            ->where('bp.status', 1)
            ->orderBy('bp.sort_order', 'ASC')
            ->orderBy('bp.name', 'ASC')
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($topicIds as $tid) {
            $map[$tid] = [];
        }

        foreach ($rows as $row) {
            $tid = (int) ($row['topic_id'] ?? 0);
            if ($tid <= 0) {
                continue;
            }
            $map[$tid][] = [
                'id'         => (int) ($row['id'] ?? 0),
                'name'       => (string) ($row['name'] ?? ''),
                'short_code' => $row['short_code'] !== null ? (string) $row['short_code'] : null,
            ];
        }

        return $map;
    }

    public function deleteEntry(int $id): bool
    {
        if (! $this->tablesReady() || $id <= 0) {
            return false;
        }

        $this->db->table('qb_topic_board_publishers')
            ->where('board_publisher_id', $id)
            ->delete();

        $this->db->table('qb_board_publishers')
            ->where('id', $id)
            ->where('system_id', self::GLOBAL_SYSTEM_ID)
            ->delete();

        return $this->db->affectedRows() > 0;
    }
}
