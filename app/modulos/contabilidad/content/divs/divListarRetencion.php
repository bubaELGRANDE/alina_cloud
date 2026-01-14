<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $anioActual = "_" . date("Y");
    $anioTxt = "";

    if($_POST['yearBD'] == $anioActual) {
        // No concatenar por el FEL actual
        $yearBD = "";
        $anioTxt = date("Y");
    } else {
        $yearBD = $_POST['yearBD'];
        $anioTxt = str_replace("_", "", $yearBD);
    }

    if(!($_POST['filtroFecha'] == "")) {
        $whereFecha = "AND (f.fechaEmision = '$_POST[filtroFecha]')";
    } else {
        $whereFecha = "";
    }

    if(!($_POST['filtroNumInterno'] == "")) {
        $whereCodigoInterno = "AND (f.facturaId = '$_POST[filtroNumInterno]')";
    } else {
        $whereCodigoInterno = "";
    }

    if(!($_POST['filtroProveedores'] == "")) {
        $whereProveedor = "AND (cp.nombreProveedor LIKE '%$_POST[filtroProveedores]%' OR cp.nombreComercial LIKE '%$_POST[filtroProveedores]%' OR fc.nombreCliente LIKE '%$_POST[filtroProveedores]%' OR fc.nombreComercialCliente LIKE '%$_POST[filtroProveedores]%')";
    } else {
        $whereProveedor = "";
    }

    $flgFiltroCCFRelacionado = "No";

    if(!($_POST['filtroFechaRelacionado'] == "")) {
        $flgFiltroCCFRelacionado = "Sí";
        $whereCCFRelacionadoFecha = "AND fechaEmisionRelacionada = '".$_POST['filtroFechaRelacionado']."'";
    } else {
        $whereCCFRelacionadoFecha = "";
    }

    if(!($_POST['filtroNumDocumentoRelacionado'] == "")) {
        $whereCCFRelacionadoNumero = "AND numeroDocumentoRelacionada LIKE '%{$_POST['filtroNumDocumentoRelacionado']}%'";
        $flgFiltroCCFRelacionado = "Sí";
    } else {
        $whereCCFRelacionadoNumero = "";
    }

    if($flgFiltroCCFRelacionado == "Sí") {
        $dataCCFRelacionado = $cloud->rows("
            SELECT facturaId FROM fel_factura_relacionada$yearBD
            WHERE flgDelete = ? $whereCCFRelacionadoFecha $whereCCFRelacionadoNumero
        ", [0]);

        if($dataCCFRelacionado) {
            $arrayFacturasIdRelacionadas = array();
            foreach($dataCCFRelacionado as $ccfRelacionado) {
                $arrayFacturasIdRelacionadas[] = $ccfRelacionado->facturaId;
            }
            $implodeFacturas = implode(",", $arrayFacturasIdRelacionadas);
            // Encontró CCF, definir facturaId a la consulta principal
            $whereCCFRelacionado = "AND f.facturaId IN ({$implodeFacturas})";
        } else {
            // No encontró CCF, no cargar ni generar nada
            $whereCCFRelacionado = "";
        }
    } else {
        // Solo consulta de abajo
        $whereCCFRelacionado = "";
    }

    $dataListaFacturas = $cloud->rows("
        SELECT
            f.facturaId AS facturaId,
            fe.sucursalId AS sucursalId,
            s.sucursal AS sucursal,
            fm.mgEstacionId AS mgEstacionId,
            mfes.mgCodEstacion AS mgCodEstacion,
            mfes.mgNombreEstacion AS mgNombreEstacion,
            f.vendedorId AS vendedorId,
            fv.tipoVendedor AS tipoVendedor,
            fv.mgCodVendedor AS mgCodVendedor,
            CASE
                WHEN fv.tipoVendedor = 'Empleado' THEN (
                    SELECT nombreCompleto FROM view_expedientes vexp
                    WHERE vexp.personaId = fv.personaId
                    LIMIT 1
                )
                ELSE fv.mgNombreVendedor
            END AS nombreVendedor,
            f.tipoDTEId AS tipoDTEId,
            cat002.codigoMH AS codTipoDTEMH,
            cat002.tipoDTE AS tipoDTE,
            fm.mgCorrelativoFactura AS numDocumentoMagic,
            f.fechaEmision AS fechaEmision,
            DATE_FORMAT(f.fechaEmision, '%d/%m/%Y') AS fechaEmisionFormat,
            f.horaEmision AS horaEmision,
            fc.clienteId AS clienteId,
            fc.nrcCliente AS nrcCliente,
            CASE
                WHEN fc.nombreCliente = '' OR fc.nombreCliente IS NULL THEN fc.nombreComercialCliente
                ELSE fc.nombreCliente
            END AS nombreCliente,
            cp.proveedorId AS proveedorId,
            CASE
                WHEN cp.nombreProveedor = '' OR cp.nombreProveedor IS NULL THEN cp.nombreComercial
                ELSE cp.nombreProveedor
            END AS nombreProveedor,
            cp.nrcProveedor AS nrcProveedor,
            cp.tipoDocumento AS tipoDocumento,
            cp.numDocumento AS numDocumento,
            td.tipoDocumentoCliente as tipoDocumentoCliente,
            fc.numDocumento as numDocumentoCliente,
            cpu.proveedorUbicacionId AS proveedorUbicacionId,
            fcu.nombreClienteUbicacion AS nombreClienteUbicacion,
            fcu.clienteUbicacionId AS clienteUbicacionId,
            cpu.nombreProveedorUbicacion AS nombreProveedorUbicacion,
            f.porcentajeIVA AS porcentajeIVA,
            fr.porcentajeIVARetenido AS porcentajeIVARetenido,
            fr.ivaRetenido AS ivaRetenido,
            fr.porcentajeIVAPercibido AS porcentajeIVAPercibido,
            fr.ivaPercibido AS ivaPercibido,
            fr.porcentajeRenta AS porcentajeRenta,
            fr.rentaRetenido AS rentaRetenido,
            f.estadoFactura AS estadoFactura,
            f.sujetoExcluidoId AS sujetoExcluidoId,
            f.obsAnulacionInterna AS obsAnulacionInterna
        FROM fel_factura$yearBD f
        JOIN fel_factura_emisor$yearBD fe ON fe.facturaId = f.facturaId
        JOIN cat_sucursales s ON s.sucursalId = fe.sucursalId
        LEFT JOIN fel_factura_magic$yearBD fm ON fm.facturaId = f.facturaId
        LEFT JOIN magic_facturacion_estaciones mfes ON mfes.mgEstacionId = fm.mgEstacionId
        LEFT JOIN fel_vendedores fv ON fv.vendedorId = f.vendedorId
        JOIN mh_002_tipo_dte cat002 ON cat002.tipoDTEId = f.tipoDTEId
        LEFT JOIN comp_proveedores_ubicaciones cpu ON cpu.proveedorUbicacionId = f.proveedorUbicacionId
        LEFT JOIN fel_clientes_ubicaciones fcu ON fcu.clienteUbicacionId = f.clienteUbicacionId
        LEFT JOIN comp_proveedores cp ON cp.proveedorId = cpu.proveedorId
        LEFT JOIN fel_factura_retenciones$yearBD fr ON fr.facturaId = f.facturaId
        LEFT JOIN fel_clientes fc ON fc.clienteId = fcu.clienteId
        LEFT JOIN mh_022_tipo_documento td ON td.tipoDocumentoClienteId = fc.tipoDocumentoMHId
        WHERE  f.flgDelete = ? AND f.estadoFactura <> ? AND f.tipoDTEId = 6 
        $whereFecha $whereCodigoInterno $whereProveedor $whereCCFRelacionado
        ORDER BY f.facturaId DESC, f.horaEmision DESC, f.fechaEmision DESC 
        LIMIT 10
    ", [0, "Anulado"]);

    // Filtros: sucursal, codigoGeneracion, fecha, tipoDTE, proveedor, codMagic, codInterno, Vendedor (Arreglar filtro vendedor, proveedor)
    $n = 0;
    foreach($dataListaFacturas as $listaFactura) {
      $n++;
      $porcentajeIVA = $listaFactura->porcentajeIVA;
      $porcentajeIVARetenido = $listaFactura->porcentajeIVARetenido;
      $porcentajeIVAPercibido = $listaFactura->porcentajeIVAPercibido;
      $porcentajeRentaRetencion = $listaFactura->porcentajeRenta;

      $existeDetalle = $cloud->count("
        SELECT facturaDetalleId FROM fel_factura_detalle$yearBD
        WHERE facturaId = ? AND flgDelete = ?
      ", [$listaFactura->facturaId, 0]);

      if($existeDetalle > 0) {
        $dataTotalDTE = $cloud->row("
            SELECT 
                SUM(subTotalDetalle) AS subTotal,
                SUM(subTotalDetalleIVA) AS subTotalIVA,
                SUM(descuentoTotal) AS descuentoTotal,
                SUM(ivaTotal) AS ivaTotal,
                SUM(totalDetalle) AS total,
                SUM(totalDetalleIVA) AS totalIVA
            FROM fel_factura_detalle$yearBD
            WHERE facturaId = ? AND flgDelete = ?
        ", [$listaFactura->facturaId, 0]);
        // Cambiar las columnas de IVA según el tipo de documento que aplique
            $subTotalDTE = $dataTotalDTE->subTotal;
            $descuentoDTE = $dataTotalDTE->descuentoTotal;
            $ivaDTE = $dataTotalDTE->ivaTotal;
            $totalDTE = $dataTotalDTE->total;
      } else {
        // Comprobante de retención pendiente
        $subTotalDTE = 0;
          $descuentoDTE = 0;
          $ivaDTE = 0;
          $totalDTE = 0;
      }

      if($listaFactura->ivaRetenido > 0) {
          $divIVARetenido = "
              <div class='row'>
                  <div class='col-6 fw-bold'>
                      (-) IVA $porcentajeIVARetenido% retenido
                  </div>
                  <div class='col-6'>
                      <div class='simbolo-moneda fw-bold'>
                          <span>$_SESSION[monedaSimbolo]</span>
                          <div>
                              ". number_format($listaFactura->ivaRetenido, 2, ".", ",") ."
                          </div>
                      </div>
                  </div>
              </div>
          ";
          if($listaFactura->tipoDTEId == 6) {
              $totalDTE;
          } else {
            $totalDTE;
          }
      } else {
          $divIVARetenido = "";
      }

      if($listaFactura->ivaPercibido > 0) {
          $divIVAPercibido = "
              <div class='row'>
                  <div class='col-6 fw-bold'>
                      (+) IVA $porcentajeIVAPercibido% percibido
                  </div>
                  <div class='col-6'>
                      <div class='simbolo-moneda fw-bold'>
                          <span>$_SESSION[monedaSimbolo]</span>
                          <div>
                              ". number_format($listaFactura->ivaPercibido, 2, ".", ",") ."
                          </div>
                      </div>
                  </div>
              </div>
          ";
          $totalDTE += $listaFactura->ivaPercibido;
      } else {
          $divIVAPercibido = "";
      }

      if($listaFactura->rentaRetenido > 0) {
          $divRentaRetenido = "
              <div class='row'>
                  <div class='col-6 fw-bold'>
                      (-) Renta $porcentajeRentaRetencion%
                  </div>
                  <div class='col-6'>
                      <div class='simbolo-moneda fw-bold'>
                          <span>$_SESSION[monedaSimbolo]</span>
                          <div>
                              ". number_format($listaFactura->rentaRetenido, 2, ".", ",") ."
                          </div>
                      </div>
                  </div>
              </div>
          ";
          $totalDTE -= $listaFactura->rentaRetenido;
      } else {
          $divRentaRetenido = "";
      }

    if ($listaFactura->proveedorUbicacionId > 0) {
      // Se hizo con esta interfaz, guardándose en tabla de proveedores
      $divProveedor = "<b><i class='fas fa-user-tie'></i> Proveedor: </b> $listaFactura->nombreProveedor - $listaFactura->nombreProveedorUbicacion<br>";
      $documentoCliente = "
        <b>NRC: </b> $listaFactura->nrcProveedor<br>
        <b>$listaFactura->tipoDocumento:</b> $listaFactura->numDocumento
      ";
    } else {
      // Se hizo desde FEL con archivo de Magic
      $divProveedor ="<b><i class='fas fa-user-tie'></i> Proveedor: </b> $listaFactura->nombreCliente - $listaFactura->nombreClienteUbicacion<br>";
      $documentoCliente = "
        <b>NRC: </b> $listaFactura->nrcCliente<br>
        <b>$listaFactura->tipoDocumentoCliente:</b> $listaFactura->numDocumentoCliente
      ";
    }

    $dataCodigosMH = $cloud->row("
        SELECT 
            ffcex.codigoGeneracion AS codigoGeneracion,
            ffcex.numeroControl AS numeroControl
        FROM fel_factura_certificacion$yearBD ffcex 
        WHERE (ffcex.estadoCertificacion = 'Certificado' OR ffcex.descripcionMsg LIKE 'RECIBIDO%') AND ffcex.facturaId = ? AND ffcex.flgDelete = ?
        ORDER BY ffcex.facturaCertificacionId DESC 
        LIMIT 1
    ", [$listaFactura->facturaId, 0]);

    if($dataCodigosMH) {
        if($n == 1) {
            // Primera card
            echo "<h5 class='fw-bold'>Resultados en el año: {$anioTxt}</h5>";
        } else {
            // Otras cards
        }
?>
      <div class="prod-card mt-2">
          <div class="row mt-3">
              <div class="col-md-9">
                  <div class="row">
                      <div class="col-md-4 mb-2">  
                          <b><i class="fas fa-file-alt"></i> Tipo de DTE: </b> <?php echo "($listaFactura->codTipoDTEMH) " . ($listaFactura->tipoDTEId == 1 ? "$listaFactura->tipoDTE / Consumidor final" : $listaFactura->tipoDTE); ?><br>
                          <b><i class="fas fa-list-ol"></i> Número DTE interno: </b><?php echo $listaFactura->facturaId; ?><br>
                          <b><i class="fas fa-list-ol"></i> Número DTE Magic: </b><?php echo $listaFactura->numDocumentoMagic; ?><br>
                          <b><i class="fas fa-list-ol"></i> Código generación: </b><br><?php echo $dataCodigosMH->codigoGeneracion; ?><br>
                          <b><i class="fas fa-list-ol"></i> Número de control: </b><br><?php echo $dataCodigosMH->numeroControl; ?><br>
                      </div>
                      <div class="col-md-4 mb-2">   
                          <b><i class="fas fa-calendar-day"></i> Fecha y hora de emisión: </b> <?php echo $listaFactura->fechaEmisionFormat; ?> - <?php echo $listaFactura->horaEmision; ?><br>
                          <b><i class="fas fa-building"></i> Sucursal: </b> <?php echo $listaFactura->sucursal; ?><br>
                          <b><i class="fas fa-cash-register"></i> Estación: </b> <?php echo $listaFactura->mgCodEstacion; ?><br>
                          <b><i class="fas fa-user-tie"></i> Vendedor: </b> <?php echo $listaFactura->nombreVendedor; ?><br>

                      </div>
                      <?php 
                          if($listaFactura->tipoDTEId == 10) {
                              $dataSujetoExcluido = $cloud->row("
                                  SELECT
                                      fse.nombreSujeto AS nombreSujeto,
                                      cat022se.codigoMH AS codigoTipoDocumentoPersonaMH,
                                      cat022se.tipoDocumentoCliente AS tipoDocumentoPersona,
                                      fse.numDocumento AS numDocumentoPersona,
                                      cpdcliente.codigoMH AS codigoDepartamentoPersona,
                                      cpdcliente.departamentoPais AS departamentoPersona,
                                      cpmcliente.codigoMH AS codigoMunicipioPersona,
                                      cpmcliente.municipioPais AS municipioPersona,
                                      fse.direccionSujeto AS direccionSujeto,
                                      cat019cliente.codigoMh AS codigoActividadPersonaMH,
                                      cat019cliente.actividadEconomica AS actividadEconomicaPersona,
                                      fse.correoSujeto AS correoSujeto
                                  FROM fel_sujeto_excluido fse
                                  JOIN mh_022_tipo_documento cat022se ON cat022se.tipoDocumentoClienteId = fse.tipoDocumentoMHId
                                  JOIN cat_paises_municipios cpmcliente ON cpmcliente.paisMunicipioId = fse.paisMunicipioId
                                  JOIN cat_paises_departamentos cpdcliente ON cpdcliente.paisDepartamentoId = cpmcliente.paisDepartamentoId
                                  JOIN mh_019_actividad_economica cat019cliente ON cat019cliente.actividadEconomicaId = fse.actividadEconomicaId
                                  WHERE fse.sujetoExcluidoId = ? AND fse.flgDelete = ?
                              ", [$listaFactura->sujetoExcluidoId, 0]);

                              $documentoCliente = '<b>'.$dataSujetoExcluido->tipoDocumentoPersona.': </b>' . $dataSujetoExcluido->numDocumentoPersona;
                      ?>
                              <div class="col-md-4 mb-2">   
                                  <b><i class="fas fa-user-tie"></i> Persona: </b> <?php echo "$dataSujetoExcluido->nombreSujeto - Dirección principal"; ?><br>
                                  <!--  
                                  <b><i class="fas fa-user-tie"></i> Correo FEL: </b> <?php //echo "$dataSujetoExcluido->correoSujeto"; ?><br>
                                  -->
                                  <?php echo $documentoCliente; ?>
                              </div>
                      <?php 
                          } else {
                      ?>
                      
                              <div class="col-md-4 mb-2">   
                                <?php 
                                    #echo $Proveedor;
                                    echo $divProveedor;
                                ?>
                                  <!--  
                                  <b><i class="fas fa-user-tie"></i> Correo FEL: </b> <?php //echo "$listaFactura->nombreProveedor - $listaFactura->nombreProveedorUbicacion"; ?><br>
                                  -->
                                  <?php #echo $nrc;
                                      echo $documentoCliente;
                                  ?>
                              </div>
                      <?php 
                          }
                      ?>
                  </div>
                  
                  <?php 
                      if($listaFactura->estadoFactura == "Anulado") {
                          // Traer motivo de anulación de la tabla: fel_factura_anulacion
                  ?>
                          <div class="row">
                              <div class="col-md-12 mb-4">
                                  <b><i class="fas fa-edit"></i> Motivo de anulación: </b><?php echo "$listaFactura->obsAnulacionInterna"; ?><br>
                              </div>
                          </div>
                  <?php 
                      } 
                  ?>
              </div>
              <div class="col-md-3 mb-4 border-start">   
                  <b><i class="fas fa-coins"></i> Totales: </b>
                      <div class='row'>
                          <div class='col-6 fw-bold'>
                              (=) Subtotal
                          </div>
                          <div class='col-6'>
                              <div class='simbolo-moneda fw-bold'>
                                  <span><?php echo $_SESSION['monedaSimbolo']; ?></span>
                                  <div><?php echo number_format($subTotalDTE, 2, ".", ","); ?></div>
                              </div>
                          </div>
                      </div>
                      <div class='row'>
                          <div class='col-6 fw-bold'>
                              (-) Descuento
                          </div>
                          <div class='col-6'>
                              <div class='simbolo-moneda fw-bold'>
                                  <span><?php echo $_SESSION['monedaSimbolo']; ?></span>
                                  <div><?php echo is_null($descuentoDTE) ? '0.00' : number_format($descuentoDTE, 2, ".", ","); ?></div>
                              </div>
                          </div>
                      </div>
                      <div class='row'>
                          <div class='col-6 fw-bold'>
                              (+) IVA <?php echo $porcentajeIVA . "%"; ?>
                          </div>
                          <div class='col-6'>
                              <div class='simbolo-moneda fw-bold'>
                                  <span><?php echo $_SESSION['monedaSimbolo']; ?></span>
                                  <div><?php echo is_null($ivaDTE) ? '0.00' : number_format($ivaDTE, 2, ".", ","); ?></div>
                              </div>
                          </div>
                      </div>
                      <?php echo $divIVARetenido;
                            echo $divIVAPercibido;
                            echo $divRentaRetenido;
                      ?>
                      <div class='row'>
                          <div class='col-6 fw-bold'>
                              (=) Total
                          </div>
                          <div class='col-6'>
                              <div class='simbolo-moneda fw-bold'>
                                  <span><?php echo $_SESSION['monedaSimbolo']; ?></span>
                                  <div><?php echo number_format($totalDTE, 2, ".", ","); ?></div>
                              </div>
                          </div>
                      </div>
              </div>
          </div>
            
          <div class="foot-card">  
              <?php 
                    $jsonComprobante = array(
                    "facturaId" 		    => $listaFactura->facturaId,
                    "tipoDTE" 			=> $listaFactura->codTipoDTEMH,
                    "yearBD"            => $yearBD
                    );
                  $jsonVerDTE = array(
                      "facturaId" 		    => $listaFactura->facturaId,
                      "tipoDTE" 			=> "($listaFactura->codTipoDTEMH) $listaFactura->tipoDTE",
                      "codigoGeneracion"        => $dataCodigosMH->codigoGeneracion,
                      "numeroControl"           => $dataCodigosMH->numeroControl,
                      "yearBD"                  => $yearBD
                  );
                  $jsonVerDTEjson = array(
                      "facturaId" 		    => $listaFactura->facturaId,
                      "tipoDTE" 			=> $listaFactura->codTipoDTEMH,
                      "yearBD"              => $yearBD
                  );
                  $jsonAnularDTE = array(
                      "typeOperation" 	    => "certificacion",
                      "facturaId" 		    => $listaFactura->facturaId,
                      "tipoDTEMH" 		    => $listaFactura->codTipoDTEMH,
                      "tipoDTE" 			=> $listaFactura->tipoDTE,
                      "proveedorId"         => $listaFactura->proveedorId,
                      "nombreProveedor"     => $listaFactura->nombreProveedor,
                      "codigoGeneracion"    => $dataCodigosMH->codigoGeneracion,
                      "numeroControl"       => $dataCodigosMH->numeroControl,
                      "yearBD"              => $yearBD
                  );
                  $jsonReporteDTE = array(
                      "file"                    => "dte",
                      "facturaId"               => $listaFactura->facturaId,
                      "fechaEmision"            => $listaFactura->fechaEmision,
                      "tipoDTEId"               => $listaFactura->tipoDTEId,
                      "codigoGeneracion"        => $dataCodigosMH->codigoGeneracion,
                      "numeroControl"           => $dataCodigosMH->numeroControl,
                      "proveedorUbicacionId"    => $listaFactura->proveedorUbicacionId,
                      "yearBD"                  => $yearBD
                  );
                  $jsonReporteTicket = array(
                      "file"                    => "ticket-dte",
                      "facturaId"               => $listaFactura->facturaId,
                      "fechaEmision"            => $listaFactura->fechaEmision,
                      "tipoDTEId"               => $listaFactura->tipoDTEId,
                      "codigoGeneracion"        => $dataCodigosMH->codigoGeneracion,
                      "numeroControl"           => $dataCodigosMH->numeroControl,
                      "proveedorUbicacionId"    => $listaFactura->proveedorUbicacionId,
                      "yearBD"                  => $yearBD
                  );
                  // JSON para agregar complemento
                  $jsonComplementoDTE = array(
                      "typeOperation" 	        => "insert",
                      "facturaId"               => $listaFactura->facturaId,
                      "proveedorUbicacionId"    => $listaFactura->proveedorUbicacionId,
                      "yearBD"                  => $yearBD
                  );
                  $jsonHistorialCorreos = array(
                      "facturaId"               => $listaFactura->facturaId,
                      "nombreCliente"           => $listaFactura->nombreProveedor,
                      "tipoDTE"                 => "($listaFactura->codTipoDTEMH) $listaFactura->tipoDTE",
                      "yearBD"                  => $yearBD
                  );

                  $jsonAnulacionInterna = array(
                    "typeOperation" 	    => "update",
                    "facturaId"             => $listaFactura->facturaId,
                    "yearBD"                => $yearBD
                );
                  
                  $cantidadCorreosEnviados = $cloud->count("
                      SELECT bitFELCorreoId FROM bit_fel_correos
                      WHERE facturaId = ? AND anio = ? AND flgDelete = ?
                  ", [$listaFactura->facturaId, $anioTxt, 0]);

                  switch($listaFactura->estadoFactura) {
                      // Hacer que el ver DTE sea la misma page pero en una modal (CIF)
                      case 'Finalizado':
                          $ambienteDTE = '01';
                          echo '
                              <button type="button" class="card-btn card-btn-primary ttip" onclick="complementoDTE('.htmlspecialchars(json_encode($jsonComplementoDTE)).');">
                                  <i class="fas fa-list-ol"></i> Complemento DTE
                                  <span class="ttiptext">Complemento en el DTE</span>
                              </button>
                          ';
                          //if(in_array(220, $_SESSION["arrayPermisos"])) {
                          // if((in_array(220, $_SESSION["arrayPermisos"])) || (in_array(200, $_SESSION["arrayPermisos"]))) {
                              echo '<button type="button" class="card-btn card-btn-info ttip" onclick="modalReportesDTE('.htmlspecialchars(json_encode($jsonReporteDTE)).');">
                                  <i class="fas fa-print"></i> Imprimir DTE
                                  <span class="ttiptext">Documento tributario electrónico</span>
                              </button> ';
                          // }

                          if((in_array(220, $_SESSION["arrayPermisos"])) || (in_array(220, $_SESSION["arrayPermisos"]))) {
                              echo '<a href="https://admin.factura.gob.sv/consultaPublica?ambiente='.$ambienteDTE.'&codGen='.$dataCodigosMH->codigoGeneracion.'&fechaEmi='.$listaFactura->fechaEmision.'" class="card-btn card-btn-primary" target="_blank">
                                  <i class="fas fa-laptop"></i> Consultar DTE
                              </a> ';
                          }
                          if((in_array(220, $_SESSION["arrayPermisos"])) || (in_array(220, $_SESSION["arrayPermisos"]))) {
                              echo '<button type="button" class="card-btn card-btn-primary" onclick="modalVerDTE('.htmlspecialchars(json_encode($jsonVerDTE)).');">
                                  <i class="far fa-file-alt"></i> Ver DTE Proveedores
                              </button> ';
                          }
                          /*
                          if((in_array(220, $_SESSION["arrayPermisos"])) || (in_array(220, $_SESSION["arrayPermisos"]))) {
                              echo '<button type="button" class="card-btn card-btn-primary" onclick="modalVerCert('.htmlspecialchars(json_encode($jsonVerDTE)).');">
                                  <i class="far fa-file-alt"></i> Ver certificación
                              </button> ';
                          }
                          */
                          if((in_array(220, $_SESSION["arrayPermisos"])) || (in_array(220, $_SESSION["arrayPermisos"]))) {
                              echo '<button type="button" class="card-btn card-btn-primary" onclick="modalJsonDTE('.htmlspecialchars(json_encode($jsonVerDTEjson)).');">
                                  <i class="far fa-file-code"></i> Ver JSON
                              </button> ';
                          }
                          echo '
                              <button type="button" class="card-btn card-btn-primary ttip" onclick="modalHistorialCorreos('.htmlspecialchars(json_encode($jsonHistorialCorreos)).');">
                                  <span class="badge rounded-pill bg-light text-dark">'.$cantidadCorreosEnviados.'</span> <i class="fas fa-envelope"></i> Enviados
                                  <span class="ttiptext">Historial de correos enviados</span>
                              </button>
                          ';
                          if((in_array(220, $_SESSION["arrayPermisos"])) || (in_array(220, $_SESSION["arrayPermisos"]))) {
                              echo '<button type="button" class="card-btn card-btn-danger" onclick="invalidarDTE('.htmlspecialchars(json_encode($jsonAnularDTE)).');">
                                  <i class="fas fa-ban"></i> Invalidar DTE
                              </button> ';
                          }
                      break;

                      case 'Anulado':
                          // Hacer que el ver DTE sea la misma page pero en una modal (CIF)
                          echo '
                              <button type="button" class="card-btn card-btn-primary">
                                  <i class="far fa-file-alt"></i> Ver DTE anulado
                              </button>
                          ';
                      break;

                      default:
                          // Pendiente
                          echo '
                            <button type="button" class="card-btn card-btn-primary" onclick="changePage(`'.$_SESSION["currentRoute"].'`, `comprobante-retencion`, `facturaId='.$listaFactura->facturaId.'`);">
                                <i class="fas fa-sync-alt"></i> Continuar Comprobante
                            </button>
                              <button type="button" class="card-btn card-btn-danger ttip" onclick="modalAnulacionInterna('.htmlspecialchars(json_encode($jsonAnulacionInterna)).');">
                              <i class="fas fa-ban"></i> Anular DTE
                                  <span class="ttiptext">Anular DTE</span>
                              </button>
                          ';
                      break;
                  }
              ?>
          </div>
      </div>
<?php
        } else {
            // No fue certificada todavia o dio error
        }
    } // foreach listaFactura

    if($n == 0) {
        echo "<div class='text-center'>No se encontraron DTE en el año {$anioTxt} con los filtros aplicados</div>";
    } else {
        // Se cargaron las cards
    }
?>