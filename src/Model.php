<?php
namespace Almhdy\JsonShelter;

class Model
{
    private JsonShelter $db;
    private string $table;

    public function __construct(JsonShelter $db, string $table)
    {
        $this->db = $db;
        $this->table = $table;
    }

    public function create(array $data): void
    {
        $this->db->insertInto($this->table, $data);
    }

    public function find(int $id): ?array
    {
        return $this->db->read($this->table, $id);
    }

    public function all(): array
    {
        return $this->db->readAll($this->table);
    }

    public function update(int $id, array $data): bool
    {
        return $this->db->updateRecord($this->table, $id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->db->deleteRecord($this->table, $id);
    }

    public function where(array $conditions): array
    {
        return $this->db->where($this->table, $conditions);
    }

    public function orderBy(string $field, string $direction = 'asc'): array
    {
        return $this->db->orderBy($this->table, $field, $direction);
    }

    public function limit(int $limit, int $offset = 0): array
    {
        return $this->db->limit($this->table, $limit, $offset);
    }

    public function search(string $field, string $keyword): array
    {
        return $this->db->search($this->table, $field, $keyword);
    }

    public function hasOne(string $relatedTable, string $foreignKey, string $localKey): array
    {
        return $this->db->hasOne($relatedTable, $foreignKey, $localKey);
    }

    public function hasMany(string $relatedTable, string $foreignKey, string $localKey): array
    {
        return $this->db->hasMany($relatedTable, $foreignKey, $localKey);
    }

    public function belongsTo(string $relatedTable, string $foreignKey, string $ownerKey): ?array
    {
        return $this->db->belongsTo($relatedTable, $foreignKey, $ownerKey);
    }
}