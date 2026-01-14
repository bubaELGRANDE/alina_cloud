<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    if(!($_POST['filtroPeriodo'] == "")) {
        $wherePeriodo = "AND (ca.cierreDeclaracionId = '$_POST[filtroPeriodo]')";
    } else {
        $wherePeriodo = "";
    }

    // Lógica de yearBD
    $arrayAnios = [];
    $anioInicio = "_2024";
    $anioInicioTxt = "2024";

    $anioFin = "_" . date("Y");
    $anioFinTxt = date("Y");

    // Iterar desde anioInicio hasta anioFin (se seguirá usando esta lógica de aquí en adelante)
    for ($anio = (int) $anioInicioTxt; $anio <= (int) $anioFinTxt; $anio++) {
        if($anio < 2024) {
            // No tenemos tablas menores a 2024, ponerle un alto a eso
            $arrayAnios[] = "_2024";
        } else {
            $arrayAnios[] = "_" . $anio;
        }
    }
    
    $n = 0;
    foreach ($arrayAnios as $yearBDBucle) {
        $anioActual = "_" . date("Y");

        if($yearBDBucle == $anioActual) {
            // Para usar las tablas fel sin guion bajo
            $yearBD = "";
            $anioTxt = date("Y");
        } else {
            // Para tablas fel de historial
            $yearBD = $yearBDBucle;
            $anioTxt = str_replace("_", "", $yearBD);
        }

        $dataAnulacion = $cloud->rows("
            SELECT 
                ca.cierreAnulacionId AS cierreAnulacionId,
                ca.cierreDeclaracionId AS cierreDeclaracionId,
                ca.facturaId AS facturaId,
                DATE_FORMAT(ca.fechaPeriodoAplica, '%d/%m/%Y') AS fechaPeriodoAplicaF,
                ff.fechaEmisionFormat AS fechaEmisionFormat,
                ca.obsCierreAnulacion AS obsCierreAnulacion,
                cd.mesNombre AS mesNombre,
                cd.anio AS anio,
                ff.tipoDTEId AS tipoDTEId,
                td.tipoDTE AS tipoDTE,
                fc.nombreCliente AS nombreCliente,
                ff.sucursal AS sucursal,
                ft.subTotal AS subTotal,
                ft.subTotalIVA AS subTotalIVA,
                ft.ivaTotal AS ivaTotal,
                ft.totalFacturaIVA AS totalFacturaIVA,
                ft.descuentoTotal AS descuentoTotal,
                ft.totalFactura AS totalFactura
            FROM fel_cierre_declaracion_anulacion ca
            JOIN fel_cierre_declaracion cd ON ca.cierreDeclaracionId = cd.cierreDeclaracionId
            JOIN view_factura$yearBD ff ON ca.facturaId = ff.facturaId
            JOIN view_factura_total$yearBD ft ON ca.facturaId = ft.facturaId
            JOIN mh_002_tipo_dte td ON ff.tipoDTEId = td.tipoDTEId
            JOIN fel_clientes_ubicaciones fu ON fu.clienteUbicacionId = ff.clienteUbicacionId
            JOIN fel_clientes fc ON fc.clienteId = fu.clienteId
            WHERE ca.flgDelete = ? $wherePeriodo
        ", [0]);

        foreach ($dataAnulacion as $dataAnulacion) {
            $n += 1;

            $jsonEliminar = array(
                'typeOperation'         => "delete",
                'operation'             => "anulacion-pasada",
                'cierreAnulacionId'  => $dataAnulacion->cierreAnulacionId
            );
            $funcionEliminar = htmlspecialchars(json_encode($jsonEliminar));

            $jsonVerDTE = array(
                "facturaId" 		=> $dataAnulacion->facturaId
            );

            $DTE = '<b>Número de DTE:</b> ' . $dataAnulacion->facturaId . '<br>
                    <b>Tipo de documento:</b> ' . $dataAnulacion->tipoDTE . '<br>
                    <b>Fecha documento:</b> '.$dataAnulacion->fechaEmisionFormat.'<br>
                    <b>Cliente:</b> ' . $dataAnulacion->nombreCliente . '<br>
                    <b>Sucursal:</b> ' . $dataAnulacion->sucursal . '<br>
                    ';
            $total = '<div class="row">
                <div class="col">
                    <b>Subtotal:</b><br>
                    <b>Descuento:</b><br>
                    <b>IVA 13.00%:</b><br>
                    <b>Total:</b>
                </div>
                <div class="col text-end">
                    $' . number_format($dataAnulacion->subTotal, 2, ".", ",") . '<br>
                    $' . number_format($dataAnulacion->descuentoTotal, 2, ".", ",") . '<br>
                    $' . number_format($dataAnulacion->ivaTotal, 2, ".", ",") . '<br>
                    $' . number_format($dataAnulacion->totalFacturaIVA, 2, ".", ",") .'
                </div>
            </div>'
            ;
            $observacion = '<b>Fecha aplica:</b> '.$dataAnulacion->fechaPeriodoAplicaF.' <br>
            <b>Observación: </b> '. $dataAnulacion->obsCierreAnulacion;

            $acciones = '
                        <button type="button" class="btn btn-danger btn-sm ttip" onclick="eliminarAnulacion('.$funcionEliminar.');">
                            <i class="fas fa-trash"></i>
                            <span class="ttiptext">Eliminar</span>
                        </button>                    
                        ';
        
            $output['data'][] = array(
                $n, // es #, se dibuja solo en el JS de datatable
                $DTE,
                $observacion,
                $total,
                $acciones
            );
        }
    }



if($n > 0) {
    echo json_encode($output);
} else {
    // No retornar nada para evitar error "null"
    echo json_encode(array('data'=>'')); 
}
