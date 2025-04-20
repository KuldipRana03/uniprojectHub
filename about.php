<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!headers_sent()) {
    // session_start();
    $isLoggedIn = isset($_SESSION['user_id']);
} else {
    // Handle error or set default value
    $isLoggedIn = false;
}

// Database configuration (if needed for dynamic content)
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'student_projects_db';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get stats from database (example - customize as needed)
$projects_count = 0;
$universities_count = 0;
$students_count = 0;

$result = $conn->query("SELECT COUNT(*) as count FROM projects");
if ($result) $projects_count = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM universities");
if ($result) $universities_count = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM users");
if ($result) $students_count = $result->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | UniProjectHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

        /* navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        navbar-brand {
            font-weight: bold;
            color: var(--primary-color) !important;
        } */
        
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/images/about-hero.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
            margin-top: 56px; /* Account for fixed navbar */
        }
        
        .mission-stats {
            display: flex;
            gap: 20px;
            margin-top: 30px;
        }
        
        .stat-item {
            text-align: center;
            background: rgba(52, 152, 219, 0.1);
            padding: 20px;
            border-radius: 10px;
            flex: 1;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .feature-card {
            transition: transform 0.3s;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
        }
        
        .feature-icon {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .team-card {
            padding: 20px;
            transition: all 0.3s;
            text-align: center;
        }
        
        .team-card img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            margin-bottom: 1rem;
        }
        
        .social-links1 a {
            color: #666;
            margin: 0 5px;
            transition: color 0.3s;
        }
        
        .social-links1 a:hover {
            color: var(--primary-color);
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
            .mission-stats {
                flex-direction: column;
            }
            
            .hero-section {
                padding: 80px 0;
            }
        }
    </style>
</head>
<body>
    <!-- headder -->
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

    <!-- Main Content -->
    <div class="about-container">
        <section class="hero-section">
            <div class="container">
                <h1>About UniProjectHub</h1>
                <p class="lead">Connecting student innovators across universities worldwide</p>
            </div>
        </section>

        <section class="py-5">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <h2>Our Mission</h2>
                        <p>UniProjectHub was founded to break down barriers between student innovators at different universities. We provide a platform where students can collaborate on projects, share knowledge, and showcase their work to a global audience.</p>
                        <div class="mission-stats mt-4">
                            <div class="stat-item">
                                <div class="stat-number" id="projects-count"><?= $projects_count ?></div>
                                <div class="stat-label">Projects</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number" id="universities-count"><?= $universities_count ?></div>
                                <div class="stat-label">Universities</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number" id="students-count"><?= $students_count ?></div>
                                <div class="stat-label">Students</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <img src="/uniprojecthub\assets\images\our-mission.jpg" alt="Students collaborating" class="img-fluid rounded shadow">
                    </div>
                </div>
            </div>
        </section>

        <section class="py-5 bg-light">
            <div class="container">
                <h2 class="text-center mb-5">Why Choose UniProjectHub?</h2>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card feature-card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <div class="feature-icon mb-3">
                                    <i class="fas fa-users fa-3x"></i>
                                </div>
                                <h3>Collaboration</h3>
                                <p>Find team members with complementary skills from different universities to bring your project to life.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card feature-card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <div class="feature-icon mb-3">
                                    <i class="fas fa-graduation-cap fa-3x"></i>
                                </div>
                                <h3>University Network</h3>
                                <p>Access talent and resources from top universities around the world.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card feature-card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <div class="feature-icon mb-3">
                                    <i class="fas fa-briefcase fa-3x"></i>
                                </div>
                                <h3>Career Opportunities</h3>
                                <p>Showcase your work to potential employers and build a professional portfolio.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-5">
            <div class="container">
                <h2 class="text-center mb-5">Meet The Team</h2>
                <div class="row justify-content-center g-4">
                    <div class="col-md-4 col-lg-3">
                        <div class="team-card">
                            <img src="/uniprojecthub\assets\images\rahul.jpg" class="rounded-circle mb-3" alt="Team member">
                            <h4>Rahul Tyagi</h4>
                            <p class="text-muted">Student</p>
                            <div class="social-links1">
                                <a href="https://www.linkedin.com/in/the-rahul-tyagi?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=android_app"><i class="fab fa-linkedin"></i></a>
                                <a href="https://github.com/the-rahul-tyagi"><i class="fab fa-github"></i></a>
                                <a href="#"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <div class="team-card">
                            <img src="/uniprojecthub\assets\images\kuldip.jpg" class="rounded-circle mb-3" alt="Team member">
                            <h4>Kuldip Rana</h4>
                            <p class="text-muted">Student</p>
                            <div class="social-links1">
                                <a href="#"><i class="fab fa-linkedin"></i></a>
                                <a href="#"><i class="fab fa-github"></i></a>
                                <a href="#"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <div class="team-card">
                            <img src="/uniprojecthub\assets\images\praveen.jpg" class="rounded-circle mb-3" alt="Team member">
                            <h4>Praveen Ray</h4>
                            <p class="text-muted">Student</p>
                            <div class="social-links1">
                                <a href="#"><i class="fab fa-linkedin"></i></a>
                                <a href="#"><i class="fab fa-github"></i></a>
                                <a href="#"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <div class="team-card">
                            <img src="/uniprojecthub\assets\images\giriraj.jpg" class="rounded-circle mb-3" alt="Team member">
                            <h4>Giriraj</h4>
                            <p class="text-muted">Student</p>
                            <div class="social-links1">
                                <a href="#"><i class="fab fa-linkedin"></i></a>
                                <a href="#"><i class="fab fa-github"></i></a>
                                <a href="#"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
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
    // Animate stats counting
    document.addEventListener('DOMContentLoaded', function() {
        const animateCount = (elementId, target) => {
            const element = document.getElementById(elementId);
            const duration = 2000;
            const start = 0;
            const increment = target / (duration / 16);
            let current = start;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    clearInterval(timer);
                    current = target;
                }
                element.textContent = Math.floor(current).toLocaleString();
            }, 16);
        };

        // Animate all stats
        animateCount('projects-count', <?= $projects_count ?>);
        animateCount('universities-count', <?= $universities_count ?>);
        animateCount('students-count', <?= $students_count ?>);
    });
    </script>
</body>
</html>
<?php
$conn->close();
?>