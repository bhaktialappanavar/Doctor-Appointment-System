<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCare - Professional Healthcare Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .hero-section { 
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); 
            color: white; 
            padding: 120px 0; 
            position: relative;
            overflow: hidden;
        }
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
            opacity: 0.3;
        }
        .hero-content { position: relative; z-index: 2; }
        .feature-card { 
            transition: all 0.4s ease; 
            border: none; 
            box-shadow: 0 8px 25px rgba(0,0,0,0.1); 
            border-radius: 1rem;
            overflow: hidden;
        }
        .feature-card:hover { 
            transform: translateY(-10px) scale(1.02); 
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        .specialty-card { 
            background: white; 
            border-radius: 1.5rem; 
            padding: 2.5rem; 
            text-align: center; 
            margin-bottom: 2rem; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .specialty-card:hover {
            transform: translateY(-8px);
            border-color: var(--primary-color);
            box-shadow: 0 15px 40px rgba(44, 90, 160, 0.2);
        }
        .stats-section {
            background: var(--gray-100);
            color: var(--gray-800);
        }
        .stat-item {
            text-align: center;
            padding: 2rem;
        }
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            display: block;
            margin-bottom: 0.5rem;
        }
        .testimonial-card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            position: relative;
        }
        .testimonial-card::before {
            content: '"';
            font-size: 4rem;
            color: var(--primary-color);
            position: absolute;
            top: -10px;
            left: 20px;
            font-family: serif;
        }
        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }
        .floating-icon {
            position: absolute;
            color: rgba(255,255,255,0.1);
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .cta-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            position: relative;
        }
        .pulse-btn {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(255, 255, 255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="index.php">
                <i class="fas fa-heartbeat me-2"></i>MediCare
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="#doctors">Doctors</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                </ul>
                <div class="d-flex">
                    <?php if (is_logged_in()): ?>
                        <a href="dashboard.php" class="btn btn-outline-primary me-2">Dashboard</a>
                        <a href="logout.php" class="btn btn-primary">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-primary me-2">Login</a>
                        <a href="register.php" class="btn btn-primary">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="floating-elements">
            <i class="fas fa-heartbeat floating-icon" style="top: 20%; left: 10%; font-size: 2rem; animation-delay: 0s;"></i>
            <i class="fas fa-stethoscope floating-icon" style="top: 60%; right: 15%; font-size: 1.5rem; animation-delay: 2s;"></i>
            <i class="fas fa-user-md floating-icon" style="top: 30%; right: 25%; font-size: 2.5rem; animation-delay: 4s;"></i>
            <i class="fas fa-pills floating-icon" style="bottom: 30%; left: 20%; font-size: 1.8rem; animation-delay: 1s;"></i>
        </div>
        <div class="container hero-content">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="mb-4">
                        <span class="badge bg-light text-primary px-3 py-2 rounded-pill mb-3">
                            <i class="fas fa-award me-2"></i>Trusted Healthcare Platform
                        </span>
                    </div>
                    <h1 class="display-3 fw-bold mb-4">Your Health, <span class="text-light">Our Priority</span></h1>
                    <p class="lead mb-4 opacity-90">Experience seamless healthcare management with our comprehensive platform. Book appointments with qualified doctors, manage your medical records, and take control of your healthcare journey with confidence.</p>
                    <div class="d-flex flex-wrap gap-3 mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-light me-2"></i>
                            <span>24/7 Online Booking</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-light me-2"></i>
                            <span>Secure Medical Records</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-light me-2"></i>
                            <span>Expert Doctors</span>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <a href="register.php" class="btn btn-light btn-lg px-4 py-3 pulse-btn">
                            <i class="fas fa-rocket me-2"></i>Get Started Free
                        </a>
                        <a href="#services" class="btn btn-outline-light btn-lg px-4 py-3">
                            <i class="fas fa-play me-2"></i>Learn More
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="position-relative">
                        <div class="bg-white rounded-3 p-4 shadow-lg">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="bg-primary bg-opacity-10 rounded-3 p-3 text-center">
                                        <i class="fas fa-calendar-check fa-2x text-primary mb-2"></i>
                                        <h6 class="text-primary mb-0">Easy Booking</h6>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-primary bg-opacity-10 rounded-3 p-3 text-center">
                                        <i class="fas fa-user-md fa-2x text-primary mb-2"></i>
                                        <h6 class="text-primary mb-0">Expert Doctors</h6>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-primary bg-opacity-10 rounded-3 p-3 text-center">
                                        <i class="fas fa-file-medical fa-2x text-primary mb-2"></i>
                                        <h6 class="text-primary mb-0">Digital Records</h6>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-primary bg-opacity-10 rounded-3 p-3 text-center">
                                        <i class="fas fa-shield-alt fa-2x text-primary mb-2"></i>
                                        <h6 class="text-primary mb-0">Secure & Safe</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">500+</span>
                        <p class="mb-0">Expert Doctors</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">10K+</span>
                        <p class="mb-0">Happy Patients</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">25+</span>
                        <p class="mb-0">Specialties</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-number">24/7</span>
                        <p class="mb-0">Support</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="services" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill mb-3">
                    <i class="fas fa-star me-2"></i>Our Services
                </span>
                <h2 class="display-5 fw-bold mb-3">Comprehensive Healthcare Solutions</h2>
                <p class="lead text-muted">Everything you need for modern healthcare management in one platform</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card card h-100 border-0">
                        <div class="card-body p-4 text-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                                <i class="fas fa-calendar-check fa-2x text-primary"></i>
                            </div>
                            <h5 class="card-title mb-3">Smart Appointment Booking</h5>
                            <p class="card-text text-muted">Schedule appointments with your preferred doctors instantly. Real-time availability, automated reminders, and easy rescheduling.</p>
                            <a href="register.php" class="btn btn-primary">Book Now</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card card h-100 border-0">
                        <div class="card-body p-4 text-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                                <i class="fas fa-file-medical fa-2x text-primary"></i>
                            </div>
                            <h5 class="card-title mb-3">Digital Medical Records</h5>
                            <p class="card-text text-muted">Secure, encrypted storage of your complete medical history. Access your records anytime, anywhere with full privacy protection.</p>
                            <a href="register.php" class="btn btn-primary">Learn More</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card card h-100 border-0">
                        <div class="card-body p-4 text-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                                <i class="fas fa-search fa-2x text-primary"></i>
                            </div>
                            <h5 class="card-title mb-3">Find Specialists</h5>
                            <p class="card-text text-muted">Advanced search and filtering to find the right specialist for your needs. Compare doctors, read reviews, and check availability.</p>
                            <a href="register.php" class="btn btn-primary">Find Doctors</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card card h-100 border-0">
                        <div class="card-body p-4 text-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                                <i class="fas fa-prescription-bottle-alt fa-2x text-primary"></i>
                            </div>
                            <h5 class="card-title mb-3">Prescription Management</h5>
                            <p class="card-text text-muted">Digital prescriptions with automatic refill reminders. Download, print, or share prescriptions with pharmacies directly.</p>
                            <a href="register.php" class="btn btn-primary">Get Started</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card card h-100 border-0">
                        <div class="card-body p-4 text-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                                <i class="fas fa-heartbeat fa-2x text-primary"></i>
                            </div>
                            <h5 class="card-title mb-3">Health Monitoring</h5>
                            <p class="card-text text-muted">Track your health metrics, upload lab reports, and monitor your wellness journey with comprehensive health analytics.</p>
                            <a href="register.php" class="btn btn-primary">Monitor Health</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card card h-100 border-0">
                        <div class="card-body p-4 text-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                                <i class="fas fa-shield-alt fa-2x text-primary"></i>
                            </div>
                            <h5 class="card-title mb-3">Secure & Private</h5>
                            <p class="card-text text-muted">Bank-level security with end-to-end encryption. Your health data is protected with the highest security standards.</p>
                            <a href="register.php" class="btn btn-primary">Security Info</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Doctors Section -->
    <section id="doctors" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Our Expert Doctors</h2>
                <p class="text-muted">Meet our qualified healthcare professionals</p>
            </div>
            <div class="row">
                <?php
                $stmt = $pdo->prepare("SELECT * FROM specialties LIMIT 6");
                $stmt->execute();
                $specialties = $stmt->fetchAll();
                foreach ($specialties as $specialty):
                ?>
                <div class="col-md-4 mb-4">
                    <div class="specialty-card">
                        <i class="<?= $specialty['icon'] ?> fa-3x text-primary mb-3"></i>
                        <h5><?= sanitize($specialty['name']) ?></h5>
                        <p class="text-muted"><?= sanitize($specialty['description']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="fw-bold mb-4">About MediCare</h2>
                    <p class="text-muted mb-4">MediCare is a comprehensive healthcare management platform designed to simplify your medical journey. We connect patients with qualified healthcare professionals through our secure, user-friendly platform.</p>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span>Secure Platform</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span>Expert Doctors</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span>24/7 Support</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span>Digital Records</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="bg-primary bg-opacity-10 rounded-3 p-4">
                        <h5 class="text-primary mb-3">Our Mission</h5>
                        <p class="text-muted">To provide accessible, efficient, and secure healthcare management solutions that empower patients and healthcare providers to deliver the best possible care.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill mb-3">
                    <i class="fas fa-heart me-2"></i>Patient Stories
                </span>
                <h2 class="display-5 fw-bold mb-3">What Our Patients Say</h2>
                <p class="lead text-muted">Real experiences from real people</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-user fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Sarah Johnson</h6>
                                <small class="text-muted">Patient</small>
                            </div>
                        </div>
                        <p class="text-muted">MediCare made booking appointments so easy! I love how I can access all my medical records in one place. The doctors are professional and caring.</p>
                        <div class="text-warning">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-user fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Michael Chen</h6>
                                <small class="text-muted">Patient</small>
                            </div>
                        </div>
                        <p class="text-muted">The platform is incredibly user-friendly. I can easily find specialists, book appointments, and even get prescription refills. Highly recommended!</p>
                        <div class="text-warning">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-user fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Priya Sharma</h6>
                                <small class="text-muted">Patient</small>
                            </div>
                        </div>
                        <p class="text-muted">As a busy professional, MediCare saves me so much time. The 24/7 booking system and digital records make healthcare management effortless.</p>
                        <div class="text-warning">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section py-5 text-white">
        <div class="container text-center">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill mb-3">
                        <i class="fas fa-rocket me-2"></i>Join Now
                    </span>
                    <h2 class="display-4 fw-bold mb-4">Ready to Transform Your Healthcare Experience?</h2>
                    <p class="lead mb-4 opacity-90">Join thousands of patients who trust MediCare for their healthcare needs. Start your journey to better health today.</p>
                    <div class="d-flex flex-wrap justify-content-center gap-3 mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>Free Registration</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>Instant Access</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>No Hidden Fees</span>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="register.php" class="btn btn-light btn-lg px-5 py-3">
                            <i class="fas fa-user-plus me-2"></i>Register Now
                        </a>
                        <a href="login.php" class="btn btn-outline-light btn-lg px-5 py-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Already a Member?
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-heartbeat me-2"></i>MediCare</h5>
                    <p class="text-muted">Professional healthcare management system.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted">&copy; 2024 MediCare. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>