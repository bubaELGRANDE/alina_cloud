<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /*
        POST:
        catPlanillaDescuentoId
        catPlanillaDescuentoIdSuperior
        tituloModal
        nombreDescuento
    */
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="parametrizacion-descuento">
<input type="hidden" id="catPlanillaDescuentoId" name="catPlanillaDescuentoId" value="<?php echo $_POST['catPlanillaDescuentoId']; ?>">
<input type="hidden" id="catPlanillaDescuentoIdSuperior" name="catPlanillaDescuentoIdSuperior" value="<?php echo $_POST['catPlanillaDescuentoIdSuperior']; ?>">
<div id="divFormulario">
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="form-outline">
                <i class="fas fa-money-check-alt trailing"></i>
                <input type="text" id="nombreDescuento" class="form-control" name="nombreDescuento" required />
                <label class="form-label" for="nombreDescuento">Nombre del descuento</label>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-outline">
                <i class="fas fa-list-ol trailing"></i>
                <input type="text" id="codigoContable" class="form-control" name="codigoContable" required />
                <label class="form-label" for="codigoContable">Código contable</label>
            </div>
        </div>
    </div>
    <div class="text-end">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Guardar
        </button>
        <button type="button" id="btnCancelarSubdescuento" class="btn btn-secondary">
            <i class="fas fa-times-circle"></i> Cancelar
        </button>
    </div>
</div>
<div id="divBtnFormulario" class="text-end">
    <button type="button" id="btnSubdescuento" class="btn btn-primary ttip">
        <i class="fas fa-plus-circle"></i> Nuevo Subdescuento
        <span class="ttiptext">Agregar un nuevo subdescuento</span>
    </button>
</div>
<div class="table-responsive">
    <table id="tblSubDescuento" class="table table-hover">
        <thead>
            <tr id="filterboxrow-subdescuento">
                <th>#</th>
                <th>Subdescuento</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<script>
    function editarSubdescuento(frmData) {
        $("#catPlanillaDescuentoId").val(frmData.catPlanillaDescuentoId);
        $("#nombreDescuento").val(frmData.nombreDescuento);
        $("#codigoContable").val(frmData.codigoContable);
        $("#typeOperation").val("update");
        $("#divFormulario").show();
        $("#divBtnFormulario").hide();
        document.querySelectorAll('.form-outline').forEach((formOutline) => {
            new mdb.Input(formOutline).update();
        });
    }

    $(document).ready(function() {
        $("#divFormulario").hide();

        $("#btnSubdescuento").click(function(e) {
            $("#divFormulario").show();
            $("#divBtnFormulario").hide();
            $("#typeOperation").val("insert");
        });

        $("#btnCancelarSubdescuento").click(function(e) {
            $("#divFormulario").hide();
            $("#divBtnFormulario").show();
            $("#frmModal")[0].reset();
            $("#typeOperation").val("insert");
        });

        // Tabla: SubDescuentos
        $('#tblSubDescuento thead tr#filterboxrow-subdescuento th').each(function(index) {
            if(index == 1) {
                var title = $('#tblSubDescuento thead tr#filterboxrow-subdescuento th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}subdescuento" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}comisiones-cero">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblSubDevengo.column($(this).index()).search($(`#input${$(this).index()}subdescuentos`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });
            
        let tblSubDescuento = $('#tblSubDescuento').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tablePlanillaParametrizacionSubDescuentos",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "catPlanillaDescuentoIdSuperior": '<?php echo $_POST["catPlanillaDescuentoIdSuperior"]; ?>'
                }
            },
            "autoWidth": false,
            "columns": [
                {"width": "5%"},
                null,
                {"width": "30%"}
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                `Subdescuento ${$("#typeOperation").val() == 'insert' ? 'agregado' : 'actualizado'} con éxito.`,
                                "success"
                            );
                            $(`#tblOtrosDescuentos`).DataTable().ajax.reload(null, false);
                            $(`#tblSubDescuento`).DataTable().ajax.reload(null, false);
                            $("#btnCancelarSubdescuento").trigger('click');
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
    });
</script>