<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();
    $dataVacaciones= $cloud->rows("
        SELECT prsExpedienteId,
               estadoVacacion,
               personaId, nombreCompleto,
               cargoPersona,
               sucursal,
               departamentoSucursal
        FROM view_expedientes 
        WHERE estadoPersona = ? AND estadoExpediente = ? AND tipoVacacion = ? AND estadoVacacion = ?
        ORDER BY apellido1, apellido2, nombre1, nombre2
    ",['Activo','Activo','Individuales',$_POST['estadoVacacion']]);

    $estado = $_POST['estadoVacacion'];

    foreach($dataVacaciones as $dataVacaciones) { 
    
    $jsonCambiarEstado = [
        "prsExpedienteId" => $dataVacaciones->prsExpedienteId,
        "typeOperation"   => "update",
        "operation"       => "cambiar-estadoVacacion",
        "estadoVacacion"  => $dataVacaciones->estadoVacacion == "Activo" ? "inactivo" : "activo"
    ];
    $funcionCambiarEstado = htmlspecialchars(json_encode($jsonCambiarEstado));
    
?>
        <div class="col-md-6 buscarVacacion">
            <?php
                if($_POST["estadoVacacion"] == "Activo"){
                    $color="activo";
                    $btnEliminarPeriodo = "
                        <button type='button' class='btn btn-danger btn-sm ttip'>
                            <i class='fas fa-trash-alt'></i>
                            <span class='ttiptext'>Eliminar periodo</span>
                        </button>
                    ";
                }else{
                    $color="alerta";
                    $btnEliminarPeriodo = "";
                }
            ?>
            <div class="exp-card <?php echo $color;?> mt-2">
                <div class="text-end mb-2">
                    <button type="button" class="btn btn-secondary btn-sm ttip" onclick="verDiasSolicitados(<?php echo $dataVacaciones->prsExpedienteId; ?>);">
                        <i class="fas fa-umbrella-beach"></i> Ver días solicitados
                        <span class="ttiptext">Ver días solicitados</span>
                    </button>
                    <?php
                        if($_POST["estadoVacacion"] == "Activo") {
                            echo "  <button type='button' class='btn btn-danger btn-sm ttip' onclick='cambiarEstadoVacacion($funcionCambiarEstado);'>
                                        <i class='fas fa-retweet'></i>
                                        <span class='ttiptext'>Cambiar estado de vacación</span>
                                    </button>";
                        } else {
                            echo "  <button type='button' class='btn btn-success btn-sm ttip' onclick='cambiarEstadoVacacion($funcionCambiarEstado);'>
                                        <i class='fas fa-retweet'></i>
                                        <span class='ttiptext'>Cambiar estado de vacación</span>
                                    </button>";
                        }
                    ?>
                </div>
                <div class="row mb-4">
                    <div class="col-md-12">
                        <i class="fas fa-user-tie"></i> <b>Empleado: </b> <?php echo $dataVacaciones->nombreCompleto; ?><br>
                        <i class="fas fa-briefcase"></i> <b>Cargo: </b> <?php echo $dataVacaciones->cargoPersona ;?><br>
                        <i class="fas fa-building"></i> <b>Sucursal (Depto.): </b> 
                        <?php 
                        echo $dataVacaciones->sucursal;
                        echo " ($dataVacaciones->departamentoSucursal)";
                        ?>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <th>#</th>
                            <th>Año</th>
                            <th>Días</th>
                            <th width="20%">Acciones</th>
                        </thead>
                        <tbody>
                            <?php
                            $dias = 0;
                            $n = 0; 
                            $dataPeriodosVacacion = $cloud->rows("
                                SELECT  ctrlVacacionId, anio,
                                diasRestantesVacacion
                                FROM ctrl_persona_vacaciones
                                WHERE personaId = ? AND flgDelete = ? AND diasRestantesVacacion > 0
                                ORDER BY anio
                            ",[$dataVacaciones->personaId, 0]);

                            if (empty($dataPeriodosVacacion )) {
                                echo "No se encontraron días de vacaciones";
                            } else {
                                foreach($dataPeriodosVacacion  as $periodoVacacion) {
                                    $n ++;
                                    $dias += $periodoVacacion->diasRestantesVacacion;

                                    if($_POST["estadoVacacion"] == "Activo") {
                                        $jsonEliminarPeriodo = [
                                            "typeOperation"     => "delete",
                                            "operation"         => "vacaciones-periodo",
                                            "ctrlVacacionId"    => $periodoVacacion->ctrlVacacionId,
                                            "nombreCompleto"    => $dataVacaciones->nombreCompleto,
                                            "anio"              => $periodoVacacion->anio
                                        ];
                                        $btnEliminarPeriodo = "
                                            <button type='button' class='btn btn-danger btn-sm ttip' onclick='eliminarPeriodoVacacion(".htmlspecialchars(json_encode($jsonEliminarPeriodo)).");'>
                                                <i class='fas fa-trash-alt'></i>
                                                <span class='ttiptext'>Eliminar periodo</span>
                                            </button>
                                        ";
                                    }else{
                                        $btnEliminarPeriodo = "";
                                    }
                            ?>
                                    <tr>
                                        <td><?php echo $n; ?></td>
                                        <td><?php echo $periodoVacacion->anio; ?></td>
                                        <td><?php echo $periodoVacacion->diasRestantesVacacion; ?></td>
                                        <td><?php echo $btnEliminarPeriodo; ?></td>
                                    </tr>
                            <?php 
                                    } // foreach de las vacaciones Inactivas
                                } //if las vacaciones Inactivas
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2">Total</td>
                                <td><?php echo $dias; ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
<?php
    } // foreach dataVacaciones
?>