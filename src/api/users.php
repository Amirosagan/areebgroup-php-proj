<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require '../config/database.php';
$config = require '../config.php';
$database = new Database($config);

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
    global $database;
    
    if (isset($_GET['id'])) {
        // Get single user
        $userId = intval($_GET['id']);
        $user = $database->getUserById($userId);
        
        if ($user) {
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
    } else {
        // Get all users
        $users = $database->getAllUsers();
        echo json_encode(['success' => true, 'users' => $users]);
    }
}

function handlePostRequest() {
    global $database;
    
    // Validate input
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;
    
    if (empty($name)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Name is required']);
        return;
    }
    
    if (strlen($name) > 255) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Name is too long (maximum 255 characters)']);
        return;
    }
    
    try {
        if ($id) {
            $existingUser = $database->getUserById($id);
            if (!$existingUser) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'User not found']);
                return;
            }
            
            $result = $database->updateUser($id, $name);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'User updated successfully']);
            } else {
                throw new Exception('Failed to update user');
            }
        } else {
            $userId = $database->addUser($name);
            if ($userId) {
                echo json_encode(['success' => true, 'message' => 'User created successfully', 'user_id' => $userId]);
            } else {
                throw new Exception('Failed to create user');
            }
        }
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'User name already exists']);
        } else {
            throw $e;
        }
    }
}

function handleDeleteRequest() {
    global $database;
    
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        return;
    }
    
    $userId = intval($_GET['id']);
    
    // Check if user exists
    $user = $database->getUserById($userId);
    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        return;
    }
    
    // Check if user has posts
    $posts = $database->getConnection()->prepare("SELECT COUNT(*) as count FROM Posts WHERE User_id = ?");
    $posts->execute([$userId]);
    $postCount = $posts->fetch()['count'];
    
    if ($postCount > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Cannot delete user with existing posts. Delete posts first.']);
        return;
    }
    
    $result = $database->deleteUser($userId);
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    } else {
        throw new Exception('Failed to delete user');
    }
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}
?> 