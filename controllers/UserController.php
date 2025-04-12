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
