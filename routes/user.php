<?php
require_once __DIR__ . '/../controllers/UserController.php';

if ($request === '/blog-api/user' && $method === 'GET') {
    getUser($pdo);
}
