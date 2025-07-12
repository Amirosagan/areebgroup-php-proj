<?php

require_once 'BaseRepository.php';
require_once 'interfaces/PostRepositoryInterface.php';

class PostRepository extends BaseRepository implements PostRepositoryInterface
{
    protected $tableName = 'Posts';

    /**
     * Find posts by user ID
     */
    public function findAll(): array
    {
        $sql = "SELECT * FROM {$this->tableName}";
        return $this->executeQuery($sql);
    }

    public function findByUserId(int $userId): array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE User_id = ? ORDER BY created_at DESC";
        return $this->executeQuery($sql, [$userId]);
    }

    /**
     * Find posts with user information
     */
    public function findAllWithUsers(): array
    {
        $sql = "SELECT p.*, u.Name as user_name 
                FROM {$this->tableName} p 
                INNER JOIN Users u ON p.User_id = u.id 
                ORDER BY p.created_at DESC";
        return $this->executeQuery($sql);
    }

    /**
     * Find post by ID with user information
     */
    public function findByIdWithUser(int $id): ?array
    {
        $sql = "SELECT p.*, u.Name as user_name 
                FROM {$this->tableName} p 
                INNER JOIN Users u ON p.User_id = u.id 
                WHERE p.id = ?";
        return $this->executeQuerySingle($sql, [$id]);
    }

    /**
     * Search posts by title
     */
    public function searchByTitle(string $title): array
    {
        $sql = "SELECT p.*, u.Name as user_name 
                FROM {$this->tableName} p 
                INNER JOIN Users u ON p.User_id = u.id 
                WHERE p.Title LIKE ? 
                ORDER BY p.created_at DESC";
        return $this->executeQuery($sql, ['%' . $title . '%']);
    }

    /**
     * Get recent posts
     */
    public function getRecentPosts(int $limit = 10): array
    {
        $sql = "SELECT p.*, u.Name as user_name 
                FROM {$this->tableName} p 
                INNER JOIN Users u ON p.User_id = u.id 
                ORDER BY p.created_at DESC 
                LIMIT ?";
        return $this->executeQuery($sql, [$limit]);
    }

    /**
     * Count posts by user
     */
    public function countByUserId(int $userId): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->tableName} WHERE User_id = ?";
        return $this->executeQueryCount($sql, [$userId]);
    }

    /**
     * Find posts with images
     */
    public function findPostsWithImages(): array
    {
        $sql = "SELECT p.*, u.Name as user_name 
                FROM {$this->tableName} p 
                INNER JOIN Users u ON p.User_id = u.id 
                WHERE p.ImgUrl IS NOT NULL AND p.ImgUrl != '' 
                ORDER BY p.created_at DESC";
        return $this->executeQuery($sql);
    }

    /**
     * Create post with validation
     */
    public function createPost(array $data): int|false
    {
        // Validate required fields
        if (empty($data['Title']) || empty($data['User_id'])) {
            return false;
        }

        // Validate user exists
        $userRepo = new UserRepository($this->database);
        if (!$userRepo->findById($data['User_id'])) {
            return false;
        }

        return $this->create($data);
    }

    /**
     * Update post with validation
     */
    public function updatePost(int $id, array $data): bool
    {
        // Validate user exists if User_id is being updated
        if (!empty($data['User_id'])) {
            $userRepo = new UserRepository($this->database);
            if (!$userRepo->findById($data['User_id'])) {
                return false;
            }
        }

        return $this->update($id, $data);
    }

    /**
     * Search posts by content
     */
    public function searchByContent(string $content): array
    {
        $sql = "SELECT p.*, u.Name as user_name 
                FROM {$this->tableName} p 
                INNER JOIN Users u ON p.User_id = u.id 
                WHERE p.content LIKE ? 
                ORDER BY p.created_at DESC";
        return $this->executeQuery($sql, ['%' . $content . '%']);
    }

    /**
     * Get posts by date range
     */
    public function getPostsByDateRange(string $startDate, string $endDate): array
    {
        $sql = "SELECT p.*, u.Name as user_name 
                FROM {$this->tableName} p 
                INNER JOIN Users u ON p.User_id = u.id 
                WHERE p.created_at BETWEEN ? AND ? 
                ORDER BY p.created_at DESC";
        return $this->executeQuery($sql, [$startDate, $endDate]);
    }

    /**
     * Get popular posts (posts with images)
     */
    public function getPopularPosts(int $limit = 10): array
    {
        $sql = "SELECT p.*, u.Name as user_name 
                FROM {$this->tableName} p 
                INNER JOIN Users u ON p.User_id = u.id 
                WHERE p.ImgUrl IS NOT NULL 
                ORDER BY p.created_at DESC 
                LIMIT ?";
        return $this->executeQuery($sql, [$limit]);
    }

    /**
     * Get posts statistics
     */
    public function getPostsStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_posts,
                    COUNT(CASE WHEN ImgUrl IS NOT NULL THEN 1 END) as posts_with_images,
                    COUNT(DISTINCT User_id) as unique_users,
                    AVG(LENGTH(content)) as avg_content_length
                FROM {$this->tableName}";
        
        $stats = $this->executeQuerySingle($sql);
        return $stats ?: [];
    }

    /**
     * Delete post and clean up image file
     */
    public function deletePostWithImage(int $id): bool
    {
        $post = $this->findById($id);
        if (!$post) {
            return false;
        }

        // Delete associated image file if exists
        if (!empty($post['ImgUrl'])) {
            $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . basename($post['ImgUrl']);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        return $this->delete($id);
    }
}
?> 