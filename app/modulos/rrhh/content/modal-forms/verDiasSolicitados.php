<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();                    
?>
<div class="col-md-12">
    <div class="table-responsive">
        <table id="tblVacacion" class="table table-hover" style="width: 100%;">
            <thead>
                <tr id="filterboxrow-vacaciones">
                    <th>#</th>
                    <th>Vacaciones</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $dataVaca = $cloud->rows("
                    SELECT
                        CONCAT(
                            IFNULL(per.apellido1, '-'),
                            ' ',
                            IFNULL(per.apellido2, '-'),
                            ', ',
                            IFNULL(per.nombre1, '-'),
                            ' ',
                            IFNULL(per.nombre2, '-')
                        ) AS nombreCompleto,
                        date_format(vaca.fhSolicitud, '%d-%m-%Y') AS fhSolicitud,
                        vaca.periodoVacaciones,
                        vaca.numDias,
                        date_format(vaca.fechaInicio, '%d-%m-%Y') AS fechaInicio,
                        date_format(vaca.fechaFin, '%d-%m-%Y') AS fechaFin,
                        date_format(vaca.fhaprobacion, '%d-%m-%Y') AS fhaprobacion
                        FROM ((th_expedientes_vacaciones vaca
                        JOIN th_expediente_personas exp ON vaca.expedienteId = exp.prsExpedienteId)
                        JOIN th_personas per ON per.personaId = exp.personaId)
                        WHERE vaca.flgDelete = 0 AND vaca.estadoSolicitud = 'Aprobado' AND vaca.expedienteId = ?
                        ORDER BY apellido1, apellido2, nombre1, nombre2
                    ", [$_POST['prsExpedienteId']]);

                    $n = 0;
                    foreach ($dataVaca as $vacaciones) {
                        $n += 1;
                        echo "
                        <tr>
                          <td>$n</td>
                          <td>
                            <i class='fas fa-user'></i>     <b>Empleado:</b> $vacaciones->nombreCompleto  <br>
                            <i class='fas fa-calendar'></i> <b>Fecha de solicitud:</b> $vacaciones->fhSolicitud <br>
                            <i class='fas fa-calendar'></i> <b>Fecha de aprobación:</b> $vacaciones->fhaprobacion <br>
                            <i class='fas fa-calendar'></i> <b>Tipo de vacación:</b> $vacaciones->periodoVacaciones
                          </td>
                          <td>
                          <i class='fas fa-umbrella-beach'></i> <b>Número de días:</b> $vacaciones->numDias <br>
                          <i class='fas fa-calendar'></i> <b>Fecha de inicio:</b> $vacaciones->fechaInicio <br>
                          <b><i class='fas fa-calendar'></i> Fecha de finalización:</b> $vacaciones->fechaFin
                          </td>
                        </tr>
                    ";
                    }
                ?>
            </tbody>
        </table>
    </div>
</div>
<script>
     $(document).ready(function() {
        //Tab: Vacaciones
        $('#tblVacacion thead tr#filterboxrow-vacaciones th').each(function(index) {
            if(index == 1 || index == 2) {
                var title = $('#tblVacacion thead tr#filterboxrow-vacaciones th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}vacaciones" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}vacaciones">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblVacacion.column($(this).index()).search($(`#input${$(this).index()}vacaciones`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });

        let tblVacacion = $('#tblVacacion').DataTable({
            "dom": 'lrtip',
            "autoWidth": false,
            "columns": [
                {"width": "5%"},
                null,
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1,2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

    });   
</script>