<?php
require_once "conexion.php";
header('Content-Type: application/json');

try {
    $titulo       = trim($_POST['titulo']);
    $descripcion  = trim($_POST['descripcion']);
    $prioridad    = $_POST['prioridad'];
    $fechaLimite  = empty($_POST['fecha_limite']) ? null : $_POST['fecha_limite'];
    $hashtagsInput = isset($_POST['hashtags']) ? trim($_POST['hashtags']) : "";

    if($titulo == ""){
        echo json_encode(["success" => false, "mensaje" => "El título es obligatorio."]);
        exit;
    }

    // 1. Insertar tarea
    $sql = $conexion->prepare("
        INSERT INTO tareas (titulo, descripcion, prioridad, fecha_limite)
        VALUES (?, ?, ?, ?)
    ");
    $sql->execute([$titulo, $descripcion, $prioridad, $fechaLimite]);
    $idTarea = $conexion->lastInsertId();

    // 2. Procesar Hashtags
    if (!empty($hashtagsInput)) {
        // Separamos por comas
        $arrayTags = explode(',', $hashtagsInput);
        
        foreach ($arrayTags as $tag) {
            $tagClean = trim($tag);
            if ($tagClean === "") continue; // Saltar si está vacío

            // ¿Ya existe el hashtag globalmente?
            $buscarTag = $conexion->prepare("SELECT id FROM hashtags WHERE nombre = ?");
            $buscarTag->execute([$tagClean]);
            $resTag = $buscarTag->fetch(PDO::FETCH_ASSOC);

            if ($resTag) {
                $idHashtag = $resTag['id'];
            } else {
                // Si no existe, lo creamos
                $insertarTag = $conexion->prepare("INSERT INTO hashtags (nombre) VALUES (?)");
                $insertarTag->execute([$tagClean]);
                $idHashtag = $conexion->lastInsertId();
            }

            // Relacionamos la tarea con el hashtag
            $relacion = $conexion->prepare("INSERT INTO tarea_hashtag (tarea_id, hashtag_id) VALUES (?, ?)");
            $relacion->execute([$idTarea, $idHashtag]);
        }
    }

    // 3. Historial
    $historial = $conexion->prepare("INSERT INTO historial (tarea_id, accion) VALUES (?, ?)");
    $historial->execute([$idTarea, "Se creó la tarea."]);

    echo json_encode(["success" => true, "mensaje" => "Tarea creada correctamente."]);

} catch(Exception $e) {
    echo json_encode(["success" => false, "mensaje" => $e->getMessage()]);
}