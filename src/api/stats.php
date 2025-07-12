<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../repositories/RepositoryFactory.php';

$config = require '../config.php';
$database = new Database($config);
$repositoryFactory = RepositoryFactory::getInstance($database);
$userRepository = $repositoryFactory->getUserRepository();
$postRepository = $repositoryFactory->getPostRepository();

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $usersCount = $userRepository->count();
    $postsCount = $postRepository->count();
    $postsStats = $postRepository->getPostsStats();
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'users' => $usersCount,
            'posts' => $postsCount,
            'posts_with_images' => $postsStats['posts_with_images'] ?? 0,
            'unique_authors' => $postsStats['unique_users'] ?? 0,
            'avg_content_length' => round($postsStats['avg_content_length'] ?? 0, 2)
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?> 