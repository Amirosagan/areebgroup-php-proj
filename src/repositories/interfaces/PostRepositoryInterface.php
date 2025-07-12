<?php

require_once 'RepositoryInterface.php';

interface PostRepositoryInterface extends RepositoryInterface
{
    /**
     * Find posts by user ID
     * @param int $userId
     * @return array
     */
    public function findByUserId(int $userId): array;

    /**
     * Find posts with user information
     * @return array
     */
    public function findAllWithUsers(): array;

    /**
     * Find post by ID with user information
     * @param int $id
     * @return array|null
     */
    public function findByIdWithUser(int $id): ?array;

    /**
     * Search posts by title
     * @param string $title
     * @return array
     */
    public function searchByTitle(string $title): array;

    /**
     * Get recent posts
     * @param int $limit
     * @return array
     */
    public function getRecentPosts(int $limit = 10): array;

    /**
     * Count posts by user
     * @param int $userId
     * @return int
     */
    public function countByUserId(int $userId): int;

    /**
     * Find posts with images
     * @return array
     */
    public function findPostsWithImages(): array;
}
?> 