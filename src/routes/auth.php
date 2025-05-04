<?php

require_once __DIR__ . '/../controllers/AuthController.php';

if ($request === '/signup' && $method === 'POST') {
    signup($pdo);
}
elseif ($request === '/login' && $method === 'POST') {
    login($pdo);
}
elseif ($request === '/logout' && $method === 'POST') {
    logout($pdo);
}
