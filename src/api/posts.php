<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../repositories/RepositoryFactory.php';
$config = require '../config.php';
$database = new Database($config);
$repositoryFactory = RepositoryFactory::getInstance($database);
$postRepository = $repositoryFactory->getPostRepository();
$userRepository = $repositoryFactory->getUserRepository();

// Global repository instances
global $postRepository, $userRepository;

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGetRequest();
            break;
        case 'POST':
            handlePostRequest();
            break;
        case 'DELETE':
            handleDeleteRequest();
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function handleGetRequest() {
    global $postRepository;
    
    if (isset($_GET['id'])) {
        // Get single post
        $postId = intval($_GET['id']);
        $post = $postRepository->findByIdWithUser($postId);
        
        if ($post) {
            echo json_encode(['success' => true, 'post' => $post]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Post not found']);
        }
    } else {
        $posts = $postRepository->findAll();
        echo json_encode(['success' => true, 'posts' => $posts]);
    }
}

function handlePostRequest() {
    global $postRepository, $userRepository;
    
    // Validate input
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;
    
    if (empty($title)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Title is required']);
        return;
    }
    
    if (empty($content)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Content is required']);
        return;
    }
    
    if (!$userId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        return;
    }
    
    if (strlen($title) > 255) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Title is too long (maximum 255 characters)']);
        return;
    }
    
    if (strlen($content) > 5000) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Content is too long (maximum 5000 characters)']);
        return;
    }
    
    // Check if user exists
    $user = $userRepository->findById($userId);
    if (!$user) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Selected user does not exist']);
        return;
    }
    
    $imgUrl = null;
    $currentImgUrl = null;
    
    // If updating, get current image URL
    if ($id) {
        $existingPost = $postRepository->findById($id);
        if (!$existingPost) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Post not found']);
            return;
        }
        $currentImgUrl = $existingPost['ImgUrl'];
    }
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handleImageUpload($_FILES['image']);
        if ($uploadResult['success']) {
            $imgUrl = $uploadResult['url'];
            
            // Delete old image if updating and new image is uploaded
            if ($currentImgUrl && file_exists('../' . $currentImgUrl)) {
                unlink('../' . $currentImgUrl);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $uploadResult['message']]);
            return;
        }
    } else {
        // Keep current image URL if updating and no new image uploaded
        $imgUrl = $currentImgUrl;
    }
    
    try {
        if ($id) {
            // Update existing post
            $result = $postRepository->updatePost($id, [
                'Title' => $title,
                'content' => $content,
                'ImgUrl' => $imgUrl,
                'User_id' => $userId
            ]);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Post updated successfully']);
            } else {
                throw new Exception('Failed to update post');
            }
        } else {
            // Create new post
            $postId = $postRepository->createPost([
                'Title' => $title,
                'content' => $content,
                'ImgUrl' => $imgUrl,
                'User_id' => $userId
            ]);
            if ($postId) {
                echo json_encode(['success' => true, 'message' => 'Post created successfully', 'post_id' => $postId]);
            } else {
                throw new Exception('Failed to create post');
            }
        }
    } catch (Exception $e) {
        // If there was an error and we uploaded a new image, delete it
        if ($imgUrl && $imgUrl !== $currentImgUrl && file_exists('../' . $imgUrl)) {
            unlink('../' . $imgUrl);
        }
        throw $e;
    }
}

function handleDeleteRequest() {
    global $postRepository;
    
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Post ID is required']);
        return;
    }
    
    $postId = intval($_GET['id']);
    
    // Use repository method that handles both post deletion and image cleanup
    $result = $postRepository->deletePostWithImage($postId);
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Post deleted successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Post not found']);
    }
}

function handleImageUpload($file) {
    // Validate file
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid image type. Only JPEG, PNG, GIF, and WebP are allowed.'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'Image too large. Maximum size is 5MB.'];
    }
    
    // Create upload directory if it doesn't exist
    $uploadDir = '../uploads/posts/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'post_' . time() . '_' . uniqid() . '.' . $extension;
    $filePath = $uploadDir . $filename;
    $relativeUrl = 'uploads/posts/' . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Optionally resize image to reasonable size
        resizeImage($filePath, $filePath, 800, 600);
        
        return ['success' => true, 'url' => $relativeUrl];
    } else {
        return ['success' => false, 'message' => 'Failed to upload image.'];
    }
}

function resizeImage($sourcePath, $destinationPath, $maxWidth, $maxHeight) {
    // Get image info
    $imageInfo = getimagesize($sourcePath);
    if (!$imageInfo) {
        return false;
    }
    
    $sourceWidth = $imageInfo[0];
    $sourceHeight = $imageInfo[1];
    $imageType = $imageInfo[2];
    
    // Calculate new dimensions
    $ratio = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
    
    // If image is already smaller, don't resize
    if ($ratio >= 1) {
        return true;
    }
    
    $newWidth = round($sourceWidth * $ratio);
    $newHeight = round($sourceHeight * $ratio);
    
    // Create source image resource
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $sourceImage = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $sourceImage = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_GIF:
            $sourceImage = imagecreatefromgif($sourcePath);
            break;
        case IMAGETYPE_WEBP:
            $sourceImage = imagecreatefromwebp($sourcePath);
            break;
        default:
            return false;
    }
    
    if (!$sourceImage) {
        return false;
    }
    
    // Create new image
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preserve transparency for PNG and GIF
    if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
    }
    
    // Resize image
    imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);
    
    // Save resized image
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($newImage, $destinationPath, 85);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($newImage, $destinationPath, 8);
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($newImage, $destinationPath);
            break;
        case IMAGETYPE_WEBP:
            $result = imagewebp($newImage, $destinationPath, 85);
            break;
        default:
            $result = false;
    }
    
    // Clean up
    imagedestroy($sourceImage);
    imagedestroy($newImage);
    
    return $result;
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}
?> 