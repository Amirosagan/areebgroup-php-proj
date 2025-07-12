<?php

$config = require_once 'config.php';

$db = new PDO(
    "mysql:host=" . $config['db']['host'] . ";port=" . $config['db']['port'] . ";dbname=" . $config['db']['dbname'],
    $config['db']['user'],
    $config['db']['pass'],
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

$routes = [
    '' => 'Views/index.view.php',
    'dashboard' => 'dashboard.php'
];

$path = $_SERVER['REQUEST_URI'];
$path = strtok($path, '?');
$path = trim($path, '/');

if (isset($routes[$path])) {
    require $routes[$path];
} else {
    require 'Views/index.view.php';
}
?>