<?php
// REACHABILITY TEST - Independent of Laravel
error_reporting(E_ALL);
ini_set('display_errors', 1);

$sessionsPath = __DIR__ . '/../storage/framework/sessions';
$isWritable = is_writable($sessionsPath);
$diskSpace = disk_free_space(__DIR__);

echo "<h1>REACHABILITY TEST v1.0</h1>";
echo "<b>Time:</b> " . date('Y-m-d H:i:s') . "<br>";
echo "<b>Sessions Path:</b> " . realpath($sessionsPath) . "<br>";
echo "<b>Is Writable:</b> " . ($isWritable ? "YES" : "NO") . "<br>";
echo "<b>Free Space:</b> " . round($diskSpace / (1024 * 1024 * 1024), 2) . " GB<br>";

echo "<h2>Session Persistence Test</h2>";
session_start();
if (!isset($_SESSION['test_counter'])) {
    $_SESSION['test_counter'] = 0;
}
$_SESSION['test_counter']++;
echo "<b>Session Counter:</b> " . $_SESSION['test_counter'] . "<br>";
echo "<b>Session ID:</b> " . session_id() . "<br>";
echo "<p>If the counter doesn't increase when you refresh, then PHP Sessions are broken on your server.</p>";

// Try to write a test file
$testFile = $sessionsPath . '/write_test.txt';
$canWrite = @file_put_contents($testFile, 'test');
echo "<b>Manual Write Test:</b> " . ($canWrite ? "SUCCESS" : "FAILED") . "<br>";
if ($canWrite)
    unlink($testFile);
?>