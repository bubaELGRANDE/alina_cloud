<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="bodegas-empleado">
<input type="hidden" name="personaId" id="personaId" value="<?php echo $_POST['personaId']; ?>">
<div id="bodegas" class="mb-4">
    <div class="row justify-content-md-center">
        <div class="col-6">
            <div class="form-select-control mb-4">
                <select class="form-select bodegas" id="bodegasSelect" name="bodegas[]" style="width: 100%;" multiple="multiple" required>
                    <option></option>
                </select>
            </div>
        </div> 
        <div class="col-6">
            <button type="submit" id="agregar" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Agregar</button>
        </div>
    </div>
</div>
<h5>Sucursal(es)</h5>
<hr>
<div class="table-responsive">
    <table id="tblBodegas" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow">
                <th>#</th>
                <th>Sucursal</th>
                <th>Bodega</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script>
    function cargarSelectBodegas() {
        asyncSelect(
            `<?php echo $_SESSION['currentRoute']; ?>content/divs/selectBodegasEmpleados`,
            {
                personaId: '<?php echo $_POST["personaId"]; ?>'
            },
            `bodegasSelect`,
            function() {}
        );
    }
    function eliminarBodegaPersona(frmData) {
        mensaje_confirmacion(
            `¿Está seguro que desea eliminar la bodega?`, 
            `Ya no podrá seleccionarse en otras operaciones.`, 
            `warning`, 
            function(param) {
                asyncData(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                    frmData,
                    function(data) {
                        if(data == "success") {
                            mensaje_do_aceptar(`Operación completada:`, `Bodega del empleado eliminada con éxito`, `success`, function() {
                                $("#tblBodegas").DataTable().ajax.reload(null, false);
                                $("#tblPersonaSucursal").DataTable().ajax.reload(null, false);
                                cargarSelectBodegas();
                            });
                        } else {
                            mensaje(
                                "Aviso:",
                                data,
                                "warning"
                            );
                        }
                    }
                );
            },
            `Sí, eliminar`,
            `Cancelar`
        );
    }

    $(document).ready(function() {
        $("#modal-container").modal("hide");
        cargarSelectBodegas();

        $("#bodegasSelect").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Bodega(s)'
        });
        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                'Se ha agregado con éxito la bodega al empleado.',
                                "success"
                            );
                            $('#tblPersonaSucursal').DataTable().ajax.reload(null, false);
                            $('#tblBodegas').DataTable().ajax.reload(null, false);
                            $('#bodegasSelect').val([]).trigger('change');
                            cargarSelectBodegas();
                        } else {
                            mensaje(
                                "Aviso:",
                                data,
                                "warning"
                            );
                        }
                    }
                );
            }
        });
        let tblEstudio = $('#tblBodegas').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableBodegasEmpleados",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "personaId": '<?php echo $_POST["personaId"]; ?>'
                }
            },
            "autoWidth": false,
            "columns": [
                {"width": "5%"},
                null,
                null,
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });    
</script>
