<?php

function getUser($pdo) {
    $email = $_COOKIE['email'] ?? null;
    $token = $_COOKIE['token'] ?? null;

    if (!$email || !$token) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }

    $stmt = $pdo->prepare("SELECT Id, Name, Email, ProfileImage, Designation FROM Users WHERE Email = ? AND Token = ?");
    $stmt->execute([$email, $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        http_response_code(200);
        echo json_encode($user);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
    }
}

function updateUserDetails($pdo) {
    $email = $_COOKIE['email'] ?? null;
    $token = $_COOKIE['token'] ?? null;

    if (!$email || !$token) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM Users WHERE Email = ? AND Token = ?");
    $stmt->execute([$email, $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid user']);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"), true);

    $name = trim($data['name'] ?? $user['Name']);
    $profileImage = trim($data['profileImage'] ?? $user['ProfileImage']);
    $designation = trim($data['designation'] ?? $user['Designation']);

    $stmt = $pdo->prepare("
        UPDATE Users SET Name = ?, ProfileImage = ?, Designation = ?
        WHERE Id = ?
    ");
    $stmt->execute([$name, $profileImage, $designation, $user['Id']]);

    http_response_code(200);
    echo json_encode([
        'message' => 'User updated successfully',
        'user' => [
            'Id' => $user['Id'],
            'Name' => $name,
            'Email' => $user['Email'],
            'ProfileImage' => $profileImage,
            'Designation' => $designation
        ]
    ]);
}

