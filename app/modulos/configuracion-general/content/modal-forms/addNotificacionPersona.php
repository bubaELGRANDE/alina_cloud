<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

?>

<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="notificacion-persona">
<div class="row mb-2">
    <div class="col-md-12">
        <div class="form-select-control">
            <select class="form-select" id="categoria" name="categoria" style="width:100%;">
                <option value=""></option>
                <option value="DEV-CLOUD">DESARROLLO CLOUD</option>
                <option value="DEV-MAGIC">DESARROLLO MAGIC</option>
                <option value="INVENTARIOS">Inventarios</option>
                <option value="BODEGA-01">(0001) Bodega Stihl</option>
                <option value="BODEGA-19">(0019) Bodega Kärcher</option>
                <option value="BODEGA-27">(0027) Bodega Hidropal</option>
                <option value="BODEGA-30">(0030) Bodega 0030</option>
                <option value="BODEGA-31">(0031) Bodega 0031</option>
                <option value="BODEGA-32">(0032) Bodega Agropal</option>
                <option value="JEFATURA-KARCHER">Jefatura Kärcher</option>
                <option value="JEFATURA-STIHL">Jefatura STIHL</option>
                <option value="JEFATURA-HIDROPAL">Jefatura Hidropal</option>
                <option value="JEFATURA-AGROPAL">Jefatura Agropal</option>
                <option value="JEFATURA-INDUSTRIAL">Jefatura Industrial</option>
                <option value="JEFATURA-CHESTERTON">Jefatura Chesterton</option>
                <option value="ADMIN-RETACEOS">Administrativos retaceos</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-select-control">
            <select id="empleado" name="empleado[]" style="width:100%;" class="form-select" required multiple>
                <option></option>
            </select>
        </div>
    </div>
</div>

<script>

    $("#categoria").select2({
        dropdownParent: $('#modal-container'),
        placeholder: "Lista de contactos"
    });

    function limitarTexto(texto, max = 50) {
        return texto.length > max ? texto.substring(0, max) + "..." : texto;
    }

    $("#empleado").select2({
        dropdownParent: $('#modal-container'),
        placeholder: "Digite el nombre de un empleado",
        ajax: {
            type: "POST",
            url: "<?php echo $_SESSION['currentRoute']; ?>content/divs/selectListarEmpleados",
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

    $("#frmModal").validate({
        submitHandler: function (form) {
            mensaje_confirmacion(
                '¿Está seguro de agregar estos empleados a la lista de notificaciones?',
                'Esta operación puede tardar más de un minuto, dependiendo de la cantidad de empleados seleccionados.',
                `warning`,
                function (param) {
                    asyncData(
                        "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                        $("#frmModal").serialize(),
                        function (data) {
                            button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                            if (data == 'success') {

                                mensaje(
                                    "Operación completada:",
                                    'Empleados Agregados.',
                                    "success"
                                );
                                $('#tblListas').DataTable().ajax.reload(null, false);
                                $("#modal-container").modal("hide");
                            } else {
                                mensaje("Aviso:", data, "warning");
                            }
                        }
                    );
                },
                'Sí, agregar',
                `Cancelar`
            );
        }
    });
</script>