// Dashboard JavaScript Functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeTabs();
    initializeImageUpload();
    initializeModals();
});

// Tab functionality
function initializeTabs() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetTab = button.getAttribute('data-tab');
            
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked button and corresponding content
            button.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });
}

// Image upload handling
function initializeImageUpload() {
    const imageInput = document.getElementById('post-image');
    const uploadArea = document.querySelector('.file-upload-area');
    const preview = document.getElementById('image-preview');

    if (imageInput && uploadArea) {
        // Handle file selection
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                displayImagePreview(file, preview);
            }
        });

        // Handle drag and drop
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.style.borderColor = '#667eea';
            uploadArea.style.background = '#f8f9ff';
        });

        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            uploadArea.style.borderColor = '#e9ecef';
            uploadArea.style.background = '';
        });

        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.style.borderColor = '#e9ecef';
            uploadArea.style.background = '';
            
            const files = e.dataTransfer.files;
            if (files.length > 0 && files[0].type.startsWith('image/')) {
                imageInput.files = files;
                displayImagePreview(files[0], preview);
            }
        });
    }
}

// Display image preview
function displayImagePreview(file, previewContainer) {
    const reader = new FileReader();
    reader.onload = function(e) {
        previewContainer.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
    };
    reader.readAsDataURL(file);
}

// Modal functionality
function initializeModals() {
    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            e.target.style.display = 'none';
        }
    });
}

// User Management Functions
function openUserModal(userId = null, userName = '') {
    const modal = document.getElementById('user-modal');
    const modalTitle = document.getElementById('user-modal-title');
    const userIdInput = document.getElementById('user-id');
    const userNameInput = document.getElementById('user-name');

    if (userId) {
        modalTitle.textContent = 'Edit User';
        userIdInput.value = userId;
        userNameInput.value = userName;
    } else {
        modalTitle.textContent = 'Add User';
        userIdInput.value = '';
        userNameInput.value = '';
    }

    modal.style.display = 'block';
}

function editUser(userId, userName) {
    openUserModal(userId, userName);
}

