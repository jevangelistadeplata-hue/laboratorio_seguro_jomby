<?php

// Carga la conexión (define $pdo) y arranca la sesión — debe ir primero
require_once "includes/funciones.php";

// Validar que el usuario haya iniciado sesión o tenga cookie guardada
if (!isset($_SESSION['usuario']) && !isset($_COOKIE['usuario_recordado'])) {
    header("Location: login.php");
    exit;
}

// Si entra a través de una cookie previa, restaurar la sesión
if (!isset($_SESSION['usuario']) && isset($_COOKIE['usuario_recordado'])) {
    $_SESSION['usuario'] = $_COOKIE['usuario_recordado'];
}

/* ========================= MENSAJES ========================= */
$mensaje = $_GET["mensaje"] ?? "";

/* ========================= AGREGAR TAREA ========================= */
if (isset($_POST["agregar"])) {

    $titulo      = trim($_POST["titulo"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $fecha       = $_POST["fecha"] ?? "";
    $prioridad   = (int) ($_POST["prioridad"] ?? 0);
    $categoria   = trim($_POST["categoria"] ?? "");

    // Validación por lista blanca
    if (
        $titulo === "" ||
        $fecha === "" ||
        !in_array($prioridad, [1, 2, 3], true) ||
        !in_array($categoria, ["Trabajo", "Estudio", "Personal"], true)
    ) {
        header("Location: index.php?mensaje=error");
        exit;
    }

    agregarTarea($titulo, $descripcion, $fecha, $prioridad, $categoria);

    header("Location: index.php?mensaje=agregada");
    exit;
}

/* ========================= FILTROS ========================= */
$buscar    = trim($_GET["buscar"] ?? "");
$estado    = $_GET["estado"] ?? "";
$categoria = $_GET["categoria"] ?? "";
$prioridad = $_GET["prioridad"] ?? "";

/* ========================= OBTENER TAREAS ========================= */
// obtenerTareas() devuelve un array (fetchAll con PDO)
$resultado = obtenerTareas($buscar, $estado, $categoria, $prioridad);

/* ========================= SEPARAR TAREAS ========================= */
$tareasPendientesLista  = [];
$tareasCompletadasLista = [];

foreach ($resultado as $tarea) {
    if ((int) $tarea["completada"] === 1) {
        $tareasCompletadasLista[] = $tarea;
    } else {
        $tareasPendientesLista[] = $tarea;
    }
}

$cantidadPendientes  = count($tareasPendientesLista);
$cantidadCompletadas = count($tareasCompletadasLista);

/* ========================= ESTADÍSTICAS GENERALES ========================= */
$totalTareas = 0;
$totalPendientes = 0;
$totalCompletadas = 0;
$prioridadBaja = 0;
$prioridadMedia = 0;
$prioridadAlta = 0;

$sqlEstadisticas = "
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN completada = 0 THEN 1 ELSE 0 END) AS pendientes,
        SUM(CASE WHEN completada = 1 THEN 1 ELSE 0 END) AS completadas,
        SUM(CASE WHEN prioridad = 1 THEN 1 ELSE 0 END) AS prioridad_baja,
        SUM(CASE WHEN prioridad = 2 THEN 1 ELSE 0 END) AS prioridad_media,
        SUM(CASE WHEN prioridad = 3 THEN 1 ELSE 0 END) AS prioridad_alta
    FROM tareas
";

// Consulta fija, sin datos externos: no necesita parámetros
$stmtEstadisticas = $pdo->query($sqlEstadisticas);
$estadisticas = $stmtEstadisticas->fetch();

if ($estadisticas) {
    $totalTareas      = (int) ($estadisticas["total"] ?? 0);
    $totalPendientes  = (int) ($estadisticas["pendientes"] ?? 0);
    $totalCompletadas = (int) ($estadisticas["completadas"] ?? 0);
    $prioridadBaja    = (int) ($estadisticas["prioridad_baja"] ?? 0);
    $prioridadMedia   = (int) ($estadisticas["prioridad_media"] ?? 0);
    $prioridadAlta    = (int) ($estadisticas["prioridad_alta"] ?? 0);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorio Seguro Jomby - Gestión de tareas</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Estadísticas */
        .stat-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.10) !important; }
        .stat-icon { font-size: 2rem; }
        .stat-number { font-size: 1.8rem; font-weight: 700; line-height: 1; }
        .stat-title { font-size: 0.85rem; color: #6c757d; }

        /* Prioridades */
        .priority-stat { font-size: 0.9rem; line-height: 1.8; }
        .priority-stat span { font-weight: 600; }

        /* Tarjetas de tareas */
        .task-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .task-card:hover { transform: translateY(-2px); box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.10) !important; }

        /* Completadas */
        .task-completed { opacity: 0.75; }
        .task-completed .task-title { text-decoration: line-through; color: #6c757d; }

        /* Información */
        .task-info { font-size: 0.9rem; }
        .task-info-label { font-size: 0.75rem; color: #6c757d; display: block; margin-bottom: 2px; }

        /* Títulos de listas */
        .section-title { font-weight: 700; }
        .section-title-pending { color: #212529; }
        .section-title-completed { color: #198754; }

        /* Separador */
        .completed-section { margin-top: 3rem; padding-top: 2rem; border-top: 2px solid #dee2e6; }

        /* Prioridades (badges) */
        .badge-priority-high { background-color: #dc3545; color: white; }
        .badge-priority-medium { background-color: #ffc107; color: #212529; }
        .badge-priority-low { background-color: #198754; color: white; }
    </style>
</head>
<body class="bg-light">

<div class="container py-4">

    <!-- ENCABEZADO -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-5 fw-bold text-primary mb-0">📋 Laboratorio Seguro Jomby</h1>
            <p class="text-muted mb-0">
                Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong>
            </p>
        </div>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">🚪 Cerrar Sesión</a>
    </div>

    <!-- MENSAJES -->
    <?php if ($mensaje === "agregada"): ?>
        <div class="alert alert-success alert-dismissible fade show">
            ✅ Tarea agregada correctamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($mensaje === "actualizada"): ?>
        <div class="alert alert-success alert-dismissible fade show">
            ✏️ Tarea actualizada correctamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($mensaje === "eliminada"): ?>
        <div class="alert alert-success alert-dismissible fade show">
            🗑️ Tarea eliminada correctamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($mensaje === "completada"): ?>
        <div class="alert alert-success alert-dismissible fade show">
            ✅ Tarea marcada como completada.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($mensaje === "pendiente"): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            ↩️ Tarea marcada como pendiente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($mensaje === "noexiste"): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            ⚠️ La tarea seleccionada no existe.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($mensaje === "error"): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            ❌ No se pudo registrar la tarea. Verifica los datos ingresados.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- ESTADÍSTICAS -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon">📋</div>
                        <div>
                            <div class="stat-title">Total</div>
                            <div class="stat-number"><?php echo $totalTareas; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card shadow-sm stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon">⏳</div>
                        <div>
                            <div class="stat-title">Pendientes</div>
                            <div class="stat-number"><?php echo $totalPendientes; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card shadow-sm stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon">✅</div>
                        <div>
                            <div class="stat-title">Completadas</div>
                            <div class="stat-number"><?php echo $totalCompletadas; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card shadow-sm stat-card h-100">
                <div class="card-body">
                    <div class="stat-title mb-2">⭐ Prioridades</div>
                    <div class="priority-stat">
                        <div>⭐ Baja: <span><?php echo $prioridadBaja; ?></span></div>
                        <div>⭐⭐ Media: <span><?php echo $prioridadMedia; ?></span></div>
                        <div>⭐⭐⭐ Alta: <span><?php echo $prioridadAlta; ?></span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- NUEVA TAREA -->
    <div class="card shadow-sm mb-4">
        <div class="card-header"><h5 class="mb-0">➕ Nueva tarea</h5></div>
        <div class="card-body">
            <form method="POST" action="index.php">
                <div class="row g-3">
                    <div class="col-12">
                        <label for="titulo" class="form-label">Tarea</label>
                        <input type="text" id="titulo" name="titulo" class="form-control"
                               placeholder="Escribe una tarea" maxlength="255" required>
                    </div>
                    <div class="col-12">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea id="descripcion" name="descripcion" class="form-control" rows="3"
                                  placeholder="Describe brevemente la tarea"></textarea>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="fecha" class="form-label">Fecha</label>
                        <input type="date" id="fecha" name="fecha" class="form-control" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="prioridad" class="form-label">Prioridad</label>
                        <select id="prioridad" name="prioridad" class="form-select" required>
                            <option value="1">⭐ Baja</option>
                            <option value="2">⭐⭐ Media</option>
                            <option value="3">⭐⭐⭐ Alta</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="categoria" class="form-label">Categoría</label>
                        <select id="categoria" name="categoria" class="form-select" required>
                            <option value="Trabajo">Trabajo</option>
                            <option value="Estudio">Estudio</option>
                            <option value="Personal">Personal</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" name="agregar" class="btn btn-primary">➕ Agregar tarea</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- BUSCAR Y FILTRAR -->
    <div class="card shadow-sm mb-4">
        <div class="card-header"><h5 class="mb-0">🔎 Buscar y filtrar</h5></div>
        <div class="card-body">
            <form method="GET" action="index.php">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="buscar" class="form-label">Buscar</label>
                        <input type="text" id="buscar" name="buscar" class="form-control"
                               placeholder="Título o descripción"
                               value="<?php echo htmlspecialchars($buscar); ?>">
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="estado" class="form-label">Estado</label>
                        <select id="estado" name="estado" class="form-select">
                            <option value="">Todas</option>
                            <option value="0" <?php echo $estado === "0" ? "selected" : ""; ?>>⏳ Pendientes</option>
                            <option value="1" <?php echo $estado === "1" ? "selected" : ""; ?>>✅ Completadas</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="filtro_categoria" class="form-label">Categoría</label>
                        <select id="filtro_categoria" name="categoria" class="form-select">
                            <option value="">Todas</option>
                            <option value="Trabajo" <?php echo $categoria === "Trabajo" ? "selected" : ""; ?>>Trabajo</option>
                            <option value="Estudio" <?php echo $categoria === "Estudio" ? "selected" : ""; ?>>Estudio</option>
                            <option value="Personal" <?php echo $categoria === "Personal" ? "selected" : ""; ?>>Personal</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="filtro_prioridad" class="form-label">Prioridad</label>
                        <select id="filtro_prioridad" name="prioridad" class="form-select">
                            <option value="">Todas</option>
                            <option value="1" <?php echo $prioridad === "1" ? "selected" : ""; ?>>⭐ Baja</option>
                            <option value="2" <?php echo $prioridad === "2" ? "selected" : ""; ?>>⭐⭐ Media</option>
                            <option value="3" <?php echo $prioridad === "3" ? "selected" : ""; ?>>⭐⭐⭐ Alta</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">🔎 Filtrar</button>
                        <a href="index.php" class="btn btn-secondary">🔄 Limpiar filtros</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- TAREAS PENDIENTES -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0 section-title section-title-pending">⏳ Tareas pendientes</h2>
        <span class="badge bg-warning text-dark"><?php echo $cantidadPendientes; ?></span>
    </div>

    <?php if ($cantidadPendientes > 0): ?>
        <?php foreach ($tareasPendientesLista as $tarea): ?>
            <?php
            if ((int) $tarea["prioridad"] === 3) {
                $badgePrioridad = "badge-priority-high";
                $textoPrioridad = "⭐⭐⭐ Alta";
            } elseif ((int) $tarea["prioridad"] === 2) {
                $badgePrioridad = "badge-priority-medium";
                $textoPrioridad = "⭐⭐ Media";
            } else {
                $badgePrioridad = "badge-priority-low";
                $textoPrioridad = "⭐ Baja";
            }
            ?>
            <div class="card shadow-sm mb-3 task-card">
                <div class="card-body">
                    <h3 class="h5 fw-bold task-title"><?php echo htmlspecialchars($tarea["titulo"]); ?></h3>

                    <?php if (!empty($tarea["descripcion"])): ?>
                        <p class="text-muted mb-3"><?php echo nl2br(htmlspecialchars($tarea["descripcion"])); ?></p>
                    <?php endif; ?>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-3 task-info">
                            <span class="task-info-label">📅 Fecha</span>
                            <?php echo htmlspecialchars($tarea["fecha"]); ?>
                        </div>
                        <div class="col-12 col-md-3 task-info">
                            <span class="task-info-label">⭐ Prioridad</span>
                            <span class="badge <?php echo $badgePrioridad; ?>"><?php echo $textoPrioridad; ?></span>
                        </div>
                        <div class="col-12 col-md-3 task-info">
                            <span class="task-info-label">📁 Categoría</span>
                            <?php echo htmlspecialchars($tarea["categoria"]); ?>
                        </div>
                        <div class="col-12 col-md-3 task-info">
                            <span class="task-info-label">Estado</span>
                            <span class="badge bg-warning text-dark">⏳ Pendiente</span>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="acciones.php?completar=<?php echo (int) $tarea["id"]; ?>" class="btn btn-sm btn-success">✅ Completar</a>
                        <a href="editar.php?id=<?php echo (int) $tarea["id"]; ?>" class="btn btn-sm btn-warning">✏️ Editar</a>
                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalEliminar"
                                data-id="<?php echo (int) $tarea["id"]; ?>"
                                data-titulo="<?php echo htmlspecialchars($tarea["titulo"], ENT_QUOTES, "UTF-8"); ?>">
                            🗑️ Eliminar
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info">📭 No hay tareas pendientes.</div>
    <?php endif; ?>

    <!-- TAREAS COMPLETADAS -->
    <?php if ($cantidadCompletadas > 0): ?>
        <div class="completed-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h4 mb-0 section-title section-title-completed">✅ Tareas completadas</h2>
                <span class="badge bg-success"><?php echo $cantidadCompletadas; ?></span>
            </div>

            <?php foreach ($tareasCompletadasLista as $tarea): ?>
                <?php
                if ((int) $tarea["prioridad"] === 3) {
                    $badgePrioridad = "badge-priority-high";
                    $textoPrioridad = "⭐⭐⭐ Alta";
                } elseif ((int) $tarea["prioridad"] === 2) {
                    $badgePrioridad = "badge-priority-medium";
                    $textoPrioridad = "⭐⭐ Media";
                } else {
                    $badgePrioridad = "badge-priority-low";
                    $textoPrioridad = "⭐ Baja";
                }
                ?>
                <div class="card shadow-sm mb-3 task-card task-completed">
                    <div class="card-body">
                        <h3 class="h5 fw-bold task-title"><?php echo htmlspecialchars($tarea["titulo"]); ?></h3>

                        <?php if (!empty($tarea["descripcion"])): ?>
                            <p class="text-muted mb-3"><?php echo nl2br(htmlspecialchars($tarea["descripcion"])); ?></p>
                        <?php endif; ?>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-3 task-info">
                                <span class="task-info-label">📅 Fecha</span>
                                <?php echo htmlspecialchars($tarea["fecha"]); ?>
                            </div>
                            <div class="col-12 col-md-3 task-info">
                                <span class="task-info-label">⭐ Prioridad</span>
                                <span class="badge <?php echo $badgePrioridad; ?>"><?php echo $textoPrioridad; ?></span>
                            </div>
                            <div class="col-12 col-md-3 task-info">
                                <span class="task-info-label">📁 Categoría</span>
                                <?php echo htmlspecialchars($tarea["categoria"]); ?>
                            </div>
                            <div class="col-12 col-md-3 task-info">
                                <span class="task-info-label">Estado</span>
                                <span class="badge bg-success">✅ Completada</span>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <a href="acciones.php?completar=<?php echo (int) $tarea["id"]; ?>" class="btn btn-sm btn-secondary">↩️ Marcar pendiente</a>
                            <a href="editar.php?id=<?php echo (int) $tarea["id"]; ?>" class="btn btn-sm btn-warning">✏️ Editar</a>
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalEliminar"
                                    data-id="<?php echo (int) $tarea["id"]; ?>"
                                    data-titulo="<?php echo htmlspecialchars($tarea["titulo"], ENT_QUOTES, "UTF-8"); ?>">
                                🗑️ Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- MODAL ELIMINAR -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEliminarLabel">🗑️ Eliminar tarea</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">¿Estás seguro de que deseas eliminar esta tarea?</p>
                <div class="alert alert-warning mb-0"><strong id="tituloTareaEliminar">Tarea</strong></div>
                <p class="text-muted small mt-3 mb-0">⚠️ Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">❌ Cancelar</button>
                <a href="#" id="btnConfirmarEliminar" class="btn btn-danger">🗑️ Sí, eliminar</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const modalEliminar = document.getElementById("modalEliminar");
    const tituloTareaEliminar = document.getElementById("tituloTareaEliminar");
    const btnConfirmarEliminar = document.getElementById("btnConfirmarEliminar");

    modalEliminar.addEventListener("show.bs.modal", function (event) {
        const boton = event.relatedTarget;
        const id = boton.getAttribute("data-id");
        const titulo = boton.getAttribute("data-titulo");

        tituloTareaEliminar.textContent = titulo;
        btnConfirmarEliminar.href = "acciones.php?eliminar=" + id;
    });
});
</script>

</body>
</html>