<?php
// Asegúrate de que NO haya espacios ni líneas en blanco antes de "<?php"

require_once "conexion.php";

if (ob_get_length()) {
    ob_clean();
}

header('Content-Type: application/json');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([]);
    exit;
}

$idTarea = intval($_GET['id']);

try {
    $query = "
        SELECT 
            comentario AS accion, 
            fecha,
            'comentario' AS tipo
        FROM comentarios 
        WHERE tarea_id = ?

         UNION ALL

         SELECT 
            accion AS accion, 
            fecha,
            'historial' AS tipo
        FROM historial 
        WHERE tarea_id = ?
        
        ORDER BY fecha DESC
    ";
    
    $sql = $conexion->prepare($query);
    $sql->execute([$idTarea, $idTarea]); 
    
    echo json_encode($sql->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo json_encode([]);
}