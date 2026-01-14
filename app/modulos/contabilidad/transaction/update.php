<?php
/*
	$update = [
		'campo1'		=> "hola :o",
		'campo2'     => "hola",
	];
	$where = ['testId' => id]; // ids, soporta múltiple where

	$cloud->update('test', $update, $where);
*/
if (isset($_SESSION["usuarioId"]) && isset($operation)) {
	switch ($operation) {
		case "parametrizacion-comision":
			/*
				POST:
				hiddenFormData = editar ^ lineaId
				typeOperation
				operation
				linea = nombre de la linea
				rangoInicio = array
				rangoFin = array
				porcentajePagar = array
				comisionPorcentajeLineaId = array
				conteoWrappersActual = número de inputs/condiciones actuales
				correlativoWrapper = Es el "id" en el que se quedó
				conteoWrappers = Es el número de filas que se crearon, se va a comparar con conteoWrapperActual
			*/
			$arrayHiddenForm = explode("^", $_POST["hiddenFormData"]);
			$lineaId = $arrayHiddenForm[1];
			// Iterar la comisionPorcentajeLineaId que se generaron
			$i = 0; // Para obtener los rangos en la posicion especifica del array
			foreach ($_POST["comisionPorcentajeLineaId"] as $comisionPorcentajeLineaId) {
				$rangoPorcentajeInicio = $_POST['rangoInicio'][$i];
				$rangoPorcentajeFin = $_POST['rangoFin'][$i];
				$porcentajePago = $_POST['porcentajePagar'][$i];

				$update = [
					'rangoPorcentajeInicio' => $rangoPorcentajeInicio,
					'rangoPorcentajeFin' => $rangoPorcentajeFin,
					'porcentajePago' => $porcentajePago
				];
				$where = ['comisionPorcentajeLineaId' => $comisionPorcentajeLineaId];
				$cloud->update('conta_comision_porcentaje_lineas', $update, $where);
				$i += 1;
			}
			// Comparar si el actual es mayor o menor que el conteoWrapper para saber si se hará insert o delete
			if ($_POST["conteoWrappers"] > $_POST["conteoWrappersActual"]) {
				// Es un insert ya que ya se iteraron todas las comisionPorcentajeLineaId en el foreach de arriba
				while ($i < $_POST["conteoWrappers"]) {
					$rangoPorcentajeInicio = $_POST['rangoInicio'][$i];
					$rangoPorcentajeFin = $_POST['rangoFin'][$i];
					$porcentajePago = $_POST['porcentajePagar'][$i];

					$insert = [
						'lineaId' => $lineaId,
						'rangoPorcentajeInicio' => $rangoPorcentajeInicio,
						'rangoPorcentajeFin' => $rangoPorcentajeFin,
						'porcentajePago' => $porcentajePago
					];
					$cloud->insert('conta_comision_porcentaje_lineas', $insert);
					$i += 1;
				}
			} else {
				// conteoWrappers es == a conteoWrappersActual o ya se eliminaron las condiciones que venían de más
			}

			// Bitácora de usuario final / jefes
			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Actualizó la parametrización de porcentajes de comisión de la Linea: " . $_POST["linea"] . ", ");

			echo "success";
			break;

		case "comision-pagar":
			/*
				hiddenFormData =  comisionPagarCalculoId ^ flgIdentificador ^ n (correlativo de las tablas)
				typeOperation
				operation
				comisionPagarCloud
				comisionPagarNueva
				motivoEditar
			*/
			// comisionPagarCalculoId ^ identificador ^ n
			$arrayFormData = explode("^", $_POST['hiddenFormData']);
			$dataInfoGeneralFactura = $cloud->row("
			        SELECT
			            nombreEmpleado,
			            nombreCliente,
			            tipoCliente,
			            tipoFactura,
			            correlativoFactura,
			            fechaFactura,
			            sucursalFactura,
			            totalFactura,
			            fechaAbono,
			            totalAbono,
			            totalAbonoCalculo,
			            comisionPagarPeriodoId,
			            tasaComisionAbono,
			            comisionAbonoPagar,
			            ivaPercibido,
			            ivaRetenido
			        FROM conta_comision_pagar_calculo
			        WHERE comisionPagarCalculoId = ? AND flgIdentificador = ?
			    ", [$arrayFormData[0], $arrayFormData[1]]);

			$update = [
				'flgComisionEditar' => 1,
				'comisionPagarEditar' => $_POST['comisionPagarNueva']
			];
			$where = [
				'comisionPagarPeriodoId' => $dataInfoGeneralFactura->comisionPagarPeriodoId,
				'nombreEmpleado' => $dataInfoGeneralFactura->nombreEmpleado,
				'nombreCliente' => $dataInfoGeneralFactura->nombreCliente,
				'correlativoFactura' => $dataInfoGeneralFactura->correlativoFactura,
				'tipoFactura' => $dataInfoGeneralFactura->tipoFactura,
				'fechaFactura' => $dataInfoGeneralFactura->fechaFactura,
				'sucursalFactura' => $dataInfoGeneralFactura->sucursalFactura,
				'flgIdentificador' => $arrayFormData[1],
				'fechaAbono' => $dataInfoGeneralFactura->fechaAbono,
				'totalAbono' => $dataInfoGeneralFactura->totalAbono,
				'flgDelete' => '0'
			];
			$cloud->update('conta_comision_pagar_calculo', $update, $where);
			// Insert a tabla bit_comisiones_editar
			$insert = [
				'comisionPagarCalculoId' => $arrayFormData[0],
				'comisionPagarCloud' => $_POST['comisionPagarCloud'],
				'comisionPagarNueva' => $_POST['comisionPagarNueva'],
				'motivoEditar' => $_POST['motivoEditar']
			];
			$bitComisionEditarId = $cloud->insert('bit_comisiones_editar', $insert);
			echo "success";
			break;

		case 'comision-clasificacion':
			/*
				POST:
				typeOperation
				operation
				comisionClasificacionId
				tipoClasificacion
				tituloClasificacion
			*/
			$existeClasificacion = $cloud->count("
					SELECT comisionClasificacionId FROM conta_comision_reporte_clasificacion
					WHERE tipoClasificacion = ? AND tituloClasificacion = ? AND comisionClasificacionId <> ? AND flgDelete = ?
				", [$_POST['tipoClasificacion'], $_POST['tituloClasificacion'], $_POST['comisionClasificacionId'], 0]);

			if ($existeClasificacion == 0) {
				$update = [
					'tituloClasificacion' => $_POST['tituloClasificacion']
				];
				$where = ['comisionClasificacionId' => $_POST['comisionClasificacionId']];
				$cloud->update("conta_comision_reporte_clasificacion", $update, $where);

				echo "success";
			} else {
				echo 'La clasificación ' . $_POST['tipoParametrizacion'] . ': ' . $_POST['tituloClasificacion'] . ' ya fue creada.';
			}
			break;

		case 'clasificacion-gasto':
			/*
				POST:
				typeOperation
				operation
				clasifGastoSalarioId
			*/
			$ExisteClasificacionGastos = $cloud->count("
					SELECT clasifGastoSalarioId FROM cat_clasificacion_gastos_salario
					WHERE nombreGastoSalario = ? AND clasifGastoSalarioId <> ? AND flgDelete = ?
				", [$_POST['descGasto'], $_POST['clasifGastoSalarioId'], 0]);

			if ($ExisteClasificacionGastos == 0) {
				$update = [
					'nombreGastoSalario' => $_POST['descGasto']
				];
				$where = ['clasifGastoSalarioId' => $_POST['clasifGastoSalarioId']];
				$cloud->update("cat_clasificacion_gastos_salario", $update, $where);
				echo "success";
			} else {
				echo "El nombre: $_POST[descGasto] ya fue creado";
			}
			break;

		case 'planilla-info-empleado':
			/*
				POST:
				codEmpleado
				personaId
				prsExpedienteId
				selectClasificacion
			*/
			$ExisteClasificacionGastos = $cloud->count("
					SELECT personaId 
					FROM th_personas
					WHERE codEmpleado = ? AND personaId <> ? AND flgDelete = ?
				", [$_POST['codEmpleado'], $_POST['personaId'], 0]);

			if ($ExisteClasificacionGastos == 0) {
				$update = [
					'codEmpleado' => $_POST['codEmpleado']
				];
				$where = ['personaId' => $_POST['personaId']];
				$cloud->update("th_personas", $update, $where);


				$update = [
					'clasifGastoSalarioId' => $_POST['selectClasificacion']
				];
				$where = ['prsExpedienteId' => $_POST['prsExpedienteId']];
				$cloud->update("th_expediente_personas", $update, $where);
				echo "success";
			} else {
				echo "El Código: $_POST[codEmpleado] ya fue asignado a otro empleado";
			}


			break;

		case 'anular-cheque':
			/*
				POST:
				chequeId,
				justificacion,
				fechaAnulacionfechaAnulacion

			*/
			$update = [
				'estadoCheque' => "Anulado",
				'obsAnular' => $_POST['justificacion'],
				'fhAnular' => $_POST['fechaAnulacion'] . " " . date('H:i:s')
			];
			$where = ['chequeId' => $_POST['chequeId']];
			$cloud->update("conta_cheques", $update, $where);
			$cloud->writeBitacora("movUpdate", "($fhActual) Anulo el cheque N°: " . $_POST['numCheque']);

			echo "success";

			break;

		case 'parametrizacion-devengo':
			/*
				POST:
				typeOperation
				operation
				catPlanillaDevengoId
				catPlanillaDevengoIdSuperior
				tipoDevengo
				nombreDevengo
				codigoContable
			*/
			$existeDevengoGravado = $cloud->count("
					SELECT catPlanillaDevengoId FROM cat_planilla_devengos
					WHERE tipoDevengo = ? AND codigoContable = ? AND catPlanillaDevengoId <> ? AND flgDelete = ?
				", [$_POST['tipoDevengo'], $_POST['codigoContable'], $_POST['catPlanillaDevengoId'], 0]);

			if ($existeDevengoGravado == 0) {
				$update = [
					'tipoDevengo' => $_POST['tipoDevengo'],
					'nombreDevengo' => $_POST['nombreDevengo'],
					'codigoContable' => $_POST['codigoContable']
				];
				$where = ['catPlanillaDevengoId' => $_POST['catPlanillaDevengoId']];
				$cloud->update("cat_planilla_devengos", $update, $where);
				$cloud->writeBitacora("movUpdate", "($fhActual) Actualizó el devengo: $_POST[nombreDevengo]: $_POST[codigoContable] ($_POST[tipoDevengo]), ");


				echo "success";
			} else {
				echo "El código contable: $_POST[codigoContable] ya fue creado.";
			}
			break;
		case 'parametrizacion-descuento':
			/*
				POST:
				typeOperation
				tituloModal
				catPlanillaDescuentoId
				catPlanillaDescuentoIdSuperior
				tblOtrosDescuentos			
			*/
			$existeDescuento = $cloud->count("
					SELECT catPlanillaDescuentoId FROM cat_planilla_descuentos
					WHERE codigoContable = ? AND catPlanillaDescuentoId <> ? AND flgDelete = ?
				", [$_POST['codigoContable'], $_POST['catPlanillaDescuentoId'], 0]);

			if ($existeDescuento == 0) {
				$update = [
					'nombreDescuento' => $_POST['nombreDescuento'],
					'codigoContable' => $_POST['codigoContable']
				];
				$where = ['catPlanillaDescuentoId' => $_POST['catPlanillaDescuentoId']];
				$cloud->update("cat_planilla_descuentos", $update, $where);
				$cloud->writeBitacora("movUpdate", "($fhActual) Actualizó el descuento: $_POST[nombreDescuento]: $_POST[codigoContable] ");

				echo "success";
			} else {
				echo "El código contable: $_POST[codigoContable] ya fue creado.";
			}
			break;
		case 'parametrizacion-renta':
			/*
				POST:
				descuentoRentaId
				tramoRenta
				nuevoEstado
			*/
			$update = [
				'estadoDescuentoRenta' => $_POST["nuevoEstado"]
			];
			$where = ['descuentoRentaId' => $_POST['descuentoRentaId']];
			$cloud->update("cat_planilla_descuentos_renta", $update, $where);
			$cloud->writeBitacora("movUpdate", "($fhActual) Actualizó el estado del tramo: $_POST[tramoRenta] a $_POST[nuevoEstado]");
			echo "success";
			break;
		case 'parametrizacion-desc-ley':
			/*
				POST:
				descuentoLeyId
				nombreDescuentoLey
				nuevoEstado
			*/
			$update = [
				'estadoDescuentoLey' => $_POST["nuevoEstado"]
			];
			$where = ['descuentoLeyId' => $_POST['descuentoLeyId']];
			$cloud->update("cat_planilla_descuentos_ley", $update, $where);
			$cloud->writeBitacora("movUpdate", "($fhActual) Actualizó el estado del tramo: $_POST[nombreDescuentoLey] a $_POST[nuevoEstado]");
			echo "success";
			break;

		case 'planilla-otros-descuentos':
			/*
				POST:
				descuentoId = Si es cero es insert
				planillaId = Si es cero no se ha generado el cálculo
				quincenaId
				prsExpedienteId
				flgSubdescuento = Sí/No si el select del descuento a aplicar es subdescuento
				nombreCompleto
				catPlanillaDescuentoId
				subCatPlanillaDescuentoId
				montoDescuento
				descripcionDescuento
			*/
			// Validar el estadoPlanilla si es Cerrada no permitir actualizar
			$catPlanillaDescuentoId = ($_POST['flgSubdescuento'] == "Sí" ? $_POST['subCatPlanillaDescuentoId'] : $_POST['catPlanillaDescuentoId']);

			if ($_POST['planillaId'] == 0) {
				$tablaDescuentos = "conta_planilla_programado_descuentos";
				$update = [
					'idDescuentoProgramado' => $catPlanillaDescuentoId,
					'descripcionDescuentoProgramado' => $_POST['descripcionDescuento'],
					'montoDescuentoProgramado' => $_POST['montoDescuento']
				];
				$where = ["planillaDescuentoProgramadoId" => $_POST['descuentoId']];
			} else {
				$tablaDescuentos = "conta_planilla_descuentos";
				$update = [
					"idDescuento" => $catPlanillaDescuentoId,
					"descripcionDescuento" => $_POST['descripcionDescuento'],
					"montoDescuento" => $_POST['montoDescuento']
				];
				$where = ["planillaDescuentoId" => $_POST['descuentoId']];
			}
			$cloud->update($tablaDescuentos, $update, $where);
			// Bitácora de usuario final / jefes
			$cloud->writeBitacora("movUpdate", "($fhActual) Actualizó otros descuentos del empleado: $_POST[nombreCompleto] (ID: $_POST[descuentoId], Quincena: $_POST[quincenaId]), ");
			echo "success";
			break;

		case 'planilla-devengos':
			/*
				POST:
				devengoId
				tipoDevengo
				planillaId
				quincenaId
				prsExpedienteId
				flgSubdevengo
				nombreCompleto
				catPlanillaDevengoId
				subCatPlanillaDevengoId
				montoDevengo
				descripcionDevengo
			*/
			// Validar el estadoPlanilla si es Cerrada no permitir actualizar
			$catPlanillaDevengoId = ($_POST['flgSubdevengo'] == "Sí" ? $_POST['subCatPlanillaDevengoId'] : $_POST['catPlanillaDevengoId']);

			if ($_POST['planillaId'] == 0) {
				$tablaDevengos = "conta_planilla_programado_devengos";
				$update = [
					'catPlanillaDevengoId' => $catPlanillaDevengoId,
					'descripcionDevengoProgramado' => $_POST['descripcionDevengo'],
					'montoDevengoProgramado' => $_POST['montoDevengo']
				];
				$where = ["planillaDevengoProgramadoId" => $_POST['devengoId']];
			} else {
				$tablaDevengos = "conta_planilla_devengos";
				$update = [
					"catPlanillaDevengoId" => $catPlanillaDevengoId,
					"descripcionDevengo" => $_POST['descripcionDevengo'],
					"montoDevengo" => $_POST['montoDevengo']
				];
				$where = ["planillaDevengoId" => $_POST['devengoId']];
			}
			$cloud->update($tablaDevengos, $update, $where);
			// Bitácora de usuario final / jefes
			$cloud->writeBitacora("movUpdate", "($fhActual) Actualizó devengo $_POST[tipoDevengo] del empleado: $_POST[nombreCompleto] (ID: $_POST[devengoId], Quincena: $_POST[quincenaId]), ");
			echo "success";
			break;

		case "datos-cliente":
			/*
				hiddenFormData
				typeOperation
				operation
				idCliente
				tipoPersona
				tipoDoc
				numeroDocumento
				nombreCliente
				nombreComercial
				nrc
				giro
				categoria
				nombreRL
				tipoDocRL
				duiRL
			 */
			$checkCheks = $cloud->row("SELECT flgAPNFD, flgPEP, flgPEPFamiliar, flgPEPAccionista FROM fel_clientes WHERE clienteId = ?", [$_POST['idCliente']]);

			$update = [
				'nrcCliente' => $_POST["nrc"],
				'tipoPersonaMHId' => $_POST["tipoPersona"],
				'nombreCliente' => $_POST["nombreCliente"],
				'nombreComercialCliente' => $_POST["nombreComercial"],
				'estadoCivilNat' => $_POST["estadoCivil"],
				'sexoNat' => $_POST["sexo"],
				'estadoCivilNat' => $_POST["estadoCivil"],
				'categoriaCliente' => $_POST["categoria"],
				'estadoCliente' => "Activo",
			];
			if ($_POST["tipoDoc"] != "") {
				$update += [
					'tipoDocumentoMHId' => $_POST["tipoDoc"],
				];
			}
			if ($_POST["nitPJ"] != "") {
				$update += [
					'numDocumento' => $_POST["nitPJ"],
				];
			} else if ($_POST["numeroDocumento"] != "") {
				$update += [
					'numDocumento' => $_POST["numeroDocumento"],
				];
			}

			if (isset($_POST["municipioId"])) {
				if ($_POST["municipioId"] != "") {
					$update += [
						'paisMunicipioIdNacimientoNat' => $_POST["municipioId"]
					];
				}
			}
			if ($_POST["nacionalidad"] != "") {
				$update += [
					'paisIdNacionalidad' => $_POST["nacionalidad"]
				];
			}
			if ($_POST["fechaNacimientoNat"] != "") {
				$update += [
					'fechaNacimientoNat' => $_POST["fechaNacimientoNat"]
				];
			}
			if ($_POST["nombreRL"] != "") {
				$update += [
					'nombreCompletoRL' => $_POST["nombreRL"]
				];
			}
			if ($_POST["tipoDocRL"] != "") {
				$update += [
					'tipoDocumentoRL' => $_POST["tipoDocRL"]
				];
			}
			if ($_POST["numeroDocumentoRL"] != "") {
				$update += [
					'numDocumentoRL' => $_POST["numeroDocumentoRL"]
				];
			}
			if ($_POST["fechaNacimientoRL"] != "") {
				$update += [
					'fechaNacimientoRL' => $_POST["fechaNacimientoRL"]
				];
			}
			if ($_POST["sexoRL"] != "") {
				$update += [
					'sexoRL' => $_POST["sexoRL"]
				];
			}
			if ($_POST["profesionRL"] != "") {
				$update += [
					'profesionRL' => $_POST["profesionRL"]
				];
			}
			if ($_POST["profesion"] != "") {
				$update += [
					'profesionNat' => $_POST["profesion"]
				];
			}
			if ($_POST["estadoCivilRL"] != "") {
				$update += [
					'estadoCivilRL' => $_POST["estadoCivilRL"]
				];
			}
			if (isset($_POST["muniNacimientoRL"])) {
				if ($_POST["muniNacimientoRL"] != "") {
					$update += [
						'paisMunicipioIdNacimientoRL' => $_POST["muniNacimientoRL"]
					];
				}
			}
			if ($_POST["giro"] != "") {
				$update += [
					'actividadEconomicaId' => $_POST["giro"]
				];
			}
			if ($_POST["giroSec"] != "") {
				$update += [
					'actividadEconomicaIdSecundaria' => $_POST["giroSec"]
				];
			}

			if ($_POST["tipoPersona"] == 1) {
				if (isset($_POST["pepPN"])) {
					$update += [
						'flgPEP' => $_POST["pepPN"]
					];
				} else {
					$update += [
						'flgPEP' => NULL
					];
				}
			}
			if ($_POST["tipoPersona"] == 2) {
				if (isset($_POST["pepPJ"])) {
					$update += [
						'flgPEP' => $_POST["pepPJ"]
					];
				} else {
					$update += [
						'flgPEP' => NULL
					];
				}
			}
			if (isset($_POST["pepPJAc"])) {
				$update += [
					'flgPEPAccionista' => $_POST["pepPJAc"]
				];
			} else {
				$update += [
					'flgPEPAccionista' => NULL
				];
			}
			if (isset($_POST["pepPNFam"])) {
				$update += [
					'flgPEPFamiliar' => $_POST["pepPNFam"]
				];
			} else {
				$update += [
					'flgPEPFamiliar' => NULL
				];
			}
			if (isset($_POST["apnfd"])) {
				$update += [
					'flgAPNFD' => $_POST["apnfd"],
				];
			} else {
				$update += [
					'flgAPNFD' => NULL,
				];
			}

			$where = ["clienteId" => $_POST['idCliente']];

			$cloud->update('fel_clientes', $update, $where);

			$respuesta = array("resultado" => "success", "idCliente" => $_POST['idCliente']);

			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Actualizó datos los datos del cliente: " . $_POST["nombreCliente"] . ", ");

			echo json_encode($respuesta);
			break;
		case "datos-cliente-PEP":
			/*
				typeOperation
				operation
				idCliente
				flgTipo
				nombreClientePEP
				cargoPublico
				fechaNombramiento
				fechaNombramientoFin
				periodo
				tipoDoc
				numeroDocumento
				tipoPEP
				tipoRelacionPEP
			*/
			$update = [
				'nombreCompletoPEP' => $_POST["nombreClientePEP"],
				'cargoPublico' => $_POST["cargoPublico"],
				'fechaNombramiento' => $_POST["fechaNombramiento"],
				'fechaFinNombramiento' => $_POST["fechaNombramientoFin"],
				'tipoDocumentoMHId' => $_POST["tipoDoc"],
				'numDocumentoPEP' => $_POST["numeroDocumento"],
				'institucionCargoPublico' => $_POST["institucion"],
				'tipoPEP' => $_POST["tipoPEP"],
				'tipoRelacionPEP' => $_POST["flgTipo"],
			];
			$where = ["clientePEPId" => $_POST["idCliente"]];
			$cliente = $cloud->update('fel_clientes_pep', $update, $where);

			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Actualizó los datos del cliente: " . $_POST["nombreClientePEP"] . ", ");

			echo "success";
			break;
		case "contacto-cliente":
			$update = [
				'clienteUbicacionId' => $_POST["idClienteC"],
				'tipoContactoId' => $_POST["tipoContacto"],
				'contactoCliente' => $_POST["contacto"],
				'descripcionContactoCliente' => $_POST["descripcion"],
			];
			$where = ['clienteContactoId' => $_POST["contactoId"]];
			$cloud->update('fel_clientes_contactos', $update, $where);

			$cloud->writeBitacora("movUpdate", '(' . $fhActual . ') Se actualizaron los datos del contacto ' . $_POST["descripcion"] . '(' . $_POST["contactoId"] . '), ');

			echo "success";
			break;
		case "direccion-cliente":
			$update = [
				'clienteId' => $_POST["idCliente"],
				'tipoUbicacion' => $_POST["tipoUbicacion"],
				'paisMunicipioId' => $_POST["municipio"],
				'nombreClienteUbicacion' => $_POST["nombreSuc"],
				'direccionClienteUbicacion' => $_POST["direccionCli"],
				'estadoClienteUbicacion' => 'Activo'
			];
			$where = ['clienteUbicacionId' => $_POST['idUbicacion']];
			$cloud->update('fel_clientes_ubicaciones', $update, $where);

			$getCliente = $cloud->row("SELECT nombreCliente FROM fel_clientes WHERE clienteId = ?", [$_POST["idCliente"]]);

			$cloud->writeBitacora("movUpdate", '(' . $fhActual . ') Se actualizaron los datos de la ubicación ' . $_POST["nombreSuc"] . '(' . $_POST['idUbicacion'] . ') del cliente ' . $getCliente->nombreCliente . ', ');


			echo "success";
			break;
		case "datos-cliente-proveedor":
			$update = [
				'clienteId' => $_POST["idCliente"],
				'tipoRelacion' => $_POST["tipoRelacion"],
				'razonSocial' => $_POST["razonSocial"],
				'paisId' => $_POST["pais"],
			];
			$where = ['clienteRelacionId' => $_POST["clienteRelacionId"]];
			$cloud->update('fel_clientes_relacion', $update, $where);

			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó el cliente principal: " . $_POST["razonSocial"] . " del cliente: " . $_POST["nombreCliente"] . ", ");

			echo "success";
			break;
		case "datos-PEP-nucleoFamiliar":
			$update = [
				'catPrsRelacionId' => $_POST["parentesco"],
				'nombreFamiliar' => $_POST["nombreFam"],
			];
			$where = ['clientePEPFamiliaId' => $_POST['PEPFamId']];
			$cloud->update('fel_clientes_pep_familia', $update, $where);

			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Ingresó el familiar: " . $_POST["nombreFam"] . " de la persona politicamente expuesta: " . $_POST["nombrePEP"] . ", ");

			echo "success";
			break;
		case "datos-PEP-sociedades":
			$update = [
				'clientePEPId' => $_POST["PEPId"],
				'razonSocial' => $_POST["razonSocial"],
				'porcentajeParticipacion' => $_POST["participacion"],
			];
			$where = ['clientePEPPatrimonialId' => $_POST['PEPSocId']];
			$cloud->update('fel_clientes_pep_relpatrimonial', $update, $where);

			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Actualizó los datos de la empresa: " . $_POST["razonSocial"] . " en la que la persona politicamente expuesta: " . $_POST["nombrePEP"] . " es accionista, ");

			echo "success";
			break;
		case "clientes-accionistas":
			$update = [
				'nombreAccionista' => $_POST["nombreAccionista"],
				'paisId' => $_POST["pais"],
				'nitAccionista' => $_POST["nitAccionista"],
				'porcentajeParticipacion' => $_POST["participacion"],
			];

			$where = ['clienteAccionistaid' => $_POST['accionistaId']];
			$cloud->update('fel_clientes_accionistas', $update, $where);

			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Actualizó al accionista: " . $_POST["nombreAccionista"] . ", ");

			echo "success";
			break;

		case "agregar-documento-retencion":

			/*
			typeOperation: update
			operation: agregar-documento-retencion
			facturaId: 3374
			facturaDetalleId: 13943
			tipoDTEId: 1
			tipoGeneracionDocId: 2
			fechaEmisionRelacionada: 2024-02-26
			numDoc: 123456
			descripcion: Otra prueba
			valorNeto: 1200
			ivaRetenido: 12.00
			*/
			//Aqui me quedé comprobante retencion pendiente 
			$update = [
				'tipoDTEId' => $_POST["tipoDTEId"],
				'tipoGeneracionDocId' => $_POST["tipoGeneracionDocId"],
				'fechaEmisionRelacionada' => $_POST["fechaEmisionRelacionada"],
				'numeroDocumentoRelacionada' => $_POST["numDoc"],
				'horaEmisionRelacionada' => date("H:i:s")
			];

			$where = ['facturaRelacionadaId' => $_POST['facturaRelacionadaId']];
			$cloud->update('fel_factura_relacionada', $update, $where);

			$update = [

				'productoId' => 25911,
				'codProductoFactura' => "-RETENCION",
				'nombreProductoFactura' => $_POST["descripcion"],
				'tipoItemMHId' => 1,
				'precioUnitario' => $_POST["valorNeto"],
				'precioVenta' => $_POST["valorNeto"],
				'subTotalDetalle' => $_POST["valorNeto"],
				'totalDetalle' => $_POST["valorNeto"],
				'ivaRetenidoDetalle' => $_POST["ivaRetenido"],
				'costoPromedio' => 0,
				'precioUnitarioIVA' => 0,
				'precioVentaIVA' => 0,
				'cantidadProducto' => 1,
			];

			$where = ['facturaDetalleId' => $_POST['facturaDetalleId']];
			$cloud->update('fel_factura_detalle', $update, $where);

			// Update a fel_factura_retenciones

			// Consultar el nuevo iva retenido
			$ivaRetenido = $cloud->row("
						SELECT SUM(ivaRetenidoDetalle) AS totalIva
						FROM fel_factura_detalle
						WHERE facturaId = ? AND flgDelete = ?
					", [$_POST["facturaId"], 0]);

			// Insertar el nuevo registro de iva retenido
			$update = [
				'ivaRetenido' => $ivaRetenido->totalIva
			];
			$where = ['facturaId' => $_POST["facturaId"]];

			$cloud->update('fel_factura_retenciones', $update, $where);

			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Actualizó el detalle del comprobante: " . $_POST["facturaDetalleId"] . ", ");

			echo "success";
			break;

		case "finalizar-comprobante":
			/*
			typeOperation: update
			operation: finalizar-compra
			facturaId: 3374
			obsCompra: hgtg

			*/
			$existeDetalle = $cloud->count("
						SELECT facturaDetalleId FROM fel_factura_detalle
						WHERE facturaId = ? AND flgDelete = ? 
					", [$_POST['facturaId'], 0]);

			if ($existeDetalle > 0) {
				$update = [
					'obsAnulacionInterna' => $_POST["obsComprobante"],
					'estadoFactura' => 'Finalizado',
					'condicionFacturaId' => 1,
					'plazoPagoId' => 1,
					'periodoPlazo' => 0
				];

				$where = ['facturaId' => $_POST['facturaId']];
				$cloud->update('fel_factura', $update, $where);

				// Insert a tabla fel_factura_pago
				$montoPago = $cloud->row("SELECT 
							SUM(totalDetalle) AS totalDetalle
						FROM fel_factura_detalle
						WHERE facturaId = ? AND flgDelete = ?
						", [$_POST['facturaId'], 0]);
				$insert = [
					'facturaId' => $_POST["facturaId"],
					'formaPagoId' => 1,
					'montoPago' => $montoPago->totalDetalle,
					'cambioEfectivo' => 0.00
				];
				$cloud->insert('fel_factura_pago', $insert);
				$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Se finalizó el comprobante de retención ");

				echo "success";
			} else {
				echo "Debe agregar al menos una factura al comprobante de retención";
			}
			break;
		case "complemento-DTE":
			$update = [
				'complementoFactura' => $_POST["descripcionComplemento"]
			];
			$where = ['facturaComplementoId' => $_POST["facturaComplementoId"]];
			$cloud->update('fel_factura_complementos' . $_POST['yearBD'], $update, $where);

			$cloud->writeBitacora("movUpdate", '(' . $fhActual . ') Se actualizaron el complemento ' . $_POST["facturaComplementoId"]);
			echo "success";
			break;

		case "anulacion-interna":
			$update = [
				'obsAnulacionInterna' => $_POST["obsAnulacionInterna"],
				'estadoFactura' => 'Anulado'
			];
			$where = ['facturaId' => $_POST['facturaId']];
			$cloud->update('fel_factura', $update, $where);

			/*$dataArchivoActual = $cloud->row("
			  SELECT facturaMagicId, archivoFactura FROM fel_factura_magic
			  WHERE facturaId = ? AND flgDelete = ?
			  LIMIT 1
			",[$_POST['facturaId'], 0]);

			$update = [
			  'archivoFactura'            => "(ANULADO-INTERNO)$dataArchivoActual->archivoFactura"
			];
			$where = ["facturaMagicId" => $dataArchivoActual->facturaMagicId];
			$cloud->update("fel_factura_magic", $update, $where);*/

			$cloud->writeBitacora("movInsert", "(" . $fhActual . ") Se actualizó el estado de la factura: ");

			echo "success";
			break;

		case "comision-compartida-calculo":
			/*
				POST:
				comisionPagarPeriodoId
			*/
			$dataParametrizacionCompartida = $cloud->rows("
					SELECT 
						pd.comisionCompartidaParamDetalleId AS comisionCompartidaParamDetalleId,
						pd.comisionCompartidaParamId AS comisionCompartidaParamId,
					    p.numRegistroCliente AS numRegistroCliente,
					    p.nombreCliente AS nombreCliente,
					    pd.codEmpleado AS codEmpleado,
					    pd.codVendedor AS codVendedor,
					    pd.nombreEmpleado AS nombreEmpleado,
					    p.descripcionParametrizacion AS descripcionParametrizacion,
					    pd.porcentajeComisionCompartida AS porcentajeComisionCompartida
					FROM conta_comision_compartida_parametrizacion_detalle pd
					JOIN conta_comision_compartida_parametrizacion p ON p.comisionCompartidaParamId = pd.comisionCompartidaParamId
					WHERE pd.flgDelete = ? AND p.flgDelete = ?
				", [0, 0]);

			foreach ($dataParametrizacionCompartida as $parametrizacionCompartida) {
				// Verificar si existen facturas con los datos del cliente y vendedor que se parametrizaron
				// Que hayan sido de contado
				$dataFacturaComision = $cloud->rows("
						SELECT 
							correlativoFactura,
							tipoFactura,
							fechaFactura,
							sucursalFactura
						FROM conta_comision_pagar_calculo
						WHERE comisionPagarPeriodoId = ? AND (numRegistroCliente = ? OR nombreCliente = ?) 
						AND (codVendedor = ? OR nombreEmpleado = ?) AND flgIdentificador = ? AND flgDelete = ?
						GROUP BY correlativoFactura, fechaFactura
						ORDER BY fechaFactura, correlativoFactura
					", [$_POST['comisionPagarPeriodoId'], $parametrizacionCompartida->numRegistroCliente, $parametrizacionCompartida->nombreCliente, $parametrizacionCompartida->codVendedor, $parametrizacionCompartida->nombreEmpleado, 'F', 0]);
				foreach ($dataFacturaComision as $facturaComision) {
					// El group by es por si hay más de una factura de ese cliente y vendedor, ahora verificar cada una por separado para aplicarle la comisión
					$dataFacturaDetalleComision = $cloud->rows("
							SELECT 
								comisionPagarCalculoId, 
								comisionPagar 
							FROM conta_comision_pagar_calculo
							WHERE comisionPagarPeriodoId = ? AND (numRegistroCliente = ? OR nombreCliente = ?) 
							AND (codVendedor = ? OR nombreEmpleado = ?) AND correlativoFactura = ? AND tipoFactura = ? AND fechaFactura = ? AND sucursalFactura = ? AND flgIdentificador = ? AND flgDelete = ?
						", [
						$_POST['comisionPagarPeriodoId'],
						$parametrizacionCompartida->numRegistroCliente,
						$parametrizacionCompartida->nombreCliente,
						$parametrizacionCompartida->codVendedor,
						$parametrizacionCompartida->nombreEmpleado,
						$facturaComision->correlativoFactura,
						$facturaComision->tipoFactura,
						$facturaComision->fechaFactura,
						$facturaComision->sucursalFactura,
						'F',
						0
					]);
					$comisionPagarContado = 0;
					$comisionPagarCalculoId = 0;
					foreach ($dataFacturaDetalleComision as $facturaDetalleComision) {
						if ($comisionPagarContado == 0) {
							$comisionPagarCalculoId = $facturaDetalleComision->comisionPagarCalculoId;
						} else {
							// Ya se guardó el comisionPagarCalculoId
						}
						$comisionPagarContado += $facturaDetalleComision->comisionPagar;
					} // foreach dataFacturaDetalleComision

					if ($comisionPagarCalculoId > 0) {
						// Se encontró factura, repartir la comisión del vendedor principal
						$porcentajeCompartidoVendedor = $parametrizacionCompartida->porcentajeComisionCompartida / 100;
						$comisionPagarCompartida = $comisionPagarContado * $porcentajeCompartidoVendedor;

						$update = [
							'flgComisionEditar' => 1,
							'comisionPagarEditar' => $comisionPagarCompartida
						];
						$where = [
							'comisionPagarPeriodoId' => $_POST['comisionPagarPeriodoId'],
							'codVendedor' => $parametrizacionCompartida->codVendedor,
							'nombreEmpleado' => $parametrizacionCompartida->nombreEmpleado,
							'nombreCliente' => $parametrizacionCompartida->nombreCliente,
							'correlativoFactura' => $facturaComision->correlativoFactura,
							'tipoFactura' => $facturaComision->tipoFactura,
							'fechaFactura' => $facturaComision->fechaFactura,
							'sucursalFactura' => $facturaComision->sucursalFactura,
							'flgIdentificador' => 'F',
							'flgDelete' => '0'
						];
						$cloud->update('conta_comision_pagar_calculo', $update, $where);

						$txtCompartida = "Comisión compartida, descripción de parametrización: $parametrizacionCompartida->descripcionParametrizacion, porcentaje compartido del vendedor = $" . number_format($comisionPagarCompartida, 2, '.', ',') . " ($parametrizacionCompartida->porcentajeComisionCompartida%). Compartida con los vendedores: ";

						// luego iterar la de los otros vendedores con los que comparte y excluirlo a él
						$dataVendedoresCompartidos = $cloud->rows("
								SELECT 
									comisionCompartidaParamDetalleId,
									nombreEmpleado,
									porcentajeComisionCompartida 
								FROM conta_comision_compartida_parametrizacion_detalle
								WHERE comisionCompartidaParamId = ? AND flgDelete = ? AND comisionCompartidaParamDetalleId <> ?  
							", [$parametrizacionCompartida->comisionCompartidaParamId, 0, $parametrizacionCompartida->comisionCompartidaParamDetalleId]);

						foreach ($dataVendedoresCompartidos as $vendedorCompartido) {
							$porcentajeDetalleCompartida = $vendedorCompartido->porcentajeComisionCompartida / 100;

							$comisionDetalleCompartida = $comisionPagarContado * $porcentajeDetalleCompartida;

							$dataComisionCompartidaCalculada = $cloud->row("
									SELECT 
										comisionCompartidaCalculoId 
									FROM conta_comision_compartida_calculo
									WHERE comisionCompartidaParamDetalleId = ? AND comisionPagarCalculoId = ? AND flgDelete = ?
									LIMIT 1
								", [$vendedorCompartido->comisionCompartidaParamDetalleId, $comisionPagarCalculoId, 0]);

							if ($dataComisionCompartidaCalculada) {
								// Había calculo anterior, borrarlo para que quede solo el insert
								$cloud->deleteById("conta_comision_compartida_calculo", "comisionCompartidaCalculoId", $dataComisionCompartidaCalculada->comisionCompartidaCalculoId);
							} else {
								// No encontró calculo anterior, solo hacer insert
							}

							$insertCalculoCompartido = [
								'comisionCompartidaParamDetalleId' => $vendedorCompartido->comisionCompartidaParamDetalleId,
								'comisionPagarCalculoId' => $comisionPagarCalculoId,
								'comisionCompartidaPagar' => $comisionDetalleCompartida
							];
							$comisionCompartidaCalculoId = $cloud->insert('conta_comision_compartida_calculo', $insertCalculoCompartido);

							$txtCompartida .= "$vendedorCompartido->nombreEmpleado = $" . number_format($comisionDetalleCompartida, 2, '.', ',') . " ($vendedorCompartido->porcentajeComisionCompartida%), ";
						}

						$insertBitacoraEditar = [
							'comisionPagarCalculoId' => $comisionPagarCalculoId,
							'comisionPagarCloud' => $comisionPagarContado,
							'comisionPagarNueva' => $comisionPagarCompartida,
							'motivoEditar' => $txtCompartida
						];
						$bitComisionEditarId = $cloud->insert('bit_comisiones_editar', $insertBitacoraEditar);
					} else {
						// No se encontró una factura con esa parametrización
					}
				} // foreach dataFacturaComision

				// Ahora verificar y procesar los abonos
				$dataFacturaComisionAbono = $cloud->rows("
						SELECT 
							comisionPagarCalculoId,
							comisionAbonoPagar,
							correlativoFactura,
							tipoFactura,
							fechaFactura,
							sucursalFactura,
							fechaAbono,
							totalAbono
						FROM conta_comision_pagar_calculo
						WHERE comisionPagarPeriodoId = ? AND (numRegistroCliente = ? OR nombreCliente = ?) 
						AND (codVendedor = ? OR nombreEmpleado = ?) AND flgIdentificador = ? AND flgDelete = ?
						GROUP BY nombreEmpleado, correlativoFactura, tipoFactura, fechaAbono, totalAbono, flgRepetidoDiferente
						ORDER BY fechaFactura, correlativoFactura
					", [$_POST['comisionPagarPeriodoId'], $parametrizacionCompartida->numRegistroCliente, $parametrizacionCompartida->nombreCliente, $parametrizacionCompartida->codVendedor, $parametrizacionCompartida->nombreEmpleado, 'A', 0]);
				foreach ($dataFacturaComisionAbono as $facturaComisionAbono) {
					$comisionPagarCalculoId = $facturaComisionAbono->comisionPagarCalculoId;
					$comisionPagarAbono = $facturaComisionAbono->comisionAbonoPagar;

					// Se encontró abono, repartir la comisión del vendedor principal
					$porcentajeCompartidoVendedor = $parametrizacionCompartida->porcentajeComisionCompartida / 100;
					$comisionPagarCompartida = $comisionPagarAbono * $porcentajeCompartidoVendedor;

					$update = [
						'flgComisionEditar' => 1,
						'comisionPagarEditar' => $comisionPagarCompartida
					];
					$where = [
						'comisionPagarPeriodoId' => $_POST['comisionPagarPeriodoId'],
						'codVendedor' => $parametrizacionCompartida->codVendedor,
						'nombreEmpleado' => $parametrizacionCompartida->nombreEmpleado,
						'nombreCliente' => $parametrizacionCompartida->nombreCliente,
						'correlativoFactura' => $facturaComisionAbono->correlativoFactura,
						'tipoFactura' => $facturaComisionAbono->tipoFactura,
						'fechaFactura' => $facturaComisionAbono->fechaFactura,
						'sucursalFactura' => $facturaComisionAbono->sucursalFactura,
						'fechaAbono' => $facturaComisionAbono->fechaAbono,
						'totalAbono' => $facturaComisionAbono->totalAbono,
						'flgIdentificador' => 'A',
						'flgDelete' => '0'
					];
					$cloud->update('conta_comision_pagar_calculo', $update, $where);

					$txtCompartida = "Comisión compartida (abono), descripción de parametrización: $parametrizacionCompartida->descripcionParametrizacion, porcentaje compartido del vendedor = $" . number_format($comisionPagarCompartida, 2, '.', ',') . " ($parametrizacionCompartida->porcentajeComisionCompartida%). Compartida con los vendedores: ";

					// luego iterar la de los otros vendedores con los que comparte y excluirlo a él
					$dataVendedoresCompartidos = $cloud->rows("
							SELECT 
								comisionCompartidaParamDetalleId,
								nombreEmpleado,
								porcentajeComisionCompartida 
							FROM conta_comision_compartida_parametrizacion_detalle
							WHERE comisionCompartidaParamId = ? AND flgDelete = ? AND comisionCompartidaParamDetalleId <> ?  
						", [$parametrizacionCompartida->comisionCompartidaParamId, 0, $parametrizacionCompartida->comisionCompartidaParamDetalleId]);

					foreach ($dataVendedoresCompartidos as $vendedorCompartido) {
						$porcentajeDetalleCompartida = $vendedorCompartido->porcentajeComisionCompartida / 100;

						$comisionDetalleCompartida = $comisionPagarAbono * $porcentajeDetalleCompartida;

						$dataComisionCompartidaCalculada = $cloud->row("
								SELECT 
									comisionCompartidaCalculoId 
								FROM conta_comision_compartida_calculo
								WHERE comisionCompartidaParamDetalleId = ? AND comisionPagarCalculoId = ? AND flgDelete = ?
								LIMIT 1
							", [$vendedorCompartido->comisionCompartidaParamDetalleId, $comisionPagarCalculoId, 0]);

						if ($dataComisionCompartidaCalculada) {
							// Había calculo anterior, borrarlo para que quede solo el insert
							$cloud->deleteById("conta_comision_compartida_calculo", "comisionCompartidaCalculoId", $dataComisionCompartidaCalculada->comisionCompartidaCalculoId);
						} else {
							// No encontró calculo anterior, solo hacer insert
						}

						$insertCalculoCompartido = [
							'comisionCompartidaParamDetalleId' => $vendedorCompartido->comisionCompartidaParamDetalleId,
							'comisionPagarCalculoId' => $comisionPagarCalculoId,
							'comisionCompartidaPagar' => $comisionDetalleCompartida
						];
						$comisionCompartidaCalculoId = $cloud->insert('conta_comision_compartida_calculo', $insertCalculoCompartido);

						$txtCompartida .= "$vendedorCompartido->nombreEmpleado = $" . number_format($comisionDetalleCompartida, 2, '.', ',') . " ($vendedorCompartido->porcentajeComisionCompartida%), ";
					}

					$insertBitacoraEditar = [
						'comisionPagarCalculoId' => $comisionPagarCalculoId,
						'comisionPagarCloud' => $comisionPagarAbono,
						'comisionPagarNueva' => $comisionPagarCompartida,
						'motivoEditar' => $txtCompartida
					];
					$bitComisionEditarId = $cloud->insert('bit_comisiones_editar', $insertBitacoraEditar);
				} // foreach dataFacturaComisionAbono
			} // foreach dataParametrizacionCompartida

			echo "success";
			break;

		case "pagos-transferencias-finalizar":
			/*
				POST:
				pagoTransferenciaId
				bancoId
				txtTitulo
			*/
			$update = [
				"estadoPago" => "Finalizado"
			];
			$where = ["pagoTransferenciaId" => $_POST['pagoTransferenciaId']];
			$cloud->update("conta_pagos_transferencias", $update, $where);

			echo "success";
			break;

		case "bonos-pagos-cierre":
			/*
				POST:
				periodoBonoId
				txtPeriodo
				fechaPagoBono
			*/
			$update = [
				"estadoPeriodoBono" => "Finalizado",
				"fechaPagoBono" => $_POST['fechaPagoBono']
			];
			$where = ["periodoBonoId" => $_POST['periodoBonoId']];
			$cloud->update("conta_periodos_bonos", $update, $where);

			$cloud->writeBitacora("movUpdate", "( $fhActual ) Aplicó el cierre de periodo para el pago de bonos: $_POST[txtPeriodo]");

			echo "success";
			break;

		case "bonos-encargados-cuenta":
			/*
				POST:
				bonoPersonaId
				nombreCompleto
				cuentaBonoId
			*/
			// Requerimiento futuro: Si solicitan que cada encargado tenga solo sus cuentas especificas, se debe crear tabla conf_bonos_personas_cuentas para agregarlas ahí y volver la modal una modal crud con datatable
			$update = [
				"cuentaBonoId" => $_POST['cuentaBonoId']
			];
			$where = ["bonoPersonaId" => $_POST['bonoPersonaId']];
			$cloud->update("conf_bonos_personas", $update, $where);

			$cloud->writeBitacora("movUpdate", "( $fhActual ) Asignó una cuenta principal al encargado: $_POST[nombreCompleto] (ID Cuenta: $_POST[cuentaBonoId])");

			echo "success";
			break;

		case "nueva-cuenta-contable":
			$cuentaPadreId = empty($_POST['cuentaPadreId']) ? 0 : $_POST['cuentaPadreId'];
			$flgCentroCostos = $_POST['flgCentroCostos'];
			$centroCostoId = ($flgCentroCostos === "Si" && !empty($_POST['centroCostoId'])) ? $_POST['centroCostoId'] : null;
			$subCentroCostoId = ($flgCentroCostos === "Si" && !empty($_POST['subCentroCostoId'])) ? $_POST['subCentroCostoId'] : null;

			$update = [
				"numeroCuenta" => $_POST['numeroCuenta'],
				"descripcionCuenta" => $_POST['descripcionCuenta'],
				"tipoCuenta" => $_POST['tipoCuenta'],
				"tipoMayoreo" => $_POST['tipoMayoreo'],
				"categoriaCuenta" => $_POST['categoriaCuenta'],
				"cuentaPadreId" => $cuentaPadreId,
				"flgCentroCostos" => $flgCentroCostos,
				"centroCostoId" => $centroCostoId,
				"centroCostoDetalleId" => $subCentroCostoId
			];

			$where = ["cuentaContaId" => $_POST['cuentaContaId']];
			$cloud->update("conta_cuentas_contables", $update, $where);

			$cloud->writeBitacora("movUpdate", "( $fhActual ) Se modificó la cuenta contable: $_POST[cuentaContaId]");

			echo "success";
			break;

		case 'centros-costos':
			/*
			  POST:
				typeOperation: update
				operation: centros-costos
				centroCostoId: 1
				codigoCentroCosto: CC01
				nombreCentroCosto: CASA MATRIZ2
			*/
			$existeClasificacion = $cloud->count("
					  SELECT codigoCentroCosto, nombreCentroCosto FROM conta_centros_costo
					  WHERE  nombreCentroCosto = ? AND centroCostoId <> ? AND flgDelete = ?
					", [$_POST['nombreCentroCosto'], $_POST['centroCostoId'], 0]);

			if ($existeClasificacion == 0) {
				$update = [
					'nombreCentroCosto' => $_POST['nombreCentroCosto'],
					'codigoCentroCosto' => $_POST['codigoCentroCosto']
				];
				$where = ['centroCostoId' => $_POST['centroCostoId']];
				$cloud->update("conta_centros_costo", $update, $where);

				echo "success";
			} else {
				echo 'El centro de costos: ' . $_POST['nombreCentroCosto'] . ' ya fue creado.';
			}
			break;

		case 'sub-centros-costos':
			/*
			POST:
				typeOperation: update
				operation: sub-centros-costos
				subCentroCostoId: 1
				codigoSubcentroCosto: ln
				nombreSubcentroCosto: xdx
			*/
			$existeClasificacion = $cloud->count("
						SELECT codigoSubcentroCosto, nombreSubcentroCosto FROM conta_subcentros_costo
						WHERE  nombreSubcentroCosto = ? AND subCentroCostoId <> ? AND flgDelete = ?
						", [$_POST['nombreSubcentroCosto'], $_POST['subCentroCostoId'], 0]);

			if ($existeClasificacion == 0) {
				$update = [
					'nombreSubcentroCosto' => $_POST['nombreSubcentroCosto'],
					'codigoSubcentroCosto' => $_POST['codigoSubcentroCosto']
				];
				$where = ['subCentroCostoId' => $_POST['subCentroCostoId']];
				$cloud->update("conta_subcentros_costo", $update, $where);

				echo "success";
			} else {
				echo 'El centro de costos: ' . $_POST['nombreSubcentroCosto'] . ' ya fue creado.';
			}
			break;
		case "cerrar-periodo-contable":
			/*
				POST:
				id // ID del periodo a cerrar
			*/

			$periodoId = $_POST["id"];

			$existe = $cloud->row("
				SELECT partidaContaPeriodoId, mes, anio, estadoPeriodoPartidas,
					CONCAT(mesNombre, ' ', anio) AS periodo
				FROM conta_partidas_contables_periodos
				WHERE partidaContaPeriodoId = ?
			", [$periodoId]);

			if ($existe) {
				$anio = $existe->anio;
				$mes = $existe->mes;

				// Calcular mes y año del período anterior
				if ($mes == 1) {
					$mesAnterior = 13; // Asumo que 13 significa "cierre anual"
					$anioAnterior = $anio - 1;
				} else {
					$mesAnterior = $mes - 1;
					$anioAnterior = $anio;
				}

				$periodoAnterior = $cloud->row("
					SELECT estadoPeriodoPartidas, CONCAT(mesNombre, ' ', anio) AS periodo
					FROM conta_partidas_contables_periodos
					WHERE mes = ? AND anio = ? AND flgDelete = ?
				", [$mesAnterior, $anioAnterior, 0]);

				if ($periodoAnterior && $periodoAnterior->estadoPeriodoPartidas == 'Activo') {
					$periodoTxt = $periodoAnterior ? $periodoAnterior->periodo : "anterior";
					echo "No es posible cerrar el período actual. El período $periodoTxt no ha sido finalizado.";
				} else {

					$pendiente = $cloud->row("
						SELECT COUNT(partidaContableId) AS total
						FROM conta_partidas_contables
						WHERE estadoPartidaContable = ? AND flgDelete = ? AND partidaContaPeriodoId = ?
					", ['Pendiente', 0, $periodoId]);

					if ($pendiente->total == 0) {

						if ($existe->estadoPeriodoPartidas === 'Activo') {
							$update = [
								"estadoPeriodoPartidas" => "Finalizado",
								"fechaCierrePeriodo" => date("Y-m-d H:i:s"),
							];
							$where = ["partidaContaPeriodoId" => $periodoId];

							$cloud->update("conta_partidas_contables_periodos", $update, $where);
							$cloud->writeBitacora(
								"movUpdate",
								"($fhActual) Se cerró el período contable: {$existe->periodo}"
							);

							echo "success";
						} else {
							echo "El período {$existe->periodo} ya está finalizado.";
						}
					} else {
						echo "No se puede cerrar el período actual porque existen partidas contables pendientes.";
					}
				}
			} else {
				echo "No se encontró el período solicitado.";
			}
			break;
		case "abrir-periodo-contable":
			/*
				POST:
				periodoContable //id
			*/

			$idPeriodo = $_POST["id"];

			//! Obtener el periodo solicitado
			$existe = $cloud->row("
					SELECT estadoPeriodoPartidas, mes, anio, concat(mesNombre,' ',anio) AS periodo 
					FROM conta_partidas_contables_periodos 
					WHERE partidaContaPeriodoId = ?", [$idPeriodo]);

			if (!$existe) {
				echo "Período no encontrado.";
				exit;
			}

			//! Verifica si ya está activo
			if ($existe->estadoPeriodoPartidas != 'Finalizado') {
				echo "El período $existe->periodo ya está Activo o no puede modificarse.";
				exit;
			}

			//? Obtener el último período finalizado
			$ultimoFinalizado = $cloud->row("
					SELECT partidaContaPeriodoId 
					FROM conta_partidas_contables_periodos 
					WHERE estadoPeriodoPartidas = 'Finalizado' 
					ORDER BY anio DESC, mes DESC 
					LIMIT 1");

			$mesActual = date('n');
			$anioActual = date('Y');

			//? Validar si es el último finalizado o el mes actual
			$esUltimoFinalizado = ($ultimoFinalizado && $ultimoFinalizado->partidaContaPeriodoId == $idPeriodo);
			$esPeriodoActual = ($existe->mes == $mesActual && $existe->anio == $anioActual);

			if ($esUltimoFinalizado || $esPeriodoActual) {
				$update = ["estadoPeriodoPartidas" => "Activo"];
				$where = ["partidaContaPeriodoId" => $idPeriodo];
				$cloud->update("conta_partidas_contables_periodos", $update, $where);
				$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Reabrió el período contable: #" . $existe->periodo);
				echo "success";
			} else {
				echo "Solo se puede abrir el último período finalizado o el período contable actual.";
			}

			break;
		case 'partida-contable-contingencia':

			$tolerancia = getToteranciaPartidas();

			$partidas = $cloud->rows("
        SELECT 
            p.partidaContableId,
            p.cargoPartida,
            p.abonoPartida,
            COALESCE(SUM(d.cargos), 0) AS totalCargos,
            COALESCE(SUM(d.abonos), 0) AS totalAbonos
        FROM conta_partidas_contables p
        LEFT JOIN conta_partidas_contables_detalle d 
            ON p.partidaContableId = d.partidaContableId 
            AND d.flgDelete = 0
        WHERE p.flgDelete = 0
        GROUP BY p.partidaContableId, p.cargoPartida, p.abonoPartida
    ", []);

			$pendientes = [];

			foreach ($partidas as $p) {
				$cargoTotal = (float) $p->totalCargos;
				$abonoTotal = (float) $p->totalAbonos;
				$cargoEncabezado = (float) $p->cargoPartida;
				$abonoEncabezado = (float) $p->abonoPartida;

				// Verificar diferencias con tolerancia
				$difCargos = abs($cargoTotal - $abonoTotal);
				$difCargoEnc = abs($cargoTotal - $cargoEncabezado);
				$difAbonoEnc = abs($abonoTotal - $abonoEncabezado);

				if (
					$difCargos > $tolerancia ||
					$difCargoEnc > $tolerancia ||
					$difAbonoEnc > $tolerancia
				) {
					$pendientes[] = $p->partidaContableId;
				}
			}

			if (!empty($pendientes)) {
				foreach ($pendientes as $id) {
					$cloud->update("conta_partidas_contables", ['estadoPartidaContable' => 'Pendiente'], [
						'partidaContableId' => $id
					]);
				}
			}

			$cloud->writeBitacora(
				"movUpdate",
				"(" . $fhActual . ") Se aplicó movimiento de contingencia con tolerancia ±" . number_format($tolerancia, 6)
			);

			echo json_encode(['status' => 'success', 'pendientes' => $pendientes]);
			break;
		case 'finalizar-partida-contable':

			$partidaContableId = $_POST['partidaContableId'] ?? 0;

			if (!$partidaContableId) {
				echo "No se recibió un ID de partida válido.";
				break;
			}

			$result = $cloud->row("
				SELECT 
					COALESCE(SUM(cargos), 0) AS cargos,
					COALESCE(SUM(abonos), 0) AS abonos
				FROM conta_partidas_contables_detalle
				WHERE partidaContableId = ? AND flgDelete = 0
			", [$partidaContableId]);

			if ($result) {
				$cargoTotal = (float) $result->cargos;
				$abonoTotal = (float) $result->abonos;
				$tolerancia = getToteranciaPartidas();
				$diferencia = abs($cargoTotal - $abonoTotal);

				if ($diferencia <= $tolerancia) {
					$update = [
						'estadoPartidaContable' => 'Finalizada',
						'cargoPartida' => $cargoTotal,
						'abonoPartida' => $abonoTotal
					];

					$where = ['partidaContableId' => $partidaContableId];
					$cloud->update("conta_partidas_contables", $update, $where);

					$cloud->writeBitacora(
						"movUpdate",
						"($fhActual) Se finalizó correctamente la partida contable ID #$partidaContableId. " .
							"Diferencia detectada: " . number_format($diferencia, 6, '.', '') .
							" (tolerancia ±" . number_format($tolerancia, 6, '.', '') . ")"
					);

					echo "success";
				} else {
					//! Si la diferencia supera la tolerancia, no se finaliza
					echo "No se puede finalizar: la partida tiene diferencia de " .
						number_format($diferencia, 6, '.', '') .
						" entre cargos y abonos.";
				}
			} else {
				echo "No es posible finalizar: la partida contable no contiene movimientos.";
			}

			break;

		case 'abrir-partida-contable':
			/*
				POST:
					partidaContableId: 010
					typeOperation: update
					operation: finalizar-partida-contable
					numPartida: 00000003
					periodoPartidas: 5
					tipoPartidas: 1
					fechaPartida: 2025-05-21
					descripcionPartida: ESTE ES UNA PARTIDA DE PRUEBA 
			*/
			$update = [
				'estadoPartidaContable' => 'Pendiente',
			];
			$where = ["partidaContableId" => $_POST['partidaContableId']];
			$cloud->update("conta_partidas_contables", $update, $where);
			$cloud->writeBitacora("movUpdate", "(" . $fhActual . ") Se volvio a abrir la partida contable con el id: #" . $_POST['partidaContableId']);
			echo "success";

			break;
		case 'partida-contable-detalle':

			$centroDeCostoId = (int) ($_POST['centroCostoId'] ?? 0);
			$subCentroCostoId = (int) ($_POST['subCentroCostoId'] ?? 0);

			$documentoId = !empty($_POST['documentoId']) && is_numeric($_POST['documentoId'])
				? (int) $_POST['documentoId']
				: null;

			if ($documentoId) {
				$dataCompra = $cloud->row("SELECT numeroControl AS numFactura FROM comp_compras$yearBD WHERE compraId = ? AND flgDelete = 0", [$documentoId]);
				$update = [
					'partidaContableId' => $_POST['partidaContableId'],
					'centroCostoId' => $centroDeCostoId,
					'subCentroCostoId' => $subCentroCostoId,
					'tipoDTEId' => 4,
					'documentoId' => $documentoId,
					'numDocumento' => $dataCompra->numFactura ?? 0,
					'partidaContaPeriodoId' => $_POST['partidaContaPeriodoId'],
					'cuentaContaId' => $_POST['cuentaId'],
					'descripcionPartidaDetalle' => $_POST['descripcion'],
					'cargos' => abs((float) str_replace(',', '', $_POST['cargos'])),
					'abonos' => abs((float) str_replace(',', '', $_POST['abonos']))
				];
			} else {
				$update = [
					'partidaContableId' => $_POST['partidaContableId'],
					'centroCostoId' => $centroDeCostoId,
					'subCentroCostoId' => $subCentroCostoId,
					'documentoId' => $documentoId ?? 0,
					'tipoDTEId' => 4,
					'partidaContaPeriodoId' => $_POST['partidaContaPeriodoId'],
					'cuentaContaId' => $_POST['cuentaId'],
					'descripcionPartidaDetalle' => $_POST['descripcion'],
					'cargos' => abs((float) str_replace(',', '', $_POST['cargos'])),
					'abonos' => abs((float) str_replace(',', '', $_POST['abonos']))
				];
			}

			$where = ['partidaContableDetalleId' => $_POST['partidaContableDetalleId']];
			$updateDetalle = $cloud->update("conta_partidas_contables_detalle", $update, $where);

			if ($updateDetalle) {
				$cloud->writeBitacora("movInsert", "($fhActual) Modificó un detalle de partida " . $_POST['partidaContableId']);

				$result = $cloud->row("
            SELECT SUM(cargos) AS cargos, SUM(abonos) AS abonos
            FROM conta_partidas_contables_detalle
            WHERE partidaContableId = ? AND flgDelete = 0
        ", [$_POST['partidaContableId']]);

				if ($result) {
					$params = [
						'cargoPartida' => (float) $result->cargos,
						'abonoPartida' => (float) $result->abonos
					];
					$whereP = ['partidaContableId' => $_POST['partidaContableId']];
					$cloud->update("conta_partidas_contables", $params, $whereP);
				}

				echo json_encode([
					'status' => 'success',
					'partidaContableId' => $_POST['partidaContableId']
				]);
			}
			break;
		case 'cuadratura-partidas':
			$partidaContableId = $_POST['partidaContableId'] ?? 0;

			if (!$partidaContableId) {
				echo json_encode(['status' => 'error', 'message' => 'No se recibió partidaContableId']);
				exit;
			}
			$result = $cloud->row("
				SELECT 
					COALESCE(SUM(d.cargos), 0) AS totalCargos,
					COALESCE(SUM(d.abonos), 0) AS totalAbonos
				FROM conta_partidas_contables_detalle d
				WHERE d.partidaContableId = ? AND d.flgDelete = 0
			", [$partidaContableId]);

			if (!$result) {
				echo json_encode(['status' => 'error', 'message' => 'No se encontraron detalles.']);
				exit;
			}

			$cargo = (float)$result->totalCargos;
			$abono = (float)$result->totalAbonos;
			$diferencia = $cargo - $abono;
			$tolerancia = getToteranciaPartidas();

			// Si la diferencia está dentro de la tolerancia, se ajusta
			if (abs($diferencia) <= $tolerancia) {

				// Buscar el movimiento más pequeño (para ajustarlo)
				$detalle = $cloud->row("
					SELECT partidaContableDetalleId, cargos, abonos
					FROM conta_partidas_contables_detalle
					WHERE partidaContableId = ? AND flgDelete = 0
					ORDER BY (ABS(cargos - abonos)) ASC
					LIMIT 1
				", [$partidaContableId]);

				if ($detalle) {
					$ajuste = abs($diferencia); // lo que hay que ajustar

					// Si cargos < abonos, subir el cargo. Si abonos < cargos, subir el abono.
					if ($cargo < $abono) {
						$nuevoCargo = (float)$detalle->cargos + $ajuste;
						$cloud->update(
							"conta_partidas_contables_detalle",
							['cargos' => $nuevoCargo],
							['partidaContableDetalleId' => $detalle->partidaContableDetalleId]
						);
					} else {
						$nuevoAbono = (float)$detalle->abonos + $ajuste;
						$cloud->update(
							"conta_partidas_contables_detalle",
							['abonos' => $nuevoAbono],
							['partidaContableDetalleId' => $detalle->partidaContableDetalleId]
						);
					}

					// Recalcular totales después del ajuste
					$result = $cloud->row("
						SELECT 
							COALESCE(SUM(cargos), 0) AS totalCargos,
							COALESCE(SUM(abonos), 0) AS totalAbonos
						FROM conta_partidas_contables_detalle
						WHERE partidaContableId = ? AND flgDelete = 0
					", [$partidaContableId]);

					$nuevoCargo = (float)$result->totalCargos;
					$nuevoAbono = (float)$result->totalAbonos;

					// Actualizar encabezado
					$cloud->update("conta_partidas_contables", [
						'cargoPartida' => $nuevoCargo,
						'abonoPartida' => $nuevoAbono
					], ['partidaContableId' => $partidaContableId]);

					// Registrar bitácora
					$cloud->writeBitacora(
						"movUpdate",
						"(" . date('Y-m-d H:i:s') . ") Se cuadró la partida #$partidaContableId ajustando un movimiento menor en ±$ajuste (tolerancia: $tolerancia)"
					);

					echo json_encode([
						'status' => 'success',
						'message' => "La partida #$partidaContableId fue ajustada automáticamente dentro de la tolerancia.",
						'cargo' => $nuevoCargo,
						'abono' => $nuevoAbono,
						'ajuste' => $ajuste,
						'tolerancia' => $tolerancia
					]);
				} else {
					echo json_encode(['status' => 'error', 'message' => 'No se encontró un movimiento para ajustar.']);
				}

			} else {
				echo json_encode([
					'status' => 'error',
					'message' => "La diferencia ($diferencia) supera la tolerancia permitida (±$tolerancia). No se realizó el ajuste.",
					'cargo' => $cargo,
					'abono' => $abono,
					'diferencia' => $diferencia,
					'tolerancia' => $tolerancia
				]);
			}
			break;

		default:
			echo "No se encontró la operación.";
			break;
	}
} else {
	header("Location: /indupal-cloud/app/");
}