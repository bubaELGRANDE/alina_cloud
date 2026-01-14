<?php 
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$tipoBitacora = isset($_POST["tipoBit"]) ? $_POST["tipoBit"] : '';
$columnas = isset($_POST["selectMod"]) ? $_POST["selectMod"] : '';
$empleados = isset($_POST["selectEmpleado"]) ? $_POST["selectEmpleado"] : '';
$fechaInicio = date("Y-m-d",  strtotime($_POST["fechaInicio"]));
$fechaFin = date("Y-m-d",  strtotime($_POST["fechaFin"]));

switch ($tipoBitacora){
    case "bitUsuarios":
?>
<div class="table-responsive">
    <table id="tblBitacora" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow">
                <th width="1%">#</th>
                <th width="19%">Usuario</th>
                <?php 
                if (in_array("movInterfaces", $columnas)){
                    echo '<th width="20%">Interfaces</th>';
                }
                if (in_array("movInsert", $columnas)){
                    echo '<th width="20%">Ingreso</th>';
                }
                if (in_array("movUpdate", $columnas)){
                    echo '<th width="20%">Actualización</th>';
                }
                if (in_array("movDelete", $columnas)){
                    echo '<th width="20%">Eliminar</th>';
                }
                ?>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<script>
    $(document).ready(function() {
        $('#tblBitacora thead tr#filterboxrow th').each(function(index) {
            if(index==1 || index == 2 || index == 3 || index == 4 || index == 5 ) {
                var title = $('#tblBitacora thead tr#filterboxrow th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblEstudio.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });
        
        let tblEstudio = $('#tblBitacora').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableBitacoras",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "idUsers": '<?php print join(',', $empleados); ?>',
                    "columnas": '<?php print join(',', $columnas); ?>',
                    "fechaInicio": '<?php echo $fechaInicio; ?>',
                    "fechaFin": '<?php echo $fechaFin; ?>',
                    "tipoBitacora": '<?php echo $tipoBitacora; ?>'
                }
            },
            "autoWidth": false,
            /*"columns": [
                null,
                null,
                null,
                null,
                null,
                null,
                null
            ],*/
            "columnDefs": [
                { "orderable": false, "targets": [1, 2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>
<?php 
        break;
    default:
?>
<div class="table-responsive">
    <table id="tblBitacora" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow">
                <th width="1%">#</th>
                <th>Ip</th>
                <th>Correo</th>
                <th>Fecha y hora</th>
                <th>Navegador</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<script>
    $(document).ready(function() {
        $('#tblBitacora thead tr#filterboxrow th').each(function(index) {
            if(index==1 || index == 2 || index == 3 || index == 4 || index == 5 ) {
                var title = $('#tblBitacora thead tr#filterboxrow th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblEstudio.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });
        
        let tblEstudio = $('#tblBitacora').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableBitacoras",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "fechaInicio": '<?php echo $fechaInicio; ?>',
                    "fechaFin": '<?php echo $fechaFin; ?>',
                    "tipoBitacora": '<?php echo $tipoBitacora; ?>'
                }
            },
            "autoWidth": false,
            /*"columns": [
                null,
                null,
                null,
                null,
                null,
                null,
                null
            ],*/
            "columnDefs": [
                { "orderable": false, "targets": [1, 2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>
<?php } ?>
