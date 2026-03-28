<?php
// REACHABILITY TEST - Independent of Laravel
error_reporting(E_ALL);
ini_set('display_errors', 1);

$sessionsPath = __DIR__ . '/../storage/framework/sessions';
$isWritable = is_writable($sessionsPath);
$diskSpace = disk_free_space(__DIR__);

echo "<h1>REACHABILITY TEST v1.1</h1>";
echo "<b>Time:</b> " . date('Y-m-d H:i:s') . "<br>";
echo "<b>Sessions Path:</b> " . realpath($sessionsPath) . "<br>";
echo "<b>Is Writable (Folders):</b> " . ($isWritable ? "YES" : "NO") . "<br>";
echo "<b>Free Space:</b> " . round($diskSpace / (1024 * 1024 * 1024), 2) . " GB<br>";

echo "<h2>Session Persistence Test</h2>";
session_start();
if (!isset($_SESSION['test_counter'])) {
    $_SESSION['test_counter'] = 0;
}
$_SESSION['test_counter']++;
echo "<b>Session Counter:</b> " . $_SESSION['test_counter'] . " (Refresh to see if it numbers up)<br>";
echo "<b>Session ID:</b> " . session_id() . "<br>";
echo "<p>If the ID is empty or the counter stays at 1, then PHP Sessions are broken on this server.</p>";

// Try to write a test file
$testFile = $sessionsPath . '/write_test.txt';
$canWrite = @file_put_contents($testFile, 'test');
echo "<b>Manual Write Test:</b> " . ($canWrite ? "SUCCESS" : "FAILED") . "<br>";
if ($canWrite)
    unlink($testFile);

echo "<h2>Database & Laravel Config Test</h2>";
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $env = file_get_contents($envFile);
    if (preg_match('/DB_DATABASE=(.*)/', $env, $matches))
        echo "<b>DB Name:</b> " . trim($matches[1]) . "<br>";
    if (preg_match('/SESSION_DRIVER=(.*)/', $env, $matches))
        echo "<b>Current Driver (in .env):</b> " . trim($matches[1]) . "<br>";
}

echo "<p>Final Resolution: We will switch to the DATABASE driver to fix the login loop.</p>";
?>