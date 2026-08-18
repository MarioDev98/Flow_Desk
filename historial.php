<?php
require_once 'components/php/conexion.php';

// Filtros usando PDO Prepared Statements
$where = [];
$params = [];

if (!empty($_GET['buscar'])) {
    $where[] = "(titulo LIKE :buscar OR id LIKE :buscar_id)";
    $params[':buscar'] = '%' . $_GET['buscar'] . '%';
    $params[':buscar_id'] = '%' . $_GET['buscar'] . '%';
}

if (!empty($_GET['prioridad'])) {
    $where[] = "prioridad = :prioridad";
    $params[':prioridad'] = $_GET['prioridad'];
}

if (!empty($_GET['estatus'])) {
    $where[] = "estatus = :estatus";
    $params[':estatus'] = $_GET['estatus'];
}

$sql = "SELECT * FROM tareas";
if (count($where) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY fecha_creacion DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute($params);
$tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Tareas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="components/css/style.css">
    <style>
        .fila-tarea { cursor: pointer; }
        .fila-tarea:hover { background-color: #f1f3f5 !important; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-light bg-white shadow-sm mb-4">
    <a class="navbar-brand" href="index.php">
        <i class='bx bx-arrow-back'></i> Volver al Tablero
    </a>
    <span class="navbar-text font-weight-bold">
        <i class='bx bx-history'></i> Historial Completo de Tareas
    </span>
</nav>

<div class="container-fluid mb-5">
    <!-- Formulario de Búsqueda y Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="historial.php" class="row">
                <div class="col-md-4 mb-2">
                    <input type="text" name="buscar" class="form-control" placeholder="Buscar por título o ID..." value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
                </div>
                <div class="col-md-3 mb-2">
                    <select name="prioridad" class="form-control">
                        <option value="">Todas las prioridades</option>
                        <?php foreach (['Baja', 'Media', 'Alta', 'Critica'] as $p): ?>
                            <option value="<?= $p ?>" <?= ($_GET['prioridad'] ?? '') === $p ? 'selected' : '' ?>><?= $p ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select name="estatus" class="form-control">
                        <option value="">Todos los estatus</option>
                        <?php foreach (['Nuevo', 'En proceso', 'Dependencia', 'Finalizado'] as $e): ?>
                            <option value="<?= $e ?>" <?= ($_GET['estatus'] ?? '') === $e ? 'selected' : '' ?>><?= $e ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-2 d-flex">
                    <button type="submit" class="btn btn-primary mr-2 flex-grow-1">
                        <i class='bx bx-search'></i> Filtrar
                    </button>
                    <?php if (!empty($_GET['buscar']) || !empty($_GET['prioridad']) || !empty($_GET['estatus'])): ?>
                        <a href="historial.php" class="btn btn-outline-secondary" title="Limpiar filtros">
                            <i class='bx bx-x-circle'></i> Limpiar
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Tareas -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Título</th>
                            <th>Prioridad</th>
                            <th>Estatus</th>
                            <th>F. Límite</th>
                            <th>F. Creación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($tareas) > 0): ?>
                            <?php foreach ($tareas as $row): ?>
                                <tr class="fila-tarea" onclick="verDetalleTarea(<?= $row['id'] ?>)">
                                    <td><strong>#<?= $row['id'] ?></strong></td>
                                    <td><?= htmlspecialchars($row['titulo']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $row['prioridad'] === 'Critica' ? 'danger' : ($row['prioridad'] === 'Alta' ? 'warning' : 'secondary') ?>">
                                            <?= htmlspecialchars($row['prioridad']) ?>
                                        </span>
                                    </td>
                                    <td><span class="badge badge-info"><?= htmlspecialchars($row['estatus']) ?></span></td>
                                    <td><?= $row['fecha_limite'] ? htmlspecialchars($row['fecha_limite']) : 'N/A' ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($row['fecha_creacion'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">No se encontraron tareas registradas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalle de Tarea -->
<div class="modal fade" id="modalDetalleTarea" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalDetalleTitulo">Detalle de la Tarea</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="modalCargando" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Cargando detalles...</p>
                </div>
                
                <div id="modalContenidoTarea" class="d-none">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <h4 id="detalleTitulo" class="font-weight-bold"></h4>
                            <p id="detalleDescripcion" class="text-muted mb-2"></p>
                        </div>
                        <div class="col-md-4 text-md-right">
                            <span id="detallePrioridad" class="badge p-2"></span>
                            <span id="detalleEstatus" class="badge badge-info p-2"></span>
                        </div>
                    </div>

                    <div class="row mb-4 bg-light p-3 rounded">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Fecha Límite</small>
                            <strong id="detalleFechaLimite">N/A</strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Fecha Creación</small>
                            <strong id="detalleFechaCreacion">N/A</strong>
                        </div>
                    </div>

                    <!-- Pestañas para Comentarios e Historial -->
                    <ul class="nav nav-tabs" id="tabDetalle" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-comentarios" data-toggle="tab" href="#secComentarios" role="tab"><i class='bx bx-comment-detail'></i> Comentarios</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-historial" data-toggle="tab" href="#secHistorial" role="tab"><i class='bx bx-history'></i> Historial / Bloqueos</a>
                        </li>
                    </ul>

                    <div class="tab-content pt-3" id="tabDetalleContent">
                        <!-- Pestaña Comentarios -->
                        <div class="tab-pane fade show active" id="secComentarios" role="tabpanel">
                            <div style="max-height: 200px; overflow-y: auto;">
                                <ul class="list-group list-group-flush" id="listaComentarios"></ul>
                            </div>
                        </div>

                        <!-- Pestaña Historial -->
                        <div class="tab-pane fade" id="secHistorial" role="tabpanel">
                            <div style="max-height: 200px; overflow-y: auto;">
                                <ul class="list-group list-group-flush" id="listaHistorialModal"></ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function verDetalleTarea(tareaId) {
    // Mostrar modal y spinner de carga
    $('#modalCargando').removeClass('d-none');
    $('#modalContenidoTarea').addClass('d-none');
    $('#modalDetalleTarea').modal('show');

    fetch(`obtener_detalle.php?id=${tareaId}`)
        .then(async response => {
            const texto = await response.text();
            try {
                return JSON.parse(texto);
            } catch (err) {
                console.error('El servidor no devolvió un JSON válido:', texto);
                throw new Error('Respuesta del servidor no válida');
            }
        })
        .then(data => {
            if (!data.success) {
                alert(data.message || 'Error al obtener datos');
                $('#modalDetalleTarea').modal('hide');
                return;
            }

            const t = data.tarea;

            // Llenar campos principales
            $('#modalDetalleTitulo').text(`Tarea #${t.id}`);
            $('#detalleTitulo').text(t.titulo || 'Sin título');
            $('#detalleDescripcion').text(t.descripcion || 'Sin descripción.');
            
            // Prioridad
            let badgeClass = 'badge-secondary';
            if (t.prioridad === 'Critica') badgeClass = 'badge-danger';
            if (t.prioridad === 'Alta') badgeClass = 'badge-warning';
            if (t.prioridad === 'Media') badgeClass = 'badge-info';
            if (t.prioridad === 'Baja') badgeClass = 'badge-success';
            $('#detallePrioridad').attr('class', `badge ${badgeClass} p-2`).text(t.prioridad || 'N/A');

            // Estatus y fechas
            $('#detalleEstatus').text(t.estatus || 'Sin estatus');
            $('#detalleFechaLimite').text(t.fecha_limite || 'Sin fecha límite');
            $('#detalleFechaCreacion').text(t.fecha_creacion || 'N/A');

            // --- Llenar Comentarios ---
            const $listaComentarios = $('#listaComentarios');
            $listaComentarios.empty();
            const comentarios = data.comentarios || [];
            
            if (comentarios.length > 0) {
                comentarios.forEach(c => {
                    $listaComentarios.append(`
                        <li class="list-group-item px-0">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted"><i class='bx bx-calendar'></i> ${c.fecha || ''}</small>
                            </div>
                            <p class="mb-0 mt-1">${c.comentario || ''}</p>
                        </li>
                    `);
                });
            } else {
                $listaComentarios.append('<li class="list-group-item text-muted text-center py-3">No hay comentarios registrados.</li>');
            }

            // --- Llenar Historial / Bloqueos ---
            const $listaHistorial = $('#listaHistorialModal');
            $listaHistorial.empty();
            const historial = data.historial || [];

            if (historial.length > 0) {
                historial.forEach(h => {
                    $listaHistorial.append(`
                        <li class="list-group-item px-0">
                            <div class="d-flex justify-content-between">
                                <span><i class='bx bx-right-arrow-alt text-primary'></i> ${h.accion || h.descripcion || ''}</span>
                                <small class="text-muted">${h.fecha || ''}</small>
                            </div>
                        </li>
                    `);
                });
            } else {
                $listaHistorial.append('<li class="list-group-item text-muted text-center py-3">No hay acciones ni bloqueos registrados.</li>');
            }

            // Ocultar spinner y mostrar contenido
            $('#modalCargando').addClass('d-none');
            $('#modalContenidoTarea').removeClass('d-none');
        })
        .catch(err => {
            console.error(err);
            alert('Ocurrió un error al cargar la información del detalle.');
            $('#modalDetalleTarea').modal('hide');
        });
}
</script>

</body>
</html>