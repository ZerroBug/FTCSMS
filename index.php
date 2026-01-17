<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login — FTCSMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
    /* ---------------- ROOT & BODY ---------------- */
    :root {
        --primary: #412461;
        --primary-dark: #2d1448;
        --accent: #7c5cff;
        --bg: #f2f4f8;
        --card-bg: #ffffff;
        --soft: #f6f7fb;
        --text-dark: #1f2937;
        --text-muted: #6b7280;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #eef1f7, #e9ecf3);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 14px;
        /* ↓ reduced */
        color: var(--text-dark);
    }

    /* ---------------- WRAPPER ---------------- */
    .login-wrapper {
        background: var(--card-bg);
        border-radius: 20px;
        /* ↓ slightly */
        box-shadow:
            0 18px 40px rgba(0, 0, 0, 0.08),
            0 6px 16px rgba(0, 0, 0, 0.05);
        width: 100%;
        max-width: 760px;
        /* ↓ reduced */
        overflow: hidden;
        display: flex;
        flex-wrap: wrap;
    }

    /* ---------------- LEFT PANEL ---------------- */
    .login-left {
        background: linear-gradient(160deg, #f7f8fd, #eef1f9);
        padding: 40px 38px;
        /* ↓ reduced */
        flex: 1;
    }

    .login-left img {
        height: 60px;
        /* ↓ reduced */
    }

    .login-left h2 {
        margin-top: 28px;
        /* ↓ reduced */
        font-weight: 700;
        font-size: 26px;
        /* ↓ reduced */
    }

    .login-left p {
        margin-top: 10px;
        font-size: 14px;
        line-height: 1.6;
        color: var(--text-muted);
    }

    /* ---------------- RIGHT PANEL ---------------- */
    .login-right {
        padding: 40px 38px;
        text-align: left !important;
        /* ↓ reduced */
        flex: 1;
    }

    .login-right h4 {
        text-align: center;
        font-weight: 700;
        letter-spacing: 0.8px;
        margin-bottom: 22px;
        /* ↓ reduced */
        font-size: 18px;
    }

    /* ---------------- TABS ---------------- */
    .nav-tabs {
        border-bottom: none;
        justify-content: center;
        margin-bottom: 22px;
        /* ↓ reduced */
    }



    /* ---------------- TABS ---------------- */
    .nav-tabs {
        border-bottom: none;
        justify-content: center;
        margin-bottom: 22px;
    }

    /* Base tab */
    .nav-tabs .nav-link {
        opacity: 0.55;
        background: var(--soft);
        color: var(--text-dark);
        position: relative;
        border-radius: 0;
        padding: 8px 22px;
        border: 1px solid transparent;
        transition: all 0.25s ease;
    }

    /* Hover (inactive only) */
    .nav-tabs .nav-link:not(.active):hover {
        opacity: 0.8;
        background: #eef1f9;
    }

    /* ACTIVE TAB — PROFESSIONAL PURPLE */
    .nav-tabs .nav-link.active {
        opacity: 1;
        background: var(--primary);
        /* 🔥 solid purple */
        color: #ffffff;
        font-weight: 600;
        border: 1px solid var(--primary-dark);
        box-shadow: 0 8px 18px rgba(65, 36, 97, 0.35);
    }

    /* Icon color inside active tab */
    .nav-tabs .nav-link.active i {
        color: #ffffff;
    }

    /* Optional: subtle indicator (very clean) */
    .nav-tabs .nav-link.active::after {
        content: "";
        position: absolute;
        bottom: -6px;
        left: 50%;
        transform: translateX(-50%);
        width: 24px;
        height: 3px;
        border-radius: 10px;
        background: var(--primary);
    }


    /* ---------------- FORM ---------------- */
    .form-label {
        font-size: 12.5px;
        /* ↓ reduced */
        font-weight: 600;
        margin-bottom: 5px;
    }

    .form-control {
        border-radius: 12px;
        padding: 11px 13px;
        /* ↓ reduced */
        background: var(--soft);
        border: 1px solid #d6dbea;
        font-size: 13.5px;
    }

    .form-control:focus {
        background: #fff;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(65, 36, 97, 0.12);
    }

    /* ---------------- INPUT GROUP ---------------- */
    .input-group-text {
        border-radius: 12px 0 0 12px;
        padding: 0 12px;
    }

    /* ---------------- CHECKBOX ---------------- */
    .form-check-label {
        font-size: 13px;
    }

    /* ---------------- BUTTON ---------------- */
    .btn-login {
        background: linear-gradient(135deg, var(--primary), var(--accent));
        color: #fff;
        padding: 12px;
        /* ↓ reduced */
        border-radius: 14px;
        font-weight: 600;
        border: none;
        width: 100%;
        margin-top: 10px;
        transition: all 0.35s ease;
    }

    .btn-login:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 26px rgba(65, 36, 97, 0.45);
    }

    /* ---------------- FORGOT LINK ---------------- */
    .forgot-link {
        display: block;
        text-align: center;
        margin-top: 14px;
        /* ↓ reduced */
        font-size: 13px;
        font-weight: 500;
    }

    /* ---------------- RESPONSIVE ---------------- */
    @media (max-width: 768px) {
        .login-wrapper {
            flex-direction: column;
        }

        .login-left,
        .login-right {
            padding: 34px 26px;
            text-align: center;
        }

        .login-left p {
            display: none;
        }
    }

    /* Make alerts fully responsive inside the card */
    .login-right .alert {
        word-wrap: break-word;
        /* Break long words */
        white-space: normal;
        /* Allow wrapping */
        font-size: 13px;
        /* Slightly smaller font to fit */
        padding: 10px 12px;
        /* Adjust padding */
        margin-bottom: 15px;
        /* Space below */
        overflow-wrap: break-word;
        /* Extra safety */
    }


    /* Tabs 2 columns on mobile */
    @media (max-width: 576px) {
        .nav-tabs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }

        .nav-tabs .nav-link {
            padding: 10px 10px;
        }
    }

    /* Force form labels to the left on mobile */
    @media (max-width: 768px) {
        .login-right .form-label {
            text-align: left !important;
        }
    }

    /* ---------------- FLOATING BUBBLES ---------------- */
    .bubbles {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: 0;
        /* behind login wrapper */
    }

    .bubbles div {
        position: absolute;
        bottom: -100px;
        border-radius: 50%;
        opacity: 0.5;
        animation: float 20s linear infinite;
    }

    /* Random sizes, positions, colors, durations, and delays */
    .bubbles div:nth-child(1) {
        width: 25px;
        height: 25px;
        left: 5%;
        background: rgba(124, 92, 255, 0.3);
        animation-duration: 12s;
        animation-delay: 0s;
    }

    .bubbles div:nth-child(2) {
        width: 40px;
        height: 40px;
        left: 15%;
        background: rgba(65, 36, 97, 0.25);
        animation-duration: 18s;
        animation-delay: 2s;
    }

    .bubbles div:nth-child(3) {
        width: 30px;
        height: 30px;
        left: 25%;
        background: rgba(124, 92, 255, 0.4);
        animation-duration: 14s;
        animation-delay: 1s;
    }

    .bubbles div:nth-child(4) {
        width: 50px;
        height: 50px;
        left: 35%;
        background: rgba(65, 36, 97, 0.2);
        animation-duration: 20s;
        animation-delay: 3s;
    }

    .bubbles div:nth-child(5) {
        width: 20px;
        height: 20px;
        left: 45%;
        background: rgba(124, 92, 255, 0.35);
        animation-duration: 10s;
        animation-delay: 0.5s;
    }

    .bubbles div:nth-child(6) {
        width: 35px;
        height: 35px;
        left: 55%;
        background: rgba(65, 36, 97, 0.3);
        animation-duration: 16s;
        animation-delay: 2.5s;
    }

    .bubbles div:nth-child(7) {
        width: 28px;
        height: 28px;
        left: 60%;
        background: rgba(124, 92, 255, 0.25);
        animation-duration: 15s;
        animation-delay: 1.5s;
    }

    .bubbles div:nth-child(8) {
        width: 45px;
        height: 45px;
        left: 70%;
        background: rgba(65, 36, 97, 0.3);
        animation-duration: 19s;
        animation-delay: 4s;
    }

    .bubbles div:nth-child(9) {
        width: 22px;
        height: 22px;
        left: 75%;
        background: rgba(124, 92, 255, 0.4);
        animation-duration: 13s;
        animation-delay: 3s;
    }

    .bubbles div:nth-child(10) {
        width: 40px;
        height: 40px;
        left: 80%;
        background: rgba(65, 36, 97, 0.2);
        animation-duration: 17s;
        animation-delay: 2s;
    }

    .bubbles div:nth-child(11) {
        width: 25px;
        height: 25px;
        left: 10%;
        background: rgba(124, 92, 255, 0.3);
        animation-duration: 12s;
        animation-delay: 1s;
    }

    .bubbles div:nth-child(12) {
        width: 30px;
        height: 30px;
        left: 30%;
        background: rgba(65, 36, 97, 0.25);
        animation-duration: 14s;
        animation-delay: 2s;
    }

    .bubbles div:nth-child(13) {
        width: 35px;
        height: 35px;
        left: 50%;
        background: rgba(124, 92, 255, 0.3);
        animation-duration: 18s;
        animation-delay: 0.5s;
    }

    .bubbles div:nth-child(14) {
        width: 20px;
        height: 20px;
        left: 65%;
        background: rgba(65, 36, 97, 0.2);
        animation-duration: 16s;
        animation-delay: 3.5s;
    }

    .bubbles div:nth-child(15) {
        width: 45px;
        height: 45px;
        left: 85%;
        background: rgba(124, 92, 255, 0.4);
        animation-duration: 20s;
        animation-delay: 1s;
    }

    /* Floating Animation */
    @keyframes float {
        0% {
            transform: translateY(0) rotate(0deg);
            opacity: 0.5;
        }

        50% {
            opacity: 0.3;
        }

        100% {
            transform: translateY(-110vh) rotate(360deg);
            opacity: 0;
        }
    }

    /* Ensure login card stays above bubbles */
    .login-wrapper {
        position: relative;
        z-index: 1;
    }
    </style>

