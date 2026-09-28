<?php

// Carga la conexión (define $pdo)
require_once "config/config.php";

// Si ya hay sesión activa, redirige al index
if (isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['registro'])) {

    $nombre   = trim($_POST['nombre'] ?? "");
    $email    = trim($_POST['email'] ?? "");
    $password = trim($_POST['password'] ?? "");

    if (!empty($nombre) && !empty($email) && !empty($password)) {

        // Verificar si el correo ya existe (consulta preparada)
        $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
        $stmtCheck->execute(['email' => $email]);

        if ($stmtCheck->fetch()) {
            $error = "El correo electrónico ya está registrado.";
        } else {
            // Se guarda un hash seguro, no MD5
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Inserción con parámetros con nombre
            $stmtInsert = $pdo->prepare(
                "INSERT INTO usuarios (nombre, email, password) VALUES (:nombre, :email, :password)"
            );

            $insertado = $stmtInsert->execute([
                'nombre'   => $nombre,
                'email'    => $email,
                'password' => $password_hash,
            ]);

            if ($insertado) {
                header("Location: login.php?mensaje=registrado");
                exit;
            } else {
                $error = "Error al registrar el usuario. Inténtalo de nuevo.";
            }
        }

    } else {
        $error = "Por favor completa todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorio Seguro Jomby - Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center vh-100">

<div class="container" style="max-width: 420px;">
    <div class="card shadow-sm">
        <div class="card-body p-4">

            <h3 class="text-center fw-bold text-primary mb-1">📋 Laboratorio Seguro Jomby</h3>
            <p class="text-center text-muted small mb-4">Crea una cuenta para gestionar tus tareas</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="registro.php">

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Tu nombre" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="ejemplo@correo.com" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" name="registro" class="btn btn-success w-100 mb-3">
                    📝 Registrarme
                </button>

                <div class="text-center">
                    <span class="small text-muted">¿Ya tienes cuenta?</span>
                    <a href="login.php" class="small fw-bold text-decoration-none">Iniciar Sesión</a>
                </div>

            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>