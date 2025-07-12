<?php
require './config/database.php';
$config = require './config.php';

$database = new Database($config);

// Initialize variables
$users = [];
$posts = [];
$usersCount = 0;
$postsCount = 0;

try {
    $users = $database->getAllUsers();
    $posts = $database->getAllPosts();
    $usersCount = $database->getUsersCount();
    $postsCount = $database->getPostsCount();
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Users & Posts</title>
    <link rel="stylesheet" href="../Assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                <div class="header-actions">
                    <a href="index.view.php" class="btn btn-secondary">
                        <i class="fas fa-upload"></i> File Upload
                    </a>
                </div>
            </div>
        </header>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon users">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $usersCount; ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon posts">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $postsCount; ?></h3>
                    <p>Total Posts</p>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="tab-container">
            <div class="tab-navigation">
                <button class="tab-btn active" data-tab="users">
                    <i class="fas fa-users"></i> Users
                </button>
                <button class="tab-btn" data-tab="posts">
                    <i class="fas fa-newspaper"></i> Posts
                </button>
            </div>

            <!-- Users Tab -->
            <div class="tab-content active" id="users">
                <div class="section-header">
                    <h2>Users Management</h2>
                    <button class="btn btn-primary" onclick="openUserModal()">
                        <i class="fas fa-plus"></i> Add User
                    </button>
                </div>
                
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="users-table-body">
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="3" class="no-data">No users found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                                        <td><?php echo htmlspecialchars($user['Name']); ?></td>
                                        <td>
                                            <button class="btn-small btn-warning" onclick="editUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['Name']); ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-small btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Posts Tab -->
            <div class="tab-content" id="posts">
                <div class="section-header">
                    <h2>Posts Management</h2>
                    <button class="btn btn-primary" onclick="openPostModal()">
                        <i class="fas fa-plus"></i> Add Post
                    </button>
                </div>
                
                <div class="posts-grid" id="posts-grid">
                    <?php if (empty($posts)): ?>
                        <div class="no-data">No posts found</div>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <div class="post-card">
                                <?php if ($post['ImgUrl']): ?>
                                    <img src="<?php echo htmlspecialchars($post['ImgUrl']); ?>" alt="<?php echo htmlspecialchars($post['Title']); ?>" class="post-image">
                                <?php else: ?>
                                    <div class="post-image-placeholder">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="post-content">
                                    <h3><?php echo htmlspecialchars($post['Title']); ?></h3>
                                    <p><?php echo htmlspecialchars(substr($post['content'], 0, 100)) . (strlen($post['content']) > 100 ? '...' : ''); ?></p>
                                    <div class="post-meta">
                                        <span class="author">
                                            <i class="fas fa-user"></i> 
                                            <?php echo htmlspecialchars($post['UserName'] ?? 'Unknown'); ?>
                                        </span>
                                    </div>
                                    <div class="post-actions">
                                        <button class="btn-small btn-warning" onclick="editPost(<?php echo $post['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-small btn-danger" onclick="deletePost(<?php echo $post['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- User Modal -->
    <div id="user-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="user-modal-title">Add User</h3>
                <span class="close" onclick="closeModal('user-modal')">&times;</span>
            </div>
            <form id="user-form" onsubmit="submitUser(event)">
                <input type="hidden" id="user-id">
                <div class="form-group">
                    <label for="user-name">Name *</label>
                    <input type="text" id="user-name" required placeholder="Enter user name">
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('user-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Post Modal -->
    <div id="post-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="post-modal-title">Add Post</h3>
                <span class="close" onclick="closeModal('post-modal')">&times;</span>
            </div>
            <form id="post-form" onsubmit="submitPost(event)" enctype="multipart/form-data">
                <input type="hidden" id="post-id">
                <div class="form-group">
                    <label for="post-title">Title *</label>
                    <input type="text" id="post-title" required placeholder="Enter post title">
                </div>
                <div class="form-group">
                    <label for="post-content">Content *</label>
                    <textarea id="post-content" required placeholder="Enter post content" rows="4"></textarea>
                </div>
                <div class="form-group">
                    <label for="post-user">User *</label>
                    <select id="post-user" required>
                        <option value="">Select a user</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>">
                                <?php echo htmlspecialchars($user['Name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="post-image">Image</label>
                    <div class="file-upload-area">
                        <input type="file" id="post-image" accept="image/*">
                        <div class="file-upload-text">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Choose image or drag it here</span>
                        </div>
                    </div>
                    <div id="image-preview" class="image-preview"></div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('post-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Post</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loading" class="loading-overlay">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading...</p>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div id="toast-container" class="toast-container"></div>

    <script src="../Assets/js/dashboard.js"></script>
</body>
</html> 