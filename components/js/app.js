let listaTareasGlobal = []; 

$(document).ready(function () {
    cargarTareas();

    // Fix: Definir la acción al guardar
    $("#guardarTarea").on("click", function () { guardarTarea(); });

    // Selector dinámico de dependencia
    $("#nuevoEstatus").on("change", function() {
        if ($(this).val() === "Dependencia") {
            $("#contenedorComentarioDependencia").removeClass("d-none");
        } else {
            // CORRECCIÓN: Ocultamos el div y reseteamos el TEXTAREA real
            $("#contenedorComentarioDependencia").addClass("d-none");
            $("#comentarioDependencia").val("");
        }
    });

    $("#btnConfirmarMovimiento").on("click", function() { ejecutarMovimiento(); });
    
    // Guardar cambios de edición
    $("#btnConfirmarEdicion").on("click", function() { ejecutarEdicion(); });

    // BUSCADOR EN TIEMPO REAL
    $("#buscadorTarea").on("input", function() { filtrarTablero(); });
});

function cargarTareas() {
    $.getJSON("components/php/obtener_tareas.php", function (tareas) {
        listaTareasGlobal = tareas;
        pintarTablero(tareas);
    });
}

function guardarTarea() {
    const titulo = $("#formNueva input[name='titulo']").val().trim();
    if(titulo === "") {
        alert("El título es obligatorio.");
        return;
    }

    $.ajax({
        url: "components/php/crear_tarea.php",
        type: "POST",
        data: $("#formNueva").serialize(),
        dataType: "json",
        success: function(res) {
            if(res.success) {
                $("#modalNueva").modal("hide");
                $("#formNueva")[0].reset(); // Limpia el formulario
                cargarTareas(); // Refresca el tablero
            } else {
                alert(res.mensaje);
            }
        },
        error: function() {
            alert("Error en el servidor al intentar crear la tarea.");
        }
    });
}

function filtrarTablero() {
    const busqueda = $("#buscadorTarea").val().toLowerCase().trim();
    if(busqueda === "") {
        pintarTablero(listaTareasGlobal);
        return;
    }
    const tareasFiltradas = listaTareasGlobal.filter(function(tarea) {
        const titulo = tarea.titulo ? tarea.titulo.toLowerCase() : "";
        const tags = tarea.tags ? tarea.tags.toLowerCase() : "";
        return titulo.includes(busqueda) || tags.includes(busqueda);
    });
    pintarTablero(tareasFiltradas);
}

function pintarTablero(tareas) {
    $("#nuevoContainer").html("");
    $("#procesoContainer").html("");
    $("#dependenciaContainer").html("");
    $("#finalizadoContainer").html("");

    let total = 0, nuevo = 0, proceso = 0, dependencia = 0, finalizado = 0;

    tareas.forEach(function (tarea) {
        total++;
        const card = crearTarjeta(tarea);
        switch (tarea.estatus) {
            case "Nuevo": $("#nuevoContainer").append(card); nuevo++; break;
            case "En proceso": $("#procesoContainer").append(card); proceso++; break;
            case "Dependencia": $("#dependenciaContainer").append(card); dependencia++; break;
            case "Finalizado": $("#finalizadoContainer").append(card); finalizado++; break;
        }
    });

    $("#total").text(total);
    $("#nuevo").text(nuevo);
    $("#proceso").text(proceso);
    $("#dependencia").text(dependencia);
    $("#finalizadas").text(finalizado);
}

function crearTarjeta(tarea) {
    let color = "secondary";
    switch (tarea.prioridad) {
        case "Baja": color = "success"; break;
        case "Media": color = "warning"; break;
        case "Alta": color = "danger"; break;
        case "Critica": color = "dark"; break;
    }

    let htmlTags = "";
    if(tarea.tags) {
        const arrayTags = tarea.tags.split(",");
        arrayTags.forEach(tag => {
            if(tag.trim() !== "") htmlTags += `<span class="badge-tag">#${tag.trim()}</span>`;
        });
    }

    let htmlBloqueo = "";
    if (tarea.estatus === "Dependencia" && tarea.ultimo_comentario) {
        htmlBloqueo = `
            <div class="alert-dependencia mt-2 mb-2">
                <strong><i class='bx bx-error-circle'></i> Motivo:</strong> ${tarea.ultimo_comentario}
            </div>
        `;
    }

    let botonEditar = "";
    if (tarea.estatus === "Nuevo") {
        botonEditar = `
            <button class="btn btn-sm btn-outline-warning" onclick="editarTarea(${tarea.id})" title="Editar tarea">
                <i class='bx bx-edit'></i>
            </button>
        `;
    }

    let fecha = tarea.fecha_limite ? tarea.fecha_limite : "Sin fecha límite";
    let descripcion = tarea.descripcion ? tarea.descripcion : "";

    return `
        <div class="card shadow-sm mb-3 tarea-card">
            <div class="card-body">
                <h5 class="font-weight-bold mb-1">${tarea.titulo}</h5>
                <div class="mb-2">${htmlTags}</div>
                <p class="mb-2 text-muted small">${descripcion}</p>
                ${htmlBloqueo}
                <span class="badge badge-${color}">${tarea.prioridad}</span>
                <hr class="my-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted"><i class='bx bx-calendar'></i> ${fecha}</small>
                    <button class="btn btn-xs p-0 text-primary small" onclick="verHistorial(${tarea.id})"><i class='bx bx-history'></i> Log</button>
                </div>
                <div class="d-flex justify-content-between">
                    <button class="btn btn-sm btn-outline-primary btn-block mr-1" onclick="abrirModalMover(${tarea.id}, '${tarea.estatus}')">
                        <i class='bx bx-transfer'></i> Mover
                    </button>
                    ${botonEditar}
                </div>
            </div>
        </div>
    `;
}

