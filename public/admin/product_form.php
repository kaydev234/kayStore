<?php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    
    // Debug: Check what files are being uploaded
    error_log("Files received: " . print_r($_FILES, true));
    
    // Handle image upload
    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = time() . '_' . $_FILES['image']['name'];
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imagePath = $targetPath;
            error_log("Image uploaded successfully: " . $imagePath);
        } else {
            error_log("Image upload failed");
        }
    }
    
    // Handle video upload with better error checking
    $videoPath = '';
    if (isset($_FILES['video']) && $_FILES['video']['error'] === 0) {
        error_log("Video file detected: " . $_FILES['video']['name']);
        
        $videoDir = 'product_videos/';
        if (!is_dir($videoDir)) {
            if (mkdir($videoDir, 0755, true)) {
                error_log("Video directory created: " . $videoDir);
            } else {
                error_log("Failed to create video directory: " . $videoDir);
                $_SESSION['error'] = "Failed to create video directory";
            }
        }
        
        $videoName = time() . '_' . $_FILES['video']['name'];
        $videoTargetPath = $videoDir . $videoName;
        
        // Check file size and type
        $maxFileSize = 50 * 1024 * 1024; // 50MB
        $allowedTypes = ['video/mp4', 'video/avi', 'video/mov', 'video/wmv'];
        
        if ($_FILES['video']['size'] > $maxFileSize) {
            error_log("Video file too large: " . $_FILES['video']['size']);
            $_SESSION['error'] = "Video file is too large. Maximum size is 50MB.";
        } elseif (!in_array($_FILES['video']['type'], $allowedTypes)) {
            error_log("Invalid video type: " . $_FILES['video']['type']);
            $_SESSION['error'] = "Invalid video format. Allowed: MP4, AVI, MOV, WMV.";
        } else {
            if (move_uploaded_file($_FILES['video']['tmp_name'], $videoTargetPath)) {
                $videoPath = $videoTargetPath;
                error_log("Video uploaded successfully: " . $videoPath);
                $_SESSION['success'] = "Product and video uploaded successfully!";
            } else {
                error_log("Video move_uploaded_file failed");
                $error = error_get_last();
                error_log("Last error: " . $error['message']);
                $_SESSION['error'] = "Failed to upload video: " . $error['message'];
            }
        }
    } else {
        $uploadErrors = [
            0 => 'There is no error, the file uploaded with success',
            1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            3 => 'The uploaded file was only partially uploaded',
            4 => 'No file was uploaded',
            6 => 'Missing a temporary folder',
            7 => 'Failed to write file to disk.',
            8 => 'A PHP extension stopped the file upload.',
        ];
        $errorCode = $_FILES['video']['error'] ?? 4;
        error_log("Video upload error: " . $uploadErrors[$errorCode]);
    }
    
    try {
        // Insert into database
        if (!empty($videoPath)) {
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, image, video, category_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $imagePath, $videoPath, $category]);
        } else {
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, image, category_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $imagePath, $category]);
        }
        
        if (!isset($_SESSION['success'])) {
            $_SESSION['success'] = "Product added successfully!";
        }
        header("Location: add_product.php");
        exit;
        
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $_SESSION['error'] = "Error adding product: " . $e->getMessage();
    }
}
?>
<?php include 'header.php'; ?>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header Card -->
            <div class="card border-0 shadow-lg mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h2 class="h4 mb-0"><i class="fas fa-plus-circle me-2"></i>Add New Product</h2>
                        </div>
                        <div class="col-auto">
                            <a href="manage_products.php" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Back to Products
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?= $success ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Product Form -->
            <div class="card border-0 shadow">
                <div class="card-body p-4">
                    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="row">
                            <!-- Left Column - Basic Info -->
                            <div class="col-lg-6">
                                <h5 class="text-primary mb-4"><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
                                
                                <!-- Product Name -->
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg" id="name" name="name" 
                                           value="<?= $_POST['name'] ?? '' ?>" required 
                                           placeholder="Enter product name">
                                    <div class="invalid-feedback">Please provide a product name.</div>
                                </div>

                                <!-- Price -->
                                <div class="mb-3">
                                    <label for="price" class="form-label fw-semibold">Price (₦) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">₦</span>
                                        <input type="number" class="form-control form-control-lg" id="price" name="price" 
                                               value="<?= $_POST['price'] ?? '' ?>" step="0.01" min="0" required 
                                               placeholder="0.00">
                                    </div>
                                    <div class="invalid-feedback">Please provide a valid price.</div>
                                </div>

                                <!-- Category -->
                                <div class="mb-3">
                                    <label for="category" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                                    <select class="form-select form-control-lg" id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="electronics" <?= ($_POST['category'] ?? '') == 'electronics' ? 'selected' : '' ?>>Electronics</option>
                                        <option value="fashion" <?= ($_POST['category'] ?? '') == 'fashion' ? 'selected' : '' ?>>Fashion</option>
                                        <option value="groceries" <?= ($_POST['category'] ?? '') == 'groceries' ? 'selected' : '' ?>>Groceries</option>
                                        <option value="home" <?= ($_POST['category'] ?? '') == 'home' ? 'selected' : '' ?>>Home Appliances</option>
                                        <option value="phones" <?= ($_POST['category'] ?? '') == 'phones' ? 'selected' : '' ?>>Phones</option>
                                        <option value="beauty" <?= ($_POST['category'] ?? '') == 'beauty' ? 'selected' : '' ?>>Beauty</option>
                                        <option value="sports" <?= ($_POST['category'] ?? '') == 'sports' ? 'selected' : '' ?>>Sports</option>
                                        <option value="other" <?= ($_POST['category'] ?? '') == 'other' ? 'selected' : '' ?>>Others</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a category.</div>
                                </div>

                                <!-- Stock Quantity -->
                                <div class="mb-3">
                                    <label for="stock_quantity" class="form-label fw-semibold">Stock Quantity <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control form-control-lg" id="stock_quantity" name="stock_quantity" 
                                           value="<?= $_POST['stock_quantity'] ?? '' ?>" min="0" required 
                                           placeholder="Enter available quantity">
                                    <div class="invalid-feedback">Please provide stock quantity.</div>
                                </div>

                                <!-- Description -->
                                <div class="mb-3">
                                    <label for="description" class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="description" name="description" rows="5" 
                                              required placeholder="Enter product description"><?= $_POST['description'] ?? '' ?></textarea>
                                    <div class="invalid-feedback">Please provide a product description.</div>
                                </div>
                            </div>

                            <!-- Right Column - Media Uploads -->
                            <div class="mb-3">
        <label for="video" class="form-label">Product Video (Optional)</label>
        <input type="file" class="form-control" id="video" name="video" accept="video/*">
        <small class="text-muted">Max size: 50MB, Allowed formats: MP4, AVI, MOV, WMV</small>
    </div>
                                <!-- Main Image -->
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Main Product Image <span class="text-danger">*</span></label>
                                    <div class="file-upload-card border rounded p-3 bg-light">
                                        <input type="file" class="form-control" name="image_main" required 
                                               accept="image/*" onchange="previewImage(this, 'mainPreview')">
                                        <small class="text-muted">This will be the primary display image</small>
                                        <div id="mainPreview" class="mt-2 text-center"></div>
                                    </div>
                                </div>

                                <!-- Additional Images -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Front View</label>
                                        <div class="file-upload-card border rounded p-3 bg-light">
                                            <input type="file" class="form-control" name="image_front" 
                                                   accept="image/*" onchange="previewImage(this, 'frontPreview')">
                                            <small class="text-muted">Optional</small>
                                            <div id="frontPreview" class="mt-2 text-center"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Back View</label>
                                        <div class="file-upload-card border rounded p-3 bg-light">
                                            <input type="file" class="form-control" name="image_back" 
                                                   accept="image/*" onchange="previewImage(this, 'backPreview')">
                                            <small class="text-muted">Optional</small>
                                            <div id="backPreview" class="mt-2 text-center"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Side View</label>
                                        <div class="file-upload-card border rounded p-3 bg-light">
                                            <input type="file" class="form-control" name="image_side" 
                                                   accept="image/*" onchange="previewImage(this, 'sidePreview')">
                                            <small class="text-muted">Optional</small>
                                            <div id="sidePreview" class="mt-2 text-center"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Product Video</label>
                                        <div class="file-upload-card border rounded p-3 bg-light">
                                            <input type="file" class="form-control" name="video" 
                                                   accept="video/mp4" onchange="previewVideo(this, 'videoPreview')">
                                            <small class="text-muted">MP4 format only</small>
                                            <div id="videoPreview" class="mt-2 text-center"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Image Preview Area -->
                                <div class="mt-4">
                                    <h6 class="fw-semibold mb-3">Image Previews</h6>
                                    <div class="row g-2" id="previewContainer">
                                        <div class="col-6">
                                            <div class="border rounded p-2 text-center bg-white">
                                                <small class="text-muted d-block">Main Image</small>
                                                <div id="mainPreview" class="image-preview"></div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded p-2 text-center bg-white">
                                                <small class="text-muted d-block">Front View</small>
                                                <div id="frontPreview" class="image-preview"></div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded p-2 text-center bg-white">
                                                <small class="text-muted d-block">Back View</small>
                                                <div id="backPreview" class="image-preview"></div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded p-2 text-center bg-white">
                                                <small class="text-muted d-block">Side View</small>
                                                <div id="sidePreview" class="image-preview"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="reset" class="btn btn-outline-secondary btn-lg me-md-2">
                                        <i class="fas fa-undo me-2"></i>Reset Form
                                    </button>
                                    <button type="submit" class="btn btn-success btn-lg px-5">
                                        <i class="fas fa-plus-circle me-2"></i>Add Product
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.file-upload-card {
    transition: all 0.3s ease;
    border: 2px dashed #dee2e6 !important;
}

