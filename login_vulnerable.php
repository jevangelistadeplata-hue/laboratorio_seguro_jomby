<?php

// ⚠️ ARCHIVO — Práctica 2: SQL Injection
// Este login es intencionalmente vulnerable.
// No usar como referencia de código seguro.

// Carga la conexión (define $pdo y activa la sesión)
require_once "config/config.php";

$error = "";
$consultaEjecutada = "";
$accesoConcedido = false;
$usuarioEncontrado = null;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {

    $email = trim($_POST['email'] ?? "");
    $password = trim($_POST['password'] ?? "");

    if (!empty($email) && !empty($password)) {

        // Cifrado débil, utilizado únicamente para esta práctica.
        $password_cifrada = md5($password);

        // ⚠️ VULNERABLE A PROPÓSITO:
        // El email y la contraseña se concatenan directamente al SQL.
        $sql = "SELECT * FROM usuarios WHERE email = '$email' AND password = '$password_cifrada'";

        $consultaEjecutada = $sql;

        try {

            $resultado = $pdo->query($sql);
            $usuario = $resultado->fetch();

            if ($usuario) {

                // No iniciamos sesión real aquí.
                // Solo mostramos si la consulta encontró un usuario.
                $accesoConcedido = true;
                $usuarioEncontrado = $usuario;

            } else {

                $error = "Correo o contraseña incorrectos.";
                $accesoConcedido = false;
            }

        } catch (PDOException $e) {

            // Se muestra el error SQL para evidenciar la vulnerabilidad.
            $error = "Error SQL: " . $e->getMessage();
            $accesoConcedido = false;
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

<title>Login VULNERABLE - Laboratorio SQL Injection</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<!-- Bootstrap Icons -->
<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
>


</head>

<body class="bg-light d-flex align-items-center vh-100">

<div class="container" style="max-width: 500px;">


<div class="card shadow-sm border-danger">

    <div class="card-body p-4">

        <h3 class="text-center fw-bold text-danger mb-1">
            ⚠️ Login Vulnerable
        </h3>

        <p class="text-center text-muted small mb-4">
            Solo para pruebas de la Práctica 2
        </p>

        <?php if (!empty($error)): ?>

            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>

        <?php if (!empty($accesoConcedido) && $usuarioEncontrado): ?>

            <div class="alert alert-success">

                ✅ Acceso concedido. Usuario encontrado:

                <strong>
                    <?= htmlspecialchars($usuarioEncontrado['nombre']) ?>
                </strong>

            </div>

        <?php endif; ?>

        <form method="POST" action="login_vulnerable.php">

            <div class="mb-3">

                <label for="email" class="form-label">
                    Correo Electrónico
                </label>

                <input
                    type="text"
                    name="email"
                    id="email"
                    class="form-control"
                    placeholder="ejemplo@correo.com"
                    required
                >

            </div>

            <div class="mb-3">

                <label for="password" class="form-label">
                    Contraseña
                </label>

                <div class="input-group">

                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control"
                        placeholder="cualquier cosa"
                        required
                    >

                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        id="btnMostrar"
                        onclick="mostrarPassword()"
                        aria-label="Mostrar contraseña"
                        title="Mostrar contraseña"
                    >

                        <i class="bi bi-eye"></i>

                    </button>

                </div>

            </div>

            <button
                type="submit"
                name="login"
                class="btn btn-danger w-100"
            >
                Entrar (vulnerable)
            </button>

        </form>

        <?php if ($consultaEjecutada !== ""): ?>

            <div class="mt-4">

                <p class="small text-muted mb-1">
                    Consulta SQL ejecutada:
                </p>

                <code
                    class="small d-block bg-dark text-light p-2 rounded"
                    style="word-break: break-all;"
                >
                    <?= htmlspecialchars($consultaEjecutada) ?>
                </code>

            </div>

        <?php endif; ?>

    </div>

</div>


</div>

<script>

function mostrarPassword() {

    const password = document.getElementById("password");
    const boton = document.getElementById("btnMostrar");
    const icono = boton.querySelector("i");

    if (password.type === "password") {

        password.type = "text";

        icono.classList.remove("bi-eye");
        icono.classList.add("bi-eye-slash");

        boton.setAttribute("aria-label", "Ocultar contraseña");
        boton.setAttribute("title", "Ocultar contraseña");

    } else {

        password.type = "password";

        icono.classList.remove("bi-eye-slash");
        icono.classList.add("bi-eye");

        boton.setAttribute("aria-label", "Mostrar contraseña");
        boton.setAttribute("title", "Mostrar contraseña");
    }
}

</script>

</body>
</html>
