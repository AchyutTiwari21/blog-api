<?php

require_once __DIR__ . '/utils/cors.php';

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require_once __DIR__ . '/db/connection.php';

$method = $_SERVER['REQUEST_METHOD'];
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Load routes
require_once __DIR__ . '/routes/auth.php';
require_once __DIR__ . '/routes/user.php';
require_once __DIR__ . '/routes/post.php';
