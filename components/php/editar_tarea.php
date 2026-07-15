<?php
require_once "conexion.php";
header('Content-Type: application/json');

try {
    $id           = intval($_POST['id']);
    $titulo       = trim($_POST['titulo']);
    $descripcion  = trim($_POST['descripcion']);
    $prioridad    = $_POST['prioridad'];
    $fechaLimite  = empty($_POST['fecha_limite']) ? null : $_POST['fecha_limite'];
    $hashtagsInput = isset($_POST['hashtags']) ? trim($_POST['hashtags']) : "";

    if ($titulo == "" || $id == 0) {
        echo json_encode(["success" => false, "mensaje" => "Datos inválidos."]);
        exit;
    }

    // 1. Actualizar datos básicos de la tarea
    $sql = $conexion->prepare("
        UPDATE tareas 
        SET titulo = ?, descripcion = ?, prioridad = ?, fecha_limite = ? 
        WHERE id = ?
    ");
    $sql->execute([$titulo, $descripcion, $prioridad, $fechaLimite, $id]);

    // 2. Actualizar Hashtags (Limpiamos los viejos y ponemos los nuevos)
    $limpiarTags = $conexion->prepare("DELETE FROM tarea_hashtag WHERE tarea_id = ?");
    $limpiarTags->execute([$id]);

    if (!empty($hashtagsInput)) {
        $arrayTags = explode(',', $hashtagsInput);
        foreach ($arrayTags as $tag) {
            $tagClean = trim($tag);
            if ($tagClean === "") continue;

            // Buscar o crear el hashtag
            $buscarTag = $conexion->prepare("SELECT id FROM hashtags WHERE nombre = ?");
            $buscarTag->execute([$tagClean]);
            $resTag = $buscarTag->fetch(PDO::FETCH_ASSOC);

            if ($resTag) {
                $idHashtag = $resTag['id'];
            } else {
                $insertarTag = $conexion->prepare("INSERT INTO hashtags (nombre) VALUES (?)");
                $insertarTag->execute([$tagClean]);
                $idHashtag = $conexion->lastInsertId();
            }

            // Insertar nueva relación
            $relacion = $conexion->prepare("INSERT INTO tarea_hashtag (tarea_id, hashtag_id) VALUES (?, ?)");
            $relacion->execute([$id, $idHashtag]);
        }
    }

    // 3. Historial
    $historial = $conexion->prepare("INSERT INTO historial (tarea_id, accion) VALUES (?, ?)");
    $historial->execute([$id, "Se editó la información de la tarea y sus hashtags."]);

    echo json_encode(["success" => true, "mensaje" => "Tarea actualizada con éxito."]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "mensaje" => $e->getMessage()]);
}