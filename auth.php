<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once __DIR__ . '/../config/helpers.php';
session_start();

// Load PHPMailer
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$action = $_GET['action'] ?? '';

// Test endpoint
if ($action === 'test') {
    echo json_encode(['success' => true, 'message' => 'API is working!']);
    exit();
}

switch ($action) {

    case 'register': {
        $body = jsonBody();
        $firstName = clean($body['firstName'] ?? '');
        $lastName = clean($body['lastName'] ?? '');
        $username = trim($firstName . ' ' . $lastName);
        $password = $body['password'] ?? '';
        $role = clean($body['role'] ?? 'visitor');
        $email = clean($body['email'] ?? '');

        if (!$username || !$password || !$email)
            respond(false, 'Name, email and password are required.');

        if (!in_array($role, ['admin', 'worker', 'visitor']))
            respond(false, 'Invalid role.');

        $hashed = password_hash($password, PASSWORD_BCRYPT);

        try {
            $pdo = getDB();
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, email) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $hashed, $role, $email]);
            respond(true, 'Registered successfully.', ['user_id' => $pdo->lastInsertId()]);
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'Duplicate'))
                respond(false, 'Email already registered.');
            respond(false, 'Registration failed: ' . $e->getMessage());
        }
        break;
    }

    case 'login': {
        $body = jsonBody();
        $email = clean($body['email'] ?? '');
        $password = $body['password'] ?? '';

        if (!$email || !$password)
            respond(false, 'Email and password required.');

        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            http_response_code(401);
            respond(false, 'Wrong email or password.');
        }

        if (!(bool)$user['is_active']) {
            http_response_code(403);
            respond(false, 'Your account has been deactivated. Please contact an administrator.');
        }

        $_SESSION['user'] = [
            'user_id'       => $user['user_id'],
            'username'      => $user['username'],
            'role'          => $user['role'],
            'email'         => $user['email'],
            'must_change_pw' => (bool)$user['must_change_pw'],
        ];

        respond(true, 'Login successful.', ['user' => $_SESSION['user']]);
        break;
    }

    case 'logout': {
        session_destroy();
        respond(true, 'Logged out.');
        break;
    }

    case 'me': {
        $user = currentUser();
        if (!$user) respond(false, 'Not logged in.');
        respond(true, 'OK', ['user' => $user]);
        break;
    }

    case 'change_password': {
        $user = currentUser();
        if (!$user) respond(false, 'Not logged in.');

        $body = jsonBody();
        $newPw = $body['new_password'] ?? '';
        $confirmPw = $body['confirm_password'] ?? '';

        if (strlen($newPw) < 8)
            respond(false, 'Password must be at least 8 characters.');
        if ($newPw !== $confirmPw)
            respond(false, 'Passwords do not match.');

        $hashed = password_hash($newPw, PASSWORD_BCRYPT);
        $pdo = getDB();
        $pdo->prepare("UPDATE users SET password = ?, must_change_pw = 0 WHERE user_id = ?")
            ->execute([$hashed, $user['user_id']]);

        // Clear the flag in session so guards elsewhere see it immediately
        $_SESSION['user']['must_change_pw'] = false;

        respond(true, 'Password updated successfully.');
        break;
    }

    // ── FORGOT PASSWORD WITH GMAIL SMTP ──
    case 'forgot_password': {
        $body = jsonBody();
        $email = clean($body['email'] ?? '');

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            respond(false, 'Please enter a valid email address.');
            break;
        }

        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT user_id, username FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Always return success even if email not found (security)
        if (!$user) {
            respond(true, 'If that email is registered, a reset link has been sent.');
            break;
        }

        // Create password_resets table if not exists
        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS password_resets (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    token VARCHAR(64) NOT NULL,
                    expires_at DATETIME NOT NULL,
                    used TINYINT(1) NOT NULL DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");
        } catch (PDOException $e) {
            // Table might already exist
        }

        // Delete old tokens
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);

        // Generate token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600);

        $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$user['user_id'], $token, $expires]);

        $resetLink = 'http://localhost/WildTrack/reset_password.html?token=' . $token;
        $name = htmlspecialchars($user['username']);

        // Email HTML content
        $emailBody = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
                .container { max-width: 500px; margin: 0 auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { background: #2a7a40; padding: 20px; text-align: center; }
                .header h1 { color: #ffffff; margin: 0; font-size: 24px; }
                .header p { color: rgba(255,255,255,0.8); margin: 5px 0 0; }
                .content { padding: 30px; }
                .button { display: inline-block; background: #2a7a40; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { background: #f4f4f4; padding: 15px; text-align: center; font-size: 12px; color: #666; }
                .warning { color: #666; font-size: 12px; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>WildTrack Malaysia</h1>
                    <p>Password Reset Request</p>
                </div>
                <div class='content'>
                    <p>Hi <strong>{$name}</strong>,</p>
                    <p>We received a request to reset your WildTrack password. Click the button below to create a new password.</p>
                    <div style='text-align: center;'>
                        <a href='{$resetLink}' class='button'>Reset My Password</a>
                    </div>
                    <p>This link will expire in <strong>1 hour</strong>.</p>
                    <div class='warning'>
                        <p>If you didn't request this, please ignore this email.</p>
                        <p>Or copy this link: <a href='{$resetLink}'>{$resetLink}</a></p>
                    </div>
                </div>
                <div class='footer'>
                    <p>© 2026 WildTrack Malaysia | Conservation Through Connection</p>
                </div>
            </div>
        </body>
        </html>
        ";

        // Send email via Gmail SMTP
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'yvonnetan2006@gmail.com';  // YOUR GMAIL ADDRESS
            $mail->Password = 'vijz vqkq ktct wwhm';       // YOUR APP PASSWORD
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->SMTPDebug = 0; // Set to 2 for debugging if needed
            
            // Recipients
            $mail->setFrom('yvonnetan2006@gmail.com', 'WildTrack Malaysia');
            $mail->addAddress($email, $name);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'WildTrack Malaysia - Reset Your Password';
            $mail->Body = $emailBody;
            $mail->AltBody = "Reset your password at: $resetLink";
            
            $mail->send();
            respond(true, 'Reset link sent! Please check your email inbox (and spam folder).');
            
        } catch (Exception $e) {
            error_log("Mail Error: " . $mail->ErrorInfo);
            // Fallback: return link if email fails
            respond(true, 'Reset link generated! (Email failed - check Gmail settings)', ['reset_link' => $resetLink]);
        }
        break;
    }

    // ── VALIDATE TOKEN ──
    case 'validate_token': {
        $token = clean($_GET['token'] ?? '');

        if (!$token) {
            respond(false, 'No token provided.');
            break;
        }

        $pdo = getDB();
        
        // Check if table exists
        $tableCheck = $pdo->query("SHOW TABLES LIKE 'password_resets'");
        if ($tableCheck->rowCount() == 0) {
            respond(false, 'System not ready. Please request a new link.');
            break;
        }

        $stmt = $pdo->prepare("
            SELECT id FROM password_resets 
            WHERE token = ? AND used = 0 AND expires_at > NOW()
            LIMIT 1
        ");
        $stmt->execute([$token]);
        
        if ($stmt->fetch()) {
            respond(true, 'Token is valid.');
        } else {
            respond(false, 'This reset link is invalid or has expired.');
        }
        break;
    }

    // ── RESET PASSWORD ──
    case 'reset_password': {
        $body = jsonBody();
        $token = clean($body['token'] ?? '');
        $password = $body['new_password'] ?? '';
        $confirm = $body['confirm_password'] ?? '';

        if (!$token) {
            respond(false, 'Reset token is missing.');
            break;
        }
        if (strlen($password) < 8) {
            respond(false, 'Password must be at least 8 characters.');
            break;
        }
        if ($password !== $confirm) {
            respond(false, 'Passwords do not match.');
            break;
        }

        $pdo = getDB();
        
        $stmt = $pdo->prepare("
            SELECT user_id, id FROM password_resets 
            WHERE token = ? AND used = 0 AND expires_at > NOW()
            LIMIT 1
        ");
        $stmt->execute([$token]);
        $row = $stmt->fetch();

        if (!$row) {
            respond(false, 'This reset link is invalid or has expired. Please request a new one.');
            break;
        }

        // Update password
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->execute([$hashed, $row['user_id']]);

        // Mark token as used
        $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
        $stmt->execute([$row['id']]);

        respond(true, 'Password reset successfully! You can now log in with your new password.');
        break;
    }

    default:
        respond(false, 'Unknown action: ' . $action);
}
?>