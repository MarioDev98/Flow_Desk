<?php

require_once "conexion.php";

header('Content-Type: application/json');

try{

    $titulo       = trim($_POST['titulo']);
    $descripcion  = trim($_POST['descripcion']);
    $prioridad    = $_POST['prioridad'];
    $fechaLimite  = empty($_POST['fecha_limite'])
                    ? null
                    : $_POST['fecha_limite'];

    if($titulo==""){

        echo json_encode([
            "success"=>false,
            "mensaje"=>"El título es obligatorio."
        ]);

        exit;
    }

    $sql = $conexion->prepare("

        INSERT INTO tareas(

            titulo,
            descripcion,
            prioridad,
            fecha_limite

        )

        VALUES(

            ?,
            ?,
            ?,
            ?

        )

    ");

    $sql->execute([

        $titulo,
        $descripcion,
        $prioridad,
        $fechaLimite

    ]);

    $idTarea = $conexion->lastInsertId();

    $historial = $conexion->prepare("

        INSERT INTO historial(

            tarea_id,
            accion

        )

        VALUES(

            ?,
            ?

        )

    ");

    $historial->execute([

        $idTarea,
        "Se creó la tarea."

    ]);

    echo json_encode([

        "success"=>true,
        "mensaje"=>"Tarea creada correctamente."

    ]);

}catch(Exception $e){

    echo json_encode([

        "success"=>false,
        "mensaje"=>$e->getMessage()

    ]);

}