.file-upload-card:hover {
    border-color: #0d6efd !important;
    background-color: #f8f9fa;
}

.image-preview {
    min-height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 4px;
    overflow: hidden;
}

.image-preview img, .image-preview video {
    max-width: 100%;
    max-height: 80px;
    object-fit: cover;
}

.preview-placeholder {
    color: #6c757d;
    font-size: 12px;
}

.card {
    border: none;
    border-radius: 15px;
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
}

.form-control, .form-select {
    border-radius: 10px;
    border: 1px solid #dee2e6;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.btn {
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-lg {
    padding: 12px 30px;
}
</style>

<script>
// Image preview functionality
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    preview.innerHTML = '';
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'img-fluid rounded';
            img.style.maxHeight = '100px';
            preview.appendChild(img);
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.innerHTML = '<div class="preview-placeholder">No image selected</div>';
    }
}

// Video preview functionality
function previewVideo(input, previewId) {
    const preview = document.getElementById(previewId);
    preview.innerHTML = '';
    
    if (input.files && input.files[0]) {
        const video = document.createElement('video');
        video.src = URL.createObjectURL(input.files[0]);
        video.controls = true;
        video.style.maxHeight = '100px';
        video.className = 'img-fluid rounded';
        preview.appendChild(video);
    } else {
        preview.innerHTML = '<div class="preview-placeholder">No video selected</div>';
    }
}

// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Initialize preview placeholders
document.addEventListener('DOMContentLoaded', function() {
    const previews = ['mainPreview', 'frontPreview', 'backPreview', 'sidePreview', 'videoPreview'];
    previews.forEach(previewId => {
        const preview = document.getElementById(previewId);
        if (preview && preview.innerHTML.trim() === '') {
            preview.innerHTML = '<div class="preview-placeholder">No file selected</div>';
        }
    });
});
</script>

<?php include 'footer.php'; ?>