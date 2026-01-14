<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    // Este archivo permite generar las mismas columnas para cualquier tipo de sincronización o exportación a Magic

    $dataSincronizacionMagic = $cloud->rows("
        SELECT
            em.bitExportacionMagicId AS bitExportacionMagicId, 
            em.descripcionExportacion AS descripcionExportacion, 
            em.personaId AS personaId, 
            em.fhExportacion AS fhExportacion,
            exp.nombreCompletoNA AS nombreCompletoNA,
            em.estadoExportacion AS estadoExportacion
        FROM bit_exportaciones_magic em
        JOIN view_expedientes exp ON exp.personaId = em.personaId
        WHERE em.descripcionExportacion = ? AND exp.estadoPersona = ? AND exp.estadoExpediente = ? AND em.flgDelete = ?
        ORDER BY em.fhExportacion DESC
    ", [$_POST["descripcionExportacion"], "Activo", "Activo", 0]);

    $n = 0;
    foreach ($dataSincronizacionMagic as $sincronizacionMagic) {
        $n++;

        switch($_POST['descripcionExportacion']) {
            case "comp_compras":
                $cantidadDetalle = $cloud->count("
                    SELECT bitExportacionMagicDetalleId FROM bit_exportaciones_magic_detalle
                    WHERE bitExportacionMagicId = ? AND flgDelete = ?
                ", [$sincronizacionMagic->bitExportacionMagicId, 0]);

                $jsonSincronizacionMagic = array(
                    "tituloModal"                       => "Sincronización de Compras hacia Magic",
                    "bitExportacionMagicId"             => $sincronizacionMagic->bitExportacionMagicId,
                    "descripcionExportacion"            => "comp_compras",
                    "estadoExportacion"                 => $sincronizacionMagic->estadoExportacion
                );

                $acciones = "
                    <button type='button' class='btn btn-primary btn-sm ttip' onclick='modalSincronizacionCompras(".htmlspecialchars(json_encode($jsonSincronizacionMagic)).");'>
                        <span class='badge rounded-pill bg-light text-dark'>{$cantidadDetalle}</span> <i class='fas fa-envelope'></i> Compras
                        <span class='ttiptext'>Historial de compras sincronizadas</span>
                    </button>
                ";

                if($sincronizacionMagic->estadoExportacion == "Pendiente") {
                    $estadoExportacion = "<span class='badge rounded-pill badge-warning'>Pendiente</span>";

                    $jsonSincronizarCompras = array(
                        "bitExportacionMagicId"             => $sincronizacionMagic->bitExportacionMagicId,
                        "descripcionExportacion"            => "comp_compras",
                        "estadoExportacion"                 => $sincronizacionMagic->estadoExportacion
                    );

                    if($cantidadDetalle == 0) {
                        $acciones .= "
                            <button type='button' class='btn btn-secondary btn-sm ttip' onclick='mensaje(`Aviso:`, `Debe seleccionar las Compras que se sincronizarán en Magic.`, `warning`);'>
                                <i class='fas fa-sync-alt'></i> Sincronizar
                                <span class='ttiptext'>Sincronizar Compras en Magic</span>
                            </button>
                        ";
                    } else { 
                        // Este es el código cuando se hace por medio de modal, pero se regresó a Sweet de confirmación
                        // Porque esos campos ya se asignan en los formularios de compras
                        /*
                        $jsonSincronizarCompras = array(
                            "tituloModal"                       => "Sincronización de Compras a Magic: {$cantidadDetalle} compras",
                            "bitExportacionMagicId"             => $sincronizacionMagic->bitExportacionMagicId,
                            "descripcionExportacion"            => "comp_compras",
                            "estadoExportacion"                 => $sincronizacionMagic->estadoExportacion
                        );

                        $acciones .= "
                            <button type='button' class='btn btn-success btn-sm ttip' onclick='modalSincronizar(".htmlspecialchars(json_encode($jsonSincronizarCompras)).");'>
                                <i class='fas fa-sync-alt'></i> Sincronizar
                                <span class='ttiptext'>Sincronizar Compras en Magic</span>
                            </button>
                        ";
                        */
                        $jsonSincronizarCompras = array(
                            "typeOperation"                     => "insert",
                            "operation"                         => "sincronizar-compras-magic-bd",
                            "bitExportacionMagicId"             => $sincronizacionMagic->bitExportacionMagicId,
                            "descripcionExportacion"            => "comp_compras",
                            "estadoExportacion"                 => $sincronizacionMagic->estadoExportacion
                        );
                        $acciones .= "
                            <button type='button' class='btn btn-success btn-sm ttip' onclick='sincronizarComprasBD(".htmlspecialchars(json_encode($jsonSincronizarCompras)).");'>
                                <i class='fas fa-sync-alt'></i> Sincronizar
                                <span class='ttiptext'>Sincronizar Compras en Magic</span>
                            </button>
                        ";
                    }
                } else {
                    $estadoExportacion = "<span class='badge rounded-pill badge-success'>Sincronizado</span>";
                }
            break;

            default:
                $acciones = "";
            break;
        }

        $output['data'][] = array(
            $n,
            $sincronizacionMagic->nombreCompletoNA,
            date("d/m/Y H:i:s", strtotime($sincronizacionMagic->fhExportacion)),
            $estadoExportacion,
            $acciones
        );
    }

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>