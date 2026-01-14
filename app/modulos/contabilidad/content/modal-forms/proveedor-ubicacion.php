<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    /*
     arrayFormData 
        Nuevo  = insert
        editar = update ^ proveedorId
     */

    //$arrayFormData      = explode("^", $_POST["arrayFormData"]);

    $txtSuccess = "Ubicación agregada con éxito.";
    $proveedor = $cloud->row("
        SELECT 
            proveedorId,
            nombreProveedor 
        FROM comp_proveedores 
        WHERE proveedorId = ? AND flgDelete = ?
    ",[$_POST["proveedorId"],0]);

    if ($_POST['typeOperation'] == "update"){
        $datProveedorUbicacion = $cloud->row("
            SELECT
                ub.proveedorUbicacionId AS proveedorUbicacionId, 
                ub.proveedorId AS proveedorId,
                ub.paisMunicipioId AS paisMunicipioId,
                (SELECT municipioPais FROM cat_paises_municipios mp WHERE mp.paisMunicipioId = ub.paisMunicipioId LIMIT 1) AS municipio, 
                ub.nombreProveedorUbicacion AS nombreProveedorUbicacion,
                ub.direccionProveedorUbicacion AS direccionProveedorUbicacion,
                ub.estadoProveedorUbicacion AS estadoProveedorUbicacion,
                mu.paisDepartamentoId AS paisDepartamentoId,
                mu.municipioPais AS municipioPais,
                de.paisId AS paisId,
                de.departamentoPais AS departamentoPais,
                pa.pais AS pais
            FROM comp_proveedores_ubicaciones ub
            JOIN cat_paises_municipios mu ON mu.paisMunicipioId = ub.paisMunicipioId
            JOIN cat_paises_departamentos de ON de.paisDepartamentoId = mu.paisDepartamentoId
            JOIN cat_paises pa ON pa.paisId = de.paisId
            WHERE ub.flgDelete = ? AND proveedorUbicacionId = ?
        ",[0,$_POST["proveedorUbicacionId"]]);
    
        $txtSuccess  = "Ubicación editada con éxito";
    } else {

    }

    // Esta modal se utiliza desde Quedan para crear nuevo proveedor
    $interfaz = isset($_POST['interfaz']) ? $_POST['interfaz'] : "proveedor";
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="proveedor-ubicacion">
<input type="hidden" id="proveedorId" name="proveedorId" value="<?php echo $proveedor->proveedorId;?>">
<input type="hidden" id="nombreProveedor" name="nombreProveedor" value="<?php echo $proveedor->nombreProveedor;?>">
<?php if ($_POST['typeOperation'] == "update"){ ?>
<input type="hidden" id="proveedorUbicacionId" name="proveedorUbicacionId" value="<?php echo $datProveedorUbicacion->proveedorUbicacionId;?>">
<?php } ?>
<div class="row">
    <div class="col-lg-12">
        <div class="form-outline mb-4">
            <i class="fas fa-map-marker-alt trailing"></i>
            <input type="text" id="nombreProveedorUbicacion" class="form-control" name="nombreProveedorUbicacion" required />
            <label class="form-label" for="nombreProveedorUbicacion">Nombre de ubicación</label>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-4">
        <div class="form-select-control mb-4">
            <select class="form-select" id="paisId" name="paisId" style="width:100%;" required>
                <option></option>
                <option value="61">El Salvador</option>
                <?php 
                    $dataPaises = $cloud->rows("
                        SELECT
                            paisId,
                            pais
                        FROM cat_paises
                        WHERE flgDelete = '0' AND paisId <> '61' ORDER BY pais ASC
                    ");
                    foreach ($dataPaises as $dataPaises) {
                        echo '<option value="'.$dataPaises->paisId.'">'.$dataPaises->pais.'</option>';
                    }
                ?>
            </select>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-select-control mb-4">
            <select class="form-select" id="departamentoId" name="departamentoId" style="width:100%;" required>
                <option></option>
            </select>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-select-control mb-4">
            <select class="form-select" id="paisMunicipioId" name="paisMunicipioId" style="width:100%;" required>
                <option></option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-outline mb-4">
            <i class="fas fa-route trailing"></i>
            <textarea id="direccionProveedorUbicacion" class="form-control" name="direccionProveedorUbicacion" required ></textarea>
            <label class="form-label" for="direccionProveedorUbicacion">Dirección del proveedor</label>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        Maska.create('#frmModal .masked');

        $("#paisId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'País'
        });
        $("#departamentoId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Estado'
        });
        $("#paisMunicipioId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Ciudad'
        });

        $("#estado").select2({
            dropdownParent: $('#modal-container'),
            placeholder: "Estado:"
            
        });
        
        $("#paisId").on("change", function() {
            var pais = $("#paisId").val();
            $.ajax({
                url: "<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListarEstados",
                type: "POST",
                dataType: "json",
                data: {pais: pais}
            }).done(function(data){
                //$("#municipio").html(data);
                var cant = data.length;
                $("#departamentoId").empty();
                $("#departamentoId").append("<option value='0' selected disabled>Estado</option>");

                $("#paisMunicipioId").empty();
                $("#paisMunicipioId").append("<option value='0' selected disabled>Ciudad</option>");
                for (var i = 0; i < cant; i++){
                    var id = data[i]['id'];
                    var depato = data[i]['departamento'];

                    $("#departamentoId").append("<option value='"+id+"'>"+depato+"</option>");
                }
                <?php 
                    // Validacion con php para prevenir error que no existe variable, sino se cumple simplemente no existe este script
                    if($_POST['typeOperation'] == "update") {
                ?>
                        $("#departamentoId").val('<?php echo $datProveedorUbicacion->paisDepartamentoId; ?>').trigger('change');
                <?php 
                    } else { 

                    }
                ?>
                
            });
        });

        $("#departamentoId").on("change", function() {
            var depto = $("#departamentoId").val();
            $.ajax({
                url: "<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListarCiudades",
                type: "POST",
                dataType: "json",
                data: {depto: depto}
            }).done(function(data){
                //$("#municipio").html(data);
                var cant = data.length;
                $("#paisMunicipioId").empty();
                $("#paisMunicipioId").append("<option value='0' selected disabled>Ciudad</option>");
                for (var i = 0; i < cant; i++){
                    var id = data[i]['id'];
                    var muni = data[i]['municipio'];

                    $("#paisMunicipioId").append("<option value='"+id+"'>"+muni+"</option>");
                }
                <?php 
                    // Validacion con php para prevenir error que no existe variable, sino se cumple simplemente no existe este script
                    if($_POST['typeOperation'] == "update") {
                ?>
                        $("#paisMunicipioId").val('<?php echo $datProveedorUbicacion->paisMunicipioId; ?>').trigger('change');
                <?php 
                    } else {
                    }
                ?>
                
            });
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
                                '<?php echo $txtSuccess;?>',
                                "success"
                            );
                            $('#modal-container').modal("hide");
                            if(`<?php echo $interfaz; ?>` == "proveedor") {
                                $("#tblUbicaciones").DataTable().ajax.reload(null, false);
                            } else if(`<?php echo $interfaz; ?>` == "otra") {
                                // Para volver a cargar el select
                                $("#proveedor").trigger('change');
                            } else {
                                // Otra interfaz
                            }
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

        <?php 
            if($_POST['typeOperation'] == "update") {
        ?>
                $("#typeOperation").val("update");
                $("#direccionProveedorUbicacion").val('<?php echo $datProveedorUbicacion->direccionProveedorUbicacion?>');
                $("#nombreProveedorUbicacion").val('<?php echo $datProveedorUbicacion->nombreProveedorUbicacion?>');
                $("#paisId").val('<?php echo $datProveedorUbicacion->paisId; ?>').trigger('change');
                $("#departamentoId").val('<?php echo $datProveedorUbicacion->paisDepartamentoId; ?>').trigger('change');
                //$("#tipoDocumentoRL").val('<?php #echo $datProveedorUbicacion->tipoDocumentoRL; ?>').trigger('change');
                $('#estado').val('<?php echo $datProveedorUbicacion->estadoProveedorUbicacion;?>').trigger('change');
        <?php
            } else{

            }
        ?>

    });
</script>