<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();
?>
<hr>
<table id="tblJefaturaEmpleados" class="table table-hover" style="width: 100%;">
    <thead>
        <tr id="filterboxrow-empleados">
            <th>#</th>
            <th>Empleado</th>
            <th>¿Empleado a cargo?</th>
            <th>¿Heredar empleados?</th>
            <th>Acciones</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php
            $dataEmpleadosACargo = $cloud->rows("
                SELECT
                    expedienteJefaturaId,
                    prsExpedienteId,
                    flgHeredarPersonal,
                    flgJefeInmediato
                FROM th_expediente_jefaturas
                WHERE jefeId = ? AND flgDelete = ?
            ", [$_POST['id'], 0]);
            $n = 0;
            foreach ($dataEmpleadosACargo as $dataEmpleadosACargo) {
            	$n += 1;

                $dataExpedienteEmpleado = $cloud->row("
                    SELECT 
                        exp.estadoExpediente as estadoExpediente,
                        per.estadoPersona as estadoPersona,
                        CONCAT(
                            IFNULL(per.apellido1, '-'),
                            ' ',
                            IFNULL(per.apellido2, '-'),
                            ', ',
                            IFNULL(per.nombre1, '-'),
                            ' ',
                            IFNULL(per.nombre2, '-')
                        ) AS nombreCompleto,
                        car.cargoPersona as cargoPersona,
                        dep.departamentoSucursal as departamentoSucursal,
                        s.sucursal as sucursal
                    FROM th_expediente_personas exp
                    JOIN th_personas per ON per.personaId = exp.personaId
                    JOIN cat_personas_cargos car ON car.prsCargoId = exp.prsCargoId
                    JOIN cat_sucursales_departamentos dep ON dep.sucursalDepartamentoId = exp.sucursalDepartamentoId
                    JOIN cat_sucursales s ON s.sucursalId = dep.sucursalId
                    WHERE exp.prsExpedienteId = ? AND exp.flgDelete = ?
                    ORDER BY per.apellido1, per.apellido2, per.nombre1, per.nombre2
                    LIMIT 1
                ",[$dataEmpleadosACargo->prsExpedienteId, 0]);

                $empleadoACargo = '
                    <b><i class="fas fa-user-tie"></i> Jefe: </b>'.$dataExpedienteEmpleado->nombreCompleto.'<br>
                    <b><i class="fas fa-briefcase"></i> Cargo:</b> '.$dataExpedienteEmpleado->cargoPersona.'<br>
                    <b><i class="fas fa-building"></i> Sucursal:</b> '.$dataExpedienteEmpleado->sucursal.'<br>
                    <b><i class="fas fa-building"></i> Departamento:</b> '.$dataExpedienteEmpleado->departamentoSucursal.'
                ';

                $checkedInmediato = "";
                if($dataEmpleadosACargo->flgJefeInmediato == "Sí") {
                    $checkedInmediato = "checked";
                } else {
                    // Sin mostrar el switch
                }
                
                $jefeInmediato = '
                    <div class="d-flex">
                        <label class="form-check-label me-2" for="flgJefeInmediato'.$dataEmpleadosACargo->expedienteJefaturaId.'">No</label>
                            <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="flgJefeInmediato'.$dataEmpleadosACargo->expedienteJefaturaId.'" name="flgJefeInmediato" onclick="jefeInmediato(`'.$dataEmpleadosACargo->expedienteJefaturaId.'`, `'.$dataEmpleadosACargo->flgJefeInmediato.'`);" '.$checkedInmediato.' />
                            </div>
                        <label class="form-check-label" for="flgJefeInmediato'.$dataEmpleadosACargo->expedienteJefaturaId.'">Si</label>
                    </div>
                ';

                $checkedHeredar = "";
                if($dataEmpleadosACargo->flgHeredarPersonal == "Sí") {
                    $checkedHeredar = "checked";
                } else {
                    // Sin mostrar el switch
                }

                $numACargo = $cloud->count("
                    SELECT expedienteJefaturaId FROM th_expediente_jefaturas
                    WHERE jefeId = ? AND flgDelete = ?
                ", [$dataEmpleadosACargo->prsExpedienteId, 0]);

                $heredar = '
                    <div class="d-flex">
                        <label class="form-check-label me-2" for="flgHeredar'.$dataEmpleadosACargo->expedienteJefaturaId.'">No</label>
                            <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="flgHeredar'.$dataEmpleadosACargo->expedienteJefaturaId.'" name="flgHeredar" onclick="heredarEmpleados(`'.$dataEmpleadosACargo->expedienteJefaturaId.'`, `'.$dataEmpleadosACargo->flgHeredarPersonal.'`);" '.$checkedHeredar.' />
                            </div>
                        <label class="form-check-label" for="flgHeredar'.$dataEmpleadosACargo->expedienteJefaturaId.'">Si</label>
                    </div>
                    <b>Empleados a cargo: </b> '.$numACargo.'
                ';

        	    $controles = '';

                $controles .= '
                    <button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarJefatura(`'.$dataEmpleadosACargo->prsExpedienteId.'`, `empleado`, `'.$_POST['id'].'`);">
                        <i class="fas fa-trash-alt"></i>
                        <span class="ttiptext">Eliminar</span>
                    </button>
                ';

                echo '
                    <tr>
                        <input type="hidden" id="hiddenPrsExpedienteId'.$n.'" name="hiddenPrsExpedienteId" value="'.$dataEmpleadosACargo->prsExpedienteId.'">
                        <td id="td'.$n.'" class="showRow" style="cursor: pointer;">'.$n.'</td>
                        <td class="showRow" style="cursor: pointer;">'.$empleadoACargo.'</td>
                        <td>'.$jefeInmediato.'</td>
                        <td>'.$heredar.'</td>
                        <td>'.$controles.'</td>
                        <td id="tdChevron'.$n.'" class="showRow" style="cursor: pointer;">
                            <i class="fas fa-chevron-down"></i>
                        </td>
                    </tr>
                    <tr class="extra-row" style="display: none;">
                        <td></td>
                        <td id="tdExtraRow'.$n.'"></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                ';
        	} // foreach

            if($n == 0) {
                echo '<td></td><td>No se encontraron registros...</td><td></td><td></td><td></td><td></td>';
            } else {
                // Se dibujo la tabla
            }
        ?>
    </tbody>
</table>
<script>
    $(document).ready(function() {
        //$("[id^='trCollapse']").hide();

        $('#tblJefaturaEmpleados thead tr#filterboxrow-empleados th').each(function(index) {
            if(index==1) {
                var title = $('#tblJefaturaEmpleados thead tr#filterboxrow-empleados th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-empleados" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}-empleados">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblJefaturaEmpleados.column($(this).index()).search($(`#input${$(this).index()}-empleados`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).update();
                });
            } else {
            }
        });

        // Agregar evento de clic a las filas existentes
        $('#tblJefaturaEmpleados tbody').on('click', '.showRow', function() {
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

        let tblJefaturaEmpleados = $('#tblJefaturaEmpleados').DataTable({
            "dom": 'lrtip',
            "bSort": false, // para respetar el order by de la consulta
            "autoWidth": false,
            "columns": [
                null,
                null,
                null,
                null,
                null,
                {"width": "5%"}
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2, 3, 4, 5] },
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>