<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    // PENDIENTE: nombre3, apellido3 (aCasada - reemplazar por apellido2 si lo tiene)

    $dataEmpleados = $cloud->rows("
        SELECT
            personaId, 
            docIdentidad,
            numIdentidad, 
            nit, 
            CONCAT(
                IFNULL(apellido1, '-'),
                ' ',
                IFNULL(apellido2, '-'),
                ', ',
                IFNULL(nombre1, '-'),
                ' ',
                IFNULL(nombre2, '-')
            ) AS nombreCompleto,
            fechaNacimiento, 
            fechaInicioLabores,
            sexo, 
            estadoPersona
        FROM th_personas
        WHERE prsTipoId = '1' AND estadoPersona = ? AND flgDelete = '0'
        ORDER BY apellido1, apellido2, nombre1, nombre2
    ", [$_POST["estado"]]);
    $n = 0;
    foreach ($dataEmpleados as $dataEmpleados) {
    	$n += 1;

        if(is_null($dataEmpleados->nit) || $dataEmpleados->nit == "") {
            $nit = "-";
        } else {
            $nit = $dataEmpleados->nit;
        }
        $arrayFechaNacimiento = explode("-", $dataEmpleados->fechaNacimiento);
        
        if(isset($arrayFechaNacimiento[1])) {
            $birthday = diferenciaFechas(strtotime(date("Y-m-d")), strtotime(date("Y") . $arrayFechaNacimiento[1] . $arrayFechaNacimiento[2]));
        } else {
            $birthday = "-";
        }
        
        $calcularEdad = date_diff(date_create($dataEmpleados->fechaNacimiento), date_create(date("Y-m-d")));

    	$empleado = '
    		<b><i class="fas fa-user-tie"></i> Nombre completo: </b>' . $dataEmpleados->nombreCompleto . ' (' . $dataEmpleados->sexo . ')<br>
            <b><i class="fas fa-address-card"></i> '.$dataEmpleados->docIdentidad.': </b>'.$dataEmpleados->numIdentidad.'<br>
            <b><i class="fas fa-address-card"></i> NIT: </b> '.$nit.'<br>
            <b><i class="fas fa-calendar"></i> F. Nacimiento: </b> '.date("d/m/Y", strtotime($dataEmpleados->fechaNacimiento)).'<br>
            <b><i class="fas fa-user-circle"></i> Edad: </b> '.$calcularEdad->format('%y').' años<br>
            <b><i class="fas fa-birthday-cake"></i> Cumpleaños: </b>'.$birthday.'
        ';	

        $estadoPersona = ($dataEmpleados->estadoPersona == "Activo") ? '<span class="text-success fw-bold">Activo</span>' : '<span class="text-danger fw-bold">Inactivo</span>';

        // Datos de Expediente
        // Validar si ya tiene expediente, ya que sino da error el wrapper al no encontrar data y utilizar switch y demás validaciones
        $poseeExpediente = $cloud->count("
            SELECT prsExpedienteId FROM th_expediente_personas
            WHERE personaId = ? AND flgDelete = '0'
        ",[$dataEmpleados->personaId]);

        if($poseeExpediente > 0) {
            // ORDER BY prsExpedienteId y LIMIT 1 es para traer el último expediente que la persona posea, así se interpreta como el "actual"
            $dataUltimoExpediente = $cloud->row("
                SELECT
                    exp.prsExpedienteId AS prsExpedienteId, 
                    exp.personaId AS personaId, 
                    exp.prsCargoId AS prsCargoId,
                    pc.cargoPersona AS cargoPersona,
                    sc.sucursalId AS sucursalId,
                    sc.sucursal AS sucursal,
                    exp.sucursalDepartamentoId AS sucursalDepartamentoId, 
                    sd.codSucursalDepartamento AS codSucursalDepartamento,
                    sd.departamentoSucursal AS departamentoSucursal,
                    exp.tipoContrato AS tipoContrato, 
                    exp.fechaInicio AS fechaInicio, 
                    exp.fechaFinalizacion AS fechaFinalizacion, 
                    exp.justificacionEstado AS justificacionEstado, 
                    exp.estadoExpediente AS estadoExpediente
                FROM th_expediente_personas exp
                LEFT JOIN cat_personas_cargos pc ON pc.prsCargoId = exp.prsCargoId
                LEFT JOIN cat_sucursales_departamentos sd ON sd.sucursalDepartamentoId = exp.sucursalDepartamentoId
                LEFT JOIN cat_sucursales sc ON sc.sucursalId = sd.sucursalId
                WHERE exp.personaId = ? AND exp.flgDelete = '0'
                ORDER BY prsExpedienteId DESC
                LIMIT 1
            ", [$dataEmpleados->personaId]);
            $flgRecontratado = 0;

            // Estos estados están en el update case = expediente-procesar-baja
            switch($dataUltimoExpediente->estadoExpediente) {
                case "Inactivo": // Este no debería suceder ya que se cambiarán automáticamente cuando se cambie de cargo
                    $estadoExpediente = '<span class="text-secondary fw-bold">Inactivo</span>';
                    $txtEstado = ""; // Para $drawJustificacionBaja
                break;

                case "Finalizado": // Alcanzó la fecha de finalización de su contrato
                    $estadoExpediente = '<span class="text-warning fw-bold">Contrato finalizado</span>';
                    $txtEstado = "finalización de contrato"; // Para $drawJustificacionBaja
                break;

                case "Jubilado":
                    $estadoExpediente = '<span class="text-secondary fw-bold">Jubilado</span>';
                    $txtEstado = "jubilación"; // Para $drawJustificacionBaja
                break;

                case "Despido":
                    $estadoExpediente = '<span class="text-danger fw-bold">Despedido</span>';
                    $txtEstado = "despido"; // Para $drawJustificacionBaja
                    $flgRecontratado = 1;
                break;

                case "Renuncia":
                    $estadoExpediente = '<span class="text-danger fw-bold">Renuncia</span>';
                    $txtEstado = "renuncia"; // Para $drawJustificacionBaja
                    $flgRecontratado = 1;
                break;

                case "Abandono":
                    $estadoExpediente = '<span class="text-danger fw-bold">Abandono</span>';
                    $txtEstado = "abandono"; // Para $drawJustificacionBaja
                    $flgRecontratado = 1;
                break;

                case "Defunción":
                    $estadoExpediente = '<span class="text-danger fw-bold">Defunción</span>';
                    $txtEstado = "defunción"; // Para $drawJustificacionBaja
                    $flgRecontratado = 1;
                break;

                case "Traslado":
                    $estadoExpediente = '<span class="text-secondary fw-bold">Trasladado</span>';
                    $txtEstado = "trasladado"; // Para $drawJustificacionBaja
                    $flgRecontratado = 1;
                break;

                default:
                    $estadoExpediente = '<span class="text-success fw-bold">Activo</span>';
                    $txtEstado = ""; // Para $drawJustificacionBaja
                break;
            }

            // Si el empleado está Activo pero su último expediente es Despedido o Renuncia, solo puede suceder si se le dió de baja y después se Recontrató, por eso esta validación
            if($dataEmpleados->estadoPersona == "Activo" && $flgRecontratado == 1) {
                $dataRecontratacion = $cloud->row("
                    SELECT
                        personaRecontratacionId, 
                        personaId, 
                        fechaRecontratacion, 
                        justificacionRecontratacion 
                    FROM bit_personas_recontratacion
                    WHERE personaId = ? AND flgDelete = '0'
                    ORDER BY personaRecontratacionId DESC
                    LIMIT 1              
                ", [$dataEmpleados->personaId]);

                if (!empty($dataRecontratacion) && !empty($dataRecontratacion->fechaRecontratacion)) {
                    $fecha = date("d/m/Y", strtotime($dataRecontratacion->fechaRecontratacion));
                } else {
                    $fecha = 'No disponible';
                }

                $justificacion = !empty($dataRecontratacion->justificacionRecontratacion) ? $dataRecontratacion->justificacionRecontratacion : 'No disponible';

        
                $drawExpediente = '
                    <span class="text-secondary fw-bold">No asignado (Recontratación)</span><br>
                    <b>Fecha de recontratación: </b> '.$fecha.'<br>
                    <b>Justificación recontratación: </b> '.$justificacion.'
                ';
            } else { // Sino, dibujar su expediente con normalidad

                $cargo = ($dataUltimoExpediente->cargoPersona == '') ? '-' : $dataUltimoExpediente->cargoPersona;
                $sucursal = ($dataUltimoExpediente->sucursal == '') ? '-' : $dataUltimoExpediente->sucursal;
                $departamento = ($dataUltimoExpediente->departamentoSucursal == '') ? '-' : $dataUltimoExpediente->departamentoSucursal .' ('.$dataUltimoExpediente->codSucursalDepartamento.')';

                if($dataEmpleados->fechaInicioLabores == "") {
                    $antiguedad = "-";
                } else {
                    $antiguedad = diferenciaFechasYMD(strtotime(date("Y-m-d")), strtotime($dataEmpleados->fechaInicioLabores));
                }

                $drawExpediente = $estadoExpediente . '
                    <br>
                    <b><i class="fas fa-briefcase"></i> Cargo: </b> '.$cargo.'<br>
                    <b><i class="fas fa-building"></i> Sucursal: </b> '.$sucursal.'<br>
                    <b><i class="fas fa-building"></i> Departamento: </b> '.$departamento.'<br>
                    <b><i class="fas fa-business-time"></i> Antigüedad:</b> '.$antiguedad.'<br>
                '; 
            }
        } else { // No se le ha creado expediente
            $drawExpediente = '<span class="text-secondary fw-bold">No asignado</span><br>';
        }

        if($dataEmpleados->estadoPersona == "Activo") {
            $btnBaja = '
                <li>
                <a role="button" class="dropdown-item link-danger" onclick="modalBajaEmpleado(`'.$dataEmpleados->nombreCompleto.'^'.$dataEmpleados->personaId.'`);">
                    <i class="fas fa-user-slash"></i> Dar de Baja
                </a>
                </li>
            '; // Se dará de baja desde Expediente, ya no :v
            $drawJustificacionBaja = "";
        } else { // Inactivo
            $disabledRecontratacion = "";
            if($poseeExpediente > 0) {
                // Mostrar el motivo por el que está Inactivo
                // estadoBaja: Activo = Todavía se encuentra de "baja", Inactivo = Fue recontratado y esa baja ya no cuenta
                $dataMotivoBaja = $cloud->row("
                    SELECT
                        personaBajaId, 
                        prsExpedienteId, 
                        fechaBaja, 
                        contratable, 
                        justificacionBaja, 
                        estadoBaja
                    FROM bit_personas_bajas
                    WHERE prsExpedienteId = ? AND estadoBaja = 'Activo' AND flgDelete = '0'
                ",[$dataUltimoExpediente->prsExpedienteId]);

                $contratable = ($dataMotivoBaja->contratable == "Sí") ? '<span class="text-success fw-bold">Sí</span>' : '<span class="text-danger fw-bold">No</span>';

                $disabledRecontratacion = ($dataMotivoBaja->contratable == "No") ? "disabled" : "";

                $drawJustificacionBaja = '
                    <b><i class="fas fa-question-circle"></i> Contratable: </b> '.$contratable.'<br>
                    <b><i class="fas fa-edit"></i> Justificación de '.$txtEstado.': </b> '.$dataMotivoBaja->justificacionBaja.'<br>
                    <b><i class="fas fa-calendar-times"></i> Fecha de '.$txtEstado.': </b> '.date("d/m/Y", strtotime($dataMotivoBaja->fechaBaja)).'
                ';
            } else { // No tiene expediente, por lo que no hay que consultar su motivo de baja
                // No debería suceder ya que solo se puede dar de baja desde el expediente
                $drawJustificacionBaja = "";
            }
            // Dibujar botón re-contratación
            $btnBaja = '
                <li>
                <a role="button" class="dropdown-item link-success" onclick="modalRecontratacionEmpleado(`'.$dataEmpleados->personaId.'`);" '.$disabledRecontratacion.'>
                    <i class="fas fa-user-check"></i> Re-contratación
                </a>
                </li>
            ';
        }

        $estado = '
            <b><i class="fas fa-user-tie"></i> Empleado: </b> ' . $estadoPersona . '<br>
            <b><i class="fas fa-briefcase"></i> Expediente: </b> '.$drawExpediente.'<br>
            '.$drawJustificacionBaja;

        $otrasAcciones = '
            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-mdb-toggle="dropdown" aria-expanded="false" >
                Acciones
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <li>
                    <a role="button" class="dropdown-item" onclick="modalRelacionEmpleado(`'.$dataEmpleados->personaId.'^'.$dataEmpleados->nombreCompleto.'`);">
                        <i class="fas fa-users"></i> Núcleo familiar
                    </a>
                </li>
                <li>
                    <a role="button" class="dropdown-item" onclick="modalEmpleadoCuentasBanco(`'.$dataEmpleados->personaId.'^'.$dataEmpleados->nombreCompleto.'`);">
                        <i class="fas fa-money-check-alt"></i> Cuentas bancarias
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                '.$btnBaja.'
            </ul>
        ';

	    $acciones = '
			<button type="button" class="btn btn-primary btn-sm ttip" onclick="changePage(`'.$_SESSION["currentRoute"].'`, `perfil-empleado`, `personaId='.$dataEmpleados->personaId.'&nombreCompleto='.$dataEmpleados->nombreCompleto.'`);">
				<i class="fas fa-address-card"></i>
                <span class="ttiptext">Perfil de Empleado</span>
			</button>
            <button type="button" class="btn btn-primary btn-sm ttip" onclick="modalContactosEmpleado(`'.$dataEmpleados->personaId.'^'.$dataEmpleados->nombreCompleto.'`);">
                <i class="fas fa-phone-square-alt"></i>
                <span class="ttiptext">Contactos de Empleado</span>
            </button>
            '.$otrasAcciones.'
            
	    ';

	    $output['data'][] = array(
	        $n, // es #, se dibuja solo en el JS de datatable
	        $empleado,
	        $estado,
	        $acciones
	    );
	} // foreach

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }

    function diferenciaFechas($fechaPublicacion,$fechaActual) {
        $diff = '';
        $diferencia = ($fechaActual - $fechaPublicacion)/60/60/24;

        $flgConvertirDias = 0;
        if($diferencia < -1) {
            $txtTiempo = "Hace ";
            $diferencia *= -1;
            $flgConvertirDias = 1;
        } else if($diferencia == 0) {
            $txtTiempo = "¡Hoy es su cumpleaños!";
        } else if($diferencia == -1) {
            $txtTiempo = "Ayer fue su cumpleaños";
        } else if($diferencia == 1) {
            $txtTiempo = "Mañana es su cumpleaños";
        } else {
            $txtTiempo = "Dentro de ";
            $flgConvertirDias = 1;
        }

        if($flgConvertirDias == 1) {
            if($diferencia > 1) {
                if($diferencia < 31) {
                    if($diferencia < 7) {
                        $txtTiempo .= $diferencia . ' días';
                    } else if($diferencia < 14) {
                        $txtTiempo .= '1 semana';
                    } else if($diferencia < 21) {
                        $txtTiempo .= '2 semanas';
                    } else {
                        $txtTiempo .= '3 semanas';
                    }
                } else if($diferencia < 365 && $diferencia > 31) {
                    if($diferencia < 62) {
                        $txtTiempo .= 'un mes';
                    } else {
                        $txtTiempo .= round($diferencia / 31) . ' meses';
                    }
                } else { // Estos no deberían ocurrir xd
                    if($txtTiempo < 730 && $diferencia > 365) {
                        $txtTiempo .= 'un año';
                    } else {
                        $txtTiempo .= round($diferencia / 365) . ' años';
                    }
                }
            } else {
                // Omitir para que se muestre "Ayer"
            }
        } else {

        }

        return $txtTiempo;
    }

    function diferenciaFechasYMD($fechaPublicacion,$fechaActual) {
        $diferencia = ($fechaPublicacion - $fechaActual);

        $anios = floor($diferencia / (365*60*60*24));
        $meses = floor(($diferencia - $anios * 365*60*60*24)/ (30*60*60*24) );
        $dias  = floor(($diferencia - $anios * 365*60*60*24 - $meses *30*60*60*24) / (60*60*24)+1);

        $txtAnio = ($anios == 0 ? "" : ($anios == 1 ? "Un año, " : $anios . " años, "));
        $txtMeses = ($meses == 0 ? "" : ($meses == 1 ? "Un mes, " : $meses . " meses, "));
        $txtDias = ($dias == 1 ? "Un día" : $dias . " días");

        $antiguiedad = $txtAnio . $txtMeses . $txtDias;

        if($fechaActual == "") {
            // Para los que no se les creó expediente
            return "-";
        } else {
            if($anios >= 0) { // La fecha de publicacion era mayor que la actual (contratación a futuro)
                return $antiguiedad;
            } else {
                return "-";
            }
        }
    }
?>