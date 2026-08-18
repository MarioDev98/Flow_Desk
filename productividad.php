<?php
require_once 'components/php/conexion.php';

$anioSeleccionado = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');

// 1. Obtener actividad filtrando únicamente estados 'Finalizado' y 'En Dependencia'
// Obtener actividad de la base de datos filtrando solo 'Dependencia' y 'Finalizado'
$sql = "SELECT DATE(fecha_actualizacion) as fecha, COUNT(*) as cantidad 
        FROM tareas 
        WHERE YEAR(fecha_actualizacion) = :anio 
          AND estatus IN ('Dependencia', 'Finalizado')
        GROUP BY DATE(fecha_actualizacion)";

$stmt = $conexion->prepare($sql);
$stmt->execute([':anio' => $anioSeleccionado]);

$actividadBD = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $actividadBD[$row['fecha']] = intval($row['cantidad']);
}

// 2. Generar TODAS las fechas del año para formar la cuadrícula completa
$dataActividad = [];
$fechaInicio = new DateTime("$anioSeleccionado-01-01");
$fechaFin = new DateTime("$anioSeleccionado-12-31");

while ($fechaInicio <= $fechaFin) {
    $fechaStr = $fechaInicio->format('Y-m-d');
    $cantidad = isset($actividadBD[$fechaStr]) ? $actividadBD[$fechaStr] : 0;
    $dataActividad[] = [$fechaStr, $cantidad];
    $fechaInicio->modify('+1 day');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productividad - MARIO M.</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- ECharts CDN -->
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <style>
        .calendar-card {
            background-color: #ffffff;
            border: 1px solid #d0d7de;
            border-radius: 8px;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-light bg-white shadow-sm mb-4">
    <a class="navbar-brand" href="index.php">
        <i class='bx bx-arrow-back'></i> Volver al Tablero
    </a>
    <span class="navbar-text font-weight-bold">
        <i class='bx bx-line-chart'></i> Métrica de Productividad 
    </span>
</nav>

<div class="container-fluid">
    <div class="card calendar-card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="card-title m-0 font-weight-bold" style="color: #24292e;">Actividad del Autor: MARIO M.</h4>
                <form method="GET" class="form-inline">
                    <label class="mr-2 font-weight-bold">Año:</label>
                    <select name="anio" class="form-control form-control-sm" onchange="this.form.submit()">
                        <?php for ($y = date('Y'); $y >= 2024; $y--): ?>
                            <option value="<?= $y ?>" <?= $y == $anioSeleccionado ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </form>
            </div>
            <!-- Contenedor del Heatmap -->
            <div id="githubCalendar" style="width: 100%; height: 230px;"></div>
        </div>
    </div>
</div>

<script>
    var chartDom = document.getElementById('githubCalendar');
    var myChart = echarts.init(chartDom);

    var dataActividad = <?= json_encode($dataActividad) ?>;

    var option = {
        tooltip: {
            position: 'top',
            padding: [6, 10],
            backgroundColor: '#24292e',
            textStyle: { color: '#fff', fontSize: 12 },
            formatter: function (p) {
                return p.data[0] + ' : <strong>' + p.data[1] + '</strong> tareas (Finalizadas / Dependencia)';
            }
        },
        visualMap: {
            min: 0,
            max: 10,
            type: 'piecewise',
            orient: 'horizontal',
            left: 'right',
            top: 0,
            itemWidth: 11,
            itemHeight: 11,
            textStyle: { fontSize: 12, color: '#57606a' },
            pieces: [
                { value: 0, color: '#ebedf0', label: 'Sin actividad' },
                { min: 1, max: 2, color: '#9be9a8', label: '1-2' },
                { min: 3, max: 5, color: '#40c463', label: '3-5' },
                { min: 6, max: 8, color: '#30a14e', label: '6-8' },
                { min: 9, color: '#216e39', label: '9+' }
            ]
        },
        calendar: {
            top: 50,
            left: 40,
            right: 20,
            cellSize: ['auto', 13],
            range: '<?= $anioSeleccionado ?>',
            itemStyle: {
                color: '#ebedf0',
                borderWidth: 2,
                borderColor: '#ffffff'
            },
            splitLine: {
                show: true,
                lineStyle: {
                    color: '#24292e',
                    width: 2.5,
                    type: 'solid'
                }
            },
            yearLabel: { show: false },
            monthLabel: {
                fontSize: 12,
                color: '#24292e',
                fontWeight: 'bold'
            },
            dayLabel: {
                firstDay: 1,
                nameMap: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
                fontSize: 10,
                color: '#57606a'
            }
        },
        series: [{
            type: 'heatmap',
            coordinateSystem: 'calendar',
            data: dataActividad,
            itemStyle: {
                borderWidth: 2,
                borderColor: '#ffffff'
            }
        }]
    };

    myChart.setOption(option);

    window.addEventListener('resize', function() {
        myChart.resize();
    });
</script>

</body>
</html>