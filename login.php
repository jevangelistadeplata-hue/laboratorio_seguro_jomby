<?php

// Carga la conexión (define $pdo)
require_once "config/config.php";

// Si ya hay sesión activa, redirige al index
if (isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}

$error = "";
$mensaje = $_GET['mensaje'] ?? "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {

    $email    = trim($_POST['email'] ?? "");
    $password = trim($_POST['password'] ?? "");

    if (!empty($email) && !empty($password)) {

        // Consulta preparada: el email nunca se concatena al SQL
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();

        // Se compara el hash guardado con password_verify (no MD5)
        if ($usuario && password_verify($password, $usuario['password'])) {

            $_SESSION['usuario'] = $usuario['nombre'];

            // El "recordarme" se guarda como un token aleatorio, no como el nombre de usuario,
            // para que no se pueda falsificar la cookie manualmente
            if (isset($_POST['recordar'])) {
                $token = bin2hex(random_bytes(32));

                // Aquí deberías guardar $token asociado a $usuario['id'] en la tabla usuarios
                // (columna remember_token) para poder validarlo después
                $stmtToken = $pdo->prepare("UPDATE usuarios SET remember_token = :token WHERE id = :id");
                $stmtToken->execute(['token' => $token, 'id' => $usuario['id']]);

                setcookie("usuario_recordado", $token, time() + (86400 * 7), "/", "", false, true);
            }

            header("Location: index.php");
            exit;

        } else {
            $error = "Correo o contraseña incorrectos.";
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
    <title>Laboratorio Seguro Jomby - Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center vh-100">

<div class="container" style="max-width: 400px;">
    <div class="card shadow-sm">
        <div class="card-body p-4">

            <h3 class="text-center fw-bold text-primary mb-1">📋 Laboratorio Seguro Jomby</h3>
            <p class="text-center text-muted small mb-4">Ingresa tus datos para continuar</p>

            <?php if ($mensaje === "registrado"): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    ✅ ¡Registro exitoso! Ya puedes iniciar sesión.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">

                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="ejemplo@correo.com" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="recordar" id="recordar" class="form-check-input">
                    <label for="recordar" class="form-check-label small text-muted">Recordarme (Guardar Cookie)</label>
                </div>

                <button type="submit" name="login" class="btn btn-primary w-100 mb-3">
                    🔑 Iniciar Sesión
                </button>

                <div class="text-center">
                    <span class="small text-muted">¿No tienes cuenta?</span>
                    <a href="registro.php" class="small fw-bold text-decoration-none">Registrarme</a>
                </div>

            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>