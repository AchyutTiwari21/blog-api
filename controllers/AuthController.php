<?php

function signup($pdo) {
    $data = json_decode(file_get_contents("php://input"), true);
    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    if (!$name || !$email || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'All fields are required']);
        return;
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO Users (Name, Email, Password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $hashed]);

    http_response_code(201);
    echo json_encode(['message' => 'User registered']);
}

function login($pdo) {
    $data = json_decode(file_get_contents("php://input"), true);
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    if (!$email || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'Email and password required']);
        return;
    }

    $stmt = $pdo->prepare("SELECT * FROM Users WHERE Email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['Password'])) {
        $token = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare("UPDATE Users SET Token = ? WHERE Id = ?");
        $stmt->execute([$token, $user['Id']]);

        setcookie('email', $email, time() + 3600, "/", "", false, true);
        setcookie('token', $token, time() + 3600, "/", "", false, true);

        http_response_code(200);
        echo json_encode(['message' => 'Login successful']);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
    }
}

function logout($pdo) {
    $email = $_COOKIE['email'] ?? '';
    $token = $_COOKIE['token'] ?? '';

    if (!$email || !$token) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }

    $stmt = $pdo->prepare("UPDATE Users SET Token = NULL WHERE Email = ? AND Token = ?");
    $stmt->execute([$email, $token]);

    setcookie('email', '', time() - 3600, "/", "", false, true);
    setcookie('token', '', time() - 3600, "/", "", false, true);

    http_response_code(200);
    echo json_encode(['message' => 'Logged out']);
}
