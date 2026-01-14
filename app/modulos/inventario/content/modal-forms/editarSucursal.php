<?php 
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$dataEditSucursal = $cloud->row("
    SELECT cat_sucursales.sucursal, cat_sucursales.direccionSucursal, cat_sucursales.paisMunicipioId,  cat_sucursales.urlLogoSucursal, cat_paises_municipios.paisDepartamentoId FROM cat_sucursales 
    JOIN cat_paises_municipios ON cat_sucursales.paisMunicipioId = cat_paises_municipios.paisMunicipioId
    WHERE sucursalId = ?
", [$_POST["arrayFormData"]]);

?>
<input type="hidden" id="typeOperation" name="typeOperation" value="update">
<input type="hidden" id="operation" name="operation" value="editarSucursal">
<input type="hidden" id="sucursalId" name="sucursalId" value="<?php echo $_POST["arrayFormData"]; ?>">

<div class="form-outline mb-4">
    <i class="fas fa-building trailing"></i>
    <input type="text" id="nombreSucursal" class="form-control" name="nombreSucursal" value="<?php echo $dataEditSucursal->sucursal; ?>" required />
    <label class="form-label" for="nombreContacto">Nombre sucursal</label>
</div>
<div class="row justify-content-md-center">
    <div class="col-6">
        <div class="form-select-control mb-4">
            <select class="form-select" id="departamento" name="departamento" style="width:100%;" required>
                <option disabled>Seleccione un departamento</option>
                <?php $dataDep = $cloud->rows("
                    SELECT paisDepartamentoId, departamentoPais FROM cat_paises_departamentos WHERE flgDelete = 0 AND paisId = 61
                ");
                    foreach($dataDep as $depto){
                        echo '<option value="'. $depto->paisDepartamentoId .'">' . $depto->departamentoPais . '</option>';
                    }
                ?>
            </select>
        </div>
    </div> 
    <div class="col-6">
        <div class="form-select-control mb-4">
            <select class="form-select" id="municipio" name="municipio" style="width:100%;" required>
                <option disabled selected>Municipio</option>
            </select>
        </div>
    </div>
</div>
<div class="form-outline mb-4">
    <i class="fas fa-map-marker-alt trailing"></i>
    <textarea type="text" id="direccion" class="form-control" name="direccion" required ><?php echo $dataEditSucursal->direccionSucursal; ?></textarea>
    <label class="form-label" for="direccion">Dirección</label>
</div>
<div class="col mb-4">
<label class="form-label" for="subirLogo">Subir logo</label>
<input type="file" class="form-control" id="subirLogo" name="subirLogo" onchange="verificarImagen();" />
</div>

<script>
    function verificarImagen() {
        if( !$(this).val() ) {
            $("#subirLogo").val('');
        } else {
            let imagen = document.getElementById("subirLogo").value;
            let idxDot = imagen.lastIndexOf(".") + 1;
            let extFile = imagen.substr(idxDot, imagen.length).toLowerCase();
            if(extFile=="jpg" || extFile=="jpeg" || extFile=="png") {
                // Imagen valida
            } else {
                mensaje(
                    "AVISO - FORMULARIO",
                    "El archivo seleccionado no coincide con una imagen. Por favor vuelva a seleccionar una imagen con formato válido.",
                    "warning"
                );
                $("#subirLogo").val('');
            }   
        }
    }
    $(document).ready(function() {
        $("#departamento").select2({
            dropdownParent: $('#modal-container')
        });
        $("#municipio").select2({
            dropdownParent: $('#modal-container')
        });
        
        $("#departamento").on("change", function() {
            var depto = $("#departamento").val();
            $.ajax({
                url: "<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListarMunicipios",
                type: "POST",
                dataType: "json",
                data: {depto: depto}
            }).done(function(data){
                //$("#municipio").html(data);
                var cant = data.length;
                $("#municipio").empty();
                $("#municipio").append("<option value='0'>Seleccione municipio...</option>");
                for (var i = 0; i < cant; i++){
                    var id = data[i]['id'];
                    var muni = data[i]['municipio'];

                    $("#municipio").append("<option value='"+id+"'>"+muni+"</option>");
                    $('select[name="municipio"]').find('option[value="<?php echo $dataEditSucursal->paisMunicipioId; ?>"]').attr("selected",true);
                }
                
            })
        });
        
        $('#departamento').val('<?php echo $dataEditSucursal->paisDepartamentoId; ?>'); // Select the option with a value of '1'
        $('#departamento').trigger('change'); // Notify any JS components that the value changed
        $('#municipio').val('<?php echo $dataEditSucursal->paisMunicipioId; ?>'); // Select the option with a value of '1'
        $('#municipio').trigger('change'); // Notify any JS components that the value changed
        
        $("#frmModal").validate({
            submitHandler: function(form) {
                let form_data = new FormData($('#frmModal')[0]); // Para que envie los input file
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncFile(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    form_data,
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                'Se han actualizado los datos de sucursal.',
                                "success"
                            );
                            var tablaUpd = $("#operation").val();
                            $("#tblSucursal").DataTable().ajax.reload(null, false);
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

        $("#modalTitle").html('Editar sucursal: <?php echo $dataEditSucursal->sucursal; ?>');
    });
</script>