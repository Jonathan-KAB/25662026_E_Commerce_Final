<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register - SeamLink</title>
    <link rel="stylesheet" href="../css/app.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            font-family: 'Elms Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register-wrapper {
            width: 100%;
            max-width: 580px;
            margin: 0 auto;
        }

        .register-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .register-header {
            background: linear-gradient(135deg, #198754 0%, #157347 100%);
            padding: 20px;
            text-align: center;
            color: white;
        }

        .register-header h1 {
            font-size: 22px;
            font-weight: 800;
            margin: 0 0 4px 0;
            color: white;
        }

        .register-header p {
            margin: 0;
            opacity: 0.95;
            font-size: 13px;
        }

        .register-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 12px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 6px;
            color: #2d3748;
        }

        .form-group label i {
            margin-right: 6px;
            color: #198754;
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            font-size: 14px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            transition: all 0.3s;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: #198754;
            box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.1);
        }

        .form-select {
            width: 100%;
            padding: 8px 12px;
            font-size: 14px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            transition: all 0.3s;
            font-family: inherit;
            background: white;
            cursor: pointer;
        }

        .form-select:focus {
            outline: none;
            border-color: #198754;
            box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.1);
        }

        .role-selection {
            display: flex;
            gap: 12px;
            margin-top: 6px;
        }

        .role-option {
            flex: 1;
            position: relative;
        }

        .role-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .role-label {
            display: block;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            font-size: 13px;
        }

        .role-label i {
            display: block;
            font-size: 20px;
            margin-bottom: 6px;
            color: #198754;
        }

        .role-option input[type="radio"]:checked + .role-label {
            border-color: #198754;
            background: rgba(25, 135, 84, 0.05);
        }

        .role-label:hover {
            border-color: #198754;
        }

        .btn-register {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #198754 0%, #157347 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 6px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(25, 135, 84, 0.3);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .register-footer {
            padding: 16px;
            background: #f7fafc;
            text-align: center;
            font-size: 13px;
            color: #4a5568;
        }

        .register-footer a {
            color: #198754;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s;
        }

        .register-footer a:hover {
            color: #157347;
            text-decoration: underline;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 8px;
        }

        .brand-badge i {
            font-size: 18px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        @media (max-width: 576px) {
            body {
                padding: 10px;
            }

            .register-header {
                padding: 20px 15px;
            }

            .register-body {
                padding: 20px 15px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .role-selection {
                flex-direction: column;
            }

            .register-footer {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="register-wrapper">
        <div class="register-card">
            <div class="register-header">
                <div class="brand-badge">
                    <i class="fas fa-cut"></i>
                    <h1>SeamLink</h1>
                </div>
                <p>Join Ghana's premier fashion marketplace</p>
            </div>
            <div class="register-body">
                <form method="POST" action="../actions/register_user_action.php" id="register-form">
                    <div class="form-group">
                        <label for="name">
                            <i class="fas fa-user"></i> Full Name
                        </label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i> Email Address
                        </label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Create a strong password" required>
                            <button type="button" class="password-toggle" data-target="#password" aria-pressed="false" aria-label="Show password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i> Confirm Password
                        </label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                            <button type="button" class="password-toggle" data-target="#confirm_password" aria-pressed="false" aria-label="Show password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone_number">
                                <i class="fas fa-phone"></i> Phone Number
                            </label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="+233 000 000 000" required>
                        </div>

                        <div class="form-group">
                            <label for="country">
                                <i class="fas fa-globe"></i> Country
                            </label>
                            <select class="form-select" id="country" name="country" required>
                                <option value="" disabled selected>Select country</option>
                                <option value="Ghana">Ghana</option>
                                <option value="Nigeria">Nigeria</option>
                                <option value="Kenya">Kenya</option>
                                <option value="South Africa">South Africa</option>
                                <option value="Egypt">Egypt</option>
                                <option value="Morocco">Morocco</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="city">
                            <i class="fas fa-map-marker-alt"></i> City
                        </label>
                        <input type="text" class="form-control" id="city" name="city" placeholder="Enter your city" required>
                    </div>

                    <div class="form-group">
                        <label>
                            <i class="fas fa-user-tag"></i> Register As
                        </label>
                        <div class="role-selection">
                            <div class="role-option">
                                <input type="radio" name="role" id="customer" value="1" checked>
                                <label for="customer" class="role-label">
                                    <i class="fas fa-shopping-bag"></i>
                                    Customer
                                </label>
                            </div>
                            <div class="role-option">
                                <input type="radio" name="role" id="fabric_seller" value="3">
                                <label for="fabric_seller" class="role-label">
                                    <i class="fas fa-box"></i>
                                    Fabric Seller
                                </label>
                            </div>
                            <div class="role-option">
                                <input type="radio" name="role" id="service_provider" value="4">
                                <label for="service_provider" class="role-label">
                                    <i class="fas fa-scissors"></i>
                                    Service Provider
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Service Type Selection (only for service providers) -->
                    <div class="form-group" id="service-type-group" style="display: none;">
                        <label for="service_type">
                            <i class="fas fa-cut"></i> Service Specialty
                        </label>
                        <select class="form-select" id="service_type" name="service_type">
                            <option value="general">General Service Provider</option>
                            <option value="tailor">✂️ Tailor</option>
                            <option value="seamstress">🪡 Seamstress</option>
                        </select>
                        <small style="color: #718096; display: block; margin-top: 4px; font-size: 12px;">
                            Choose your area of expertise in tailoring and alterations
                        </small>
                    </div>

                    <button type="submit" class="btn-register">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>
            </div>
            <div class="register-footer">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/ui.js"></script>
    <script>
        // Show/hide service type based on role selection
        $(document).ready(function() {
            $('input[name="role"]').on('change', function() {
                if ($(this).val() === '4') { // Service Provider role
                    $('#service-type-group').slideDown(300);
                    $('#service_type').prop('required', true);
                } else {
                    $('#service-type-group').slideUp(300);
                    $('#service_type').prop('required', false);
                    $('#service_type').val('general');
                }
            });
        });
    </script>
    <script src="../js/register.js"></script>
</body>

</html>