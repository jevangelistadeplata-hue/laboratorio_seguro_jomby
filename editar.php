<?php

// --- Carga de dependencias ---
require_once "config/config.php";
require_once "includes/funciones.php";

// Comprueba que se haya recibido el ID
if (!isset($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$id = (int) $_GET["id"];

// Busca la tarea existente
$tarea = obtenerTarea($id);

if (!$tarea) {
    header("Location: index.php?mensaje=noexiste");
    exit;
}

// --- Procesar el envío del formulario ---
if (isset($_POST["actualizar"])) {

    $titulo      = trim($_POST["titulo"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $fecha       = $_POST["fecha"] ?? "";
    $prioridad   = (int) ($_POST["prioridad"] ?? 0);
    $categoria   = trim($_POST["categoria"] ?? "");

    // Validaciones (lista blanca de valores permitidos)
    if (
        $titulo === "" ||
        $fecha === "" ||
        !in_array($prioridad, [1, 2, 3], true) ||
        !in_array($categoria, ["Trabajo", "Estudio", "Personal"], true)
    ) {
        $error = "Los datos enviados no son válidos.";

        // Conservar las entradas del usuario si la validación falla
        $tarea["titulo"]      = $titulo;
        $tarea["descripcion"] = $descripcion;
        $tarea["fecha"]       = $fecha;
        $tarea["prioridad"]   = $prioridad;
        $tarea["categoria"]   = $categoria;

    } else {
        // Actualizar usando la función global (PDO con parámetros con nombre)
        actualizarTarea($id, $titulo, $descripcion, $fecha, $prioridad, $categoria);

        header("Location: index.php?mensaje=actualizada");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar tarea - Laboratorio Seguro Jomby</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">

    <!-- Encabezado -->
    <div class="text-center mb-4">
        <h1 class="fw-bold">📋 Laboratorio Seguro Jomby</h1>
        <p class="text-muted">Modifica los datos de tu tarea</p>
    </div>

    <!-- Alerta de error -->
    <?php if (isset($error)): ?>
        <div class="alert alert-danger text-center mb-4">
            ❌ <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Formulario -->
    <div class="card shadow-sm mx-auto" style="max-width: 600px;">
        <div class="card-header bg-white">
            <h4 class="mb-0 fs-5 fw-bold">✏️ Editar tarea</h4>
        </div>

        <div class="card-body">
            <form method="POST" action="editar.php?id=<?php echo $id; ?>">

                <!-- Título -->
                <div class="mb-3">
                    <label for="titulo" class="form-label">Tarea</label>
                    <input 
                        type="text" 
                        id="titulo" 
                        name="titulo" 
                        class="form-control" 
                        value="<?php echo htmlspecialchars($tarea["titulo"]); ?>" 
                        maxlength="255" 
                        required
                    >
                </div>

                <!-- Descripción -->
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea 
                        id="descripcion" 
                        name="descripcion" 
                        class="form-control" 
                        rows="3"
                    ><?php echo htmlspecialchars($tarea["descripcion"] ?? ""); ?></textarea>
                </div>

                <!-- Fecha -->
                <div class="mb-3">
                    <label for="fecha" class="form-label">Fecha límite</label>
                    <input 
                        type="date" 
                        id="fecha" 
                        name="fecha" 
                        class="form-control" 
                        value="<?php echo htmlspecialchars($tarea["fecha"]); ?>" 
                        required
                    >
                </div>

                <!-- Prioridad -->
                <div class="mb-3">
                    <label for="prioridad" class="form-label">Prioridad</label>
                    <select id="prioridad" name="prioridad" class="form-select" required>
                        <option value="1" <?php echo (int)$tarea["prioridad"] === 1 ? "selected" : ""; ?>>⭐ Baja</option>
                        <option value="2" <?php echo (int)$tarea["prioridad"] === 2 ? "selected" : ""; ?>>⭐⭐ Media</option>
                        <option value="3" <?php echo (int)$tarea["prioridad"] === 3 ? "selected" : ""; ?>>⭐⭐⭐ Alta</option>
                    </select>
                </div>

                <!-- Categoría -->
                <div class="mb-4">
                    <label for="categoria" class="form-label">Categoría</label>
                    <select id="categoria" name="categoria" class="form-select" required>
                        <option value="Trabajo" <?php echo $tarea["categoria"] === "Trabajo" ? "selected" : ""; ?>>Trabajo</option>
                        <option value="Estudio" <?php echo $tarea["categoria"] === "Estudio" ? "selected" : ""; ?>>Estudio</option>
                        <option value="Personal" <?php echo $tarea["categoria"] === "Personal" ? "selected" : ""; ?>>Personal</option>
                    </select>
                </div>

                <!-- Botones -->
                <div class="d-flex gap-2 justify-content-end">
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" name="actualizar" class="btn btn-primary">💾 Guardar cambios</button>
                </div>

            </form>
        </div>
    </div>

</div>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>