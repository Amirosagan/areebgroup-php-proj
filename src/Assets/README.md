# Assets Directory Structure

This directory contains all static assets for the PHP File Upload application.

## Directory Structure

```
src/Assets/
├── css/
│   └── style.css        # Main stylesheet for the application
├── js/
│   └── upload.js        # JavaScript functionality for file uploads
└── README.md            # This file
```

## Files Description

### CSS Files
- **style.css**: Contains all styling for the file upload interface including:
  - Modern gradient backgrounds
  - Responsive design
  - Drag & drop styling
  - Button animations
  - Progress bar styling
  - File link styling

### JavaScript Files
- **upload.js**: Contains all interactive functionality including:
  - File selection handling
  - Drag and drop functionality
  - File information display
  - Progress bar simulation
  - Form submission handling
  - File size formatting utilities

## Usage

These assets are referenced in the PHP views using relative paths:

```html
<!-- In your PHP view files -->
<link rel="stylesheet" href="../Assets/css/style.css">
<script src="../Assets/js/upload.js"></script>
```

## Benefits of External Assets

1. **Separation of Concerns**: HTML, CSS, and JavaScript are properly separated
2. **Maintainability**: Easier to maintain and update styles and scripts
3. **Caching**: Browsers can cache static assets for better performance
4. **Reusability**: Assets can be reused across multiple views
5. **Organization**: Clean project structure 