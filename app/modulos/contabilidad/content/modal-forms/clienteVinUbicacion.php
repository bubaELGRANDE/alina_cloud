<?php
	@session_start();
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");

    if ($_POST['typeOperation'] == 'update'){
        $dataUbicacion = $cloud->row("SELECT 
        u.clienteUbicacionId, u.clienteId, p.paisId, d.paisDepartamentoId, u.tipoUbicacion, u.paisMunicipioId, u.nombreClienteUbicacion, u.direccionClienteUbicacion, u.estadoClienteUbicacion
        FROM fel_clientes_ubicaciones u
        JOIN cat_paises_municipios m ON u.paisMunicipioId = m.paisMunicipioId
        JOIN cat_paises_departamentos d ON m.paisDepartamentoId = d.paisDepartamentoId
        JOIN cat_paises p ON d.paisId = p.paisId
        WHERE clienteUbicacionId = ?", [$_POST['clienteUbicacionId']]);
    }

?>
    <input type="hidden" id="typeOperation" name="typeOperation" value="<?php echo $_POST['typeOperation']; ?>">
	<input type="hidden" id="operation" name="operation" value="direccion-cliente">
	<input type="hidden" id="idCliente" name="idCliente" value="<?php echo $_POST['idCliente']; ?>">
	<input type="hidden" id="nombreCliente" name="nombreCliente" value="<?php echo $_POST['nombreCliente']; ?>">
	<input type="hidden" id="idUbicacion" name="idUbicacion" value="">
	
    <div class="row">
        <div class="col-md-6">
            <div class="form-outline mb-4">
                <i class="fas flist-ul trailing"></i>
                <input type="text" id="nombreSuc" class="form-control" name="nombreSuc" required>
                <label class="form-label" for="nombreSuc">Descripción dirección</label>
            </div>
        </div>
        <div class="col-md-6">
            <select id="tipoUbicacion" name="tipoUbicacion" style="width: 100%;" required>
                <option></option>
                <?php if ($_POST["tipoPersona"] == 1){
                    echo '<option value="Dirección de cliente">Dirección de cliente</option>
                        <option value="Dirección sucursal">Dirección de sucursal</option>
                        <option value="Dirección de lugar de trabajo">Dirección de lugar de trabajo</option>
                        ';
                } else {
                    echo '<option value="Dirección de cliente">Dirección de cliente</option>
                    <option value="Dirección sucursal">Dirección de sucursal</option>
                    <option value="Dirección de representante legal">Dirección de representante legal</option>
                    <option value="Dirección de lugar de trabajo de representante legal">Dirección de lugar de trabajo de representante legal</option>
                    ';
                } ?>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-select-control mb-4">
                <select id="pais" name="pais" style="width: 100%;" required>
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
        <div class="col-md-4">
            <div class="form-select-control mb-4">
                <select id="depto" name="depto" style="width: 100%;" required>
                    <option></option>
                    
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-select-control mb-4">
                <select id="municipio" name="municipio" style="width: 100%;" required>
                    <option></option>
                    
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-outline mb-4">
                <i class="fas flist-ul trailing"></i>
                <textarea type="text" id="direccionCli" class="form-control" name="direccionCli" required></textarea>
                <label class="form-label" for="direccionCli">Dirección</label>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $("#pais").select2({
                dropdownParent: $('#modal-container'),
                placeholder: 'País', 
                allowClear: true
            });
            $("#depto").select2({
                dropdownParent: $('#modal-container'),
                placeholder: 'Departamento', 
                allowClear: true
            });
            $("#municipio").select2({
                dropdownParent: $('#modal-container'),
                placeholder: 'Municipio', 
                allowClear: true
            });
            $("#tipoContacto").select2({
                dropdownParent: $('#modal-container'),
                placeholder: 'Tipo de contacto', 
                allowClear: true
            });
            $("#ubicacionSuc").select2({
                dropdownParent: $('#modal-container'),
                placeholder: 'Ubicación o Sucursal', 
                allowClear: true
            });
            $("#tipoUbicacion").select2({
                dropdownParent: $('#modal-container'),
                placeholder: 'Tipo de ubicación', 
                allowClear: true
            });

            $("#pais").on("change", function() {
                var pais = $("#pais").val();
                $.ajax({
                    url: "<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListarEstados.php/",
                    type: "POST",
                    dataType: "json",
                    data: {pais: pais}
                }).done(function(data){
                    //$("#municipio").html(data);
                    var cant = data.length;
                    $("#depto").empty();
                    $("#depto").append("<option value='0' selected disabled>Estado</option>");

                    $("#municipio").empty();
                    $("#municipio").append("<option value='0' selected disabled>Ciudad</option>");
                    for (var i = 0; i < cant; i++){
                        var id = data[i]['id'];
                        var depato = data[i]['departamento'];

                        $("#depto").append("<option value='"+id+"'>"+depato+"</option>");
                    }
                    <?php if ($_POST['typeOperation'] == 'update'){ ?>
                        $("#depto").val(<?php echo $dataUbicacion->paisDepartamentoId;?>).trigger('change');
                    <?php } ?>
                });
            });

            $("#depto").on("change", function() {
                var depto = $("#depto").val();
                $.ajax({
                    url: "<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListarCiudades.php/",
                    type: "POST",
                    dataType: "json",
                    data: {depto: depto}
                }).done(function(data){
                    //$("#municipio").html(data);
                    var cant = data.length;
                    $("#municipio").empty();
                    $("#municipio").append("<option value='0' selected disabled>Ciudad</option>");
                    for (var i = 0; i < cant; i++){
                        var id = data[i]['id'];
                        var muni = data[i]['municipio'];

                        $("#municipio").append("<option value='"+id+"'>"+muni+"</option>");
                    }
                    <?php if ($_POST['typeOperation'] == 'update'){ ?>
                        $("#municipio").val(<?php echo $dataUbicacion->paisMunicipioId;?>).trigger('change');
                    <?php } ?>
                });
            });

            $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation.php/", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");

						if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                'Dirección actualizada con éxito.',
                                "success"
                            );
							
                            $('#tblUbicaciones').DataTable().ajax.reload(null, false);
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

        <?php if ($_POST['typeOperation'] == 'update'){ ?>
            $("#nombreSuc").val('<?php echo $dataUbicacion->nombreClienteUbicacion;?>');
            $("#tipoUbicacion").val('<?php echo $dataUbicacion->tipoUbicacion;?>').trigger('change');
            $("#pais").val('<?php echo $dataUbicacion->paisId;?>').trigger('change');
            $("#direccionCli").val('<?php echo $dataUbicacion->direccionClienteUbicacion;?>');
            $("#idUbicacion").val('<?php echo $dataUbicacion->clienteUbicacionId;?>');
        <?php } ?>
    });
    </script>