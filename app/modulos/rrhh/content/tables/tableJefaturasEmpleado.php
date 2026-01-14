<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    // Función para eliminar registros duplicados basados en el "id"
    function eliminarDuplicados($array, $clave) {
        $tempArray = array();
        $resultado = array();

        foreach ($array as $elemento) {
            if (!isset($tempArray[$elemento[$clave]])) {
                $tempArray[$elemento[$clave]] = true;
                $resultado[] = $elemento;
            }
        }

        return $resultado;
    }
?>
<hr>
<table id="tblJefaturasEmpleado" class="table table-hover" style="width: 100%;">
    <thead>
        <tr id="filterboxrow-jefes">
            <th>#</th>
            <th>Jefatura</th>
            <th>¿Jefe inmediato?</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
            $dataJefesEmpleado = $cloud->rows("
                SELECT
                    jef.expedienteJefaturaId AS expedienteJefaturaId,
                    jef.jefeId AS jefeId,
                    jef.flgJefeInmediato AS flgJefeInmediato,
                    CONCAT(
                        IFNULL(pers.apellido1, '-'),
                        ' ',
                        IFNULL(pers.apellido2, '-'),
                        ', ',
                        IFNULL(pers.nombre1, '-'),
                        ' ',
                        IFNULL(pers.nombre2, '-')
                    ) AS nombreCompleto,
                    car.cargoPersona as cargoPersona,
                    dep.departamentoSucursal as departamentoSucursal,
                    s.sucursal as sucursal
                FROM th_expediente_jefaturas jef
                LEFT JOIN th_expediente_personas exp ON exp.prsExpedienteId = jef.jefeId
                LEFT JOIN th_personas pers ON pers.personaId = exp.personaId
                LEFT JOIN cat_personas_cargos car ON car.prsCargoId = exp.prsCargoId
                LEFT JOIN cat_sucursales_departamentos dep ON dep.sucursalDepartamentoId = exp.sucursalDepartamentoId
                LEFT JOIN cat_sucursales s ON s.sucursalId = dep.sucursalId
                WHERE jef.prsExpedienteId = ? AND jef.flgDelete = ?
            ", [$_POST['id'], 0]);
            $n = 0;
            $jefaturasEmpleado = array();
            foreach ($dataJefesEmpleado as $dataJefesEmpleado) {
                $n += 1;
                $prsExpedienteId = $dataJefesEmpleado->jefeId;

                $checkedInmediato = "";
                if($dataJefesEmpleado->flgJefeInmediato == "Sí") {
                    $checkedInmediato = "checked";
                } else {
                    // Sin mostrar el switch
                }

                $jefaturasEmpleado[] = array(
                    "n"                     => $n,
                    "jefatura"              => '
                        <b><i class="fas fa-user-tie"></i> Jefe: </b>'.$dataJefesEmpleado->nombreCompleto.'<br>
                        <b><i class="fas fa-briefcase"></i> Cargo:</b> '.$dataJefesEmpleado->cargoPersona.'<br>
                        <b><i class="fas fa-building"></i> Sucursal:</b> '.$dataJefesEmpleado->sucursal.'<br>
                        <b><i class="fas fa-building"></i> Departamento:</b> '.$dataJefesEmpleado->departamentoSucursal.'
                    ',
                    "jefeInmediato"         => '
                        <div class="d-flex">
                            <label class="form-check-label me-2" for="flgJefeInmediato'.$dataJefesEmpleado->expedienteJefaturaId.'">No</label>
                                <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="flgJefeInmediato'.$dataJefesEmpleado->expedienteJefaturaId.'" name="flgJefeInmediato" onclick="jefeInmediato(`'.$dataJefesEmpleado->expedienteJefaturaId.'`, `'.$dataJefesEmpleado->flgJefeInmediato.'`);" '.$checkedInmediato.' />
                                </div>
                            <label class="form-check-label" for="flgJefeInmediato'.$dataJefesEmpleado->expedienteJefaturaId.'">Si</label>
                        </div>
                    ',
                    "acciones"              => '
                        <button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarJefatura(`'.$_POST['id'].'`, `empleado`, `'.$dataJefesEmpleado->jefeId.'`);">
                            <i class="fas fa-trash-alt"></i>
                            <span class="ttiptext">Eliminar</span>
                        </button>
                    ',
                    "chevron"               => 'Flecha',
                    "prsExpedienteId"       => $prsExpedienteId
                );

                $flgRepetir = true;
                $numJefe = 1;
                // Buscar jefes de los jefes que hereden personal
                while($flgRepetir) {
                    // En la primera vuelta no se hereda, ya que es el jefe del empleado para verificar
                    // Si tiene otro jefe y en la siguiente vuelta se heredarán
                    $whereHeredar = ($numJefe == 1 ? '' : " AND jef.flgHeredarPersonal = 'Sí' ");

                    $sqlJefe = "
                        SELECT
                            jef.jefeId AS jefeId,
                            CONCAT(
                                IFNULL(pers.apellido1, '-'),
                                ' ',
                                IFNULL(pers.apellido2, '-'),
                                ', ',
                                IFNULL(pers.nombre1, '-'),
                                ' ',
                                IFNULL(pers.nombre2, '-')
                            ) AS nombreCompleto,
                            car.cargoPersona as cargoPersona,
                            dep.departamentoSucursal as departamentoSucursal,
                            s.sucursal as sucursal
                        FROM th_expediente_jefaturas jef
                        LEFT JOIN th_expediente_personas exp ON exp.prsExpedienteId = jef.jefeId
                        LEFT JOIN th_personas pers ON pers.personaId = exp.personaId
                        LEFT JOIN cat_personas_cargos car ON car.prsCargoId = exp.prsCargoId
                        LEFT JOIN cat_sucursales_departamentos dep ON dep.sucursalDepartamentoId = exp.sucursalDepartamentoId
                        LEFT JOIN cat_sucursales s ON s.sucursalId = dep.sucursalId
                        WHERE jef.prsExpedienteId = ? AND jef.flgDelete = ? $whereHeredar
                    ";
                    $existeJefeHereda = $cloud->count($sqlJefe, [$prsExpedienteId, 0]);

                    // Verificar si se encontró un jefe para el empleado actual
                    if($existeJefeHereda > 0) {
                        $n += 1;
                        $dataJefeHereda = $cloud->row($sqlJefe, [$prsExpedienteId, 0]);

                        $jefaturasEmpleado[] = array(
                            "n"                     => $n,
                            "jefatura"              => '
                                <b><i class="fas fa-user-tie"></i> Jefe: </b>'.$dataJefeHereda->nombreCompleto.'<br>
                                <b><i class="fas fa-briefcase"></i> Cargo:</b> '.$dataJefeHereda->cargoPersona.'<br>
                                <b><i class="fas fa-building"></i> Sucursal:</b> '.$dataJefeHereda->sucursal.'<br>
                                <b><i class="fas fa-building"></i> Departamento:</b> '.$dataJefeHereda->departamentoSucursal.'
                            ',
                            "jefeInmediato"         => '
                                <b>Jefatura heredada de: </b> '.$dataJefesEmpleado->nombreCompleto.'
                            ',
                            "acciones"              => '',
                            "prsExpedienteId"       => $dataJefeHereda->jefeId
                        );
                        $prsExpedienteId = $dataJefeHereda->jefeId;
                    } else {
                        // No se encontró un jefe, por lo que se termina el bucle
                        $flgRepetir = false;
                    }
                    $numJefe += 1;
                }
            }

            if(count($jefaturasEmpleado) == 0) {
                echo '<td></td><td>No se encontraron registros...</td><td></td><td></td>';
            } else {
                // Se dibujará la tabla
                // Por si las jefaturas se vuelven una cadena interminable heredando por todos lados
                $jefaturasEmpleado = eliminarDuplicados($jefaturasEmpleado, 'prsExpedienteId');
                foreach ($jefaturasEmpleado as $jefaturasEmpleado) {
                    echo '
                        <tr>
                            <td>'.$jefaturasEmpleado["n"].'</td>
                            <td>'.$jefaturasEmpleado["jefatura"].'</td>
                            <td>'.$jefaturasEmpleado["jefeInmediato"].'</td>
                            <td>'.$jefaturasEmpleado["acciones"].'</td>
                        </tr>
                    ';
                }
            }
        ?>
    </tbody>
</table>
<script>
    $(document).ready(function() {
        //$("[id^='trCollapse']").hide();

        $('#tblJefaturasEmpleado thead tr#filterboxrow-jefes th').each(function(index) {
            if(index==1) {
                var title = $('#tblJefaturasEmpleado thead tr#filterboxrow-jefes th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-jefes" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}-jefes">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblJefaturasEmpleado.column($(this).index()).search($(`#input${$(this).index()}-jefes`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).update();
                });
            } else {
            }
        });

        // Agregar evento de clic a las filas existentes
        $('#tblJefaturasEmpleado tbody').on('click', '.showRow', function() {
            let filaActual = $(this).closest('tr');

            // Obtener la fila siguiente a la fila clicada
            let nextRow = filaActual.next();

            // Obtener el valor de una columna específica en la fila siguiente
            let n = filaActual.find('td').eq(0).text();

            // Obtener el id de la persona que tiene empleados a cargo
            let jefeId = filaActual.find('input[type="hidden"]').val();

            // Cambio de flechas
            if(nextRow.is(":visible")) {
                $(`#tdChevron${n}`).html(`<i class="fas fa-chevron-down"></i>`);

                // Limpiar la data
                $(`#tdExtraRow${n}`).html('');
            } else {
                $(`#tdChevron${n}`).html(`<i class="fas fa-chevron-up"></i>`);

                // Cargar los empleados a cargo
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>content/divs/rowEmpleadosACargo", 
                    {
                        jefeId: jefeId
                    },
                    function(data) {
                        $(`#tdExtraRow${n}`).html(data);
                    }
                );
            }

            // Mostrar u ocultar la fila siguiente
            nextRow.toggle();
        });

        let tblJefaturasEmpleado = $('#tblJefaturasEmpleado').DataTable({
            "dom": 'lrtip',
            "bSort": false, // para respetar el order by de la consulta
            "autoWidth": false,
            "columns": [
                null,
                null,
                {"width": "20%"},
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2, 3] },
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>