async function submitUser(event) {
    event.preventDefault();
    
    const userId = document.getElementById('user-id').value;
    const userName = document.getElementById('user-name').value;
    
    if (!userName.trim()) {
        showToast('Please enter a user name', 'error');
        return;
    }

    showLoading(true);

    try {
        const formData = new FormData();
        formData.append('name', userName.trim());
        if (userId) {
            formData.append('id', userId);
        }

        const response = await fetch('../api/users.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showToast(userId ? 'User updated successfully!' : 'User added successfully!', 'success');
            closeModal('user-modal');
            reloadUsers();
        } else {
            showToast(result.message || 'Operation failed', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Network error occurred', 'error');
    } finally {
        showLoading(false);
    }
}

async function deleteUser(userId) {
    if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        return;
    }

    showLoading(true);

    try {
        const response = await fetch(`../api/users.php?id=${userId}`, {
            method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
            showToast('User deleted successfully!', 'success');
            reloadUsers();
        } else {
            showToast(result.message || 'Delete failed', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Network error occurred', 'error');
    } finally {
        showLoading(false);
    }
}

// Post Management Functions
function openPostModal(postId = null) {
    const modal = document.getElementById('post-modal');
    const modalTitle = document.getElementById('post-modal-title');
    const form = document.getElementById('post-form');

    if (postId) {
        modalTitle.textContent = 'Edit Post';
        loadPostData(postId);
    } else {
        modalTitle.textContent = 'Add Post';
        form.reset();
        document.getElementById('post-id').value = '';
        document.getElementById('image-preview').innerHTML = '';
    }

    modal.style.display = 'block';
}

async function loadPostData(postId) {
    showLoading(true);

    try {
        const response = await fetch(`../api/posts.php?id=${postId}`);
        const result = await response.json();

        if (result.success && result.post) {
            const post = result.post;
            document.getElementById('post-id').value = post.id;
            document.getElementById('post-title').value = post.Title;
            document.getElementById('post-content').value = post.content;
            document.getElementById('post-user').value = post.User_id;
            
            // Show current image if exists
            const preview = document.getElementById('image-preview');
            if (post.ImgUrl) {
                preview.innerHTML = `<img src="${post.ImgUrl}" alt="Current image">`;
            }
        } else {
            showToast('Failed to load post data', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Network error occurred', 'error');
    } finally {
        showLoading(false);
    }
}

function editPost(postId) {
    openPostModal(postId);
}

async function submitPost(event) {
    event.preventDefault();
    
    const postId = document.getElementById('post-id').value;
    const title = document.getElementById('post-title').value;
    const content = document.getElementById('post-content').value;
    const userId = document.getElementById('post-user').value;
    const imageFile = document.getElementById('post-image').files[0];
    
    if (!title.trim() || !content.trim() || !userId) {
        showToast('Please fill in all required fields', 'error');
        return;
    }

    showLoading(true);

    try {
        const formData = new FormData();
        formData.append('title', title.trim());
        formData.append('content', content.trim());
        formData.append('user_id', userId);
        
        if (postId) {
            formData.append('id', postId);
        }
        
        if (imageFile) {
            formData.append('image', imageFile);
        }

        const response = await fetch('../api/posts.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showToast(postId ? 'Post updated successfully!' : 'Post added successfully!', 'success');
            closeModal('post-modal');
            reloadPosts();
        } else {
            showToast(result.message || 'Operation failed', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Network error occurred', 'error');
    } finally {
        showLoading(false);
    }
}

async function deletePost(postId) {
    if (!confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
        return;
    }

    showLoading(true);

    try {
        const response = await fetch(`../api/posts.php?id=${postId}`, {
            method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
            showToast('Post deleted successfully!', 'success');
            reloadPosts();
        } else {
            showToast(result.message || 'Delete failed', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Network error occurred', 'error');
    } finally {
        showLoading(false);
    }
}

async function reloadUsers() {
    try {
        const response = await fetch('../api/users.php');
        const result = await response.json();

        if (result.success) {
            updateUsersTable(result.users);
            updateUserSelect(result.users);
            updateStats();
        }
    } catch (error) {
        console.error('Error reloading users:', error);
    }
}

async function reloadPosts() {
    try {
        const response = await fetch('../../api/posts.php');
        const result = await response.json();

        if (result.success) {
            updatePostsGrid(result.posts);
            updateStats();
        }
    } catch (error) {
        console.error('Error reloading posts:', error);
    }
}

function updateUsersTable(users) {
    const tbody = document.getElementById('users-table-body');
    
    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="no-data">No users found</td></tr>';
        return;
    }

    tbody.innerHTML = users.map(user => `
        <tr>
            <td>${escapeHtml(user.id)}</td>
            <td>${escapeHtml(user.Name)}</td>
            <td>
                <button class="btn-small btn-warning" onclick="editUser(${user.id}, '${escapeHtml(user.Name)}')">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-small btn-danger" onclick="deleteUser(${user.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function updatePostsGrid(posts) {
    const grid = document.getElementById('posts-grid');
    
    if (posts.length === 0) {
        grid.innerHTML = '<div class="no-data">No posts found</div>';
        return;
    }

    grid.innerHTML = posts.map(post => `
        <div class="post-card">
            ${post.ImgUrl ? 
                `<img src="${escapeHtml(post.ImgUrl)}" alt="${escapeHtml(post.Title)}" class="post-image">` :
                `<div class="post-image-placeholder"><i class="fas fa-image"></i></div>`
            }
            <div class="post-content">
                <h3>${escapeHtml(post.Title)}</h3>
                <p>${escapeHtml(post.content.length > 100 ? post.content.substring(0, 100) + '...' : post.content)}</p>
                <div class="post-meta">
                    <span class="author">
                        <i class="fas fa-user"></i> 
                        ${escapeHtml(post.UserName || 'Unknown')}
                    </span>
                </div>
                <div class="post-actions">
                    <button class="btn-small btn-warning" onclick="editPost(${post.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-small btn-danger" onclick="deletePost(${post.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

function updateUserSelect(users) {
    const select = document.getElementById('post-user');
    const currentValue = select.value;
    
    select.innerHTML = '<option value="">Select a user</option>' + 
        users.map(user => `<option value="${user.id}">${escapeHtml(user.Name)}</option>`).join('');
    
    if (currentValue) {
        select.value = currentValue;
    }
}

async function updateStats() {
    try {
        const response = await fetch('../api/stats.php');
        const result = await response.json();

        if (result.success) {
            document.querySelector('.stat-icon.users + .stat-content h3').textContent = result.stats.users;
            document.querySelector('.stat-icon.posts + .stat-content h3').textContent = result.stats.posts;
        }
    } catch (error) {
        console.error('Error updating stats:', error);
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.display = 'none';
    
    const form = modal.querySelector('form');
    if (form) {
        form.reset();
        const hiddenId = form.querySelector('input[type="hidden"]');
        if (hiddenId) {
            hiddenId.value = '';
        }
    }
    
    const preview = modal.querySelector('.image-preview');
    if (preview) {
        preview.innerHTML = '';
    }
}

function showLoading(show) {
    const loading = document.getElementById('loading');
    loading.style.display = show ? 'block' : 'none';
}

function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const icon = type === 'success' ? 'fas fa-check-circle' : 
                 type === 'error' ? 'fas fa-exclamation-triangle' : 
                 'fas fa-info-circle';
    
    toast.innerHTML = `
        <i class="${icon}"></i>
        <span>${escapeHtml(message)}</span>
    `;
    
    container.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 5000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Event listeners for global functions
window.openUserModal = openUserModal;
window.editUser = editUser;
window.deleteUser = deleteUser;
window.submitUser = submitUser;
window.openPostModal = openPostModal;
window.editPost = editPost;
window.deletePost = deletePost;
window.submitPost = submitPost;
window.closeModal = closeModal; 