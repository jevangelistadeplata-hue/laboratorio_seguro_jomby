<?php

// Carga la conexión con la base de datos (define $pdo)
require_once __DIR__ . "/../config/config.php";

// --- Registra una nueva tarea ---
function agregarTarea(
    string $titulo,
    string $descripcion,
    string $fecha,
    int $prioridad,
    string $categoria
): void {
    global $pdo;

    $sql = "INSERT INTO tareas (titulo, descripcion, fecha, prioridad, categoria)
            VALUES (:titulo, :descripcion, :fecha, :prioridad, :categoria)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'titulo'      => $titulo,
        'descripcion' => $descripcion,
        'fecha'       => $fecha,
        'prioridad'   => $prioridad,
        'categoria'   => $categoria,
    ]);
}

// --- Obtiene las tareas aplicando filtros ---
function obtenerTareas(
    string $buscar = "",
    string $estado = "",
    string $categoria = "",
    string $prioridad = ""
): array {
    global $pdo;

    $sql = "SELECT * FROM tareas WHERE 1 = 1";
    $parametros = [];

    // Filtro por búsqueda de texto
    if ($buscar !== "") {
        $sql .= " AND (titulo LIKE :buscar1 OR descripcion LIKE :buscar2)";
        $parametros['buscar1'] = "%$buscar%";
        $parametros['buscar2'] = "%$buscar%";
    }

    // Filtro por estado
    if ($estado !== "") {
        $sql .= " AND completada = :estado";
        $parametros['estado'] = (int) $estado;
    }

    // Filtro por categoría
    if ($categoria !== "") {
        $sql .= " AND categoria = :categoria";
        $parametros['categoria'] = $categoria;
    }

    // Filtro por prioridad
    if ($prioridad !== "") {
        $sql .= " AND prioridad = :prioridad";
        $parametros['prioridad'] = (int) $prioridad;
    }

    $sql .= " ORDER BY fecha_creacion DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametros);

    return $stmt->fetchAll();
}

// --- Obtiene una tarea por su ID ---
function obtenerTarea(int $id): ?array {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM tareas WHERE id = :id");
    $stmt->execute(['id' => $id]);

    $tarea = $stmt->fetch();
    return $tarea ?: null;
}

// --- Actualiza los datos de una tarea ---
function actualizarTarea(
    int $id,
    string $titulo,
    string $descripcion,
    string $fecha,
    int $prioridad,
    string $categoria
): void {
    global $pdo;

    $sql = "UPDATE tareas
            SET titulo = :titulo, descripcion = :descripcion, fecha = :fecha,
                prioridad = :prioridad, categoria = :categoria
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'titulo'      => $titulo,
        'descripcion' => $descripcion,
        'fecha'       => $fecha,
        'prioridad'   => $prioridad,
        'categoria'   => $categoria,
        'id'          => $id,
    ]);
}

// --- Alterna entre pendiente y completada ---
function completarTarea(int $id): void {
    global $pdo;

    $stmt = $pdo->prepare("UPDATE tareas SET completada = NOT completada WHERE id = :id");
    $stmt->execute(['id' => $id]);
}

// --- Elimina una tarea por su ID ---
function eliminarTarea(int $id): void {
    global $pdo;

    $stmt = $pdo->prepare("DELETE FROM tareas WHERE id = :id");
    $stmt->execute(['id' => $id]);
}