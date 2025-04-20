<?php
require_once '../config.php';

if (!headers_sent()) {
    // session_start();
    $isLoggedIn = isset($_SESSION['user_id']);
} else {
    // Handle error or set default value
    $isLoggedIn = false;
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$user = [];
$stmt = $conn->prepare("SELECT u.*, un.name AS university_name 
                       FROM users u 
                       LEFT JOIN universities un ON u.university_id = un.id 
                       WHERE u.id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Get user projects
$projects = [];
$stmt = $conn->prepare("SELECT p.*, 
                       (SELECT COUNT(*) FROM project_members WHERE project_id = p.id) AS member_count
                       FROM projects p
                       WHERE p.created_by = ?
                       ORDER BY p.created_at DESC
                       LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
}
$stmt->close();

// Get projects user is member of
$member_projects = [];
$stmt = $conn->prepare("SELECT p.*, 
                       (SELECT COUNT(*) FROM project_members WHERE project_id = p.id) AS member_count
                       FROM projects p
                       JOIN project_members pm ON p.id = pm.project_id
                       WHERE pm.user_id = ? AND p.created_by != ?
                       ORDER BY pm.joined_at DESC
                       LIMIT 5");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $member_projects[] = $row;
}
$stmt->close();

// Handle profile updates
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitizeInput($_POST['full_name']);
    $bio = sanitizeInput($_POST['bio']);
    $university_id = isset($_POST['university_id']) ? (int)$_POST['university_id'] : null;
    
    // Handle file upload
if (!empty($_FILES['profile_pic']['name'])) {
    $target_dir = "assets/uploads/profiles/";
    
    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true); // Creates directory recursively
    }
    
    $file_type = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_type;
    $target_file = $target_dir . $new_filename;
        
        // Validate upload
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($file_type, $allowed_types)) {
            $error = 'Only JPG, JPEG, PNG & GIF files are allowed.';
        } elseif ($_FILES['profile_pic']['size'] > $max_size) {
            $error = 'File too large. Max 2MB allowed.';
        } elseif (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
            // Delete old profile picture if it exists
            if (!empty($user['profile_pic']) && file_exists($user['profile_pic'])) {
                unlink($user['profile_pic']);
            }
            $user['profile_pic'] = $target_file;
        } else {
            $error = 'Error uploading file.';
        }
    }
    
    if (empty($error)) {
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, bio = ?, university_id = ?, profile_pic = ? WHERE id = ?");
        $profile_pic = $user['profile_pic'] ?? null;
        $stmt->bind_param("ssisi", $full_name, $bio, $university_id, $profile_pic, $user_id);
        
        if ($stmt->execute()) {
            $success = 'Profile updated successfully!';
            // Refresh user data
            $stmt = $conn->prepare("SELECT u.*, un.name AS university_name FROM users u LEFT JOIN universities un ON u.university_id = un.id WHERE u.id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $_SESSION['full_name'] = $user['full_name'];
        } else {
            $error = 'Error updating profile: ' . $conn->error;
        }
    }
}

