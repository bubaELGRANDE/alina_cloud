<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="unidadCategoriaCategoria">
<input type="hidden" id="inventarioCategoriaId" name="inventarioCategoriaId"
    value="<?= $_POST['inventarioCategoriaId'] ?? 0 ?>">
<div class="card mb-4 shadow-sm card-importante">
    <div class="card-body">
        <div id="secEspecificaciones" class="collapse show">
            <p class="text-muted">
                Estas especificaciones corresponden a la categoría principal seleccionada y se utilizarán
                para generar el SKU automáticamente.
                <br><strong>Por ahora están deshabilitadas, hasta implementar la lógica dinámica.</strong>
            </p>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label required">Unidad de Medida</label>
                    <select id="catProdEspecificacionId" name="catProdEspecificacionId" class="form-select">
                        <option value="">Seleccione Unidad de Medida</option>
                    </select>
                </div>
                <div class="col-md-4    d-flex align-items-center">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="esObligatoria" id="esObligatoria">
                        <label class="form-check-label" for="esObligatoria">Obligatoria</label>
                    </div>
                </div>
                <div class="col-md-12 d-flex align-items-end">
                    <button type="button" onclick="agregarEspecificacion()" class="btn btn-outline-secondary">
                        <i class="fas fa-plus-circle"></i> Agregar
                    </button>
                </div>
            </div>
            <div class="table-responsive mt-4">
                <table id="tblCategoriaEspecificacion" class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 30%">Tipo</th>
                            <th style="width: 30%">Nombre</th>
                            <th style="width: 10%">Magnitud</th>
                            <th style="width: 10%">Obligatoria</th>
                            <th style="width: 10%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaEspecificaciones">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    function agregarEspecificacion() {
        asyncData(
            "<?php echo $_SESSION['currentRoute']; ?>transaction/operation/",
            $("#frmModal").serialize(),
            function (data) {
                button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                if (data == "success") {
                    mensaje(
                        "Operación completada:",
                        'Especificación agregada correctamente.',
                        "success"
                    );
                    $("#tblCategoriaEspecificacion").DataTable().ajax.reload(null, false);
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
    $(document).ready(function () {
        $("#catProdEspecificacionId").select2({
            width: "100%",
            dropdownParent: $('#modal-container'),
            placeholder: "Seleccione especificación",
            ajax: {
                type: "POST",
                url: "<?php echo $_SESSION['currentRoute']; ?>content/divs/selectEspecificacion",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        busquedaSelect: params.term,
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });
        let tbl = $('#tblCategoriaEspecificacion').DataTable({
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tablaEspecificacionCategoria",
                "data": {
                    "inventarioCategoriaId": <?= $_POST['inventarioCategoriaId'] ?? 0 ?>
                }
            },
            "rowReorder": true,
            "autoWidth": false,
            "columns": [
                null,
                null,
                null,
                null,
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [2, 3] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            },
            "pageLength": 3,
            "searching": false,
            "lengthChange": false,
            "info": false
        });
    });
</script>