function abrirModalMover(id, estatusActual) {
    // NUEVO: Limpiamos por completo el campo de comentarios de dependencia al abrir el modal
    $("#comentarioDependencia").val(""); 
    
    $("#moverIdTarea").val(id);
    $("#nuevoEstatus").val(estatusActual).change();
    $("#modalMover").modal("show");
}

function ejecutarMovimiento() {
    const estatus = $("#nuevoEstatus").val();
    const comentario = $("#comentarioDependencia").val().trim();

    if (estatus === "Dependencia" && comentario === "") {
        alert("Por favor, justifica el motivo de la dependencia.");
        return;
    }

    $.ajax({
        url: "components/php/mover_tarea.php",
        type: "POST",
        data: $("#formMover").serialize(),
        dataType: "json",
        success: function(res) {
            if (res.success) {
                $("#modalMover").modal("hide");
                // NUEVO: Reseteamos tras el guardado exitoso
                $("#comentarioDependencia").val("");
                cargarTareas();
            } else { alert(res.mensaje); }
        }
    });
}

// Carga los datos actuales en el modal de edición
function editarTarea(id) {
    const tarea = listaTareasGlobal.find(t => t.id == id);
    if(tarea) {
        $("#editarIdTarea").val(tarea.id);
        $("#editarTitulo").val(tarea.titulo);
        $("#editarDescripcion").val(tarea.descripcion);
        $("#editarPrioridad").val(tarea.prioridad);
        $("#editarFechaLimite").val(tarea.fecha_limite);
        $("#modalEditar").modal("show");
    }
}

function ejecutarEdicion() {
    $.ajax({
        url: "components/php/editar_tarea.php",
        type: "POST",
        data: $("#formEditar").serialize(),
        dataType: "json",
        success: function(res) {
            if(res.success) {
                $("#modalEditar").modal("hide");
                cargarTareas();
            } else { alert(res.mensaje); }
        }
    });
}

function verHistorial(idTarea) {
    $("#listaHistorial").html("<li class='list-group-item text-muted'>Cargando eventos...</li>");
    $("#modalHistorial").modal("show");
    
    $.getJSON("components/php/obtener_historial.php?id=" + idTarea, function(logs) {
        $("#listaHistorial").html("");
        if(logs.length === 0) {
            $("#listaHistorial").append("<li class='list-group-item text-muted'>Sin registros aún.</li>");
            return;
        }

        for (let i = 0; i < logs.length; i++) {
            let log = logs[i];
            let fechaActual = log.fecha_registro || log.fecha;
            let mostrarAccion = log.accion;

            // Detectamos si es una acción de "Dependencia" y si el siguiente elemento es su comentario asociado (misma fecha)
            if (
                log.tipo === 'historial' && 
                log.accion.toLowerCase().includes('dependencia') && 
                i + 1 < logs.length
            ) {
                let siguienteLog = logs[i + 1];
                let siguienteFecha = siguienteLog.fecha_registro || siguienteLog.fecha;

                if (siguienteLog.tipo === 'comentario' && siguienteFecha === fechaActual) {
                    // Los unimos en una sola línea elegante
                    mostrarAccion = `${log.accion} <strong class="text-danger">💬 Comentario:</strong> <span class="text-dark">${siguienteLog.accion}</span>`;
                    i++; // Saltamos el siguiente índice para no duplicar el comentario abajo
                }
            } else if (log.tipo === 'comentario') {
                // Si es un comentario huérfano (no asociado a un cambio de estado en el mismo segundo), lo mostramos normal
                mostrarAccion = `💬 Comentario: ${log.accion}`;
            }

            $("#listaHistorial").append(`
                <li class="list-group-item small">
                    <span class="text-secondary">[${fechaActual}]</span> ${mostrarAccion}
                </li>
            `);
        }
    }).fail(function() {
        $("#listaHistorial").html("<li class='list-group-item text-danger'>Error al conectar con el servidor de logs.</li>");
    });
}