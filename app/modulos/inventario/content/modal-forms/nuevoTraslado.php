<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();
$sucursalId = $_POST['sucursalId'];
?>

<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="traslado-interno">
<input type="hidden" id="sucursalId" name="sucursalId" value="<?php echo (int) $sucursalId; ?>">

<div class="row mb-3">
    <div class="col-md-4">
        <div class="form-select-control">
            <label for="selectUbicacionSalida">Ubicación de salida</label>
            <select class="form-select" id="selectUbicacionSalida" name="selectUbicacionSalida" style="width:100%;"
                required>
                <option value="" disabled selected>Seleccione ubicación de salida</option>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-select-control">
            <label for="selectUbicacionEntrada">Ubicación de entrada</label>
            <select class="form-select" id="selectUbicacionEntrada" name="selectUbicacionEntrada" style="width:100%;"
                required>
                <option value="" disabled selected>Seleccione ubicación de entrada</option>
            </select>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="form-select-control">
            <label for="selectProductos">Productos a trasladar</label>
            <select class="form-select" id="selectProductos" name="selectProductosTemp[]" style="width:100%;" multiple
                disabled>
                <option value="" disabled selected>Primero seleccione la ubicación de salida</option>
            </select>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {


        $("#selectUbicacionSalida").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Seleccione ubicación de salida',
            width: "100%",
            ajax: {
                type: "POST",
                url: "<?php echo $_SESSION['currentRoute']; ?>content/divs/selectUbicacionesSucursal",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        sucursalId: $("#sucursalId").val(),
                        busquedaSelect: params.term,
                    };
                },
                processResults: function (data) {
                    return { results: data };
                },
                cache: true
            }
        });


        $("#selectUbicacionEntrada").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Seleccione ubicación de entrada',
            width: "100%",
            ajax: {
                type: "POST",
                url: "<?php echo $_SESSION['currentRoute']; ?>content/divs/selectUbicacionesSucursal",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        sucursalId: $("#sucursalId").val(),
                        busquedaSelect: params.term,
                    };
                },
                processResults: function (data) {
                    return { results: data };
                },
                cache: true
            }
        });


        $("#selectProductos").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Primero seleccione la ubicación de salida',
            allowClear: true,
            multiple: true,
            width: '100%',
            ajax: {
                type: "POST",
                url: "<?php echo $_SESSION['currentRoute']; ?>content/divs/selectListarProductosUbicacion",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        busquedaSelect: params.term,

                        ubicacionId: $('#selectUbicacionSalida').val()
                    };
                },
                processResults: function (data) {
                    return { results: data };
                },
                cache: true
            }
        });

        $('#selectUbicacionSalida').on('change', function () {
            const ubicacionSalidaId = $(this).val();

            $('#selectProductos').val(null).trigger('change');

            if (ubicacionSalidaId) {

                $('#selectProductos').prop('disabled', false);
                $('#selectProductos').data('select2').$container
                    .find('.select2-selection__placeholder')
                    .text('Busque y seleccione productos a trasladar');
            } else {

                $('#selectProductos').prop('disabled', true);
                $('#selectProductos').data('select2').$container
                    .find('.select2-selection__placeholder')
                    .text('Primero seleccione la ubicación de salida');
            }
        });

        $('#selectUbicacionEntrada').on('change', function () {
            const entrada = $(this).val();
            const salida = $('#selectUbicacionSalida').val();
            if (entrada && salida && entrada === salida) {
                mensaje(
                    "Aviso:",
                    'La ubicación de entrada no puede ser la misma que la de salida.',
                    "warning"
                );
                $(this).val(null).trigger('change');
            }
        });

        $("#frmModal").validate({
            submitHandler: function (form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation/",
                    $("#frmModal").serialize(),
                    function (data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if (data == "success") {
                            mensaje(
                                "Operación completada:",
                                'Productos trasladados con éxito.',
                                "success"
                            );
                            $(`#tblTraslado`).DataTable().ajax.reload(null, false);
                            $('#modal-container').modal("hide");

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