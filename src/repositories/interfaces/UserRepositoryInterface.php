<?php

require_once 'RepositoryInterface.php';

interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Find user by name
     * @param string $name
     * @return array|null
     */
    public function findByName(string $name): ?array;

    /**
     * Check if user exists by name
     * @param string $name
     * @return bool
     */
    public function existsByName(string $name): bool;

    /**
     * Get users with their post counts
     * @return array
     */
    public function getUsersWithPostCounts(): array;

    /**
     * Search users by name pattern
     * @param string $pattern
     * @return array
     */
    public function searchByName(string $pattern): array;
}
?> 