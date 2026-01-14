<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="periodo-contable">
<div class="row">
    <div class="col-md-6">
        <div class="form-select-control mb-4">
            <select class="form-select" id="mes" name="mes" style="width:100%;" required>
                <option></option>
                <?php
                $mesesAnio = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre", "Cierre");
                for ($i = 0; $i < count($mesesAnio); $i++) {
                    echo '<option value="' . ($i + 1) . '">' . $mesesAnio[$i] . '</option>';
                }
                ?>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-select-control mb-4">
            <select class="form-select" id="anio" name="anio" style="width: 100%;" required>
                <option></option>
                <?php
                for ($i = date("Y"); $i <= 2030; $i++) {
                    echo '<option value="' . $i . '">' . $i . '</option>';
                }
                ?>
            </select>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $("#mes").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Mes a declarar'
        });

        $("#anio").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Año a declarar'
        });

        $("#frmModal").validate({
            submitHandler: function (form) {
                mensaje_confirmacion(
                    '¿Está seguro que desea agregar este período?',
                    `Una vez creado no se puede eliminar.`,
                    `warning`,
                    function (param) {
                        button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                        asyncData(
                            "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                            $("#frmModal").serialize(),
                            function (data) {
                                button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                                if (data == "success") {
                                    $("#modal-container").modal("hide");
                                    $('#tblPeriodos').DataTable().ajax.reload(null, false);
                                } else {
                                    mensaje("Aviso:", data, "warning");
                                }
                            }
                        );
                    },
                    'Sí, aplicar',
                    `Cancelar`
                );
            }
        });
    });
</script>