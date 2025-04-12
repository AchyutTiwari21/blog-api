<?php

require_once __DIR__ . '/../controllers/AuthController.php';

if ($request === '/blog-api/signup' && $method === 'POST') {
    signup($pdo);
}
elseif ($request === '/blog-api/login' && $method === 'POST') {
    login($pdo);
}
elseif ($request === '/blog-api/logout' && $method === 'POST') {
    logout($pdo);
}
