<?php
@session_start();
require_once("../../logic/mgc/datos94.php");

$fhActual = date("Y-m-d H:i:s");

// Verificar POST, sino existe asignar uno por defecto
$modalSize = (isset($_POST["modalSize"])) ? $_POST["modalSize"] : 'lg';
$modalTitle = (isset($_POST["modalTitle"])) ? $_POST["modalTitle"] : 'Titulo...';
$modalForm = (isset($_POST["modalForm"])) ? ($_POST["modalForm"] == "changeDefaultPassword" || $_POST['modalForm'] == "cambioExpediente") ? "modulos/escritorio/content/modal-forms/" . $_POST['modalForm'] . "/" : $_SESSION["currentRoute"] . "content/modal-forms/" . $_POST["modalForm"] . "/" : 'modulos/escritorio/content/views/404/';
$flgFormData = (isset($_POST["formData"])) ? (is_array($_POST["formData"]) ? 'JSON' : 'Variable') : 'N^A^0';
$formData = (isset($_POST["formData"])) ? $_POST["formData"] : 'N^A^0'; // Variables para el archivo de modal-forms, debe ser una string y en el modal-forms recibira "POST arrayFormData"
$buttonResetShow = (isset($_POST["buttonResetShow"])) ? $_POST["buttonResetShow"] : false;
$buttonResetIcon = (isset($_POST["buttonResetIcon"])) ? $_POST["buttonResetIcon"] : 'sync-alt';
$buttonResetText = (isset($_POST["buttonResetText"])) ? $_POST["buttonResetText"] : 'Limpiar';
$buttonCustomShow = (isset($_POST["buttonCustomShow"])) ? $_POST["buttonCustomShow"] : false;
$buttonCustomIcon = (isset($_POST["buttonCustomIcon"])) ? $_POST["buttonCustomIcon"] : 'sync-alt';
$buttonCustomText = (isset($_POST["buttonCustomText"])) ? $_POST["buttonCustomText"] : 'Acción';
$buttonCustomClass = (isset($_POST["buttonCustomClass"])) ? $_POST["buttonCustomClass"] : 'secondary';
$buttonAcceptShow = (isset($_POST["buttonAcceptShow"])) ? $_POST["buttonAcceptShow"] : false;
$buttonAcceptIcon = (isset($_POST["buttonAcceptIcon"])) ? $_POST["buttonAcceptIcon"] : 'save';
$buttonAcceptText = (isset($_POST["buttonAcceptText"])) ? $_POST["buttonAcceptText"] : 'Guardar';
$buttonAcceptClass = (isset($_POST["buttonAcceptClass"])) ? $_POST["buttonAcceptClass"] : 'primary';
$buttonCancelShow = (isset($_POST["buttonCancelShow"])) ? $_POST["buttonCancelShow"] : false;
$buttonCancelIcon = (isset($_POST["buttonCancelIcon"])) ? $_POST["buttonCancelIcon"] : 'times-circle';
$buttonCancelText = (isset($_POST["buttonCancelText"])) ? $_POST["buttonCancelText"] : 'Cancelar';
$buttonCancelActionShow = (isset($_POST["buttonCancelActionShow"])) ? $_POST["buttonCancelActionShow"] : false;
$buttonCancelActionIcon = (isset($_POST["buttonCancelActionIcon"])) ? $_POST["buttonCancelActionIcon"] : 'times-circle';
$buttonCancelActionText = (isset($_POST["buttonCancelActionText"])) ? $_POST["buttonCancelActionText"] : 'Cancelar';

$devPermiso = (isset($_POST["modalDev"])) ? $_POST["modalDev"] : 0;

$arrayPermisos = explode("^", $devPermiso); // Siempre deben venir 2,3,4 = desarrollo y permisos normales
$dev = $arrayPermisos[0];
$permiso1 = (isset($arrayPermisos[1])) ? $arrayPermisos[1] : 0;
$permiso2 = (isset($arrayPermisos[2])) ? $arrayPermisos[2] : 0;
$permiso3 = (isset($arrayPermisos[3])) ? $arrayPermisos[3] : 0;

