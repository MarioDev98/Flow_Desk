<?php
require_once "conexion.php";

// Obtenemos las tareas agrupando sus hashtags y recuperando el último comentario de bloqueo
$sql = $conexion->query("
    SELECT t.*, 
           (SELECT comentario FROM comentarios WHERE tarea_id = t.id ORDER BY fecha DESC LIMIT 1) as ultimo_comentario,
           GROUP_CONCAT(h.nombre SEPARATOR ',') as tags
    FROM tareas t
    LEFT JOIN tarea_hashtag th ON t.id = th.tarea_id
    LEFT JOIN hashtags h ON th.hashtag_id = h.id
    GROUP BY t.id
    ORDER BY t.fecha_creacion ASC
");

echo json_encode($sql->fetchAll(PDO::FETCH_ASSOC));