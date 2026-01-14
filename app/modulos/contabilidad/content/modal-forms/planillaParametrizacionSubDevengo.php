<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /*
        POST:
        catPlanillaDevengoId
        catPlanillaDevengoIdSuperior
        tituloModal
        nombreDevengo
        tipoDevengo
        tblDevengo
    */
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="parametrizacion-devengo">
<input type="hidden" id="catPlanillaDevengoId" name="catPlanillaDevengoId" value="<?php echo $_POST['catPlanillaDevengoId']; ?>">
<input type="hidden" id="catPlanillaDevengoIdSuperior" name="catPlanillaDevengoIdSuperior" value="<?php echo $_POST['catPlanillaDevengoIdSuperior']; ?>">
<input type="hidden" id="tipoDevengo" name="tipoDevengo" value="<?php echo $_POST['tipoDevengo']; ?>">
<div id="divFormulario">
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="form-outline">
                <i class="fas fa-money-check-alt trailing"></i>
                <input type="text" id="nombreDevengo" class="form-control" name="nombreDevengo" required />
                <label class="form-label" for="nombreDevengo">Nombre del devengo</label>
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
        <button type="button" id="btnCancelarSubdevengo" class="btn btn-secondary">
            <i class="fas fa-times-circle"></i> Cancelar
        </button>
    </div>
</div>
<div id="divBtnFormulario" class="text-end">
    <button type="button" id="btnSubdevengo" class="btn btn-primary ttip">
        <i class="fas fa-plus-circle"></i> Nuevo Subdevengo
        <span class="ttiptext">Agregar un nuevo subdevengo</span>
    </button>
</div>
<div class="table-responsive">
    <table id="tblSubDevengo" class="table table-hover">
        <thead>
            <tr id="filterboxrow-subdevengo">
                <th>#</th>
                <th>Subdevengo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<script>
    function editarSubdevengo(frmData) {
        $("#catPlanillaDevengoId").val(frmData.catPlanillaDevengoId);
        $("#nombreDevengo").val(frmData.nombreDevengo);
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

        $("#btnSubdevengo").click(function(e) {
            $("#divFormulario").show();
            $("#divBtnFormulario").hide();
            $("#typeOperation").val("insert");
        });

        $("#btnCancelarSubdevengo").click(function(e) {
            $("#divFormulario").hide();
            $("#divBtnFormulario").show();
            $("#frmModal")[0].reset();
            $("#typeOperation").val("insert");
        });

        // Tabla: SubDevengos
        $('#tblSubDevengo thead tr#filterboxrow-subdevengo th').each(function(index) {
            if(index == 1) {
                var title = $('#tblSubDevengo thead tr#filterboxrow-subdevengo th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}subdevengo" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}comisiones-cero">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblSubDevengo.column($(this).index()).search($(`#input${$(this).index()}subdevengo`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });
            
        let tblSubDevengo = $('#tblSubDevengo').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tablePlanillaParametrizacionSubDevengos",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "catPlanillaDevengoIdSuperior": '<?php echo $_POST["catPlanillaDevengoIdSuperior"]; ?>',
                    "tipoDevengo": '<?php echo $_POST["tipoDevengo"]; ?>',
                    "tblDevengo": '<?php echo $_POST['tblDevengo']; ?>'
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
                                `Subdevengo ${$("#typeOperation").val() == 'insert' ? 'agregado' : 'actualizado'} con éxito.`,
                                "success"
                            );
                            $(`#<?php echo $_POST['tblDevengo']; ?>`).DataTable().ajax.reload(null, false);
                            $(`#tblSubDevengo`).DataTable().ajax.reload(null, false);
                            $("#btnCancelarSubdevengo").trigger('click');
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