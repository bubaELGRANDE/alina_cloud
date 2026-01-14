<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
?>
<input type="hidden" id="expedienteAmonestacionId" name="expedienteAmonestacionId" value="<?php echo $_POST["expedienteAmonestacionId"];?>">
<input type="hidden" id="extension" name="extension" value="pdf">
<!-- Para que se limpie el input de quedan del proveedor -->
<input type="hidden" id="numCargaInterfaz" name="numCargaInterfaz" value="0">
<div class="row">
    <div class="col-md-3">
        <div class="form-select-control mb-4">
            <select id="file" name="file" style="width: 100%;" required>
                <option></option>
                <option value="formato-amonestacion">Amonestación de personal</option>
                <option value="listado-amonestacion">Listado de amonestaciones</option>
            </select>
        </div>
        <div id="idAmonestacion" class="form-select-control mb-4">
            <div class="form-select-control mb-4">    
                <select id="filtroAnio" name="filtroAnio" style="width: 100%;" required>
                        <option></option>
                        <?php 
                            for ($i=date("Y"); $i >= 2024; $i--) { 
                                echo '<option value="'.$i.'">'.$i.'</option>';
                            }
                        ?>
                    </select>
            </div>
            <div class="form-select-control mb-4">    
                <select id="amonestacionReporte" name="amonestacionReporte" style="width: 100%;" required >
                    <option></option>
                </select>
            </div>
        </div>
        <div id="divListarAmonestaciones" class="form-select-control mb-4">
            <div class="form-outline mb-4 input-daterange">
                <i class="fas fa-calendar trailing"></i>
                <input type="date" id="fechaInicio" class="form-control" name="fechaInicio"  required />
                <label class="form-label" for="fechaInicio">Fecha de Inicio</label>
            </div>
            <div class="form-outline mb-4 input-daterange">
                <i class="fas fa-calendar trailing"></i>
                <input type="date" id="fechaFin" class="form-control" name="fechaFin"  required />
                <label class="form-label" for="fechaFin">Fecha de fin</label>
            </div>
            <div class="form-select-control mb-4">    
                <select id="estadoAmonestacion" name="estadoAmonestacion" style="width: 100%;" required>
                    <option></option>
                    <?php 
                        $estadoAmonestacion = array("Activo", "Anulado");
                        $estadoAmonestacionText = array("Amonestaciones vigentes", "Amonestaciones anuladas");
                        for ($i=0; $i < count($estadoAmonestacion); $i++) { 
                            echo '<option value="'.$estadoAmonestacion[$i].'" '.($i == 0 ? 'selected' : '').'>'.$estadoAmonestacionText[$i].'</option>';
                        }
                    ?>
                </select>
            </div>
        </div>
    </div>
    <div id="divReporte" class="col-md-9">
    </div>
</div>

<script>
    $(document).ready(function() {
        $("#idAmonestacion").hide();
        $("#divListarAmonestaciones").hide();

        $("#file").select2({
        dropdownParent: $('#modal-container'),
        placeholder: 'Tipo de reporte'
        });
        $("#amonestacionReporte").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Amonestación'
        });

        $("#estadoAmonestacion").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Estado'
        });
        $("#filtroAnio").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Año'
        });
        $("#listarAmonestacion").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Listado de amonestaciones'
        });

        $("#file").on("change", function(){
            var tipoReport = $("#file").val();
            if (tipoReport == "listado-amonestacion"){
                $("#idAmonestacion").hide();
                $("#divListarAmonestaciones").show();

             
            } else if(tipoReport == "formato-amonestacion") {
                $("#idAmonestacion").show();
                $("#divListarAmonestaciones").hide();
                $("#amonestacionReporte").trigger('change');
            } else {
                $("#idAmonestacion").hide();
                $("#divListarAmonestaciones").hide();
            }
        });
        
        $("#filtroAnio").change(function(e) {
            asyncSelect(
                `<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListarAmonestacion`,
                {
                    anioAmonestacion: $(this).val()
                  
                },
                `amonestacionReporte`,
                function() {
                    $("#amonestacionReporte").val('<?php echo $_POST["expedienteAmonestacionId"]; ?>').trigger('change');
                    <?php if($_POST["expedienteAmonestacionId"] > 0){ ?>
                                $("#btnModalAccept").click();
                    <?php } ?>
                }
            );
        });
        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>reportes", 
                    $("#frmModal").serialize(),
                    function(data) {
                        // Mantener el botón disabled para prevenir que generen más de uno sino carga
                        button_icons("btnModalAccept", "fas fa-print", "Generar reporte", "enabled");
                        $("#divReporte").html(data);
                    }
                );
            }
        });
        //Cargar el expedienteAmonestacionId
        <?php if ($_POST["expedienteAmonestacionId"] > 0){ ?>
            $("#file").val('formato-amonestacion').trigger('change');
            $("#filtroAnio").val('<?php echo $_POST["anioAmonestacion"]; ?>').trigger('change');
        <?php  } ?>
    });
</script>