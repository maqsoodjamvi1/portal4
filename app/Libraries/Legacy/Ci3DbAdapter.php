<?php

namespace App\Libraries\Legacy;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\ResultInterface;

/**
 * Minimal CI3-style database API for legacy Admin controllers.
 */
class Ci3DbAdapter
{
    protected BaseConnection $conn;

    protected ?\CodeIgniter\Database\BaseBuilder $builder = null;

    /** @var list<array{0: array<string, mixed>|string, 1: mixed, 2: mixed}> */
    protected array $pendingWhere = [];

    /** @var list<array{0: string, 1: mixed}> */
    protected array $pendingSelect = [];

    /** @var list<array{0: string, 1: string}> */
    protected array $pendingOrderBy = [];

    /** @var array{0: int, 1: int}|null */
    protected ?array $pendingLimit = null;

    /** @var list<string> */
    protected array $pendingGroupBy = [];

    public function __construct(BaseConnection $conn)
    {
        $this->conn = $conn;
    }

    public function select($select = '*', $escape = null): self
    {
        if ($this->builder !== null) {
            $this->builder->select($select, $escape);

            return $this;
        }

        $this->pendingSelect[] = [$select, $escape];

        return $this;
    }

    public function from($table): self
    {
        $this->builder = $this->conn->table($table);

        foreach ($this->pendingSelect as [$select, $escape]) {
            $this->builder->select($select, $escape);
        }
        $this->pendingSelect = [];

        return $this;
    }

    /**
     * @param array<string, mixed>|string $where
     */
    public function where($where, $value = null, $escape = null): self
    {
        if ($this->builder !== null) {
            if (is_array($where)) {
                $this->builder->where($where);
            } else {
                $this->builder->where($where, $value, $escape);
            }

            return $this;
        }

        $this->pendingWhere[] = [$where, $value, $escape];

        return $this;
    }

    protected function applyPendingWhere(\CodeIgniter\Database\BaseBuilder $builder): void
    {
        foreach ($this->pendingWhere as [$where, $value, $escape]) {
            if (is_array($where)) {
                $builder->where($where);
            } else {
                $builder->where($where, $value, $escape);
            }
        }
        $this->pendingWhere = [];
    }

    public function join(string $table, string $cond, string $type = ''): self
    {
        if ($this->builder === null) {
            throw new \RuntimeException('CI3 DB: call from() before join().');
        }
        $this->builder->join($table, $cond, $type);

        return $this;
    }

    public function group_by($field): self
    {
        if ($this->builder !== null) {
            $this->builder->groupBy($field);

            return $this;
        }

        $this->pendingGroupBy[] = $field;

        return $this;
    }

    public function order_by(string $field, string $direction = 'ASC'): self
    {
        if ($this->builder !== null) {
            $this->builder->orderBy($field, $direction);

            return $this;
        }

        $this->pendingOrderBy[] = [$field, $direction];

        return $this;
    }

    public function limit($value, $offset = 0): self
    {
        if ($this->builder !== null) {
            $this->builder->limit((int) $value, (int) $offset);

            return $this;
        }

        $this->pendingLimit = [(int) $value, (int) $offset];

        return $this;
    }

    /**
     * @param array<string, mixed>|string $data
     */
    public function set($data, bool $escape = true): self
    {
        if ($this->builder === null) {
            throw new \RuntimeException('CI3 DB: call from() before set().');
        }
        $this->builder->set($data, '', $escape);

        return $this;
    }

    protected function applyPendingClauses(\CodeIgniter\Database\BaseBuilder $builder): void
    {
        $this->applyPendingWhere($builder);

        foreach ($this->pendingGroupBy as $field) {
            $builder->groupBy($field);
        }
        $this->pendingGroupBy = [];

        foreach ($this->pendingOrderBy as [$field, $direction]) {
            $builder->orderBy($field, $direction);
        }
        $this->pendingOrderBy = [];

        if ($this->pendingLimit !== null) {
            [$value, $offset] = $this->pendingLimit;
            $builder->limit($value, $offset);
            $this->pendingLimit = null;
        }
    }

    public function get($table = '')
    {
        if ($table !== '' && $table !== null) {
            $builder = $this->conn->table($table);
            $this->applyPendingClauses($builder);

            return new Ci3ResultAdapter($builder->get());
        }

        if ($this->builder === null) {
            throw new \RuntimeException('CI3 DB: call from() or pass table to get().');
        }

        $result       = $this->builder->get();
        $this->builder = null;

        return new Ci3ResultAdapter($result);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insert(string $table, array $data): bool
    {
        return $this->conn->table($table)->insert($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $table, array $data): bool
    {
        if ($this->builder !== null) {
            $result        = (bool) $this->builder->update($data);
            $this->builder = null;

            return $result;
        }

        $builder = $this->conn->table($table);
        $this->applyPendingWhere($builder);

        return $builder->update($data);
    }

    public function delete(string $table): bool
    {
        if ($this->builder !== null) {
            return (bool) $this->builder->delete();
        }

        return $this->conn->table($table)->delete();
    }

    public function insert_id(): int
    {
        return (int) $this->conn->insertID();
    }

    public function escape($str): string
    {
        return $this->conn->escape($str);
    }

    public function query(string $sql)
    {
        $result = $this->conn->query($sql);

        if ($result === false) {
            throw new \RuntimeException('CI3 DB query failed.');
        }

        return new Ci3ResultAdapter($result);
    }

    public function trans_start(): void
    {
        $this->conn->transStart();
    }

    public function trans_begin(): void
    {
        $this->trans_start();
    }

    public function trans_complete(): bool
    {
        return $this->conn->transComplete();
    }
}

class Ci3ResultAdapter
{
    protected ResultInterface $result;

    public function __construct(ResultInterface $result)
    {
        $this->result = $result;
    }

    public function row(?string $type = 'object')
    {
        return $this->result->getRow(0, $type);
    }

    public function result(?string $type = 'object'): array
    {
        return $this->result->getResult($type);
    }

    public function row_array(): ?array
    {
        return $this->result->getRowArray();
    }

    public function result_array(): array
    {
        return $this->result->getResultArray();
    }
}
