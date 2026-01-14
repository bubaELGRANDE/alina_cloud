<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="permisos-DTE">
<input type="hidden" name="personaSucursalId" id="personaSucursalId" value="<?php echo $_POST['personaSucursalId']; ?>">

<div class="row">
    <div class="col text-end">
        <div class="col-md-12 text-end">
            <button type="button" id="btnPermiso" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Nuevo permiso</button>
        </div>
    </div>
</div>
<div id="agregarPermisoDTE" style="display:none;">
    <div class="form-select-control mb-4">
        <select id="tipoDTE" name="tipoDTE[]" multiple="multiple" style="width: 100%;" required>
            <option></option>
            <?php
                $tipoDTE = $cloud->rows("
                    SELECT 
                        tipoDTEId,
                        codigoMH,
                        tipoDTE
                    FROM mh_002_tipo_dte
                    WHERE flgDelete = 0;
                ");
                foreach($tipoDTE as $tipoDTE){
                    echo '<option value='.$tipoDTE->tipoDTEId.'> ('.$tipoDTE->codigoMH.') ' .$tipoDTE->tipoDTE.'</option>';
                }
            ?>
        </select>
    </div>

    <div class="row mb-4">
        <div class="col text-end">
            <div class="col-md-12 text-end">
                <button type="submit" id="submit" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Agregar</button>
                <button type="button" id="cancelarPermiso" class="btn btn-secondary"><i class="fas fa-times-circle"></i> Cancelar</button>
            </div>
        </div>
    </div>

</div>
<div class="row mt-4">
    <div class="col-md-6">
        <p><b><i class="fas fa-building"></i> Sucursal: </b> <?php echo $_POST['sucursal']?></p>
    </div>
    <div class="col-md-6">
        <p><b><i class="fas fa-user-tie"></i> Nombre: </b> <?php echo $_POST['nombrePersona']?></p>
    </div>
</div>

<div class="tab-content mt-3" id="ntab-content">
    <div class="tab-pane fade show active" id="ntab-content-1" role="tabpanel" aria-labelledby="ntab-1">  
        <table id="tblPermisosDTE" class="table table-hover" style="width: 100%;">
            <thead>
                <tr id="filterboxrow">
                    <th>#</th>
                    <th>Tipo DTE</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
<script>
    function eliminarPermisoDTE (frmData){
        mensaje_confirmacion(
            `¿Esta seguro que desea eliminar el permiso?`,
            `Se eliminará del catálogo.`,
            `warning`,
            (param) => {
                asyncData(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation/',
                    frmData,
                    (data) => {
                        if (data=="success") {
                            mensaje_do_aceptar(
                                `Operación completada`,
                                `Permiso eliminado con éxito`,
                                `success`,
                                () => {
                                $(`#tblPermisosDTE`).DataTable().ajax.reload(null,false);
                            });
                        }else{
                            mensaje(
                                "Aviso:",
                                data,
                                "warning"
                            );
                        }
                    }
                );
            },
            `Eliminar`,
            `Cancelar`
        )
    }
    $(document).ready(function() {
        $("#btnPermiso").click(function(e){
            $("#agregarPermisoDTE").toggle();
            $("#btnPermiso").toggle();
        });
        $("#cancelarPermiso").click(function(e){
            $("#agregarPermisoDTE").toggle();
            $("#btnPermiso").toggle();

            $("#frmModal")[0].reset();
            button_icons("submit", "fas fa-plus-circle", "Agregar", "enabled");
        });
        $("#tipoDTE").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Tipo DTE'
        });

        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation.php/", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("submit", "fas fa-plus-circle", "Agregar", "enabled");

						if(data == "success") {
                            mensaje_do_aceptar(
                                "Operación completada:",
                                'Complemento registrado con éxito',
                                "success",
                                function(){
                                    $("#agregarPermisoDTE").toggle();
                                    $("#tipoDTE").val('').trigger('change');
                                    $("#btnPermiso").toggle();
                                    $("#frmModal")[0].reset();
                                    $('#tblPermisosDTE').DataTable().ajax.reload(null, false);

                                    button_icons("submit", "fas fa-plus-circle", "Agregar");
                                }
                            )

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

        $('#tblPermisosDTE thead tr#filterboxrow th').each(function(index) {
            if(index==1 || index==2) {
                var title = $('#tblPermisosDTE thead tr#filterboxrow th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblPermisosDTE.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });
        
        let tblPermisosDTE = $('#tblPermisosDTE').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tablePermisoDTE",
                "data": { 
                    
                }
            },
            "autoWidth": false,
            "columns": [
                {"width": "10%"},
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
