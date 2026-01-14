<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="ubicacion">
<input type="hidden" id="sucursalId" name="sucursalId" value="<?= $_POST["sucursalId"] ?? 0 ?>">
<div class="row g-2">
    <!-- Nombre -->
    <div class="col-md-12">
        <label class="form-label">Ubicación <span class="text-danger">*</span></label>
        <input type="text" name="nombreUbicacion" class="form-control" required>
    </div>
    <!-- Abreviatura -->
    <div class="col-md-12">
        <label class="form-label">Código de ubicación</label>
        <input type="text" name="codigoUbicacion" class="form-control">
    </div>
</div>
<script>
    $(document).ready(function () {
        $("#frmModal").validate({
            submitHandler: function (form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation/",
                    $("#frmModal").serialize(),
                    function (data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if (data == "success") {
                            mensaje(
                                "Operación completada:",
                                'Ubicación agregada con éxito.',
                                "success"
                            );
                            $(`#tblUbicaciones`).DataTable().ajax.reload(null, false);
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

        function primerasDosSilabas(palabra) {

            const vocales = "aeiouáéíóúüAEIOUÁÉÍÓÚÜ";
            let silabas = [];
            let actual = "";

            for (let i = 0; i < palabra.length; i++) {
                actual += palabra[i];


                if (vocales.includes(palabra[i])) {
                    silabas.push(actual);
                    actual = "";
                }
            }


            if (actual.trim() !== "") silabas.push(actual);


            return (silabas[0] || "") + (silabas[1] || "");
        }

        $(document).on("input", "input[name='nombreUbicacion']", function () {
            const nombre = $(this).val().trim();

            if (nombre.length === 0) {
                $("input[name='codigoUbicacion']").val("");
                return;
            }

            const partes = nombre.split(/\s+/);

            let abreviatura = "";

            if (partes.length === 1) {

                abreviatura = primerasDosSilabas(partes[0]);
            } else {

                abreviatura = partes.map(p => p.charAt(0)).join("");
            }

            $("input[name='codigoUbicacion']").val(abreviatura.toUpperCase());
        });
    });
</script>