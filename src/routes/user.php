<?php
require_once __DIR__ . '/../controllers/UserController.php';

if ($request === '/user' && $method === 'GET') {
    getUser($pdo);
}

elseif ($request === '/update-user' && $method === 'PUT') {
    updateUserDetails($pdo);
}
