<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "status" => 405, "message" => "Only POST requests are allowed."], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(["success" => false, "status" => 400, "message" => "Both username and password are required."], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

$conn = new mysqli("localhost", "root", "", "test_db");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "status" => 500, "message" => "Database connection failed."], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    http_response_code(404);
    echo json_encode(["success" => false, "status" => 404, "message" => "User not found."], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

$storedPassword = $user['password'];
$loginOk = false;

$loginOk = false;

if (!empty($storedPassword)) {
    $info = password_get_info($storedPassword);

    if (!empty($info['algo'])) {
        $loginOk = password_verify($password, $storedPassword);
    } else {
        $loginOk = ($password === $storedPassword);
    }
}

if (!$loginOk) {
    http_response_code(401);
    echo json_encode(["success" => false, "status" => 401, "message" => "Wrong password."], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

$token = bin2hex(random_bytes(16));

http_response_code(200);
echo json_encode([
    "success" => true,
    "status" => 200,
    "message" => "Login success.",
    "token" => $token
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>