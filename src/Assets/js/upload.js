// File Upload JavaScript Functionality
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('fileInput');
    const uploadArea = document.getElementById('uploadArea');
    const selectedFile = document.getElementById('selectedFile');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const submitBtn = document.getElementById('submitBtn');
    const progressBar = document.getElementById('progressBar');
    const progressFill = document.getElementById('progressFill');

    // File selection handling
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            displayFileInfo(file);
            submitBtn.disabled = false;
        } else {
            hideFileInfo();
            submitBtn.disabled = true;
        }
    });

    // Drag and drop handling
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            displayFileInfo(files[0]);
            submitBtn.disabled = false;
        }
    });

    // Click to upload
    uploadArea.addEventListener('click', function() {
        fileInput.click();
    });

    // Form submission with progress simulation
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        if (!fileInput.files[0]) {
            e.preventDefault();
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Uploading...';
        progressBar.style.display = 'block';

        // Simulate progress
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90;
            progressFill.style.width = progress + '%';
        }, 200);

        // Reset after a short delay to show completion
        setTimeout(() => {
            clearInterval(interval);
            progressFill.style.width = '100%';
        }, 2000);
    });

    // Helper functions
    function displayFileInfo(file) {
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        selectedFile.classList.add('show');
    }

    function hideFileInfo() {
        selectedFile.classList.remove('show');
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
}); 