<?php
require_once 'Validator.php';

// Extended Validator for file uploads
class FileValidator extends Validator {
    public function fileUpload($file, $field_name, $options = []) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = "$field_name upload failed";
            return $this;
        }
        
        // Check file size
        $max_size = $options['max_size'] ?? 5242880; // 5MB default
        if ($file['size'] > $max_size) {
            $this->errors[] = "$field_name must be smaller than " . ($max_size / 1024 / 1024) . "MB";
        }
        
        // Check file type
        $allowed_types = $options['allowed_types'] ?? ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowed_types)) {
            $this->errors[] = "$field_name must be a valid image file (allowed: " . implode(', ', $allowed_types) . ")";
        }
        
        // Check file extension
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_exts = $options['allowed_extensions'] ?? ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_ext, $allowed_exts)) {
            $this->errors[] = "$field_name must have a valid extension (allowed: " . implode(', ', $allowed_exts) . ")";
        }
        
        return $this;
    }
}

$validator = new FileValidator();
$upload_success = false;

if ($_POST) {
    // Validate regular form fields
    $validator->required($_POST['title'] ?? '', 'Title')
              ->minLength($_POST['title'] ?? '', 3, 'Title')
              ->maxLength($_POST['title'] ?? '', 100, 'Title');
    
    $validator->required($_POST['description'] ?? '', 'Description')
              ->maxLength($_POST['description'] ?? '', 500, 'Description');
    
    // Validate file upload
    if (isset($_FILES['image'])) {
        $validator->fileUpload($_FILES['image'], 'Image', [
            'max_size' => 2097152, // 2MB
            'allowed_types' => ['image/jpeg', 'image/png', 'image/gif'],
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif']
        ]);
    } else {
        $validator->errors[] = "Image file is required";
    }
    
    // Process if valid
    if (!$validator->hasErrors()) {
        // In a real app, you would move the uploaded file and save to database
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $filename = uniqid() . '_' . $_FILES['image']['name'];
        $filepath = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) {
            $upload_success = true;
            $uploaded_file = $filepath;
        } else {
            $validator->errors[] = "Failed to save uploaded file";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>File Upload Validation</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .error { color: red; background: #ffeeee; padding: 10px; margin: 10px 0; border-left: 4px solid red; }
        .success { color: green; background: #eeffee; padding: 10px; margin: 10px 0; border-left: 4px solid green; }
        .form-group { margin: 15px 0; }
        input, textarea { padding: 8px; width: 300px; border: 1px solid #ddd; }
        textarea { height: 80px; }
        button { padding: 10px 20px; background: #007cba; color: white; border: none; cursor: pointer; }
        .form-container { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
        .file-info { background: #f0f8ff; padding: 10px; margin: 10px 0; }
        .help { color: #666; font-size: 0.9em; }
    </style>
</head>
<body>
    <h1>File Upload Validation</h1>
    <p>This example shows how to validate file uploads securely.</p>
    
    <?php if ($upload_success): ?>
        <div class="success">
            <h3>✅ Upload Successful!</h3>
            <strong>File Details:</strong><br>
            Title: <?= htmlspecialchars($_POST['title']) ?><br>
            Description: <?= htmlspecialchars($_POST['description']) ?><br>
            Original filename: <?= htmlspecialchars($_FILES['image']['name']) ?><br>
            File size: <?= number_format($_FILES['image']['size'] / 1024, 2) ?> KB<br>
            File type: <?= htmlspecialchars($_FILES['image']['type']) ?><br>
            Saved as: <?= htmlspecialchars($filename) ?><br>
            
            <?php if (file_exists($uploaded_file)): ?>
                <br><img src="<?= htmlspecialchars($uploaded_file) ?>" alt="Uploaded image" style="max-width: 200px; max-height: 200px;">
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($validator->hasErrors()): ?>
        <div class="error">
            <strong>⚠️ Upload Failed - <?= $validator->count() ?> error(s):</strong><br>
            <?php foreach ($validator->getErrors() as $error): ?>
                <div>• <?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="form-container">
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label><strong>Title:</strong> *</label><br>
                <input type="text" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                <div class="help">3-100 characters</div>
            </div>
            
            <div class="form-group">
                <label><strong>Description:</strong> *</label><br>
                <textarea name="description" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                <div class="help">Maximum 500 characters</div>
            </div>
            
            <div class="form-group">
                <label><strong>Image File:</strong> *</label><br>
                <input type="file" name="image" accept="image/*" required>
                <div class="help">Max 2MB, JPG/PNG/GIF only</div>
            </div>
            
            <button type="submit">🔄 Upload File</button>
        </form>
    </div>
    
    <h2>💡 File Upload Security</h2>
    <div style="background: #f0f8ff; padding: 15px; margin: 20px 0;">
        <h3>What We Validate:</h3>
        <ul>
            <li><strong>Upload Status:</strong> Check if file uploaded without errors</li>
            <li><strong>File Size:</strong> Prevent huge files that could crash server</li>
            <li><strong>MIME Type:</strong> Check actual file content type</li>
            <li><strong>File Extension:</strong> Double-check with file extension</li>
            <li><strong>File Name:</strong> Generate unique names to prevent conflicts</li>
        </ul>
        
        <h3>Security Best Practices:</h3>
        <ul>
            <li><strong>Never trust client data:</strong> Validate everything on server</li>
            <li><strong>Move uploaded files:</strong> Don't leave them in temp directory</li>
            <li><strong>Unique filenames:</strong> Prevent overwriting existing files</li>
            <li><strong>Restricted upload directory:</strong> Don't allow executable files</li>
            <li><strong>Size limits:</strong> Prevent disk space exhaustion</li>
        </ul>
    </div>
    
    <div class="file-info">
        <h3>📁 Upload Information</h3>
        <strong>Maximum file size:</strong> 2MB (2,097,152 bytes)<br>
        <strong>Allowed types:</strong> JPG, JPEG, PNG, GIF images<br>
        <strong>Upload directory:</strong> uploads/ (created automatically)<br>
        <strong>File naming:</strong> unique_id + original_name
    </div>
    
    <h3>🧪 Test Cases:</h3>
    <div style="background: #fffacd; padding: 15px; margin: 10px 0;">
        <strong>Valid:</strong> Upload a small JPG/PNG image with title and description<br>
        <strong>Too large:</strong> Try to upload a file larger than 2MB<br>
        <strong>Wrong type:</strong> Try to upload a .txt file or .exe file<br>
        <strong>No file:</strong> Submit form without selecting a file
    </div>
    
    <?php
    // Show current PHP upload settings
    echo "<h3>📊 Server Upload Settings:</h3>";
    echo "<div class='file-info'>";
    echo "<strong>upload_max_filesize:</strong> " . ini_get('upload_max_filesize') . "<br>";
    echo "<strong>post_max_size:</strong> " . ini_get('post_max_size') . "<br>";
    echo "<strong>max_file_uploads:</strong> " . ini_get('max_file_uploads') . "<br>";
    echo "</div>";
    ?>
    
    <p>
        <a href="api_validation.php">← Previous: API validation</a> | 
        <a href="custom_validation.php">Next: Custom validation →</a>
    </p>
</body>
</html>