</head>

<body>
    <!-- Floating Bubbles -->
    <div class="bubbles">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
    </div>

    <div class="login-wrapper">

        <!-- LEFT -->
        <div class="login-left">
            <img src="assets/images/logo.png" alt="ftcsms Logo">
            <h2>Welcome Back</h2>
            <p>Sign in to continue to <strong>FTCSMS</strong>.</p>
            <p>
                Manage teaching activities, assessments, academic records, and institutional resources
                securely through the FTCSMS platform.
            </p>
        </div>

        <!-- RIGHT -->
        <div class="login-right">

            <h4>LOGIN </h4>

            <!-- Alerts -->
            <?php
        if (isset($_SESSION['alert'])) {
            echo $_SESSION['alert'];
            unset($_SESSION['alert']);
        }
        ?>

            <!-- Tabs -->
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#teacher">
                        Teacher
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#admin">
                        Admin
                    </button>
                </li>
            </ul>

            <div class="tab-content">

                <!-- Teacher Login -->
                <div class="tab-pane fade show active" id="teacher">
                    <form action="handlers/process_teacher_login.php" method="POST">

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" name="email" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" name="password" class="form-control password-field" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="remember">
                            <label class="form-check-label">Remember me</label>
                        </div>

                        <button class="btn-login">
                            <i class="fas fa-right-to-bracket me-2"></i> Sign in as Teacher
                        </button>

                        <a href="forgot_password.php?type=teacher" class="forgot-link">
                            Forgot password?
                        </a>

                    </form>
                </div>

                <!-- Admin Login -->
                <div class="tab-pane fade" id="admin">
                    <form action="handlers/process_user_login.php" method="POST">

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" name="password" class="form-control password-field" required>
                                <button class="btn-login">
                                    <i class="fas fa-right-to-bracket me-2"></i> Sign in as Admin
                                </button>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="remember">
                            <label class="form-check-label">Remember me</label>
                        </div>



                        <a href="forgot_password.php?type=admin" class="forgot-link">
                            Forgot password?
                        </a>

                    </form>

                </div>

            </div>
        </div>
    </div>

    <!-- ...your existing code above remains unchanged... -->

    </div> <!-- end of login-wrapper -->



    <!-- Loader -->
    <div class="page-loader" id="pageLoader">
        <div class="spinner"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // Loader
    document.querySelectorAll("form").forEach(form => {
        form.addEventListener("submit", () => {
            document.getElementById("pageLoader").style.display = "flex";
        });
    });

    // Show / Hide Password
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.closest('.input-group').querySelector('.password-field');
            const icon = this.querySelector('i');

            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });
    </script>

</body>

</html>


</body>

</html>