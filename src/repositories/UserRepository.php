<?php

require_once 'BaseRepository.php';
require_once 'interfaces/UserRepositoryInterface.php';

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    protected $tableName = 'Users';

    /**
     * Find user by name
     */
    public function findByName(string $name): ?array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE Name = ?";
        return $this->executeQuerySingle($sql, [$name]);
    }

    /**
     * Check if user exists by name
     */
    public function existsByName(string $name): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->tableName} WHERE Name = ?";
        return $this->executeQueryCount($sql, [$name]) > 0;
    }

    /**
     * Get users with their post counts
     */
    public function getUsersWithPostCounts(): array
    {
        $sql = "SELECT u.*, COUNT(p.id) as post_count 
                FROM {$this->tableName} u 
                LEFT JOIN Posts p ON u.id = p.User_id 
                GROUP BY u.id 
                ORDER BY u.id DESC";
        return $this->executeQuery($sql);
    }

    /**
     * Search users by name pattern
     */
    public function searchByName(string $pattern): array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE Name LIKE ? ORDER BY Name ASC";
        return $this->executeQuery($sql, ['%' . $pattern . '%']);
    }

    /**
     * Get user with their recent posts
     */
    public function getUserWithRecentPosts(int $userId, int $limit = 5): ?array
    {
        $user = $this->findById($userId);
        if (!$user) {
            return null;
        }

        $sql = "SELECT p.* FROM Posts p 
                WHERE p.User_id = ? 
                ORDER BY p.created_at DESC 
                LIMIT ?";
        
        $posts = $this->executeQuery($sql, [$userId, $limit]);
        $user['recent_posts'] = $posts;
        
        return $user;
    }

    /**
     * Get users who have posted in the last N days
     */
    public function getActiveUsers(int $days = 30): array
    {
        $sql = "SELECT DISTINCT u.* FROM {$this->tableName} u 
                INNER JOIN Posts p ON u.id = p.User_id 
                WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) 
                ORDER BY u.Name ASC";
        return $this->executeQuery($sql, [$days]);
    }

    /**
     * Get user statistics
     */
    public function getUserStats(int $userId): array
    {
        $sql = "SELECT 
                    COUNT(p.id) as total_posts,
                    COUNT(CASE WHEN p.ImgUrl IS NOT NULL THEN 1 END) as posts_with_images,
                    MIN(p.created_at) as first_post,
                    MAX(p.created_at) as last_post
                FROM Posts p 
                WHERE p.User_id = ?";
        
        $stats = $this->executeQuerySingle($sql, [$userId]);
        return $stats ?: [];
    }

    /**
     * Create user with validation
     */
    public function createUser(string $name): int|false
    {
        // Check if user already exists
        if ($this->existsByName($name)) {
            return false;
        }

        return $this->create(['Name' => $name]);
    }

    /**
     * Update user name with validation
     */
    public function updateUserName(int $id, string $name): bool
    {
        // Check if another user with this name exists
        $existingUser = $this->findByName($name);
        if ($existingUser && $existingUser['id'] != $id) {
            return false;
        }

        return $this->update($id, ['Name' => $name]);
    }
}
?> 