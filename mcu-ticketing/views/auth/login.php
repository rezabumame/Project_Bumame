<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bumame Ticketing</title>
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bumame-blue: #204EAB;
            --bumame-light-blue: #4FA3D1;
            --bumame-bg: #F0F8FF;
        }
        body { 
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            display: flex; 
            align-items: center; 
            justify-content: center; 
            height: 100vh; 
            font-family: 'Poppins', sans-serif;
            overflow: hidden;
            position: relative;
        }
        
        /* Abstract Medical Background Shapes */
        .bg-shape {
            position: absolute;
            opacity: 0.1;
            z-index: -1;
        }
        .shape-1 {
            top: -10%;
            left: -10%;
            width: 500px;
            height: 500px;
            background: var(--bumame-blue);
            border-radius: 50%;
            filter: blur(80px);
        }
        .shape-2 {
            bottom: -10%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: var(--bumame-light-blue);
            border-radius: 50%;
            filter: blur(60px);
        }

        .login-card { 
            width: 90%; 
            max-width: 400px; 
            border: none; 
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(32, 78, 171, 0.15); 
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .login-card:hover {
            transform: translateY(-5px);
        }
        
        .card-header-custom {
            background: white;
            padding: 30px 30px 10px 30px;
            padding-top: 40px;
            text-align: center;
            color: var(--bumame-blue);
            border-bottom: none;
        }
        
        .brand-icon {
            margin-bottom: 5px;
            color: var(--bumame-blue);
            line-height: 1;
        }

        .brand-icon img {
            max-width: 180px;
            max-height: 60px;
            width: auto;
            height: auto;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }

        .form-label {
            font-weight: 500;
            color: #212529;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #dee2e6;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: var(--bumame-blue);
            box-shadow: 0 0 0 0.2rem rgba(32, 78, 171, 0.15);
        }

        .input-group-text {
            background-color: white;
            border-left: none;
            border-radius: 0 10px 10px 0;
            border-color: #dee2e6;
        }

        /* Password input specific to merge with icon */
        #password {
            border-right: none;
            border-radius: 10px 0 0 10px;
        }
        
        .password-toggle {
            cursor: pointer;
            color: #6c757d;
        }
        
        .card-body-custom {
            padding-top: 20px !important;
        }
        
        .btn-primary-custom { 
            background: var(--bumame-blue); 
            border: none; 
            padding: 12px;
            font-weight: 600;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .btn-primary-custom:hover { 
            background: #1a3e8a; 
            transform: none;
            box-shadow: none;
        }
    </style>
</head>
<body>
    <!-- Background Shapes -->
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>

    <div class="card login-card">
        <div class="card-header-custom">
            <div class="brand-icon">
                <img src="assets/images/logo.png?v=<?php echo time(); ?>" alt="Bumame Logo">
            </div>
            <p class="mb-0 opacity-75 small letter-spacing-1">Bumame Ticketing System</p>
        </div>
        <div class="card-body card-body-custom p-4 p-md-5">
            <?php if(isset($error_message) && !empty($error_message)): ?>
                <div class="alert alert-danger d-flex align-items-center shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div><?php echo $error_message; ?></div>
                </div>
            <?php endif; ?>
            
            <form action="index.php?page=auth_login" method="POST">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="text" name="username" class="form-control" placeholder="Enter your email" required>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                        <span class="input-group-text password-toggle" onclick="togglePassword()">
                            <i class="far fa-eye" id="toggleIcon"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-primary-custom w-100 text-white">
                    Sign In
                </button>
            </form>
            
            <div class="footer-text mt-4 text-center text-muted small">
                &copy; <?php echo date('Y'); ?> Bumame Cahaya Medika. All rights reserved.
            </div>

            <script>
                function togglePassword() {
                    const passwordInput = document.getElementById('password');
                    const toggleIcon = document.getElementById('toggleIcon');
                    
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        toggleIcon.classList.remove('fa-eye');
                        toggleIcon.classList.add('fa-eye-slash');
                    } else {
                        passwordInput.type = 'password';
                        toggleIcon.classList.remove('fa-eye-slash');
                        toggleIcon.classList.add('fa-eye');
                    }
                }
            </script>
        </div>
    </div>
</body>
</html>