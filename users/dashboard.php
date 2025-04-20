<?php
include(__DIR__ . '/../config.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /uniprojecthub\auth\login.php");
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

// Get user projects (created by user)
$createdProjects = [];
$stmt = $conn->prepare("SELECT p.*, un.name AS university_name,
                       COUNT(pm.id) AS member_count
                       FROM projects p
                       LEFT JOIN universities un ON p.university_id = un.id
                       LEFT JOIN project_members pm ON p.id = pm.project_id
                       WHERE p.created_by = ?
                       GROUP BY p.id
                       ORDER BY p.created_at DESC
                       LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $createdProjects[] = $row;
}
$stmt->close();

// Get user projects (member of)
$memberProjects = [];
$stmt = $conn->prepare("SELECT p.*, un.name AS university_name,
                       COUNT(pm.id) AS member_count
                       FROM projects p
                       LEFT JOIN universities un ON p.university_id = un.id
                       LEFT JOIN project_members pm ON p.id = pm.project_id
                       WHERE pm.user_id = ? AND p.created_by != ?
                       GROUP BY p.id
                       ORDER BY p.created_at DESC
                       LIMIT 5");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $memberProjects[] = $row;
}
$stmt->close();

// Get recent activity
$recentActivity = [];
$stmt = $conn->prepare("SELECT pu.*, p.title AS project_title
                       FROM project_updates pu
                       JOIN projects p ON pu.project_id = p.id
                       JOIN project_members pm ON p.id = pm.project_id
                       WHERE pm.user_id = ?
                       ORDER BY pu.posted_at DESC
                       LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recentActivity[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | UniProjectHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
    :root {
        --primary-color: #3498db;
        --primary-dark: #2980b9;
        --secondary-color: #2c3e50;
        --accent-color: #e74c3c;
        --light-color: #ecf0f1;
        --lighter-color: #f8f9fa;
        --dark-color: #2c3e50;
        --success-color: #2ecc71;
        --warning-color: #f39c12;
        --info-color: #3498db;
        --border-color: #e0e0e0;
        --text-color: #333;
        --text-light: #777;
        --shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        --shadow-hover: 0 8px 25px rgba(0, 0, 0, 0.1);
        --transition: all 0.3s ease;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        background-color: #f5f5f5;
        color: var(--text-color);
        line-height: 1.6;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .main-content {
        flex: 1;
    }

    .container {
        width: 100%;
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
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
        transition: var(--transition);
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
        box-shadow: var(--shadow-hover);
    }

    .btn-outline:hover {
        background-color: var(--primary-color);
        color: white;
    }

    .user-menu {
        display: flex;
        align-items: center;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #ddd;
        margin-right: 0.5rem;
        overflow: hidden;
    }

    .user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .btn-sm {
        padding: 0.3rem 1rem;
        font-size: 0.9rem;
    }

    /* Dashboard Layout */
    .dashboard-container {
        display: grid;
        grid-template-columns: 260px 1fr;
        gap: 2rem;
        padding: 2rem 0;
    }

    /* Sidebar */
    .sidebar {
        background: white;
        border-radius: 10px;
        box-shadow: var(--shadow);
        padding: 0;
        height: fit-content;
        position: sticky;
        top: 90px;
        overflow: hidden;
        transition: var(--transition);
    }

    .sidebar:hover {
        box-shadow: var(--shadow-hover);
    }

    .user-profile {
        text-align: center;
        padding: 2rem 1.5rem 1.5rem;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        position: relative;
        margin-bottom: 1.5rem;
    }

    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background-color: #ddd;
        margin: 0 auto 1rem;
        overflow: hidden;
        border: 4px solid white;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .user-profile h3 {
        margin-bottom: 0.5rem;
        color: white;
        font-weight: 600;
    }

    .user-profile p {
        color: rgba(255,255,255,0.8);
        font-size: 0.9rem;
    }

    .profile-status {
        position: absolute;
        bottom: -15px;
        left: 50%;
        transform: translateX(-50%);
        background: var(--success-color);
        color: white;
        padding: 0.3rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .sidebar-menu {
        list-style: none;
        padding: 0 1.5rem 1.5rem;
    }

    .sidebar-menu li {
        margin-bottom: 0.5rem;
        position: relative;
    }

    .sidebar-menu a {
        display: flex;
        align-items: center;
        padding: 0.8rem 1rem;
        color: var(--text-color);
        text-decoration: none;
        border-radius: 8px;
        transition: var(--transition);
        font-weight: 500;
    }

    .sidebar-menu a:hover, .sidebar-menu a.active {
        background-color: var(--light-color);
        color: var(--primary-color);
        transform: translateX(5px);
    }

    .sidebar-menu a:hover::before, .sidebar-menu a.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
        background: var(--primary-color);
        border-radius: 0 4px 4px 0;
    }

    .sidebar-menu i {
        width: 24px;
        margin-right: 0.8rem;
        text-align: center;
        font-size: 1.1rem;
    }

    .sidebar-menu .badge {
        margin-left: auto;
        background: var(--primary-color);
        color: white;
        padding: 0.2rem 0.5rem;
        border-radius: 10px;
        font-size: 0.7rem;
        font-weight: bold;
    }

    /* Main Content Area */
    .dashboard-content {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding: 1.5rem;
        background: white;
        border-radius: 10px;
        box-shadow: var(--shadow);
    }

    .dashboard-header h2 {
        color: var(--secondary-color);
        font-weight: 700;
        font-size: 1.8rem;
    }

    /* Dashboard Grid Layout */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
    }

    .dashboard-card {
        background: white;
        border-radius: 10px;
        box-shadow: var(--shadow);
        padding: 1.5rem;
        transition: var(--transition);
    }

    .dashboard-card:hover {
        box-shadow: var(--shadow-hover);
        transform: translateY(-3px);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 10px;
        text-align: center;
        box-shadow: var(--shadow);
        transition: var(--transition);
        border-top: 4px solid var(--primary-color);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
    }

    .stat-card h3 {
        font-size: 2.2rem;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
        font-weight: 700;
    }

    .stat-card p {
        color: var(--text-light);
        font-size: 0.9rem;
    }

    .section {
        margin-bottom: 0;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .section-header h3 {
        color: var(--secondary-color);
        font-weight: 600;
        font-size: 1.3rem;
        position: relative;
        padding-left: 15px;
    }

    .section-header h3::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        height: 70%;
        width: 4px;
        background: var(--primary-color);
        border-radius: 4px;
    }

    .section-header a {
        color: var(--primary-color);
        font-size: 0.9rem;
        text-decoration: none;
        font-weight: 500;
        transition: var(--transition);
    }

    .section-header a:hover {
        color: var(--primary-dark);
        text-decoration: underline;
    }

    .projects-list, .activity-list {
        list-style: none;
    }

    .project-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.2rem;
        border-bottom: 1px solid var(--border-color);
        transition: var(--transition);
        background: white;
        border-radius: 8px;
        margin-bottom: 1rem;
    }

    .project-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .project-item:hover {
        background-color: var(--light-color);
        transform: translateX(5px);
    }

    .project-info h4 {
        margin-bottom: 0.5rem;
        color: var(--secondary-color);
        font-weight: 600;
    }

    .project-meta {
        display: flex;
        gap: 1.2rem;
        color: var(--text-light);
        font-size: 0.85rem;
    }

    .project-meta span {
        display: flex;
        align-items: center;
    }

    .project-meta i {
        margin-right: 0.3rem;
        font-size: 0.9rem;
    }

    .activity-item {
        padding: 1.2rem;
        border-bottom: 1px solid var(--border-color);
        transition: var(--transition);
        background: white;
        border-radius: 8px;
        margin-bottom: 1rem;
    }

    .activity-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .activity-item:hover {
        background-color: var(--light-color);
    }

    .activity-content {
        margin-bottom: 0.5rem;
        display: flex;
        align-items: flex-start;
    }

    .activity-icon {
        background: var(--light-color);
        color: var(--primary-color);
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        flex-shrink: 0;
    }

    .activity-text {
        flex-grow: 1;
    }

    .activity-text p {
        margin-bottom: 0.3rem;
    }

    .activity-meta {
        color: var(--text-light);
        font-size: 0.8rem;
        display: flex;
        align-items: center;
    }

    .activity-meta i {
        margin-right: 0.3rem;
    }

    /* Footer */
    footer {
        background-color: var(--secondary-color);
        color: white;
        padding: 3rem 0 1rem;
        margin-top: 2rem;
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

    /* Responsive Styles */
    @media (max-width: 1200px) {
        .dashboard-container {
            grid-template-columns: 1fr;
        }
        
        .sidebar {
            position: static;
        }
        
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr 1fr;
        }
        
        .project-item {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .project-item .btn {
            margin-top: 1rem;
            align-self: flex-end;
        }
        
        .dashboard-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
    }

    @media (max-width: 576px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .project-meta {
            flex-direction: column;
            gap: 0.5rem;
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
                </ul>
                <div class="user-menu">
                    <div class="user-avatar">
                    <img src="<?php echo !empty($user['profile_pic']) ? htmlspecialchars($user['profile_pic']) : 'https://via.placeholder.com/100?text=' . substr($user['full_name'] ?? $user['username'], 0, 1); ?>" 
                    alt="<?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?>">
                    </div>
                    <span><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></span>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="dashboard-container">
                <!-- Sidebar -->
                <aside class="sidebar">
                    <div class="user-profile">
                        <div class="profile-avatar">
                        <img src="<?php echo !empty($user['profile_pic']) ? htmlspecialchars($user['profile_pic']) : 'https://via.placeholder.com/100?text=' . substr($user['full_name'] ?? $user['username'], 0, 1); ?>" 
                        alt="<?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?>">
                        </div>
                        <h3><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></h3>
                        <p><?php echo htmlspecialchars($user['university_name'] ?? 'No university'); ?></p>
                        <span class="profile-status">Active</span>
                    </div>
                    
                    <ul class="sidebar-menu">
                        <li><a href="/uniprojecthub\users\dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="/uniprojecthub\users\profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                        <li><a href="my-projects.php"><i class="fas fa-project-diagram"></i> My Projects <span class="badge"><?php echo count($createdProjects) + count($memberProjects); ?></span></a></li>
                        <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages <span class="badge">0</span></a></li>
                        <li><a href="notifications.php"><i class="fas fa-bell"></i> Notifications <span class="badge">0</span></a></li>
                        <li><a href="/uniprojecthub\users\settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                        <li><a href="/uniprojecthub\auth\logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </aside>
                
                <!-- Dashboard Content -->
                <div class="dashboard-content">
                    <!-- Dashboard Header -->
                    <div class="dashboard-header">
                        <h2>Welcome Back, <?php echo htmlspecialchars(explode(' ', $user['full_name'] ?? $user['username'])[0]); ?>!</h2>
                        <a href="/uniprojecthub\projects\create-project.php" class="btn"><i class="fas fa-plus"></i> Create New Project</a>
                    </div>
                    
                    <!-- Stats Cards -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <h3><?php echo count($createdProjects); ?></h3>
                            <p><i class="fas fa-project-diagram"></i> Projects Created</p>
                        </div>
                        <div class="stat-card">
                            <h3><?php echo count($memberProjects); ?></h3>
                            <p><i class="fas fa-users"></i> Projects Joined</p>
                        </div>
                        <div class="stat-card">
                            <h3>0</h3>
                            <p><i class="fas fa-envelope"></i> Messages</p>
                        </div>
                        <div class="stat-card">
                            <h3>0</h3>
                            <p><i class="fas fa-bell"></i> Notifications</p>
                        </div>
                    </div>
                    
                    <!-- Dashboard Grid Layout -->
                    <div class="dashboard-grid">
                        <!-- My Projects Section -->
                        <div class="dashboard-card">
                            <div class="section">
                                <div class="section-header">
                                    <h3>My Projects</h3>
                                    <a href="my-projects.php">View All <i class="fas fa-arrow-right"></i></a>
                                </div>
                                <ul class="projects-list">
                                    <?php if (empty($createdProjects)): ?>
                                        <li class="project-item">
                                            <div class="project-info">
                                                <h4>No Projects Created Yet</h4>
                                                <div class="project-meta">
                                                    <span>Create your first project to get started</span>
                                                </div>
                                            </div>
                                            <a href="/uniprojecthub\projects\create-project.php" class="btn">Create Project</a>
                                        </li>
                                    <?php else: ?>
                                        <?php foreach ($createdProjects as $project): ?>
                                            <li class="project-item">
                                                <div class="project-info">
                                                    <h4><?php echo htmlspecialchars($project['title']); ?></h4>
                                                    <div class="project-meta">
                                                        <span><i class="fas fa-university"></i> <?php echo htmlspecialchars($project['university_name'] ?? 'No university'); ?></span>
                                                        <span><i class="fas fa-users"></i> <?php echo $project['member_count']; ?> members</span>
                                                        <span><i class="far fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($project['created_at'])); ?></span>
                                                    </div>
                                                </div>
                                                <a href="/uniprojecthub\projects\project-details.php?id=<?php echo $project['id']; ?>" class="btn btn-outline">View Project</a>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Joined Projects Section -->
                        <div class="dashboard-card">
                            <div class="section">
                                <div class="section-header">
                                    <h3>Projects I'm Part Of</h3>
                                    <a href="my-projects.php">View All <i class="fas fa-arrow-right"></i></a>
                                </div>
                                <ul class="projects-list">
                                    <?php if (empty($memberProjects)): ?>
                                        <li class="project-item">
                                            <div class="project-info">
                                                <h4>No Joined Projects Yet</h4>
                                                <div class="project-meta">
                                                    <span>Browse projects to join and collaborate</span>
                                                </div>
                                            </div>
                                            <a href="/uniprojecthub\projects\projects.php" class="btn">Browse Projects</a>
                                        </li>
                                    <?php else: ?>
                                        <?php foreach ($memberProjects as $project): ?>
                                            <li class="project-item">
                                                <div class="project-info">
                                                    <h4><?php echo htmlspecialchars($project['title']); ?></h4>
                                                    <div class="project-meta">
                                                        <span><i class="fas fa-university"></i> <?php echo htmlspecialchars($project['university_name'] ?? 'No university'); ?></span>
                                                        <span><i class="fas fa-users"></i> <?php echo $project['member_count']; ?> members</span>
                                                        <span><i class="far fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($project['created_at'])); ?></span>
                                                    </div>
                                                </div>
                                                <a href="/uniprojecthub\projects\project-details.php?id=<?php echo $project['id']; ?>" class="btn btn-outline">View Project</a>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Recent Activity Section -->
                        <div class="dashboard-card">
                            <div class="section">
                                <div class="section-header">
                                    <h3>Recent Activity</h3>
                                    <a href="activity.php">View All <i class="fas fa-arrow-right"></i></a>
                                </div>
                                <ul class="activity-list">
                                    <?php if (empty($recentActivity)): ?>
                                        <li class="activity-item">
                                            <div class="activity-content">
                                                <div class="activity-icon">
                                                    <i class="fas fa-info-circle"></i>
                                                </div>
                                                <div class="activity-text">
                                                    <p>No recent activity to display</p>
                                                </div>
                                            </div>
                                        </li>
                                    <?php else: ?>
                                        <?php foreach ($recentActivity as $activity): ?>
                                            <li class="activity-item">
                                                <div class="activity-content">
                                                    <div class="activity-icon">
                                                        <i class="fas fa-bullhorn"></i>
                                                    </div>
                                                    <div class="activity-text">
                                                        <p><strong><?php echo htmlspecialchars($activity['project_title']); ?></strong>: <?php echo htmlspecialchars(substr($activity['content'], 0, 100)); ?>...</p>
                                                    </div>
                                                </div>
                                                <div class="activity-meta">
                                                    <i class="far fa-clock"></i> <?php echo date('M d, Y h:i A', strtotime($activity['posted_at'])); ?>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Add smooth hover effects
            $('.stat-card, .project-item, .activity-item, .dashboard-card').hover(
                function() {
                    $(this).css('transform', 'translateY(-3px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );
            
            // Add active state to menu items
            $('.sidebar-menu a').click(function() {
                $('.sidebar-menu a').removeClass('active');
                $(this).addClass('active');
            });
        });
    </script>
</body>
</html>