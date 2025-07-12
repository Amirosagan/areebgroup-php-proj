<?php

require_once './uploade/utils.php';

$message = '';
$messageType = '';
$uploadedFilePath = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $file = $_FILES['file'];
    $result = uploadFile($file);
    if ($result['success']) {
        $message = "File uploaded successfully!";
        $messageType = 'success';
        $uploadedFilePath = $result['file_path'] ?? '';
    } else {
        $message = "File upload failed: " . $result['error'];
        $messageType = 'error';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload</title>
    <link rel="stylesheet" href="../Assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="upload-icon">📁</div>
        <h1>File Upload</h1>
        <p class="subtitle">Select a file to upload to the server</p>
        
        <div style="text-align: center; margin-bottom: 20px;">
            <a href="dashboard" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s ease;">
                <i class="fas fa-tachometer-alt" style="font-size: 14px;"></i>
                Go to Dashboard
            </a>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
                <?php if ($messageType === 'success' && $uploadedFilePath): ?>
                    <br><br>
                    <a href="<?php echo htmlspecialchars($uploadedFilePath); ?>" target="_blank" class="file-link">
                        📁 View Uploaded File
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" id="uploadForm">
            <div class="upload-area" id="uploadArea">
                <input type="file" name="file" class="file-input" id="fileInput" accept="*/*">
                <div class="upload-text">📁 Choose a file or drag it here</div>
                <div class="upload-hint">Click to browse or drag and drop</div>
            </div>

            <div class="selected-file" id="selectedFile">
                <div class="file-name" id="fileName"></div>
                <div class="file-size" id="fileSize"></div>
            </div>

            <div class="progress-bar" id="progressBar">
                <div class="progress-fill" id="progressFill"></div>
            </div>

            <button type="submit" class="submit-btn" id="submitBtn" disabled>
                Upload File
            </button>
        </form>
    </div>

    <script src="../Assets/js/upload.js"></script>
</body>
</html>