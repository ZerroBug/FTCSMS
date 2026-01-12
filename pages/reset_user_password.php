<?php
session_start();
require_once '../includes/db_connection.php';
if (
    !isset($_SESSION['user_id']) || 
    !isset($_SESSION['user_role']) || 
    ($_SESSION['user_role'] !== 'Super_Admin' && 
     $_SESSION['user_role'] !== 'Administrator' && 
     $_SESSION['user_role'] !== 'Accountant')
) {
    // Destroy session for security
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}

$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

      $user_photo = $_SESSION['user_photo'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Reset Password — FTCSMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <link href="../assets/css/styles.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
    body {
        font-family: "Poppins", sans-serif;
        background: linear-gradient(135deg, #f4f6f9, #e9e2f5);
    }

    .section-card {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        padding: 2rem 2.5rem;
        max-width: 480px;
        margin: 1rem auto;
    }

    .form-control {
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 0.95rem;
        border: 1px solid #d1c4e9;
    }

    .btn-primary {
        border-radius: 12px;
        font-weight: 600;
        padding: 10px 25px;
        background: #412461;
        border: none;
    }

    .btn-primary:hover {
        background: #2d1448;
    }

    .password-toggle-btn {
        cursor: pointer;
        color: #7b5fc5;
        margin-left: 10px;
        font-size: 1.1rem;
    }

    h4 i {
        color: #412461;
    }

    .form-label {
        font-weight: 500;
        font-size: 0.95rem;
        text-align: left;
    }

    .form-group {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
    }

    .form-group label {
        flex: 0 0 35%;
        margin-bottom: 0;
    }

    .form-group .form-control-wrapper {
        flex: 1;
        display: flex;
        align-items: center;
    }

    .form-group .form-control-wrapper input {
        flex: 1;
    }

    .alert {
        border-radius: 12px;
        font-size: 0.95rem;
    }

    .strength-bar {
        height: 6px;
        border-radius: 6px;
        background: #ddd;
        margin-top: 5px;
    }

    .strength-fill {
        height: 100%;
        width: 0%;
        border-radius: 6px;
        transition: width 0.3s ease;
    }

    .strength-text {
        font-size: 0.85rem;
        margin-top: 4px;
        font-weight: 500;
    }

    @media (max-width: 576px) {
        .section-card {
            margin: 2rem 1rem;
            padding: 1.8rem 1.5rem;
        }

        .form-group {
            flex-direction: column;
            align-items: flex-start;
        }

        .form-group label {
            flex: none;
            margin-bottom: 0.3rem;
        }

        .form-group .form-control-wrapper {
            width: 100%;
            flex-direction: row;
        }
    }
    </style>
</head>

<body>

    <?php
if ($_SESSION['user_role'] === 'Super_Admin') {
    include '../includes/super_admin_sidebar.php';
} elseif ($_SESSION['user_role'] === 'Administrator') {
    include '../includes/administrator_sidebar.php';
} elseif ($_SESSION['user_role'] === 'Accountant') {
    include '../includes/accounts_sidebar.php';
}
?>

    <?php include '../includes/topbar.php'; ?>

    <main class="main container">

        <div class="section-card">

            <h4 class="fw-bold mb-2 text-center">
                <i class="fas fa-key me-2"></i> Reset Password
            </h4>
            <small class="d-block text-center mb-3">Update your password to keep your account secure</small>

            <!-- Alert Section -->
            <div id="alertBox" class="alert d-none" role="alert"></div>

            <form id="resetPasswordForm" method="POST" action="../handlers/process_reset_user_password.php">

                <!-- Current Password -->
                <div class="form-group">
                    <label for="currentPassword" class="form-label">Current Password</label>
                    <div class="form-control-wrapper">
                        <input type="password" id="currentPassword" name="current_password" class="form-control"
                            required>
                        <i class="fas fa-eye password-toggle-btn" onclick="togglePassword('currentPassword')"></i>
                    </div>
                </div>

                <!-- New Password -->
                <div class="form-group">
                    <label for="newPassword" class="form-label">New Password</label>
                    <div class="form-control-wrapper">
                        <input type="password" id="newPassword" name="new_password" class="form-control" required>
                        <i class="fas fa-eye password-toggle-btn" onclick="togglePassword('newPassword')"></i>
                    </div>
                    <div class="strength-bar mt-1">
                        <div id="strengthFill" class="strength-fill"></div>
                    </div>
                    <div id="strengthText" class="strength-text"></div>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                    <div class="form-control-wrapper">
                        <input type="password" id="confirmPassword" name="confirm_password" class="form-control"
                            required>
                        <i class="fas fa-eye password-toggle-btn" onclick="togglePassword('confirmPassword')"></i>
                    </div>
                </div>

                <div class="text-end mt-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Password
                    </button>
                </div>

            </form>

        </div>

    </main>

    <script>
    function togglePassword(id) {
        const input = document.getElementById(id);
        input.type = input.type === 'password' ? 'text' : 'password';
    }

    const form = document.getElementById('resetPasswordForm');
    const alertBox = document.getElementById('alertBox');
    const newPasswordInput = document.getElementById('newPassword');
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');

    // Password strength logic
    newPasswordInput.addEventListener('input', () => {
        const val = newPasswordInput.value;
        let strength = 0;

        if (val.length >= 6) strength += 1;
        if (val.match(/[A-Z]/)) strength += 1;
        if (val.match(/[0-9]/)) strength += 1;
        if (val.match(/[\W]/)) strength += 1;

        let width = (strength / 4) * 100;
        strengthFill.style.width = width + '%';

        switch (strength) {
            case 0:
            case 1:
                strengthFill.style.background = '#ff4d4f';
                strengthText.textContent = 'Weak';
                strengthText.style.color = '#ff4d4f';
                break;
            case 2:
                strengthFill.style.background = '#faad14';
                strengthText.textContent = 'Fair';
                strengthText.style.color = '#faad14';
                break;
            case 3:
                strengthFill.style.background = '#52c41a';
                strengthText.textContent = 'Good';
                strengthText.style.color = '#52c41a';
                break;
            case 4:
                strengthFill.style.background = '#1890ff';
                strengthText.textContent = 'Strong';
                strengthText.style.color = '#1890ff';
                break;
        }
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const currentPassword = document.getElementById('currentPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        if (newPassword !== confirmPassword) {
            alertBox.className = 'alert alert-danger';
            alertBox.textContent = 'New password and confirmation do not match.';
            alertBox.classList.remove('d-none');
            return;
        }

        fetch('../handlers/process_reset_user_password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `current_password=${encodeURIComponent(currentPassword)}&new_password=${encodeURIComponent(newPassword)}&confirm_password=${encodeURIComponent(confirmPassword)}`
            })
            .then(res => res.json())
            .then(data => {
                alertBox.className = data.success ? 'alert alert-success' : 'alert alert-danger';
                alertBox.textContent = data.message;
                alertBox.classList.remove('d-none');
                if (data.success) {
                    form.reset();
                    strengthFill.style.width = '0%';
                    strengthText.textContent = '';
                }
            })
            .catch(err => {
                alertBox.className = 'alert alert-danger';
                alertBox.textContent = 'An error occurred. Try again.';
                alertBox.classList.remove('d-none');
                console.error(err);
            });
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>