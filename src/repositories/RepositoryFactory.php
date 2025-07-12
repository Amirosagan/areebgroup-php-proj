<?php

require_once 'UserRepository.php';
require_once 'PostRepository.php';

class RepositoryFactory
{
    private static $instance = null;
    private $database;
    private $repositories = [];

    private function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(Database $database): self
    {
        if (self::$instance === null) {
            self::$instance = new self($database);
        }
        return self::$instance;
    }

    /**
     * Get User Repository
     */
    public function getUserRepository(): UserRepository
    {
        if (!isset($this->repositories['user'])) {
            $this->repositories['user'] = new UserRepository($this->database);
        }
        return $this->repositories['user'];
    }

    /**
     * Get Post Repository
     */
    public function getPostRepository(): PostRepository
    {
        if (!isset($this->repositories['post'])) {
            $this->repositories['post'] = new PostRepository($this->database);
        }
        return $this->repositories['post'];
    }

    /**
     * Get all repositories
     */
    public function getAllRepositories(): array
    {
        return [
            'user' => $this->getUserRepository(),
            'post' => $this->getPostRepository()
        ];
    }

    /**
     * Clear repository cache (useful for testing)
     */
    public function clearCache(): void
    {
        $this->repositories = [];
    }

    /**
     * Get database instance
     */
    public function getDatabase(): Database
    {
        return $this->database;
    }
}
?> 