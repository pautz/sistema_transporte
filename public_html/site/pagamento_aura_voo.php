<?php
session_start();

// Database connection
$servername = "localhost";
$username = "username"; // replace with your DB username
$password = "password"; // replace with your DB password
$dbname = "database"; // replace with your DB name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// User authentication
if (!isset($_SESSION['user_id'])) {
    die('Access denied: Please log in.');
}

$user_id = $_SESSION['user_id'];

// Function to get user balance
function getUserBalance($conn, $user_id) {
    $sql = "SELECT balance FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($balance);
    $stmt->fetch();
    return $balance;
}

// Validate user balance
$balance = getUserBalance($conn, $user_id);
$flight_cost = 100; // example flight cost
if ($balance < $flight_cost) {
    die('Insufficient balance.');
}

// Password verification
if (!password_verify($_POST['password'], $_SESSION['password_hash'])) {
    die('Invalid password.');
}

// AURA transfer to flight wallet
function transferToWallet($conn, $user_id, $amount) {
    // Transfer logic here
    return true;
}

if (!transferToWallet($conn, $user_id, $flight_cost)) {
    die('Transfer failed.');
}

// Transaction logging
$sql = "INSERT INTO transactions (user_id, amount, date) VALUES (?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("id", $user_id, $flight_cost);
$stmt->execute();

// Generate digital signature
$signature = hash('sha256', $user_id . $flight_cost . time());

// Receipt generation with QR code
function generateQRCode($data) {
    // Use a QR code library to generate QR code
}

$qr_data = "Transaction ID: $signature, Amount: $flight_cost";
generateQRCode($qr_data);

// Success confirmation
echo "Payment successful! Transaction ID: $signature";

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            font-family: Arial, sans-serif;
            color: white;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>AURA Payment System</h1>
    <p>Your payment was successful. Thank you for your reservation!</p>
</body>
</html>
