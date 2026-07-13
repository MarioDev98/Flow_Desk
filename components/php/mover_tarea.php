<?php
require_once "conexion.php";
header('Content-Type: application/json');

try {
    $idTarea    = intval($_POST['id_tarea']);
    $nuevoEstado = $_POST['estatus'];
    $comentario  = isset($_POST['comentario']) ? trim($_POST['comentario']) : "";

    if (empty($idTarea) || empty($nuevoEstado)) {
        echo json_encode(["success" => false, "mensaje" => "Datos incompletos."]);
        exit;
    }

    // Si el estado es Dependencia, obligamos a que el comentario contenga texto
    if ($nuevoEstado === "Dependencia" && empty($comentario)) {
        echo json_encode(["success" => false, "mensaje" => "Falta la justificación de la dependencia."]);
        exit;
    }

    // Iniciamos transacción para asegurar consistencia
    $conexion->beginTransaction();

    // 1. Actualizar el estatus en la tabla tareas
    $sql = $conexion->prepare("UPDATE tareas SET estatus = ? WHERE id = ?");
    $sql->execute([$nuevoEstado, $idTarea]);

    // 2. Insertar en el historial la acción
    $accionHistorial = "Se cambió el estado a: " . $nuevoEstado;
    $historial = $conexion->prepare("INSERT INTO historial (tarea_id, accion) VALUES (?, ?)");
    $historial->execute([$idTarea, $accionHistorial]);

    // 3. Si se movió a dependencia, guardamos el por qué en la tabla de comentarios
    if ($nuevoEstado === "Dependencia") {
        $comentSql = $conexion->prepare("INSERT INTO comentarios (tarea_id, comentario) VALUES (?, ?)");
        $comentSql->execute([$idTarea, "[Bloqueo/Dependencia]: " . $comentario]);
    }

    $conexion->commit();

    echo json_encode([
        "success" => true,
        "mensaje" => "Estado actualizado correctamente."
    ]);

} catch (Exception $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }
    echo json_encode([
        "success" => false,
        "mensaje" => "Error del servidor: " . $e->getMessage()
    ]);
}