<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NCF Repository - Corporate Event Management</title>
    <link rel="stylesheet" href="assets/css/corporate-style.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
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
            background: radial-gradient(ellipse at center, rgba(184, 134, 11, 0.05) 0%, transparent 70%);
            pointer-events: none;
        }
        
        .hero-content {
            background: var(--light-bg);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 90%;
            border: 1px solid var(--border-light);
            position: relative;
            z-index: 1;
        }
        
        .hero-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-gold), var(--light-gold), var(--primary-gold));
            border-radius: 20px 20px 0 0;
        }
        
        .hero-logo {
            font-size: 3rem;
            font-weight: 700;
            background: linear-gradient(45deg, var(--primary-gold), var(--light-gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            text-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .hero-subtitle {
            color: var(--text-light);
            margin-bottom: 2rem;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .hero-buttons .btn {
            margin: 0;
            min-width: 160px;
        }
        
        .feature-info {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-light);
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .feature-item {
            text-align: center;
            padding: 1rem;
        }
        
        .feature-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .feature-title {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }
        
        .feature-desc {
            font-size: 0.9rem;
            color: var(--text-light);
        }
        
        @media (max-width: 768px) {
            .hero-content {
                padding: 2rem;
            }
            
            .hero-logo {
                font-size: 2.5rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .hero-buttons .btn {
                width: 100%;
                max-width: 280px;
            }
        }
    </style>
</head>
<body>
    <div class="hero-section">
        <div class="hero-content fade-in">
            <div class="hero-logo">
                <img src="assets/images/logo.png" alt="Ministry of Finance, Planning and Economic Development" class="logo-image" style="height: 80px; width: 80px; margin-right: 1rem; border-radius: 50%; object-fit: cover;">
                NCF Repository
            </div>
            <p class="hero-subtitle">Professional Event File Management System<br>Streamlined, Secure, and Accessible</p>
            
            <div class="hero-buttons">
                <a href="admin/" class="btn btn-primary">
                    🔐 Admin Portal
                </a>
                <a href="client/" class="btn btn-secondary">
                    📱 Client Access
                </a>
            </div>
            
            <div class="feature-info">
                <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 1rem;">
                    Comprehensive file management solution for corporate events
                </p>
                
                <div class="feature-grid">
                    <div class="feature-item">
                        <div class="feature-icon">🔧</div>
                        <div class="feature-title">Admin Control</div>
                        <div class="feature-desc">Upload & manage files with ease</div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">📱</div>
                        <div class="feature-title">QR Access</div>
                        <div class="feature-desc">Generate & share QR codes</div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">🔒</div>
                        <div class="feature-title">Secure</div>
                        <div class="feature-desc">Protected file storage</div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">📊</div>
                        <div class="feature-title">Analytics</div>
                        <div class="feature-desc">Track usage & statistics</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer style="background: var(--dark-bg); color: rgba(255,255,255,0.8); text-align: center; padding: 3rem 2rem; margin-top: 4rem;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <div class="footer-logo">
                <img src="assets/images/logo.png" alt="Ministry of Finance, Planning and Economic Development" class="logo-image">
                <span style="color: var(--accent-gold); font-weight: 700; font-size: 1.5rem;">NCF Repository</span>
            </div>
            <p style="margin: 1.5rem 0; font-size: 1.1rem;">
                <strong>Ministry of Finance, Planning and Economic Development</strong>
            </p>
            <p style="margin-bottom: 1.5rem; color: rgba(255,255,255,0.7);">
                Professional Event File Management System<br>
                Republic of Uganda
            </p>
            <div style="border-top: 1px solid rgba(255,255,255,0.2); padding-top: 1.5rem;">
                <p style="font-size: 0.9rem; opacity: 0.6;">
                    © <?php echo date('Y'); ?> Republic of Uganda • All Rights Reserved
                </p>
            </div>
        </div>
    </footer>
    
    <script>
        // Add smooth animations
        document.addEventListener('DOMContentLoaded', function() {
            const heroContent = document.querySelector('.hero-content');
            setTimeout(() => {
                heroContent.style.opacity = '1';
                heroContent.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>