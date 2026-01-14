<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();
?>

<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="categoria">

<div class=" card mb-4 shadow-sm card-opcional">
    <div class="card-body">
        <h5 class="card-title mb-3 title-opcional section-toggle" data-bs-toggle="collapse" data-bs-target="#secEstado">
            <span class="title-label">
                <i class="fa-solid fa-circle-info text-primary title-icon"></i>
                <span>Datos de Categoria</span>
            </span>
        </h5>
        <div id="general">
            <div class="row g-4">

                <!-- Nombre -->
                <div class="col-md-6">
                    <label class="form-label">Nombre categoría <span class="text-danger">*</span></label>
                    <input type="text" name="nombreCategoria" class="form-control" required>
                </div>

                <!-- Abreviatura -->
                <div class="col-md-3">
                    <label class="form-label">Abreviatura</label>
                    <input type="text" name="abreviaturaCategoria" class="form-control">
                </div>

                <!-- Categoría principal -->
                <div class="col-md-3 d-flex align-items-center">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="flgPrincipal" id="flgPrincipal">
                        <label class="form-check-label" for="flgPrincipal">Categoría principal</label>
                    </div>
                </div>
            </div>
        </div>
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
                                'Categoría agregada con éxito.',
                                "success"
                            );
                            var tblCatagoria = $("#operation").val();
                            $("#tblCatagoria").DataTable().ajax.reload(null, false);
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

        $(document).on("input", "input[name='nombreCategoria']", function () {
            const nombre = $(this).val().trim();

            if (nombre.length === 0) {
                $("input[name='abreviaturaCategoria']").val("");
                return;
            }

            const partes = nombre.split(/\s+/);

            let abreviatura = "";

            if (partes.length === 1) {

                abreviatura = primerasDosSilabas(partes[0]);
            } else {

                abreviatura = partes.map(p => p.charAt(0)).join("");
            }

            $("input[name='abreviaturaCategoria']").val(abreviatura.toUpperCase());
        });


        <?php
        if (!empty($arrayFormData[1])) {
            ?>
            $("#modalTitle").html('Editar Categoría: <?php echo $datCategoria->nombreCategoria ?>');
            <?php
        }
        ?>

    });
</script>