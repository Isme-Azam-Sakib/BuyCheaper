<?php
include '../config/database.php';
include '../includes/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - BuyCheaper</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@100..900&family=Poppins:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .about-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)),
                        url('../assets/images/about-hero.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 20px;
            text-align: center;
            border-radius: 10px;
            margin-bottom: 40px;
        }

        .hero-section h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }

        .hero-section p {
            font-size: 1.2em;
            max-width: 800px;
            margin: 0 auto;
        }

        .mission-section {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-bottom: 60px;
        }

        .mission-card {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .mission-card:hover {
            transform: translateY(-5px);
        }

        .mission-card i {
            font-size: 2.5em;
            color: #007bff;
            margin-bottom: 20px;
        }

        .mission-card h3 {
            margin-bottom: 15px;
            color: #333;
        }

        .mission-card p {
            color: #666;
            line-height: 1.6;
        }

        .story-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 60px;
            align-items: center;
        }

        .story-content h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .story-content p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 15px;
        }

        .story-image {
            border-radius: 10px;
            overflow: hidden;
        }

        .story-image img {
            width: 100%;
            height: auto;
            display: block;
        }

        .team-section {
            padding: 80px 0;
        }

        .team-section h2 {
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 60px;
            color: #2d3436;
            position: relative;
        }

        .team-section h2:after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: #007bff;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .team-member {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .team-member:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .member-image {
            position: relative;
            height: 300px;
            overflow: hidden;
        }

        .member-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .member-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0) 0%, rgba(0,0,0,0.7) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .team-member:hover .member-overlay {
            opacity: 1;
        }

        .team-member:hover .member-image img {
            transform: scale(1.1);
        }

        .member-info {
            padding: 25px;
        }

        .member-info h3 {
            font-size: 1.4em;
            color: #2d3436;
            margin: 0 0 5px 0;
        }

        .position {
            display: block;
            color: #007bff;
            font-weight: 500;
            margin-bottom: 15px;
            font-size: 1.1em;
        }

        .member-description {
            color: #636e72;
            font-size: 0.95em;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .social-links {
            display: flex;
            gap: 15px;
        }

        .social-links a {
            color: #2d3436;
            font-size: 1.2em;
            transition: color 0.3s ease;
        }

        .social-links a:hover {
            color: #007bff;
        }

        @media (max-width: 1200px) {
            .team-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .team-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .team-section h2 {
                font-size: 2em;
            }

            .member-image {
                height: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="about-container">
        <section class="hero-section">
            <h1>About BuyCheaper</h1>
            <p>Your trusted companion in finding the best PC component deals across Bangladesh</p>
        </section>

        <section class="mission-section">
            <div class="mission-card">
                <i class="fas fa-bullseye"></i>
                <h3>Our Mission</h3>
                <p>To simplify PC component shopping by providing real-time price comparisons and helping users make informed decisions.</p>
            </div>
            <div class="mission-card">
                <i class="fas fa-chart-line"></i>
                <h3>Our Vision</h3>
                <p>To become Bangladesh's leading platform for PC component price comparison and build recommendations.</p>
            </div>
            <div class="mission-card">
                <i class="fas fa-handshake"></i>
                <h3>Our Values</h3>
                <p>Transparency, accuracy, and user-centric service in everything we do.</p>
            </div>
        </section>

        <section class="story-section">
            <div class="story-content">
                <h2>Our Story</h2>
                <p>BuyCheaper was born from a simple observation: finding the best prices for PC components in Bangladesh was too time-consuming. Our founder, a PC enthusiast, spent countless hours comparing prices across different stores before making purchases.</p>
                <p>This experience led to the creation of BuyCheaper in 2024. We started with a simple goal: to create a platform that automatically tracks prices across major PC component retailers in Bangladesh and presents them in an easy-to-compare format.</p>
                <p>Today, we help thousands of users make informed decisions about their PC component purchases, saving both time and money.</p>
            </div>
            <div class="story-image">
                <img src="../assets/images/about-story.jpg" alt="BuyCheaper Story">
            </div>
        </section>

        <section class="team-section">
            <h2>Meet Our Team</h2>
            <div class="team-grid">
                <div class="team-member">
                    <div class="member-image">
                        <img src="../assets/sakib.png" alt="Team Member">
                        <div class="member-overlay"></div>
                    </div>
                    <div class="member-info">
                        <h3>Sakib</h3>
                        <span class="position">Lead Developer</span>
                        <div class="member-description">
                            <p>Worked on developing the project from scratch. Also developed the primary prototype of the first increment.</p>
                        </div>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-github"></i></a>
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                        </div>
                    </div>
                </div>

                <div class="team-member">
                    <div class="member-image">
                        <img src="../assets/efti.png" alt="Team Member">
                        <div class="member-overlay"></div>
                    </div>
                    <div class="member-info">
                        <h3>Efti</h3>
                        <span class="position">Secondary Designer & Developer</span>
                        <div class="member-description">
                            <p>Coordinated the team to keep everything on schedule. Also monitored progress, resolved any issues that arose, and made sure our project aligns with the overall goals and requirements.</p>
                        </div>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-github"></i></a>
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                        </div>
                    </div>
                </div>

                <div class="team-member">
                    <div class="member-image">
                        <img src="../assets/shohayel.png" alt="Team Member">
                        <div class="member-overlay"></div>
                    </div>
                    <div class="member-info">
                        <h3>Shohayel Ahmed Dip</h3>
                        <span class="position">Test Engineer</span>
                        <div class="member-description">
                            <p>Worked on developing & executing test plans for the prototype, conducted manual testing, identified bugs.</p>
                        </div>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-github"></i></a>
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                        </div>
                    </div>
                </div>

                <div class="team-member">
                    <div class="member-image">
                        <img src="../assets/tasabil.png" alt="Team Member">
                        <div class="member-overlay"></div>
                    </div>
                    <div class="member-info">
                        <h3>Tasabil Islam Mojumder</h3>
                        <span class="position">UI/UX Designer</span>
                        <div class="member-description">
                            <p>Designed the user interface, focusing on creating an intuitive and visually appealing layout. Collaborated with the development team to ensure a seamless and user-friendly experience.</p>
                        </div>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-github"></i></a>
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                        </div>
                    </div>
                </div>

                <div class="team-member">
                    <div class="member-image">
                        <img src="../assets/imam.png" alt="Team Member">
                        <div class="member-overlay"></div>
                    </div>
                    <div class="member-info">
                        <h3>Imam Hossain Rakib</h3>
                        <span class="position">Analyst</span>
                        <div class="member-description">
                            <p>Evaluated the current system, collaborated with the team to ensure smooth integration, documented key components and validated changes through testing. Key role is to ensures a user-focused, cohesive approach to project development.</p>
                        </div>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-github"></i></a>
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html> 