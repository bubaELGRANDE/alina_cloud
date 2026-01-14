<?php

/*
 * DELETE ESPECIFICANDO CAMPOS (condiciones):
 * 	$delete = ['columnas' => "hola xd"];
 * 	$cloud->delete('test', $delete);
 * DELETE POR ID:
 * 	$cloud->deleteById('tabla', "columnaId", id);
 * DELETE MULTIPLE ID:
 * 	$cloud->deleteByIds('tabla', "columnaId", "2, 4, 6, N");
 */
if (isset($_SESSION['usuarioId']) && isset($operation)) {
	switch ($operation) {
		case 'periodo-contable':
			/*
			 * POST:
			 * id
			 */
			$cloud->deleteById('conta_partidas_contables_periodos', 'partidaContaPeriodoId ', $_POST['id']);
			$cloud->writeBitacora('movDelete', '(' . $fhActual . ') Eliminó la período contable:' . $_POST['id']);
			echo 'success';
			break;
		case 'parametrizacion-comision':
			/*
			 * POST:
			 * typeOperation
			 * operation
			 * id
			 */
			$cloud->deleteById('conta_comision_porcentaje_lineas', 'comisionPorcentajeLineaId ', $_POST['id']);

			$dataCondicion = $cloud->row("
                SELECT
                CONCAT('(', l.abreviatura, ') ', l.linea) AS nombreLinea,
                cpl.rangoPorcentajeInicio AS rangoPorcentajeInicio,
                cpl.rangoPorcentajeFin AS rangoPorcentajeFin,
                cpl.porcentajePago AS porcentajePago
                FROM conta_comision_porcentaje_lineas cpl
                JOIN temp_cat_lineas l ON l.lineaId = cpl.lineaId
                ", [$_POST['id']]);

			$cloud->writeBitacora('movDelete', '(' . $fhActual . ') Eliminó la condición de la línea: ' . $dataCondicion->nombreLinea . ' Rango inicio: ' . $dataCondicion->rangoPorcentajeInicio . ' Rango fin: ' . $dataCondicion->rangoPorcentajeFin . ' Porcentaje de pago: ' . $dataCondicion->porcentajePago . ', ');
			echo 'success';
			break;

		case 'comision-clasificacion':
			/*
			 * POST:
			 * typeOperation
			 * operation
			 * comisionClasificacionId
			 * tblClasif
			 */
			$cloud->deleteById('conta_comision_reporte_clasificacion', 'comisionClasificacionId', $_POST['comisionClasificacionId']);

			$dataClasificacion = $cloud->row("
                SELECT tituloClasificacion FROM conta_comision_reporte_clasificacion
                WHERE comisionClasificacionId = ?
                ", [$_POST['comisionClasificacionId']]);

			$existeClasificacion = $cloud->count('
					SELECT comisionClasificacionDetalleId FROM conta_comision_reporte_clasificacion_detalle
					WHERE comisionClasificacionId = ?
				', [$_POST['comisionClasificacionId']]);

			if ($existeClasificacion == 0) {
				$cloud->writeBitacora('movDelete', '(' . $fhActual . ') Eliminó la clasificación: ' . $dataClasificacion->tituloClasificacion . ', ');
			} else {
				// Tiene detalles
				$totalEliminados = $cloud->deleteById('conta_comision_reporte_clasificacion_detalle', 'comisionClasificacionId', $_POST['comisionClasificacionId']);
				$cloud->writeBitacora('movDelete', '(' . $fhActual . ') Eliminó la clasificación de comisiones: ' . $dataClasificacion->tituloClasificacion . ' con un total de ' . $totalEliminados . ' detalles de clasificación, ');
			}

			echo 'success';
			break;

		case 'comision-clasificacion-detalle':
			/*
			 * POST:
			 * typeOperation
			 * operation
			 * comisionClasificacionDetalleId
			 * tblClasif
			 */
			$cloud->deleteById('conta_comision_reporte_clasificacion_detalle', 'comisionClasificacionDetalleId', $_POST['comisionClasificacionDetalleId']);

			$dataParamDetalle = $cloud->row("
                SELECT valorClasificacion FROM conta_comision_reporte_clasificacion_detalle
                WHERE comisionClasificacionDetalleId = ?
                ", [$_POST['comisionClasificacionDetalleId']]);

			$cloud->writeBitacora('movDelete', '(' . $fhActual . ') Eliminó la clasificación detalle (comisiones): ' . $dataParamDetalle->valorClasificacion . ', ');

			echo 'success';
			break;

		case 'clasificacion-gasto':
			/*
			 * POST:
			 * typeOperation
			 * operation
			 * clasifGastoSalarioId
			 */

			$update = [
				'clasifGastoSalarioId' => 0
			];
			$where = ['clasifGastoSalarioId-duplicado' => $_POST['clasifGastoSalarioId']];
			$cloud->update('th_expediente_personas', $update, $where);

			$cloud->deleteById('cat_clasificacion_gastos_salario', 'clasifGastoSalarioId', $_POST['clasifGastoSalarioId']);
			$cloud->writeBitacora('movInsert', '(' . $fhActual . ') Eliminó la clasificacion: ' . $_POST['nombreGastoSalario']);

			echo 'success';
			break;

		case 'clasificacion-gasto-empleado':
			$update = [
				'clasifGastoSalarioId' => 0
			];
			$where = ['prsExpedienteId' => $_POST['prsExpedienteId']];
			$cloud->update('th_expediente_personas', $update, $where);
			$cloud->writeBitacora('movInsert', '(' . $fhActual . ') Eliminó el empleado :' . 'empleado' . 'de la clasificacion' . 'clasificacion');

			echo 'success';
			break;

		case 'parametrizacion-devengo':
			/*
			 * POST:
			 * typeOperation
			 * operation
			 * catPlanillaDevengoId
			 * tipoDevengo
			 * nombreDevengo
			 */
			$cloud->deleteById('cat_planilla_devengos', 'catPlanillaDevengoId', $_POST['catPlanillaDevengoId']);
			$cloud->deleteById('cat_planilla_devengos', 'catPlanillaDevengoIdSuperior', $_POST['catPlanillaDevengoId']);

			$cloud->writeBitacora('movDelete', "( $fhActual ) Eliminó el devengo: $_POST[nombreDevengo] ($_POST[tipoDevengo]), ");

			echo 'success';
			break;

		case 'parametrizacion-descuento':
			/*
			 * POST:
			 * typeOperation
			 * tituloModal
			 * catPlanillaDescuentoId
			 * catPlanillaDescuentoIdSuperior
			 * tblOtrosDescuentos
			 */
			$cloud->deleteById('cat_planilla_descuentos', 'catPlanillaDescuentoId', $_POST['catPlanillaDescuentoId']);
			$cloud->deleteById('cat_planilla_descuentos', 'catPlanillaDescuentoIdSuperior', $_POST['catPlanillaDescuentoId']);

			$cloud->writeBitacora('movDelete', "( $fhActual ) Eliminó el decuento: $_POST[nombreDescuento]");

			echo 'success';
			break;

		case 'planilla-otros-descuentos':
			/*
			 * POST:
			 * typeOperation
			 * operation
			 * nombreCompleto
			 * prsExpedienteId
			 * quincenaId
			 * descuentoId
			 * nombreDescuento
			 */
			// Validar el estadoPlanilla si es Cerrada no permitir eliminar
			$dataQuincena = $cloud->row('
					SELECT
						estadoQuincena
					FROM cat_quincenas
					WHERE quincenaId = ? AND flgDelete = ?
				', [$_POST['quincenaId'], 0]);

			if ($dataQuincena->estadoQuincena == 'Pendiente') {
				$cloud->deleteById('conta_planilla_programado_descuentos', 'planillaDescuentoProgramadoId', $_POST['descuentoId']);
			} else {
				// Calculada
				$cloud->deleteById('conta_planilla_descuentos', 'planillaDescuentoId', $_POST['descuentoId']);
			}
			$cloud->writeBitacora('movDelete', "( $fhActual ) Eliminó el descuento del empleado: $_POST[nombreCompleto] (ID: $_POST[descuentoId], Quincena: $_POST[quincenaId])");
			echo 'success';
			break;

		case 'planilla-devengos':
			/*
			 * POST:
			 * nombreCompleto
			 * prsExpedienteId
			 * quincenaId
			 * devengoId
			 * nombreDevengo
			 * tipoDevengo
			 */
			// Validar el estadoPlanilla si es Cerrada no permitir eliminar
			$dataQuincena = $cloud->row('
					SELECT
						estadoQuincena
					FROM cat_quincenas
					WHERE quincenaId = ? AND flgDelete = ?
				', [$_POST['quincenaId'], 0]);

			if ($dataQuincena->estadoQuincena == 'Pendiente') {
				$cloud->deleteById('conta_planilla_programado_devengos', 'planillaDevengoProgramadoId', $_POST['devengoId']);
			} else {
				// Calculada
				$cloud->deleteById('conta_planilla_devengos', 'planillaDevengoId', $_POST['devengoId']);
			}
			$cloud->writeBitacora('movDelete', "( $fhActual ) Eliminó el devengo $_POST[tipoDevengo] del empleado: $_POST[nombreCompleto] (ID: $_POST[devengoId], Quincena: $_POST[quincenaId])");
			echo 'success';
			break;

		case 'datos-cliente-PEP':
			$cloud->deleteById('fel_clientes_pep', 'clientePEPId', $_POST['clientePEPId']);
			$cloud->writeBitacora('movDelete', "( $fhActual ) Eliminó a la persona politicamente expuesta: $_POST[nombreCliente] )");
			echo 'success';
			break;

		case 'datos-cliente':
			$cloud->deleteById('fel_clientes', 'clienteId', $_POST['clienteId']);
			$cloud->writeBitacora('movDelete', "( $fhActual ) Eliminó al cliente: $_POST[nombreCliente] )");
			echo 'success';
			break;

		case 'contacto-cliente':
			/*
			 * typeOperation
			 * operation
			 * clienteUbicacionId
			 * clienteContactoId
			 */

			$cloud->deleteById('fel_clientes_contactos', 'clienteContactoId', $_POST['clienteContactoId']);

			$cloud->writeBitacora('movDelete', '(' . $fhActual . ') Eliminó el contacto: (' . $_POST['tipoContacto'] . ') ' . $_POST['contactoCliente'] . ', del cliente: ' . $_POST['nombreCliente'] . ', ');

			echo 'success';
			break;

		case 'direccion-cliente':
			$cloud->deleteById('fel_clientes_ubicaciones', 'clienteUbicacionId', $_POST['clienteUbicacionId']);
			$cloud->deleteById('fel_clientes_contactos', 'clienteUbicacionId', $_POST['clienteUbicacionId']);

			$cloud->writeBitacora('movDelete', '(' . $fhActual . ') Eliminó la ubicación: ' . $_POST['nombreUbicacion'] . ', del cliente: ' . $_POST['nombreCliente'] . ', ');

			echo 'success';
			break;

		case 'datos-PEP-nucleoFamiliar':
			$cloud->deleteById('fel_clientes_pep_familia', 'clientePEPFamiliaId', $_POST['clientePEPFamId']);

			$cloud->writeBitacora('movDelete', '(' . $fhActual . ') Eliminó al familiar: ' . $_POST['nombreFam'] . ', ');

			echo 'success';
			break;

		case 'datos-PEP-sociedades':
			$cloud->deleteById('fel_clientes_pep_relpatrimonial', 'clientePEPPatrimonialId', $_POST['clientePEPSocId']);

			$cloud->writeBitacora('movDelete', '(' . $fhActual . ') Eliminó la empresa: ' . $_POST['razonSocial'] . ', ');

			echo 'success';
			break;

		case 'clientes-accionistas':
			$cloud->deleteById('fel_clientes_accionistas', 'clienteAccionistaid', $_POST['clienteAccionistaid']);

			$cloud->writeBitacora('movDelete', '(' . $fhActual . ') Eliminó al accionista: ' . $_POST['nombreAccionista'] . ', ');

			echo 'success';
			break;

		case 'datos-cliente-proveedor':
			$cloud->deleteById('fel_clientes_relacion', 'clienteRelacionId', $_POST['clienteRelacionId']);

			$cloud->writeBitacora('movDelete', '(' . $fhActual . ') Eliminó al ' . $_POST['tipoRelacion'] . ': ' . $_POST['razonSocial'] . ' del cliente: ' . $_POST['nombreCliente'] . ', ');

			echo 'success';
			break;

		case 'comprobante-detalle-delete':
			/*
			 * typeOperation
			 * operation
			 * facturaId
			 * facturaDetalleId
			 */

			$cloud->deleteById('fel_factura_detalle', 'facturaDetalleId', $_POST['facturaDetalleId']);

			$ivaRetenido = $cloud->row('
					SELECT SUM(ivaRetenidoDetalle) AS totalIva
					FROM fel_factura_detalle
					WHERE facturaId = ? AND flgDelete = ?
				', [$_POST['facturaId'], 0]);

			// Insertar el nuevo registro de iva retenido
			$update = [
				'ivaRetenido' => $ivaRetenido->totalIva
			];
			$where = ['facturaId' => $_POST['facturaId']];

			$cloud->update('fel_factura_retenciones', $update, $where);

			$cloud->writeBitacora('movDelete', '(' . $fhActual . ') Eliminó el comprobante: (' . $_POST['facturaDetalleId'] . '),');

			echo 'success';
			break;

		case 'complemento-DTE':
			$cloud->deleteById('fel_factura_complementos' . $_POST['yearBD'], 'facturaComplementoId', $_POST['facturaComplementoId']);

			$cloud->writeBitacora('movDelete', '(' . $fhActual . ') Eliminó el complemento: (' . $_POST['facturaComplementoId'] . '), ');

			echo 'success';
			break;

		case 'eliminar-periodo-declarado':
			$cloud->deleteById('fel_cierre_declaracion', 'cierreDeclaracionId', $_POST['idDeclaracion']);

			$cloud->writeBitacora('movDelete', '(' . $fhActual . ') Eliminó la declaración de: ' . $_POST['periodo'] . ', ');

			echo 'success';
			break;

		case 'anulacion-pasada':
			$cloud->deleteById('fel_cierre_declaracion_anulacion', 'cierreAnulacionId', $_POST['cierreAnulacionId']);

			$cloud->writeBitacora('movDelete', '(' . $fhActual . ') Eliminó la anulación de periodos pasados ,');

			echo 'success';
			break;

		case 'eliminar-parametrizacion-compartida':
			$cloud->deleteById('conta_comision_compartida_parametrizacion', 'comisionCompartidaParamId', $_POST['idParam']);

			$cloud->writeBitacora('movDelete', '(' . $fhActual . ') Eliminó la parametrización compartida del cliente ' . $_POST['nombreCliente'] . ', ');

			echo 'success';
			break;

		case 'eliminar-parametrizacion-compartida-detalle':
			$cloud->deleteById('conta_comision_compartida_parametrizacion_detalle', 'comisionCompartidaParamDetalleId', $_POST['idParam']);

			$cloud->writeBitacora('movDelete', '(' . $fhActual . ') Eliminó la parametrización compartida del vendedor ' . $_POST['vendedor'] . ', ');

			echo 'success';
			break;

		case 'pagos-transferencias-quedan':
			$cloud->deleteById('conta_pagos_transferencias_detalle', 'pagoTransferenciaDetalleId', $_POST['pagoTransferenciaDetalleId']);

			$cloud->writeBitacora('movDelete', '(' . $fhActual . ") Eliminó un pago por transferencia de Quedan (Ref.: $_POST[pagoTransferenciaDetalleId]), ");

			echo 'success';
			break;

		case 'pagos-transferencias-otros-pagos':
			$cloud->deleteById('conta_pagos_transferencias_detalle', 'pagoTransferenciaDetalleId', $_POST['pagoTransferenciaDetalleId']);

			$cloud->writeBitacora('movDelete', '(' . $fhActual . ") Eliminó un pago por transferencia de Otros pagos (Ref.: $_POST[pagoTransferenciaDetalleId]), ");

			echo 'success';
			break;

		case 'bonos-encargados':
			/*
			 * POST:
			 * bonoPersonaId
			 * nombreCompleto
			 */

			$cloud->deleteById('conf_bonos_personas', 'bonoPersonaId', $_POST['bonoPersonaId']);
			$cloud->writeBitacora('movDelete', "( $fhActual ) Eliminó a: $_POST[nombreCompleto] de la parametrización de encargados de bonos");

			echo 'success';
			break;

		case 'bonos-encargados-detalle':
			/*
			 * POST:
			 * bonoPersonaDetalleId
			 * nombreCompleto
			 * nombreEncargado
			 */

			$cloud->deleteById('conf_bonos_personas_detalle', 'bonoPersonaDetalleId', $_POST['bonoPersonaDetalleId']);
			$cloud->writeBitacora('movDelete', "( $fhActual ) Eliminó al empleado: $_POST[nombreCompleto] de la parametrización de bonos del encargado: $_POST[nombreEncargado]");

			echo 'success';
			break;

		case 'bonos-pagos-empleado':
			/*
			 * POST:
			 * planillaBonoId
			 * montoBono
			 * nombreCompleto
			 * nombreEncargado
			 */

			$cloud->deleteById('conta_planilla_bonos', 'planillaBonoId', $_POST['planillaBonoId']);
			$cloud->writeBitacora('movDelete', "( $fhActual ) Eliminó el bono del  empleado: $_POST[nombreCompleto] de la parametrización de bonos del encargado: $_POST[nombreEncargado]");

			echo 'success';
			break;

		case 'sincronizacion-magic-compras-detalle':
			/*
			 * POST:
			 * bitExportacionMagicDetalleId
			 * tablaExportacion
			 * tablaExportacionId
			 */

			$cloud->deleteById('bit_exportaciones_magic_detalle', 'bitExportacionMagicDetalleId', $_POST['bitExportacionMagicDetalleId']);
			$cloud->writeBitacora('movDelete', "({$fhActual}) Eliminó una Compra de la Sincronización con Magic (Bitácora ID: {$_POST['bitExportacionMagicDetalleId']}, Compra: {$_POST['tablaExportacionId']}, Tabla: {$_POST['tablaExportacion']}");

			echo 'success';
			break;
		case 'partida-contable':
			/*
			 * POST:
			 * id
			 */
			
			$update = [
				'flgDelete' => 1
			];

			$where = [
				'partidaContableId' => $_POST['partidaContableId']
			];

			$cloud->update('conta_partidas_contables_detalle', $update, $where);
			$cloud->deleteById('conta_partidas_contables', 'partidaContableId ', $_POST['partidaContableId']);
			$cloud->writeBitacora('movDelete', '(' . $fhActual . ') Eliminó el un registro de la partida contable contable:' . $_POST['partidaContableId']);
			echo 'success';
			break;
		case 'partida-contable-detalle':
			/*
			 * POST:
			 * id
			 */
			$cloud->deleteById('conta_partidas_contables_detalle', 'partidaContableDetalleId ', $_POST['id']);
			$cloud->writeBitacora('movDelete', '(' . $fhActual . ') Eliminó el un registro de la partida contable contable:' . $_POST['id']);
			echo 'success';
			break;
		default:
			echo 'No se encontró la operación.';
			break;
	}
} else {
	header('Location: /indupal-cloud/app/');
}
?>