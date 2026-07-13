<?php
require_once "conexion.php";
header('Content-Type: application/json');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([]);
    exit;
}

$idTarea = intval($_GET['id']);

try {
    // Ajusta los nombres de tus columnas (ej: fecha o fecha_registro) según tu tabla 'historial'
    $sql = $conexion->prepare("SELECT accion, fecha FROM historial WHERE tarea_id = ? ORDER BY fecha DESC");
    $sql->execute([$idTarea]);
    
    echo json_encode($sql->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo json_encode([]);
}