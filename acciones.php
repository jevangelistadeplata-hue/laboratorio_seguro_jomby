<?php

// Carga la conexión y las funciones del proyecto
require_once "config/config.php";
require_once "includes/funciones.php";

// --- Marcar como completada o pendiente ---
if (isset($_GET["completar"])) {
    $id = (int) $_GET["completar"];

    // Verificamos si la tarea existe antes de cambiar el estado
    $tarea = obtenerTarea($id);

    if ($tarea) {
        completarTarea($id);

        // Si estaba completada (1), al alternar pasa a pendiente (0)
        $mensaje = ($tarea["completada"] == 1) ? "pendiente" : "completada";
        header("Location: index.php?mensaje=" . $mensaje);
        exit;
    }

    header("Location: index.php?mensaje=noexiste");
    exit;
}

// --- Eliminar tarea ---
if (isset($_GET["eliminar"])) {
    $id = (int) $_GET["eliminar"];

    $tarea = obtenerTarea($id);

    if ($tarea) {
        eliminarTarea($id);
        header("Location: index.php?mensaje=eliminada");
        exit;
    }

    header("Location: index.php?mensaje=noexiste");
    exit;
}

// Redirección por defecto si no se recibe ninguna acción
header("Location: index.php");
exit;