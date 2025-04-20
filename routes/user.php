<?php
require_once __DIR__ . '/../controllers/UserController.php';

if ($request === '/blog-api/user' && $method === 'GET') {
    getUser($pdo);
}

elseif ($request === '/blog-api/update-user' && $method === 'PUT') {
    updateUserDetails($pdo);
}
