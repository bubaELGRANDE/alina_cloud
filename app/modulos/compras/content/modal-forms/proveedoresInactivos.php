<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();


?>
<input type="hidden" id="typeOperation" name="typeOperation" value="update">
<input type="hidden" id="operation" name="operation" value="proveedores-inactivos">
<input type="hidden" id="proveedorId" name="proveedorId" value="<?php echo $_POST['proveedorId']; ?>">

<div class="form-outline">
    <i class="fas fa-edit trailing"></i>
    <textarea id="justificacionProveedor" class="form-control" name="justificacionProveedor" required rows="3"></textarea>
    <label class="form-label" for="justificacionProveedor">Justificación de el cambio de estado</label>
</div>

<script>
    $(document).ready(function() {
        $("#frmModal").validate({
            submitHandler: function(form) {
                mensaje_confirmacion(
                    '¿Está seguro que desea cambiar el estado del proveedor?', 
                    `Se cambiara el estado del proveedor a inactivo`, 
                    `warning`, 
                    function(param) {
                        button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                        asyncData(
                            "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                            $("#frmModal").serialize(),
                            function(data) {
                                button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                                if(data == "success") {
                                    mensaje_do_aceptar(
                                        "Operación completada:",
                                        "El proveedor paso a estado inactivo",
                                        "success",
                                        function() {
                                            $(`#tblProveedores`).DataTable().ajax.reload(null, false);
                                            $(`#tblProveedoresInactivos`).DataTable().ajax.reload(null, false);
                                            $('#modal-container').modal("hide");
                                        }
                                    );
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
                    'Sí, cambiar',
                    `Cancelar`
                );
            }
        });
    }); 
</script>