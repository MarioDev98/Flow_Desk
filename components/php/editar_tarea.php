<?php
require_once "conexion.php";
header('Content-Type: application/json');

try {
    $id           = intval($_POST['id']);
    $titulo       = trim($_POST['titulo']);
    $descripcion  = trim($_POST['descripcion']);
    $prioridad    = $_POST['prioridad'];
    $fechaLimite  = empty($_POST['fecha_limite']) ? null : $_POST['fecha_limite'];

    if ($titulo == "" || $id == 0) {
        echo json_encode(["success" => false, "mensaje" => "Datos inválidos."]);
        exit;
    }

    // Actualizamos la información de la tarea
    $sql = $conexion->prepare("
        UPDATE tareas 
        SET titulo = ?, descripcion = ?, prioridad = ?, fecha_limite = ? 
        WHERE id = ?
    ");
    $sql->execute([$titulo, $descripcion, $prioridad, $fechaLimite, $id]);

    // Insertamos registro en el historial detallando los cambios
    $historial = $conexion->prepare("INSERT INTO historial (tarea_id, accion) VALUES (?, ?)");
    $historial->execute([$id, "Se editó la información de la tarea (Prioridad: $prioridad)."]);

    echo json_encode(["success" => true, "mensaje" => "Tarea actualizada con éxito."]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "mensaje" => $e->getMessage()]);
}