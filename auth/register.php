<?php
include(__DIR__ . '/../config.php');

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: /uniprojectHub-main/users/dashboard.php");
    exit();
}

$error = '';
$success = '';

// Fetch universities for dropdown
$universities = [];
$uni_result = $conn->query("SELECT id, name FROM universities ORDER BY name ASC");
if ($uni_result) {
    while ($row = $uni_result->fetch_assoc()) {
        $universities[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $university_id = !empty($_POST['university_id']) ? intval($_POST['university_id']) : null;

    if (empty($name) || empty($email) || empty($password)) {
        $error = "Name, email, and password are required fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check if email already exists
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            $error = "An account with this email already exists.";
        } else {
            // Handle profile picture
            $profile_path = null;
            if (!empty($_FILES['profile_pic']['name'])) {
                $target_dir = __DIR__ . "/../assets/uploads/users/";
                if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
                
                $file_type = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
                $new_filename = uniqid() . '.' . $file_type;
                $target_file = $target_dir . $new_filename;
                
                $allowed_types = ['jpg', 'jpeg', 'png'];
                if (in_array($file_type, $allowed_types) && move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
                    $profile_path = "/uniprojectHub-main/assets/uploads/users/" . $new_filename;
                }
            }
            
            // Hash password and insert
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, university_id, profile_pic) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssis", $name, $email, $hashed_password, $university_id, $profile_path);
            
            if ($stmt->execute()) {
                // Auto-login
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['full_name'] = $name;
                
                header("Location: /uniprojectHub-main/users/dashboard.php");
                exit();
            } else {
                $error = "Error registering account. Please try again.";
            }
            $stmt->close();
        }
        $stmt_check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | UniProjectHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #007bff;
            --primary-dark: #0056b3;
            --secondary-color: #2c3e50;
            --light-color: #ecf0f1;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

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
        
        .logo i { margin-right: 10px; }
        
        .nav-links {
            display: flex;
            list-style: none;
        }
        
        .nav-links li { margin-left: 1.5rem; }
        .nav-links a {
            text-decoration: none;
            color: var(--secondary-color);
            font-weight: 500;
            transition: color 0.3s;
        }
        .nav-links a:hover { color: var(--primary-color); }
        
        .auth-buttons .btn { margin-left: 1rem; }

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
            background-color: var(--primary-dark);
            color: white;
            transform: translateY(-2px);
        }

        .auth-container {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem 15px;
        }

        .auth-card {
            background: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 800px;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-header h2 {
            font-size: 2rem;
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }

        .form-col {
            flex: 1 1 50%;
            padding: 0 10px;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--secondary-color);
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #777;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        select.form-control {
            appearance: none;
            cursor: pointer;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #777;
            left: auto;
        }

        .strength-meter-container {
            margin-top: 8px;
            height: 5px;
            width: 100%;
            background: #e0e0e0;
            border-radius: 3px;
            overflow: hidden;
        }

        .strength-meter {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
        }

        .strength-text {
            font-size: 0.8rem;
            margin-top: 5px;
            color: #777;
        }

        .btn-block {
            width: 100%;
            padding: 0.8rem;
            font-size: 1rem;
            margin-top: 1rem;
        }

        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
        }

        .auth-footer a {
            color: var(--primary-color);
            text-decoration: none;
        }

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

        @media (max-width: 768px) {
            .form-col {
                flex: 1 1 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="/uniprojectHub-main/index.php" class="logo">
                    <i class="fas fa-project-diagram"></i>
                    <span>UniProjectHub</span>
                </a>
                <ul class="nav-links">
                    <li><a href="/uniprojectHub-main/index.php">Home</a></li>
                    <li><a href="/uniprojectHub-main/universities/universities.php">Universities</a></li>
                    <li><a href="/uniprojectHub-main/about.php">About</a></li>
                    <li><a href="/uniprojectHub-main/contact.php">Contact</a></li>
                </ul>
                <div class="auth-buttons">
                    <a href="/uniprojectHub-main/auth/login.php" class="btn btn-outline">Login</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Create an Account</h2>
                <p>Join the global student collaboration platform</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="register.php" method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <div class="input-group">
                                <i class="fas fa-user"></i>
                                <input type="text" id="name" name="name" class="form-control" placeholder="John Doe" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" class="form-control" placeholder="you@university.edu" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="university_id">University (Optional)</label>
                            <div class="input-group">
                                <i class="fas fa-university"></i>
                                <select id="university_id" name="university_id" class="form-control">
                                    <option value="">Select your university...</option>
                                    <?php foreach ($universities as $uni): ?>
                                        <option value="<?php echo $uni['id']; ?>"><?php echo htmlspecialchars($uni['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" class="form-control" placeholder="Create a password" required>
                                <i class="fas fa-eye password-toggle" onclick="togglePasswordVisibility('password', this)"></i>
                            </div>
                            <div class="strength-meter-container">
                                <div class="strength-meter" id="strengthMeter"></div>
                            </div>
                            <div class="strength-text" id="strengthText">Password strength</div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm your password" required>
                                <i class="fas fa-eye password-toggle" onclick="togglePasswordVisibility('confirm_password', this)"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="profile_pic">Profile Picture (Optional)</label>
                            <div class="input-group">
                                <i class="fas fa-image"></i>
                                <input type="file" id="profile_pic" name="profile_pic" class="form-control" accept=".jpg,.jpeg,.png">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label style="display:flex; align-items:center; font-weight:normal; font-size:0.9rem; cursor:pointer;">
                        <input type="checkbox" required style="margin-right: 10px;">
                        I agree to the <a href="/uniprojectHub-main/terms.php" style="color:var(--primary-color); margin-left: 4px; text-decoration:none;">Terms of Service</a> & Privacy Policy
                    </label>
                </div>

                <button type="submit" class="btn btn-block">Register Account</button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="/uniprojectHub-main/auth/login.php">Login here</a></p>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(fieldId, icon) {
            const field = document.getElementById(fieldId);
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        const passwordInput = document.getElementById('password');
        const meter = document.getElementById('strengthMeter');
        const text = document.getElementById('strengthText');

        passwordInput.addEventListener('input', function() {
            const val = passwordInput.value;
            let strength = 0;
            
            if (val.length >= 6) strength += 25;
            if (val.match(/[A-Z]/)) strength += 25;
            if (val.match(/[0-9]/)) strength += 25;
            if (val.match(/[^a-zA-Z0-9]/)) strength += 25;

            meter.style.width = strength + '%';

            if (strength <= 25) {
                meter.style.backgroundColor = 'var(--danger-color)';
                text.textContent = 'Weak';
                text.style.color = 'var(--danger-color)';
            } else if (strength <= 50) {
                meter.style.backgroundColor = 'var(--warning-color)';
                text.textContent = 'Fair';
                text.style.color = 'var(--warning-color)';
            } else if (strength <= 75) {
                meter.style.backgroundColor = '#17a2b8';
                text.textContent = 'Good';
                text.style.color = '#17a2b8';
            } else {
                meter.style.backgroundColor = 'var(--success-color)';
                text.textContent = 'Strong';
                text.style.color = 'var(--success-color)';
            }

            if (val.length === 0) {
                meter.style.width = '0%';
                text.textContent = 'Password strength';
                text.style.color = '#777';
            }
        });
    </script>
</body>
</html>
