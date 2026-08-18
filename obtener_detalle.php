<?php
// Configurar encabezado para respuesta JSON
header('Content-Type: application/json; charset=utf-8');

require_once 'components/php/conexion.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID de tarea no proporcionado']);
    exit;
}

try {
    // 1. Obtener datos principales de la tarea
    $stmt = $conexion->prepare("SELECT * FROM tareas WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $tarea = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tarea) {
        echo json_encode(['success' => false, 'message' => 'No se encontró la tarea especificada']);
        exit;
    }

    // 2. Obtener comentarios asociados a la tarea
    $comentarios = [];
    try {
        $stmtComentarios = $conexion->prepare("SELECT * FROM comentarios WHERE tarea_id = :id ORDER BY fecha DESC");
        $stmtComentarios->execute([':id' => $id]);
        $comentarios = $stmtComentarios->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $comentarios = [];
    }

    // 3. Obtener historial / bloqueos asociados a la tarea
    $historial = [];
    try {
        $stmtHistorial = $conexion->prepare("SELECT * FROM historial WHERE tarea_id = :id ORDER BY fecha DESC");
        $stmtHistorial->execute([':id' => $id]);
        $historial = $stmtHistorial->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $historial = [];
    }

    // Respuesta JSON unificada
    echo json_encode([
        'success' => true,
        'tarea' => $tarea,
        'comentarios' => $comentarios ?: [],
        'historial' => $historial ?: []
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
}
?>