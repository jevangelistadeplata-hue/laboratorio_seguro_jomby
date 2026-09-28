<?php

declare(strict_types=1);
session_start();

// --- Datos de conexión a la base de datos ---
$host    = 'localhost';
$db      = 'laboratorio_seguro_jomby';
$user    = 'root';   // luego lo cambiamos por uno dedicado, sin privilegios de root
$pass    = '';
$puerto  = 3307;
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$puerto;dbname=$db;charset=$charset";

// --- Opciones de PDO ---
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

// --- Conexión ---
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    exit('Error de conexión a la base de datos.');
}