// Get universities for dropdown
$universities = [];
$result = $conn->query("SELECT id, name FROM universities ORDER BY name");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $universities[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | UniProjectHub</title>
    <link rel="stylesheet" href="/uniprojecthub\assets\css\header.css">
    <link rel="stylesheet" href="/uniprojecthub\assets\css\common.css">
    <link rel="stylesheet" href="/uniprojecthub\assets\css\footer.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .profile-card {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        
        .profile-card:hover {
            transform: translateY(-5px);
        }
        
        .nav-pills .nav-link.active {
            background-color: var(--primary-color);
        }
        
        .project-card {
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s;
        }
        
        .project-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .skill-tag {
            background-color: var(--light-color);
            color: var(--dark-color);
            padding: 5px 10px;
            border-radius: 50px;
            font-size: 0.8rem;
            margin-right: 5px;
            margin-bottom: 5px;
            display: inline-block;
        }
        
        .edit-profile-btn {
            position: absolute;
            top: 10px;
            right: 10px;
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

        /* Footer */
        footer {
            background-color: var(--secondary-color);
            color: white;
            padding: 3rem 0 1rem;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .footer-column h3 {
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 0.8rem;
        }
        
        .footer-links a {
            color: #bbb;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
        }
        
        .social-links a {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s;
        }
        
        .social-links a:hover {
            background-color: var(--primary-color);
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #bbb;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .profile-avatar {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="/uniprojecthub\index.php" class="logo">
                    <i class="fas fa-project-diagram"></i>
                    <span>UniProjectHub</span>
                </a>
                <ul class="nav-links">
                    <li><a href="/uniprojecthub\index.php">Home</a></li>
                    <li><a href="/uniprojecthub\projects\projects.php">Projects</a></li>
                    <li><a href="/uniprojecthub\universities\universities.php">Universities</a></li>
                    <li><a href="/uniprojecthub\about.php">About</a></li>
                    <li><a href="/uniprojecthub\contact.php">Contact</a></li>
                    <?php if ($isLoggedIn): ?>
                        <li><a href="/uniprojecthub\users\dashboard.php">Dashboard</a></li>
                    <?php endif; ?>
                </ul>
                <div class="auth-buttons">
                    <?php if ($isLoggedIn): ?>
                        <a href="/uniprojecthub\auth\logout.php" class="btn">Logout</a>
                    <?php else: ?>
                        <a href="/uniprojecthub\auth\login.php" class="btn btn-outline">Login</a>
                        <a href="/uniprojecthub\auth\register.php" class="btn">Register</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <!-- Profile Header -->
    <div class="profile-header">
        <div class="container text-center">
            <img src="<?php echo !empty($user['profile_pic']) ? htmlspecialchars($user['profile_pic']) : 'assets/images/default-profile.jpg'; ?>" 
                 alt="Profile picture" 
                 class="profile-avatar mb-3">
            <h1><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></h1>
            <p class="lead mb-0">
                <?php if (!empty($user['university_name'])): ?>
                    <i class="fas fa-university me-1"></i> <?php echo htmlspecialchars($user['university_name']); ?>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mb-5">
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-4">
                <div class="card profile-card mb-4">
                    <div class="card-body">
                        <button class="btn btn-sm btn-primary edit-profile-btn" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        
                        <h5 class="card-title">About Me</h5>
                        <p class="card-text"><?php echo !empty($user['bio']) ? nl2br(htmlspecialchars($user['bio'])) : 'No bio yet.'; ?></p>
                        
                        <hr>
                        
                        <h5>Contact</h5>
                        <p class="mb-1"><i class="fas fa-envelope me-2"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                        <?php if (!empty($user['university_name'])): ?>
                            <p class="mb-0"><i class="fas fa-university me-2"></i> <?php echo htmlspecialchars($user['university_name']); ?></p>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <h5>Skills</h5>
                        <div class="skills-container">
                            <span class="skill-tag"><i class="fas fa-code me-1"></i> Web Development</span>
                            <span class="skill-tag"><i class="fas fa-robot me-1"></i> AI</span>
                            <span class="skill-tag"><i class="fas fa-database me-1"></i> Database</span>
                            <!-- These would be dynamic in a real system -->
                        </div>
                    </div>
                </div>
                
                <div class="card profile-card">
                    <div class="card-body">
                        <h5 class="card-title">Statistics</h5>
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="h4 mb-0"><?php echo count($projects); ?></div>
                                <small class="text-muted">Projects</small>
                            </div>
                            <div class="col-4">
                                <div class="h4 mb-0"><?php echo count($member_projects); ?></div>
                                <small class="text-muted">Collaborations</small>
                            </div>
                            <div class="col-4">
                                <div class="h4 mb-0">12</div>
                                <small class="text-muted">Connections</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="col-lg-8">
                <ul class="nav nav-pills mb-4" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="projects-tab" data-bs-toggle="pill" data-bs-target="#projects" type="button">
                            My Projects
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="collaborations-tab" data-bs-toggle="pill" data-bs-target="#collaborations" type="button">
                            My Collaborations
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="activity-tab" data-bs-toggle="pill" data-bs-target="#activity" type="button">
                            Recent Activity
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="profileTabsContent">
                    <!-- My Projects Tab -->
                    <div class="tab-pane fade show active" id="projects" role="tabpanel">
                        <?php if (empty($projects)): ?>
                            <div class="alert alert-info">
                                You haven't created any projects yet. <a href="/uniprojecthub\projects\create-project.php" class="alert-link">Create your first project</a>.
                            </div>
                        <?php else: ?>
                            <?php foreach ($projects as $project): ?>
                                <div class="card project-card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <h5 class="card-title mb-1">
                                                <a href="project-details.php?id=<?php echo $project['id']; ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($project['title']); ?>
                                                </a>
                                            </h5>
                                            <span class="badge bg-<?php echo $project['status'] === 'active' ? 'success' : ($project['status'] === 'completed' ? 'primary' : 'secondary'); ?>">
                                                <?php echo ucfirst($project['status']); ?>
                                            </span>
                                        </div>
                                        <p class="card-text text-muted small mb-2">
                                            Created on <?php echo date('M d, Y', strtotime($project['created_at'])); ?> • 
                                            <?php echo $project['member_count']; ?> members
                                        </p>
                                        <p class="card-text"><?php echo substr(htmlspecialchars($project['description']), 0, 150); ?>...</p>
                                        <?php if (!empty($project['tags'])): ?>
                                            <div class="mt-2">
                                                <?php 
                                                $tags = explode(',', $project['tags']);
                                                foreach ($tags as $tag): 
                                                    if (!empty(trim($tag))):
                                                ?>
                                                    <span class="badge bg-light text-dark me-1"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                                <?php 
                                                    endif;
                                                endforeach; 
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="text-center mt-3">
                                <a href="my-projects.php" class="btn btn-outline-primary">View All Projects</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Collaborations Tab -->
                    <div class="tab-pane fade" id="collaborations" role="tabpanel">
                        <?php if (empty($member_projects)): ?>
                            <div class="alert alert-info">
                                You haven't joined any projects yet. <a href="projects.php" class="alert-link">Browse projects</a> to collaborate.
                            </div>
                        <?php else: ?>
                            <?php foreach ($member_projects as $project): ?>
                                <div class="card project-card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title mb-1">
                                            <a href="project-details.php?id=<?php echo $project['id']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($project['title']); ?>
                                            </a>
                                        </h5>
                                        <p class="card-text text-muted small mb-2">
                                            Joined on <?php echo date('M d, Y', strtotime($project['joined_at'])); ?> • 
                                            <?php echo $project['member_count']; ?> members
                                        </p>
                                        <p class="card-text"><?php echo substr(htmlspecialchars($project['description']), 0, 150); ?>...</p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="text-center mt-3">
                                <a href="my-collaborations.php" class="btn btn-outline-primary">View All Collaborations</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Activity Tab -->
                    <div class="tab-pane fade" id="activity" role="tabpanel">
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex mb-3">
                                    <img src="assets/images/default-profile.jpg" class="rounded-circle me-3" width="50" height="50" alt="User">
                                    <div>
                                        <h6 class="mb-0">You updated your profile</h6>
                                        <small class="text-muted">2 hours ago</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex mb-3">
                                    <img src="assets/images/default-profile.jpg" class="rounded-circle me-3" width="50" height="50" alt="User">
                                    <div>
                                        <h6 class="mb-0">You joined "AI Campus Navigation" project</h6>
                                        <small class="text-muted">3 days ago</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex mb-3">
                                    <img src="assets/images/default-profile.jpg" class="rounded-circle me-3" width="50" height="50" alt="User">
                                    <div>
                                        <h6 class="mb-0">You created "Sustainable Energy Monitoring" project</h6>
                                        <small class="text-muted">1 week ago</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <div class="mb-3">
                                    <img id="profilePreview" src="<?php echo !empty($user['profile_pic']) ? htmlspecialchars($user['profile_pic']) : 'assets/images/default-profile.jpg'; ?>" 
                                         class="rounded-circle img-thumbnail" 
                                         width="150" 
                                         height="150" 
                                         alt="Profile preview">
                                </div>
                                <div class="mb-3">
                                    <label for="profile_pic" class="form-label">Change Profile Picture</label>
                                    <input class="form-control" type="file" id="profile_pic" name="profile_pic" accept="image/*">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                </div>
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="university_id" class="form-label">University</label>
                                    <select class="form-select" id="university_id" name="university_id">
                                        <option value="">Select your university</option>
                                        <?php foreach ($universities as $university): ?>
                                            <option value="<?php echo $university['id']; ?>" <?php echo ($user['university_id'] == $university['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($university['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="bio" class="form-label">Bio</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>About UniProjectHub</h3>
                    <p>A platform connecting students from different universities to collaborate on projects and showcase their innovative work.</p>
                    <div class="social-links" style="margin-top: 1rem;">
                        <a href="https://www.facebook.com/share/1EXf5MiDum/"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://x.com/the_rahul_tyagi?t=62a2rbMupFceUrxiX1FI6Q&s=08"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.linkedin.com/in/the-rahul-tyagi?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=android_app"><i class="fab fa-linkedin-in"></i></a>
                        <a href="https://www.instagram.com/the_rahul_tyagi?utm_source=qr&igsh=dXJxdHEyNXYxN2Zz"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="/uniprojecthub\index.php">Home</a></li>
                        <li><a href="/uniprojecthub\projects\projects.php">Projects</a></li>
                        <li><a href="/uniprojecthub\universities\universities.php">Universities</a></li>
                        <li><a href="/uniprojecthub\about.php">About Us</a></li>
                        <li><a href="/uniprojecthub\contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Resources</h3>
                    <ul class="footer-links">
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contact Us</h3>
                    <ul class="footer-links">
                        <li><i class="fas fa-envelope"></i> contact@uniprojecthub.com</li>
                        <li><i class="fas fa-phone"></i> +91 1234567890</li>
                        <li><i class="fas fa-map-marker-alt"></i> 123 University Ave, Tech City, TC 10001</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 UniProjectHub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Profile picture preview
        document.getElementById('profile_pic').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('profilePreview').src = event.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>