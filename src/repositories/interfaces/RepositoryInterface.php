<?php

interface RepositoryInterface 
{
    /**
     * Find a record by ID
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array;

    /**
     * Find all records
     * @return array
     */
    public function findAll(): array;

    /**
     * Create a new record
     * @param array $data
     * @return int|false The ID of the created record or false on failure
     */
    public function create(array $data);

    /**
     * Update a record by ID
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a record by ID
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Count total records
     * @return int
     */
    public function count(): int;
}
?> 