<?php
require_once "conexion.php";
header('Content-Type: application/json');

try {
    // Se puede recibir por POST o por GET según tu llamada de JS
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id == 0) {
        echo json_encode(["success" => false, "mensaje" => "ID de tarea no válido."]);
        exit;
    }

    // 1. Eliminar relaciones en la tabla intermedia de hashtags
    $borrarTags = $conexion->prepare("DELETE FROM tarea_hashtag WHERE tarea_id = ?");
    $borrarTags->execute([$id]);

    // 2. Eliminar el historial y comentarios asociados para evitar errores de FK
    $borrarHistorial = $conexion->prepare("DELETE FROM historial WHERE tarea_id = ?");
    $borrarHistorial->execute([$id]);
    
    $borrarComentarios = $conexion->prepare("DELETE FROM comentarios WHERE tarea_id = ?");
    $borrarComentarios->execute([$id]);

    // 3. Finalmente eliminar la tarea
    $sql = $conexion->prepare("DELETE FROM tareas WHERE id = ?");
    $sql->execute([$id]);

    echo json_encode(["success" => true, "mensaje" => "Tarea eliminada correctamente."]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "mensaje" => $e->getMessage()]);
}