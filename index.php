<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Tareas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="components/css/style.css">
</head>
<body>

<nav class="navbar navbar-light shadow-sm">
    <span class="navbar-brand">
        <i class='bx bx-task'></i> Mis Tareas
    </span>
    <button class="btn btn-primary" data-toggle="modal" data-target="#modalNueva">
        <i class='bx bx-plus'></i> Nueva tarea
    </button>
</nav>

<div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col-md-2">
                <a href="historial.php" class="text-decoration-none text-dark">
                    <div class="card dashboard-card" style="cursor: pointer;">
                        <div class="card-body">
                            <h5>Total</h5>
                            <h2 id="total">0</h2>
                    </div>
            </div>
                </a>
        </div>
        <div class="col-md-2"><div class="card dashboard-card"><div class="card-body"><h5>Nuevas</h5><h2 id="nuevo">0</h2></div></div></div>
        <div class="col-md-2"><div class="card dashboard-card"><div class="card-body"><h5>Dependencia</h5><h2 id="dependencia">0</h2></div></div></div>
        <div class="col-md-2"><div class="card dashboard-card"><div class="card-body"><h5>Proceso</h5><h2 id="proceso">0</h2></div></div></div>
        <div class="col-md-2"><div class="card dashboard-card"><div class="card-body"><h5>Finalizadas</h5><h2 id="finalizadas">0</h2></div></div></div>
   <div class="col-md-2">
        <a href="productividad.php" class="text-decoration-none text-dark">
            <div class="card dashboard-card" style="cursor: pointer;">
                <div class="card-body">
                    <h5>Autor:</h5>
                    <h2>MARIO M.</h2>
                </div>
            </div>
        </a>
    </div>
</div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-5">
                    <input type="text" id="buscadorTarea" class="form-control" placeholder="Buscar por título o hashtag (ej: dev)...">
                </div>
                <div class="col-md-3">
                    <select id="filtroPrioridad" class="form-control">
                        <option value="">Todas las prioridades</option>
                        <option value="Baja">Baja</option>
                        <option value="Media">Media</option>
                        <option value="Alta">Alta</option>
                        <option value="Crítica">Crítica</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="date" id="filtroFecha" class="form-control">
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3"><div class="kanban-column"><h4> Nuevo</h4><div id="nuevoContainer"></div></div></div>
        <div class="col-lg-3"><div class="kanban-column"><h4> En proceso</h4><div id="procesoContainer"></div></div></div>
        <div class="col-lg-3"><div class="kanban-column"><h4> Dependencia</h4><div id="dependenciaContainer"></div></div></div>
        <div class="col-lg-3"><div class="kanban-column"><h4> Finalizado</h4><div id="finalizadoContainer"></div></div></div>
    </div>
</div>

<div class="modal fade" id="modalNueva">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5>Nueva tarea</h5><button class="close" data-dismiss="modal">×</button></div>
            <div class="modal-body">
                <form id="formNueva">
                    <div class="form-group"><label>Título</label><input type="text" name="titulo" class="form-control" required></div>
                    <div class="form-group"><label>Descripción</label><textarea name="descripcion" class="form-control"></textarea></div>
                    <div class="form-group"><label>Hashtags (separados por comas)</label><input type="text" name="hashtags" class="form-control" placeholder="ej: dev, infra, urgente"></div>
                    <div class="form-group">
                        <label>Prioridad</label>
                        <select name="prioridad" class="form-control"><option>Baja</option><option selected>Media</option><option>Alta</option><option>Critica</option></select>
                    </div>
                    <div class="form-group"><label>Fecha límite</label><input type="date" name="fecha_limite" class="form-control"></div>
                </form>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button id="guardarTarea" class="btn btn-primary">Guardar</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalMover" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Actualizar Estado</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <form id="formMover">
                    <input type="hidden" name="id_tarea" id="moverIdTarea">
                    <div class="form-group">
                        <label>Selecciona el nuevo estado</label>
                        <select name="estatus" id="nuevoEstatus" class="form-control" required>
                            <option value="Nuevo"> Nuevo</option>
                            <option value="En proceso"> En proceso</option>
                            <option value="Dependencia"> Dependencia</option>
                            <option value="Finalizado"> Finalizado</option>
                        </select>
                    </div>
                    <div class="form-group d-none" id="contenedorComentarioDependencia">
                        <label class="text-danger font-weight-bold">¿Por qué está en Dependencia? (Obligatorio)</label>
                        <textarea name="comentario" id="comentarioDependencia" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button type="button" id="btnConfirmarMovimiento" class="btn btn-primary">Actualizar</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalHistorial" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Historial y Bloqueos</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div style="max-height: 220px; overflow-y: auto; display: block;">
                    <ul class="list-group list-group-flush" id="listaHistorial"></ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5>Editar Tarea</h5><button class="close" data-dismiss="modal">×</button></div>
            <div class="modal-body">
                <form id="formEditar">
                    <input type="hidden" name="id" id="editarIdTarea">
                    <div class="form-group"><label>Título</label><input type="text" name="titulo" id="editarTitulo" class="form-control" required></div>
                    <div class="form-group"><label>Descripción</label><textarea name="descripcion" id="editarDescripcion" class="form-control"></textarea></div>
                    <div class="form-group">
                    <label>Hashtags (separados por comas)</label>
                    <input type="text" name="hashtags" id="editarHashtags" class="form-control" placeholder="ej: dev, infra, urgente">
                    </div>
                    <div class="form-group">
                        <label>Prioridad</label>
                        <select name="prioridad" id="editarPrioridad" class="form-control">
                            <option value="Baja">Baja</option>
                            <option value="Media">Media</option>
                            <option value="Alta">Alta</option>
                            <option value="Critica">Critica</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Fecha límite</label><input type="date" name="fecha_limite" id="editarFechaLimite" class="form-control"></div>
                </form>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button id="btnConfirmarEdicion" class="btn btn-warning">Actualizar Cambios</button></div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="components/js/app.js"></script>
</body>
</html>