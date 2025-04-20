<?php
// Include database configuration
require_once __DIR__ . '/config.php';

// Start session and include database configuration
if (!headers_sent()) {
    // session_start();
    $isLoggedIn = isset($_SESSION['user_id']);
} else {
    $isLoggedIn = false;
}

// Check if database connection is established
if (!$conn) {
    die("Database connection failed");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Project Hub | Home</title>
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
            color: #333;
            line-height: 1.6;
            overflow-x: hidden;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Header */
        header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
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

        /* Video Background Hero Section */
        .video-hero {
            position: relative;
            height: 100vh;
            min-height: 600px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
        }

        .video-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
        }

        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 0;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            padding: 0 20px;
            animation: fadeInUp 1s ease-out;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-content p {
            font-size: 1.3rem;
            margin-bottom: 2.5rem;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        .search-bar {
            max-width: 600px;
            margin: 0 auto;
            display: flex;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .search-bar input {
            flex: 1;
            padding: 1rem;
            border: none;
            outline: none;
            font-size: 1rem;
            background: transparent;
        }

        .search-bar button {
            padding: 0 1.5rem;
            background-color: var(--accent-color);
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-bar button:hover {
            background-color: #c0392b;
        }

        .scroll-down {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            font-size: 2rem;
            animation: bounce 2s infinite;
            cursor: pointer;
            z-index: 1;
        }

        /* Features Section */
        .features {
            padding: 5rem 0;
            background-color: white;
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title h2 {
            font-size: 2.5rem;
            color: var(--secondary-color);
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }

        .section-title h2:after {
            content: '';
            position: absolute;
            width: 60px;
            height: 4px;
            background: var(--primary-color);
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }

        .section-title p {
            color: #777;
            max-width: 700px;
            margin: 0 auto;
            font-size: 1.1rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            text-align: center;
            border-top: 4px solid var(--primary-color);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .feature-card h3 {
            margin-bottom: 1rem;
            color: var(--secondary-color);
            font-size: 1.4rem;
        }

        /* Projects Section */
        .projects {
            padding: 5rem 0;
            background-color: #f9f9f9;
            position: relative;
        }

        .projects:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100px;
            background: linear-gradient(to bottom, white, transparent);
            z-index: 1;
        }

        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }

        .project-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
        }

        .project-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .project-image {
            height: 200px;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .project-image:after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, transparent, rgba(0, 0, 0, 0.7));
        }

        .project-content {
            padding: 1.5rem;
        }

        .project-content h3 {
            margin-bottom: 0.5rem;
            color: var(--secondary-color);
            font-size: 1.3rem;
        }

        .project-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            color: #777;
            font-size: 0.9rem;
        }

        .project-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .tag {
            background-color: var(--light-color);
            color: var(--secondary-color);
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
        }

        /* Universities Section */
        .universities {
            padding: 5rem 0;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            position: relative;
        }

        .universities:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100px;
            background: linear-gradient(to bottom, #f9f9f9, transparent);
            z-index: 1;
        }

        .universities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .university-card {
            background: white;
            padding: 2rem 1.5rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            text-align: center;
        }

        .university-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .university-logo {
            width: 120px;
            height: 120px;
            margin: 0 auto 1.5rem;
            background-color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .university-card:hover .university-logo {
            transform: scale(1.1);
        }

        .university-logo img {
            max-width: 80%;
            max-height: 80%;
        }

        .university-card h3 {
            margin-bottom: 0.5rem;
            color: var(--secondary-color);
        }

        /* Stats Section */
        .stats {
            padding: 4rem 0;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            text-align: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .stat-item {
            padding: 1.5rem;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        /* Testimonials Section */
        .testimonials {
            padding: 5rem 0;
            background-color: white;
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .testimonial-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            position: relative;
        }

        .testimonial-card:before {
            content: '"';
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 5rem;
            color: rgba(52, 152, 219, 0.1);
            font-family: Georgia, serif;
            line-height: 1;
        }

        .testimonial-content {
            position: relative;
            z-index: 1;
            margin-bottom: 1.5rem;
            font-style: italic;
            color: #555;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #ddd;
            margin-right: 1rem;
            overflow: hidden;
        }

        .author-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .author-info h4 {
            color: var(--secondary-color);
            margin-bottom: 0.2rem;
        }

        .author-info p {
            color: #777;
            font-size: 0.9rem;
        }

        /* Call to Action */
        .cta {
            padding: 5rem 0;
            background: linear-gradient(rgba(44, 62, 80, 0.9), rgba(44, 62, 80, 0.9)), url('https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            text-align: center;
        }

        .cta h2 {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
        }

        .cta p {
            max-width: 700px;
            margin: 0 auto 2.5rem;
            font-size: 1.1rem;
        }

        /* footer */
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
            position: relative;
            padding-bottom: 10px;
        }

        .footer-column h3:after {
            content: '';
            position: absolute;
            width: 40px;
            height: 3px;
            background: var(--primary-color);
            bottom: 0;
            left: 0;
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
            padding-left: 5px;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
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
            transform: translateY(-3px);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #bbb;
            font-size: 0.9rem;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0) translateX(-50%);
            }
            40% {
                transform: translateY(-20px) translateX(-50%);
            }
            60% {
                transform: translateY(-10px) translateX(-50%);
            }
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                padding: 1rem 0;
            }
            
            .nav-links {
                margin: 1rem 0;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .nav-links li {
                margin: 0.5rem;
            }
            
            .auth-buttons {
                margin-top: 1rem;
            }
            
            .hero-content h1 {
                font-size: 2.5rem;
            }

            .hero-content p {
                font-size: 1.1rem;
            }

            .section-title h2 {
                font-size: 2rem;
            }
        }

        @media (max-width: 480px) {
            .hero-content h1 {
                font-size: 2rem;
            }

            .search-bar {
                flex-direction: column;
                border-radius: 5px;
            }

            .search-bar input {
                width: 100%;
                border-radius: 5px 5px 0 0;
            }

            .search-bar button {
                width: 100%;
                padding: 0.8rem;
                border-radius: 0 0 5px 5px;
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

    <!-- Video Hero Section -->
    <section class="video-hero">
        <video class="video-background" autoplay muted loop>
            <source src="/uniprojecthub\assets\images\videoplayback (1).mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="video-overlay"></div>
        <div class="hero-content">
            <h1>Collaborate. Create. Innovate.</h1>
            <p>Discover and collaborate on student projects from universities across the globe. Showcase your work and find talented collaborators for your next big idea.</p>
            <div class="search-bar">
                <input type="text" placeholder="Search projects, universities, or technologies...">
                <button><i class="fas fa-search"></i></button>
            </div>
        </div>
        <div class="scroll-down" onclick="document.querySelector('.features').scrollIntoView({ behavior: 'smooth' })">
            <i class="fas fa-chevron-down"></i>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="section-title">
                <h2>Why Use Our Platform?</h2>
                <p>Our platform connects students from different universities to collaborate on innovative projects and showcase their work to a global audience.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Collaboration</h3>
                    <p>Find and connect with talented students from different universities to work on projects together. Expand your network and learn from peers worldwide.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3>University Network</h3>
                    <p>Access projects from top universities and see what students are working on around the world. Discover research trends and academic excellence.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <h3>Portfolio Building</h3>
                    <p>Showcase your projects to potential employers and build an impressive portfolio. Get recognized for your skills and achievements.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Projects Section -->
    <section class="projects">
        <div class="container">
            <div class="section-title">
                <h2>Featured Projects</h2>
                <p>Check out some of the most innovative projects created by students across our network.</p>
            </div>
            <div class="projects-grid">
                <?php
                try {
                    // Get featured projects from database
                    $query = "SELECT p.id, p.title, p.description, p.tags, 
                                    u.username, u.full_name, 
                                    un.name AS university_name, un.logo AS university_logo,
                                    COUNT(pm.id) AS member_count
                            FROM projects p
                            LEFT JOIN users u ON p.created_by = u.id
                            LEFT JOIN universities un ON p.university_id = un.id
                            LEFT JOIN project_members pm ON p.id = pm.project_id
                            GROUP BY p.id 
                            ORDER BY p.created_at DESC 
                            LIMIT 3";
                    
                    $result = $conn->query($query);
                    
                    if ($result && $result->num_rows > 0) {
                        while ($project = $result->fetch_assoc()):
                ?>
                            <div class="project-card">
                                <div class="project-image" style="background-image: url('https://source.unsplash.com/random/600x400/?project,<?php echo urlencode($project['title']); ?>');">
                                </div>
                                <div class="project-content">
                                    <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                                    <div class="project-meta">
                                        <span><i class="fas fa-university"></i> <?php echo htmlspecialchars($project['university_name']); ?></span>
                                        <span><i class="fas fa-user"></i> <?php echo $project['member_count']; ?> members</span>
                                    </div>
                                    <?php if (!empty($project['tags'])): ?>
                                        <div class="project-tags">
                                            <?php 
                                            $projectTags = explode(',', $project['tags']);
                                            foreach ($projectTags as $tag): 
                                                $tag = trim($tag);
                                                if (!empty($tag)):
                                            ?>
                                                <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
                                            <?php 
                                                endif;
                                            endforeach; 
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                    <p><?php echo htmlspecialchars(substr($project['description'], 0, 150) . '...'); ?></p>
                                    <a href="/uniprojecthub/projects/project-details.php?id=<?php echo $project['id']; ?>" class="btn" style="margin-top: 1rem;">View Project</a>
                                </div>
                            </div>
                <?php
                        endwhile;
                    } else {
                        echo '<p>No projects found in the database.</p>';
                    }
                } catch (Exception $e) {
                    echo '<p>Error loading projects. Please try again later.</p>';
                }
                ?>
            </div>
            <div style="text-align: center; margin-top: 3rem;">
                <a href="/uniprojecthub/projects/projects.php" class="btn">View All Projects</a>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
            <div class="section-title">
                <h2>What Our Users Say</h2>
                <p>Hear from students and professors who have benefited from our platform</p>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        UniProjectHub helped me find collaborators for my research project from different universities. The platform is intuitive and the community is very supportive.
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Sarah Johnson">
                        </div>
                        <div class="author-info">
                            <h4>Sarah Johnson</h4>
                            <p>Computer Science Student, MIT</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        As a professor, I encourage my students to use this platform to showcase their work and find interdisciplinary collaboration opportunities.
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Dr. Michael Chen">
                        </div>
                        <div class="author-info">
                            <h4>Dr. Michael Chen</h4>
                            <p>Professor, Stanford University</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        Through UniProjectHub, I connected with students from other countries for a joint project that won us an international competition!
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Priya Patel">
                        </div>
                        <div class="author-info">
                            <h4>Priya Patel</h4>
                            <p>Engineering Student, IIT Delhi</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Universities Section -->
    <section class="universities">
        <div class="container">
            <div class="section-title">
                <h2>Participating Universities</h2>
                <p>Join students from these prestigious institutions in collaborative innovation.</p>
            </div>
            <div class="universities-grid">
                <?php
                try {
                    // Get top 4 universities from database
                    $query = "SELECT id, name, location, logo, 
                                    (SELECT COUNT(*) FROM users WHERE university_id = universities.id) AS student_count,
                                    (SELECT COUNT(*) FROM projects WHERE university_id = universities.id) AS project_count
                            FROM universities
                            ORDER BY name
                            LIMIT 4";
                    
                    $result = $conn->query($query);
                    
                    if ($result && $result->num_rows > 0) {
                        while ($university = $result->fetch_assoc()):
                ?>
                            <div class="university-card">
                                <div class="university-logo">
                                    <img src="<?php echo $university['logo'] ? htmlspecialchars($university['logo']) : '/uniprojecthub/assets/images/default-university.png'; ?>" alt="<?php echo htmlspecialchars($university['name']); ?>">
                                </div>
                                <h3><?php echo htmlspecialchars($university['name']); ?></h3>
                                <p><?php echo htmlspecialchars($university['location']); ?></p>
                                <a href="/uniprojecthub/universities/university.php?id=<?php echo $university['id']; ?>" class="btn btn-outline" style="margin-top: 1rem;">View Projects</a>
                            </div>
                <?php
                        endwhile;
                    } else {
                        echo '<p>No universities found in the database.</p>';
                    }
                } catch (Exception $e) {
                    echo '<p>Error loading universities. Please try again later.</p>';
                }
                ?>
            </div>
            <div style="text-align: center; margin-top: 3rem;">
                <a href="/uniprojecthub/universities/universities.php" class="btn">View All Universities</a>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta">
        <div class="container">
            <h2>Ready to Showcase Your Projects?</h2>
            <p>Join thousands of students who are already collaborating and showcasing their work on UniProjectHub.</p>
            <div style="display: flex; justify-content: center; gap: 1rem; margin-top: 2rem;">
                <a href="/uniprojecthub/auth/register.php" class="btn" style="padding: 0.8rem 2rem;">Get Started</a>
                <a href="/uniprojecthub/about.php" class="btn btn-outline" style="padding: 0.8rem 2rem;">Learn More</a>
            </div>
        </div>
    </section>

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
            // Smooth scrolling for anchor links
            $('a[href*="#"]').on('click', function(e) {
                e.preventDefault();
                
                $('html, body').animate(
                    {
                        scrollTop: $($(this).attr('href')).offset().top,
                    },
                    500,
                    'linear'
                );
            });
            
            // Search functionality
            $('.search-bar button').click(function() {
                const searchTerm = $('.search-bar input').val().trim();
                if (searchTerm) {
                    window.location.href = `projects.php?search=${encodeURIComponent(searchTerm)}`;
                }
            });
            
            $('.search-bar input').keypress(function(e) {
                if (e.which === 13) {
                    $('.search-bar button').click();
                }
            });
            
            // Mobile menu toggle (would be implemented in a real scenario)
            // Animation for feature cards on scroll
            $(window).scroll(function() {
                $('.feature-card').each(function() {
                    const cardPosition = $(this).offset().top;
                    const scrollPosition = $(window).scrollTop() + $(window).height();
                    
                    if (scrollPosition > cardPosition) {
                        $(this).addClass('animated');
                    }
                });
            });
        });
    </script>
</body>
</html>