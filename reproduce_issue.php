<?php
require_once 'config.php';

// 1. Get initial count
$res = $conn->query("SELECT COUNT(*) as cnt FROM admin");
$row = $res->fetch_assoc();
$initial_count = $row['cnt'];
echo "Initial Admin Count: " . $initial_count . "<br>";

// 2. Simulate Login Logic (Read-Only)
$username = "check_test"; 
$email = "check@test.com";
$password = "password";

$stmt = $conn->prepare("SELECT id_admin, username, password FROM admin WHERE username = ? AND email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$result = $stmt->get_result();
echo "Login Check Performed (Found: " . $result->num_rows . ")<br>";

// 3. Get final count
$res = $conn->query("SELECT COUNT(*) as cnt FROM admin");
$row = $res->fetch_assoc();
$final_count = $row['cnt'];
echo "Final Admin Count: " . $final_count . "<br>";

if ($final_count > $initial_count) {
    echo "CRITICAL: Data INCREASED! Logic error confirmed.";
} else {
    echo "STATUS: Normal. Data did NOT increase.";
}
?>
