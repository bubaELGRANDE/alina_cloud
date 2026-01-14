<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="parametrizacion-compartida">

<div id="divClienteId" class="form-select-control mb-4">
    <select id="clienteId" name="clienteId" style="width: 100%;" required>
        <option></option>
    </select>
</div>
<div class="form-outline mb-2">
    <i class="fas fa-edit trailing"></i>
    <textarea id="descParam" class="form-control" name="descParam" required ></textarea>
    <label class="form-label" for="descParam">Descripción</label>
</div>

<script>
    $(document).ready(function() {
        $("#clienteId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Seleccionar cliente',
            ajax: {
                type: "POST",
                url: "<?php echo $_SESSION['currentRoute']; ?>content/divs/selectListarClientesComisionesAjax",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        txtBuscar: params.term //Input de búsqueda
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });
        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation/", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                "Empleado agregado con éxito a la clasificación.",
                                "success"
                            );
                            $("#tblComisionCompa").DataTable().ajax.reload(null, false);
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