$flgPermiso = 0;
if ($devPermiso == -1 || (in_array($dev, $_SESSION["arrayPermisos"]) || in_array($permiso1, $_SESSION["arrayPermisos"]) || in_array($permiso2, $_SESSION["arrayPermisos"]) || in_array($permiso3, $_SESSION["arrayPermisos"]))) {
    $flgPermiso = 1;
    // Bitacora
    $cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Ingresó a " . $modalTitle . ", ");
} else {
    // NPermiso
    // Bitacora
    $cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y visualizar el contenido de " . $modalTitle . ", ");
}
?>
<form id="frmModal">
    <?php
    if ($flgFormData == "JSON") {
        // No se manda nada, ya que todas las variables viajan en un POST para cada una
    } else {
        ?>
        <input type="hidden" id="hiddenFormData" name="hiddenFormData" value="<?php echo $formData; ?>">
        <?php
    }
    ?>

    <style>
        body {
            background: #f5f6f7;
        }

        /* Leyenda */
        .legend-dot {
            width: 16px;
            height: 16px;
            border-left: 4px solid;
            margin-right: 8px;
        }

        /* Prioridades en borde izquierdo de cada card */
        .card-obligatorio {
            border-left: 4px solid #dc3545 !important;
        }

        /* rojo */
        .card-importante {
            border-left: 4px solid #fd7e14 !important;
        }

        /* naranja */
        .card-opcional {
            border-left: 4px solid #0d6efd !important;
        }

        /* azul */
        /* Títulos suaves por prioridad */
        .title-obligatorio,
        .title-importante,
        .title-opcional {
            border-radius: 6px;
            padding: 6px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* pastel azul */
        /* Ícono a la izquierda del texto del título */
        .title-label {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .title-icon {
            font-size: 1.1rem;
        }

        /* Tooltip y requerido */
        .tooltip-label {
            cursor: help;
            text-decoration: dotted underline;
        }

        .required:after {
            content: " *";
            color: #dc3545;
        }

        /* Animación colapsable */
        .collapse {
            transition: all 0.25s ease-in-out;
        }

        /* Botón regresar en título */
        .btn-back {
            font-size: .9rem;
            padding: 2px 10px;
        }

        /* Chips de tags */
        .tag-chip {
            background: #f1f1f1;
            border-radius: 20px;
            padding: 6px 12px;
            display: inline-flex;
            align-items: center;
            font-size: 0.9rem;
        }

        .tag-chip .btn-close {
            font-size: 0.65rem;
            margin-left: 6px;
        }
    </style>
    <div class="modal fade" id="modal-container" tabindex="-1" role="dialog" data-mdb-backdrop="static"
        data-mdb-keyboard="false" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog modal-<?php echo $modalSize; ?> modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content container py-4">
                <h4 class="mb-4 fw-bold" id="modalTitle"><?php echo $modalTitle; ?></h4>
                <div id="modalBody" class="modal-body">
                </div>
                <div class="modal-footer d-flex justify-content-end">
                    <?php
                    if ($flgPermiso == 1) {
                        if ($buttonResetShow) {
                            ?>
                            <div class="me-auto">
                                <button type="button" id="btnModalReset" class="btn btn-secondary">
                                    <i class="fas fa-sync-alt"></i> Limpiar
                                </button>
                            </div>
                            <?php
                        } else {
                        }

                        if ($buttonCustomShow) {
                            ?>
                            <div class="me-auto">
                                <button type="button" id="btnModalCustom" class="btn btn-<?php echo $buttonCustomClass; ?>">
                                    <i class="fas fa-<?php echo $buttonCustomIcon; ?>"></i> <?php echo $buttonCustomText; ?>
                                </button>
                            </div>
                            <?php
                        } else {
                        }
                        ?>
                        <div id="divModalFooterMsj" class="me-auto">
                        </div>
                        <?php
                        if ($buttonAcceptShow) {
                            ?>
                            <button type="submit" id="btnModalAccept" class="btn btn-<?php echo $buttonAcceptClass; ?>">
                                <i class="fas fa-<?php echo $buttonAcceptIcon; ?>"></i>
                                <?php echo $buttonAcceptText; ?>
                            </button>
                            <?php
                        } else {
                        }
                        if ($buttonCancelActionShow) {
                            ?>
                            <button type="button" id="btnModalCancelAction" class="btn btn-secondary">
                                <i class="fas fa-<?php echo $buttonCancelActionIcon; ?>"></i>
                                <?php echo $buttonCancelActionText; ?>
                            </button>
                            <?php
                        } else {
                        }
                        if ($buttonCancelShow) {
                            ?>
                            <button type="button" id="btnModalCancel" class="btn btn-secondary" data-mdb-dismiss="modal">
                                <i class="fas fa-<?php echo $buttonCancelIcon; ?>"></i>
                                <?php echo $buttonCancelText; ?>
                            </button>
                            <?php
                        } else {
                        }
                    } else { // NPermiso
                        ?>
                        <button type="button" id="btnModalCancel" class="btn btn-secondary" data-mdb-dismiss="modal">
                            <i class="fas fa-times-circle"></i>
                            Cerrar
                        </button>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</form>
<script>
    $(document).ready(function () {
        if (<?php echo $flgPermiso; ?> == 1) {
            if ('<?php echo $flgFormData; ?>' == 'JSON') {
                asyncData("<?php echo $modalForm; ?>", <?php echo json_encode($formData); ?>, function (data) {
                    $("#modalBody").html(data);
                    document.querySelectorAll('.form-outline').forEach((formOutline) => {
                        new mdb.Input(formOutline).init();
                    });
                });
            } else {
                asyncDoDataReturn("<?php echo $modalForm; ?>", { arrayFormData: $("#hiddenFormData").val() }, function (data) {
                    $("#modalBody").html(data);
                    document.querySelectorAll('.form-outline').forEach((formOutline) => {
                        new mdb.Input(formOutline).init();
                    });
                });
            }
        } else {
            asyncDoDataReturn("modulos/escritorio/content/views/403/", { arrayFormData: $("#hiddenFormData").val() }, function (data) {
                $("#modalBody").html(data);
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            });
        }
    });
</script>