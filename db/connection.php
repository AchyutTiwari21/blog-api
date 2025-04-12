<?php

$host = 'localhost';
$db   = 'Blog';
$user = 'root'; 
$pass = null;  

$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
