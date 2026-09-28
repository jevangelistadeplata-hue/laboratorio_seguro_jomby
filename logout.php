<?php

// Carga la conexión (define $pdo)
require_once "config/config.php";

// Si existe la cookie "recordarme", invalida el token guardado en la base de datos
if (isset($_COOKIE['usuario_recordado'])) {
    $token = $_COOKIE['usuario_recordado'];

    $stmt = $pdo->prepare("UPDATE usuarios SET remember_token = NULL WHERE remember_token = :token");
    $stmt->execute(['token' => $token]);
}

// Destruir todas las variables de sesión
$_SESSION = array();
session_destroy();

// Eliminar la cookie 'usuario_recordado' estableciendo su tiempo de expiración en el pasado
if (isset($_COOKIE['usuario_recordado'])) {
    setcookie("usuario_recordado", "", time() - 3600, "/");
}

// Redirigir al formulario de login
header("Location: login.php");
exit;