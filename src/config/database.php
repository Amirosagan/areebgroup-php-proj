<?php
class Database {
    private $host;
    private $port;
    private $dbname;
    private $username;
    private $password;
    private $connection;

    public function __construct($config) {
        $this->host = $config['db']['host'];
        $this->port = $config['db']['port'];
        $this->dbname = $config['db']['dbname'];
        $this->username = $config['db']['user'];
        $this->password = $config['db']['pass'];
        $this->connect();
    }

    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
            $this->connection = new PDO($dsn, $this->username, $this->password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    // Get all users
    public function getAllUsers() {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM Users ORDER BY Name ASC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Error fetching users: " . $e->getMessage());
        }
    }

    // Get user by ID
    public function getUserById($id) {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM Users WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Error fetching user: " . $e->getMessage());
        }
    }

    // Add new user
    public function addUser($name) {
        try {
            $stmt = $this->connection->prepare("INSERT INTO Users (Name) VALUES (?)");
            $stmt->execute([$name]);
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Error adding user: " . $e->getMessage());
        }
    }

    // Update user
    public function updateUser($id, $name) {
        try {
            $stmt = $this->connection->prepare("UPDATE Users SET Name = ? WHERE id = ?");
            return $stmt->execute([$name, $id]);
        } catch (PDOException $e) {
            throw new Exception("Error updating user: " . $e->getMessage());
        }
    }

    // Delete user
    public function deleteUser($id) {
        try {
            $stmt = $this->connection->prepare("DELETE FROM Users WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new Exception("Error deleting user: " . $e->getMessage());
        }
    }

    // Get all posts with user names
    public function getAllPosts() {
        try {
            $stmt = $this->connection->prepare("
                SELECT p.*, u.Name as UserName 
                FROM Posts p 
                LEFT JOIN Users u ON p.User_id = u.id 
                ORDER BY p.id DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Error fetching posts: " . $e->getMessage());
        }
    }

    // Get post by ID
    public function getPostById($id) {
        try {
            $stmt = $this->connection->prepare("
                SELECT p.*, u.Name as UserName 
                FROM Posts p 
                LEFT JOIN Users u ON p.User_id = u.id 
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Error fetching post: " . $e->getMessage());
        }
    }

    // Add new post
    public function addPost($title, $content, $imgUrl, $userId) {
        try {
            $stmt = $this->connection->prepare("
                INSERT INTO Posts (Title, content, ImgUrl, User_id) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$title, $content, $imgUrl, $userId]);
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Error adding post: " . $e->getMessage());
        }
    }

    // Update post
    public function updatePost($id, $title, $content, $imgUrl, $userId) {
        try {
            $stmt = $this->connection->prepare("
                UPDATE Posts 
                SET Title = ?, content = ?, ImgUrl = ?, User_id = ? 
                WHERE id = ?
            ");
            return $stmt->execute([$title, $content, $imgUrl, $userId, $id]);
        } catch (PDOException $e) {
            throw new Exception("Error updating post: " . $e->getMessage());
        }
    }

    // Delete post
    public function deletePost($id) {
        try {
            $stmt = $this->connection->prepare("DELETE FROM Posts WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new Exception("Error deleting post: " . $e->getMessage());
        }
    }

    // Get posts count
    public function getPostsCount() {
        try {
            $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM Posts");
            $stmt->execute();
            return $stmt->fetch()['count'];
        } catch (PDOException $e) {
            return 0;
        }
    }

    // Get users count
    public function getUsersCount() {
        try {
            $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM Users");
            $stmt->execute();
            return $stmt->fetch()['count'];
        } catch (PDOException $e) {
            return 0;
        }
    }
}

?> 