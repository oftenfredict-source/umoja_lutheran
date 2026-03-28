<?php
// EMERGENCY LOGIN BYPASS - Independent of Laravel Kernel
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load Laravel's environment to get DB credentials
$envFile = __DIR__ . '/../.env';
$config = [];
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[$name] = trim($value, '"\'');
    }
}

// DB Connection
try {
    $dsn = "mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_DATABASE'] . ";charset=utf8mb4";
    $pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check Credentials (Emergency Admin)
$email = 'storekeeper@gmail.com';
$stmt = $pdo->prepare("SELECT * FROM staff WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found: $email");
}

// Manual Session Start
session_start();
$_SESSION['login_verified'] = true;
$_SESSION['staff_id'] = $user['id'];
$_SESSION['user_role'] = $user['role'];

// Set a long-lived cookie manually
setcookie('umoja_session_hard', session_id(), time() + (86400 * 30), '/', '', true, true);

echo "<h1>EMERGENCY LOGIN v1.0</h1>";
echo "Logged in as: " . $user['name'] . " (ID: " . $user['id'] . ")<br>";
echo "Session ID: " . session_id() . "<br>";
echo "<b>CLICK HERE TO GO TO DASHBOARD:</b> <a href='/dashboard'>Go to Dashboard</a>";
echo "<p>If the dashboard still asks for login, tell me the Session ID above.</p>";
?>