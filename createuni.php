<?php
include(__DIR__ . '/../config.php');

// Redirect if not logged in (removed admin check)
if (!isset($_SESSION['user_id'])) {
    header("Location: /uniprojecthub/auth/login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $location = sanitizeInput($_POST['location']);
    $description = sanitizeInput($_POST['description']);
    
    // Validate inputs
    if (empty($name) || empty($location)) {
        $error = "Name and location are required fields.";
    } else {
        // Handle logo upload
        $logo_path = null;
        if (!empty($_FILES['logo']['name'])) {
            $target_dir = __DIR__ . "/../assets/uploads/universities/";
            
            // Create directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_type = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_type;
            $target_file = $target_dir . $new_filename;
            
            // Validate upload
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            if (!in_array($file_type, $allowed_types)) {
                $error = 'Only JPG, JPEG, PNG, GIF & SVG files are allowed.';
            } elseif ($_FILES['logo']['size'] > $max_size) {
                $error = 'File too large. Max 2MB allowed.';
            } elseif (move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
                $logo_path = "/uniprojecthub/assets/uploads/universities/" . $new_filename;
            } else {
                $error = 'Error uploading logo.';
            }
        }
        
        if (empty($error)) {
            // Insert new university
            $stmt = $conn->prepare("INSERT INTO universities (name, location, description, logo) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $location, $description, $logo_path);
            
            if ($stmt->execute()) {
                $success = "University added successfully!";
                // Clear form on success
                $name = $location = $description = '';
            } else {
                $error = "Error adding university. Please try again.";
            }
            
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add University | UniProjectHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --success-color: #2ecc71;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        /* Header Styles */
        header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }
        
        .logo {
            display: flex;
            align-items: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .logo i {
            margin-right: 10px;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
        }
        
        .nav-links li {
            margin-left: 1.5rem;
        }
        
        .nav-links a {
            text-decoration: none;
            color: var(--secondary-color);
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
            color: var(--primary-color);
        }
        
        .auth-buttons .btn {
            margin-left: 1rem;
        }
        
        .btn {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn:hover {
            background-color: #2980b9;
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-outline:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        /* Form Styles */
        .form-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
            padding: 2rem 0;
        }
        
        .form-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            padding: 2.5rem;
        }
        
        .form-title {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--secondary-color);
        }
        
        .form-title h2 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--secondary-color);
        }
        
        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        /* Logo Upload Styles */
        .logo-upload {
            text-align: center;
        }
        
        .logo-preview {
            width: 200px;
            height: 200px;
            border-radius: 10px;
            margin: 0 auto 10px;
            overflow: hidden;
            border: 3px dashed #ddd;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .logo-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .logo-preview::after {
            content: "\f03e";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 2rem;
            color: #ccc;
            z-index: -1;
        }
        
        .logo-preview.has-image::after {
            display: none;
        }
        
        /* Alerts */
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 5px;
            font-weight: 500;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="/uniprojecthub/index.php" class="logo">
                    <i class="fas fa-project-diagram"></i>
                    <span>UniProjectHub</span>
                </a>
                <ul class="nav-links">
                    <li><a href="/uniprojecthub/index.php">Home</a></li>
                    <li><a href="/uniprojecthub/universities/universities.php">Universities</a></li>
                    <li><a href="/uniprojecthub/about.php">About</a></li>
                    <li><a href="/uniprojecthub/contact.php">Contact</a></li>
                </ul>
                <div class="auth-buttons">
                    <a href="/uniprojecthub/auth/logout.php" class="btn btn-outline">Logout</a>
                </div>
            </nav>
        </div>
    </header>

    <!-- University Form -->
    <section class="form-container">
        <div class="container">
            <div class="form-card">
                <div class="form-title">
                    <h2>Add New University</h2>
                    <p>Fill in the details to register a new university</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <form action="add_university.php" method="POST" enctype="multipart/form-data" id="universityForm">
                    <div class="form-group">
                        <label for="name">University Name *</label>
                        <input type="text" id="name" name="name" class="form-control" required value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Location *</label>
                        <input type="text" id="location" name="location" class="form-control" required value="<?php echo isset($location) ? htmlspecialchars($location) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control"><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="logo">University Logo</label>
                        <div class="logo-upload">
                            <div class="logo-preview" id="logoPreview">
                                <img src="" alt="Logo preview" id="logoPreviewImg">
                            </div>
                            <input type="file" id="logo" name="logo" accept="image/*" class="form-control" style="display: none;">
                            <button type="button" class="btn btn-outline" id="chooseLogo" style="width: 100%; margin-top: 10px;">
                                <i class="fas fa-image"></i> Choose Logo
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn" style="width: 100%; padding: 1rem;">Add University</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Logo preview functionality
            $('#chooseLogo').click(function() {
                $('#logo').click();
            });
            
            $('#logo').change(function() {
                const file = this.files[0];
                const preview = $('#logoPreview');
                const previewImg = $('#logoPreviewImg');
                
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        previewImg.attr('src', event.target.result);
                        preview.addClass('has-image');
                    }
                    reader.readAsDataURL(file);
                } else {
                    previewImg.attr('src', '');
                    preview.removeClass('has-image');
                }
            });
            
            // Form validation
            $('#universityForm').submit(function() {
                const name = $('#name').val().trim();
                const location = $('#location').val().trim();
                
                if (!name) {
                    alert('University name is required');
                    return false;
                }
                
                if (!location) {
                    alert('Location is required');
                    return false;
                }
                
                return true;
            });
        });
    </script>
</body>
</html>