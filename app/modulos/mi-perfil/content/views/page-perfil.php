<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();
    $_SESSION["pageActive"] = "m1-1^1^N/A";
    $_SESSION["currentPage"] = 1;
    $_SESSION["currentToken"] = "principal";

    $dataEmpleado = $cloud->row("
        SELECT
            CONCAT(
                IFNULL(per.apellido1, '-'),
                ' ',
                IFNULL(per.apellido2, '-'),
                ', ',
                IFNULL(per.nombre1, '-'),
                ' ',
                IFNULL(per.nombre2, '-')
            ) AS nombreCompleto,
            per.docIdentidad AS docIdentidad,
            per.numIdentidad AS numIdentidad, 
            per.fechaExpiracionIdentidad AS fechaExpiracionIdentidad,
            per.nit AS nit, 
            per.fechaNacimiento AS fechaNacimiento, 
            per.fechaInicioLabores AS fechaInicioLabores,
            per.sexo AS sexo, 
            per.estadoCivil AS estadoCivil, 
            per.tipoSangre AS tipoSangre,
            per.nombreOrganizacionIdAFP AS nombreOrganizacionIdAFP,
            nameafp.nombreOrganizacion AS nombreOrganizacionAFP,
            nameafp.abreviaturaOrganizacion AS abreviaturaOrganizacionAFP,
            per.nup AS nup,
            per.nombreOrganizacionIdISSS AS nombreOrganizacionIdISSS,
            nameisss.nombreOrganizacion AS nombreOrganizacionISSS,
            nameisss.abreviaturaOrganizacion AS abreviaturaOrganizacionISSS,
            per.numISSS AS numISSS,
            per.paisId AS paisId, 
            pa.pais AS nacionalidad,
            pa.iconBandera AS iconBandera,
            per.paisMunicipioIdDUI AS paisMunicipioIdDUI, 
            pmdui.municipioPais AS municipioDUI,
            pddui.paisDepartamentoId AS paisDepartamentoIdDUI,
            pddui.departamentoPais AS departamentoDUI,
            per.zonaResidenciaDUI AS zonaResidenciaDUI, 
            per.paisMunicipioIdActual AS paisMunicipioIdActual, 
            pmactual.municipioPais AS municipioActual,
            pdactual.paisDepartamentoId AS paisDepartamentoIdActual,
            pdactual.departamentoPais AS departamentoActual,
            per.zonaResidenciaActual AS zonaResidenciaActual, 
            per.vehiculoPropio AS vehiculoPropio, 
            per.vehiculosPropios AS vehiculosPropios, 
            per.estadoPersona AS estadoPersona
        FROM th_personas per
        LEFT JOIN cat_paises pa ON pa.paisId = per.paisId
        LEFT JOIN cat_paises_municipios pmdui ON pmdui.paisMunicipioId = per.paisMunicipioIdDUI
        LEFT JOIN cat_paises_departamentos pddui ON pddui.paisDepartamentoId = pmdui.paisDepartamentoId
        LEFT JOIN cat_paises_municipios pmactual ON pmactual.paisMunicipioId = per.paisMunicipioIdActual
        LEFT JOIN cat_paises_departamentos pdactual ON pdactual.paisDepartamentoId = pmactual.paisDepartamentoId
        LEFT JOIN cat_nombres_organizaciones nameafp ON nameafp.nombreOrganizacionId = per.nombreOrganizacionIdAFP
        LEFT JOIN cat_nombres_organizaciones nameisss ON nameisss.nombreOrganizacionId = per.nombreOrganizacionIdISSS
        WHERE per.personaId = ?
    ", [$_SESSION["personaId"]]);
    ?>
<h2>
    Perfil de Empleado
</h2>
<hr>
<ul class="nav nav-tabs mb-3" id="ntab" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active" id="ntab-1" data-mdb-toggle="pill" href="#ntab-content-1" role="tab" aria-controls="ntab-content-1" aria-selected="true">
            General
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="ntab-2" data-mdb-toggle="pill" href="#ntab-content-2" role="tab" aria-controls="ntab-content-2" aria-selected="false">
            Estudios
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="ntab-3" data-mdb-toggle="pill" href="#ntab-content-3" role="tab" aria-controls="ntab-content-3" aria-selected="false">
            Experiencia Laboral
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="ntab-4" data-mdb-toggle="pill" href="#ntab-content-4" role="tab" aria-controls="ntab-content-4" aria-selected="false">
            Habilidades
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="ntab-5" data-mdb-toggle="pill" href="#ntab-content-5" role="tab" aria-controls="ntab-content-5" aria-selected="false">
            Licencias
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="ntab-6" data-mdb-toggle="pill" href="#ntab-content-6" role="tab" aria-controls="ntab-content-6" aria-selected="false">
            Enfermedades/Alergias
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="ntab-7" data-mdb-toggle="pill" href="#ntab-content-7" role="tab" aria-controls="ntab-content-7" aria-selected="false">
            Adjuntos
        </a>
    </li>
</ul>
<div class="tab-content" id="ntab-content">
    <div class="perfil tab-pane fade show active" id="ntab-content-1" role="tabpanel" aria-labelledby="ntab-1">
        <div class="row justify-content-between">
            <div class="col-md-3">
                <?php 
                    $dataPersImg = $cloud->row("
                    SELECT COUNT(prsAdjuntoId) 
                    AS fotoPerfil FROM th_personas_adjuntos 
                    WHERE flgDelete = '0' AND descripcionPrsAdjunto = 'Actual' AND personaId =?"
                    , [$_SESSION["personaId"]]) ;

                    if ($dataPersImg->fotoPerfil == 1){
                        $dataUserImg = $cloud->row("
                        SELECT 
                        urlPrsAdjunto
                        FROM th_personas_adjuntos 
                        WHERE personaId = ? AND descripcionPrsAdjunto = 'Actual' AND flgDelete = '0'
                    ", [$_SESSION["personaId"]]);

                        $fotoPerfil = $dataUserImg->urlPrsAdjunto;
                    } else {
                        $fotoPerfil = "mi-perfil/user-default.jpg";
                    }
                ?>
                <div class="user-pic" style="background-image: url('../libraries/resources/images/<?php echo $fotoPerfil; ?>');">
                </div>
                <div class="info-emp">
                    <?php 
                        $estadoPersona = ($dataEmpleado->estadoPersona == "Activo") ? '<span class="text-success fw-bold">Activo</span>' : '<span class="text-danger fw-bold">Inactivo</span>';
                    ?>
                    <p>
                        <b><i class="fas fa-info-circle"></i> Estado:</b> <?php echo $estadoPersona; ?><br>
                        <b><i class="fas fa-calendar-day"></i> Fecha de nacimiento:</b> <?php echo date("d/m/Y", strtotime($dataEmpleado->fechaNacimiento)); ?><br>
                        <b><i class="fas fa-user-circle"></i> Edad:</b> <?php echo date_diff(date_create($dataEmpleado->fechaNacimiento), date_create(date("Y-m-d")))->format('%y'); ?> años<br>
                        <b><i class="fas fa-venus-mars"></i> Sexo:</b> <?php echo ($dataEmpleado->sexo == "F") ? "Femenino" : "Masculino"; ?><br>
                        <b><i class="fas fa-info-circle"></i> Estado civil:</b> <?php echo $dataEmpleado->estadoCivil; ?><br>
                        <b><i class="fas fa-business-time"></i> Antigüedad:</b> <?php echo diferenciaFechasYMD(strtotime(date("Y-m-d")), strtotime($dataEmpleado->fechaInicioLabores)); ?>
                        <br><br>
                    </p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary btn-sm" onclick="modalContactosEmpleado(`<?php echo $_SESSION['personaId'] . '^' . $dataEmpleado->nombreCompleto; ?>`);">
                            <i class="fas fa-phone-square-alt"></i> Contactos
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="modalRelacionEmpleado(`<?php echo $_SESSION['personaId'] . '^' . $dataEmpleado->nombreCompleto; ?>`);">
                            <i class="fas fa-users"></i> Núcleo familiar
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="row">
                    <div class="col-12">
                        <h3 class="display-5"><?php echo $dataEmpleado->nombreCompleto; ?></h3>
                        <hr>
                        <h4>Datos personales</h4>
                    </div>
                    <div class="row">
                        <div class="col-md-5">
                            <p>
                                <b></b>
                                <b><i class="fas fa-globe-americas"></i> País de origen:</b> 
                                <img src="../libraries/resources/images/<?php echo $dataEmpleado->iconBandera; ?>" alt="<?php echo $dataEmpleado->nacionalidad; ?>">
                                <?php echo $dataEmpleado->nacionalidad; ?>
                                <br>
                                <b><i class="fas fa-address-card"></i> <?php echo $dataEmpleado->docIdentidad; ?>:</b> <?php echo $dataEmpleado->numIdentidad . " (Expiración: " . str_replace("-", "/", $dataEmpleado->fechaExpiracionIdentidad) . ")"; ?><br>
                                <b><i class="fas fa-address-card"></i> NIT:</b> <?php echo (is_null($dataEmpleado->nit) || $dataEmpleado->nit == "" ? '-' : $dataEmpleado->nit); ?><br>
                                <b><i class="fas fa-money-check-alt"></i> NUP: </b> <?php echo $dataEmpleado->nup . " (" . $dataEmpleado->abreviaturaOrganizacionAFP . ")"; ?><br>
                                <b><i class="fas fa-ambulance"></i> ISSS: </b> <?php echo $dataEmpleado->numISSS; ?><br>
                                <b><i class="fas fa-syringe"></i> Tipo de sangre: </b> <?php echo (is_null($dataEmpleado->tipoSangre) || $dataEmpleado->tipoSangre == "" ? '-' : $dataEmpleado->tipoSangre); ?><br>
                                <br>
                                <h6 class="fw-bold">Lugar de residencia: Según DUI</h6>
                                <b><i class="fas fa-map-marked-alt"></i> Departamento:</b> <?php echo $dataEmpleado->departamentoDUI; ?><br>
                                <b><i class="fas fa-map-marked-alt"></i> Municipio:</b> <?php echo $dataEmpleado->municipioDUI; ?><br>
                                <b><i class="fas fa-map-marker-alt"></i> Dirección:</b> <?php echo $dataEmpleado->zonaResidenciaDUI; ?><br>
                                <br>
                                <h6 class="fw-bold">Lugar de residencia: Actual</h6>
                                <b><i class="fas fa-map-marked-alt"></i> Departamento:</b> <?php echo $dataEmpleado->departamentoActual; ?><br>
                                <b><i class="fas fa-map-marked-alt"></i> Municipio:</b> <?php echo $dataEmpleado->municipioActual; ?><br>
                                <b><i class="fas fa-map-marker-alt"></i> Dirección:</b> <?php echo $dataEmpleado->zonaResidenciaActual; ?><br>
                            </p>
                        </div>
                        <div class="col-md-5">
                            <p>
                                <b><i class="fas fa-car-side"></i> Vehículo propio:</b> <?php echo $dataEmpleado->vehiculoPropio; ?><br>
                            </p>
                            <p>
                                <?php if(!empty($dataEmpleado->vehiculosPropios)) { 
                                    echo '<ul>';
                                        $arrayVehiculos = explode(",", $dataEmpleado->vehiculosPropios);
                                        foreach($arrayVehiculos as $item){
                                            if (!empty($item)){
                                                echo '<li>'.$item.'</li>';
                                            }
                                        } 
                                    echo '</ul>';
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="ntab-content-2" role="tabpanel" aria-labelledby="ntab-2">
        <div class="table-responsive">
            <table id="tblEmpleadoEstudios" class="table table-hover" style="width: 100%;">
                <thead>
                    <tr id="filterboxrow-estudio">
                        <th>#</th>
                        <th>Estudio</th>
                        <th>Periodo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $dataEmpleadoEstudios = $cloud->rows("
                        SELECT
                            pe.prsEducacionId AS prsEducacionId, 
                            pe.centroEstudio AS centroEstudio, 
                            pe.nivelEstudio AS nivelEstudio, 
                            pe.prsArEstudioId AS prsArEstudioId, 
                            ar.areaEstudio AS areaEstudio,
                            pe.nombreCarrera AS nombreCarrera, 
                            pe.paisId AS paisId, 
                            p.pais AS pais,
                            p.iconBandera AS iconBandera,
                            pe.numMesInicio AS numMesInicio, 
                            pe.mesInicio AS mesInicio, 
                            pe.anioInicio AS anioInicio, 
                            pe.numMesFinalizacion AS numMesFinalizacion, 
                            pe.mesFinalizacion AS mesFinalizacion, 
                            pe.anioFinalizacion AS anioFinalizacion, 
                            pe.estadoEstudio AS estadoEstudio
                        FROM th_personas_educacion pe
                        LEFT JOIN cat_personas_ar_estudio ar ON ar.prsArEstudioId = pe.prsArEstudioId
                        JOIN cat_paises p ON p.paisId = pe.paisId
                        WHERE pe.personaId = ? AND pe.flgDelete = '0'
                    ", [$_SESSION["personaId"]]);
                    $n = 0;
                    if (empty($dataEmpleadoEstudios)){
                        echo '<tr><td  colspan="3">No se encontraron registros.</td></tr>';
                    } else {
                        foreach ($dataEmpleadoEstudios as $dataEmpleadoEstudios) {
                            $n += 1;
                            if($dataEmpleadoEstudios->nivelEstudio == "Técnico/Profesional" || $dataEmpleadoEstudios->nivelEstudio == "Universidad" || $dataEmpleadoEstudios->nivelEstudio == "Postgrado" || $dataEmpleadoEstudios->nivelEstudio == "Diplomado" || $dataEmpleadoEstudios->nivelEstudio == "Curso" || $dataEmpleadoEstudios->nivelEstudio == "Curso - INSAFORP") {
    
                                if($dataEmpleadoEstudios->nivelEstudio == "Diplomado") {
                                    $txtCarrera = "Nombre del diplomado";
                                } else if($dataEmpleadoEstudios->nivelEstudio == "Curso" || $dataEmpleadoEstudios->nivelEstudio == "Curso - INSAFORP") {
                                    $txtCarrera = "Nombre del curso";
                                } else {
                                    $txtCarrera = "Carrera";
                                }
                                $carreraNivel = '
                                    <br>
                                    <b><i class="fas fa-graduation-cap"></i> '.$txtCarrera.': </b> '.$dataEmpleadoEstudios->nombreCarrera.'<br>
                                    <b><i class="fas fa-chalkboard-teacher"></i> Área de estudio: </b> '.$dataEmpleadoEstudios->areaEstudio.'
                                ';
                            } else {
                                $carreraNivel = '';
                            }
    
                            $estudio = '
                                <b><i class="fas fa-user-graduate"></i> Lugar de estudio: </b> '.$dataEmpleadoEstudios->centroEstudio.'<br>
                                <b><i class="fas fa-chart-bar"></i> Nivel de estudio: </b> '.$dataEmpleadoEstudios->nivelEstudio.'<br>
                                <b><i class="fas fa-globe-americas"></i> País de estudio: </b>
                                <img src="../libraries/resources/images/'.$dataEmpleadoEstudios->iconBandera.'" alt="'.$dataEmpleadoEstudios->pais.'">
                                '.$dataEmpleadoEstudios->pais.'
                                '.$carreraNivel.'
                            ';  
    
                            if($dataEmpleadoEstudios->estadoEstudio == "Cursando" || $dataEmpleadoEstudios->estadoEstudio == "Incompleto") {
                                $estadoFinalizacion = $dataEmpleadoEstudios->estadoEstudio;
                            } else {
                                $estadoFinalizacion = $dataEmpleadoEstudios->mesFinalizacion . ' - ' . $dataEmpleadoEstudios->anioFinalizacion;
                            }
    
                            $periodo = '
                                <b><i class="fas fa-calendar"></i> Inicio: </b> '.$dataEmpleadoEstudios->mesInicio.' - '.$dataEmpleadoEstudios->anioInicio.'<br>
                                <b><i class="fas fa-calendar-check"></i> Finalización: </b> '.$estadoFinalizacion.'
                            ';
    
                            echo '<tr><td>' . $n . '</td>
                            <td>' . $estudio . '</td>
                            <td>' . $periodo . '</td>
                            </tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane fade" id="ntab-content-3" role="tabpanel" aria-labelledby="ntab-3">
        <div class="table-responsive">
            <table id="tblEmpleadoExpLaboral" class="table table-hover" style="width: 100%;">
                <thead>
                    <tr id="filterboxrow-explab">
                        <th>#</th>
                        <th>Experiencia</th>
                        <th>Periodo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                         $dataEmpleadoExpLaboral = $cloud->rows("
                            SELECT
                                exp.prsExpLaboralId AS prsExpLaboralId, 
                                exp.personaId AS personaId, 
                                exp.lugarTrabajo AS lugarTrabajo, 
                                exp.paisId AS paisId, 
                                p.pais AS pais,
                                p.iconBandera AS iconBandera,
                                exp.prsArExperienciaId prsArExperienciaId, 
                                ar.areaExperiencia AS areaExperiencia,
                                exp.cargoTrabajo AS cargoTrabajo, 
                                exp.numMesInicio AS numMesInicio, 
                                exp.mesInicio AS mesInicio, 
                                exp.anioInicio AS anioInicio, 
                                exp.numMesFinalizacion AS numMesFinalizacion, 
                                exp.mesFinalizacion AS mesFinalizacion, 
                                exp.anioFinalizacion AS anioFinalizacion,
                                exp.motivoRetiro AS motivoRetiro
                            FROM th_personas_exp_laboral exp
                            JOIN cat_personas_ar_experiencia ar ON ar.prsArExperienciaId = exp.prsArExperienciaId
                            JOIN cat_paises p ON p.paisId = exp.paisId
                            WHERE exp.personaId = ? AND exp.flgDelete = '0'
                        ", [$_SESSION["personaId"]]);

                        $n = 0;
                        if (empty($dataEmpleadoEstudios)){
                            echo '<tr><td  colspan="3">No se encontraron registros.</td></tr>';
                        } else {
                            foreach ($dataEmpleadoExpLaboral as $dataEmpleadoExpLaboral) {
                                $n += 1;

                                $expLaboral = '
                                    <b><i class="fas fa-building"></i> Lugar de trabajo: </b> '.$dataEmpleadoExpLaboral->lugarTrabajo.'<br>
                                    <b><i class="fas fa-briefcase"></i> Cargo desempeñado: </b> '.$dataEmpleadoExpLaboral->cargoTrabajo.'<br>
                                    <b><i class="fas fa-user-tie"></i> Área de trabajo: </b> '.$dataEmpleadoExpLaboral->areaExperiencia.'<br>
                                    <b><i class="fas fa-globe-americas"></i> País de trabajo: </b>
                                    <img src="../libraries/resources/images/'.$dataEmpleadoExpLaboral->iconBandera.'" alt="'.$dataEmpleadoExpLaboral->pais.'">
                                    '.$dataEmpleadoExpLaboral->pais.'
                                ';  

                                $numMesFinalizacion = $dataEmpleadoExpLaboral->numMesFinalizacion + 1;
                                $anioFinalizacion = ($numMesFinalizacion == 13) ? $dataEmpleadoExpLaboral->anioFinalizacion + 1 : $dataEmpleadoExpLaboral->anioFinalizacion;
                                $numMesFinalizacion = ($numMesFinalizacion == 13) ? 1 : $numMesFinalizacion;
                                $fechaInicio = strtotime($dataEmpleadoExpLaboral->anioInicio . "-" . $dataEmpleadoExpLaboral->numMesInicio . "-1");
                                $fechaFin = strtotime($anioFinalizacion . "-" . $numMesFinalizacion . "-1");

                                $periodo = '
                                    <b><i class="fas fa-calendar"></i> Inicio: </b> '.$dataEmpleadoExpLaboral->mesInicio.' - '.$dataEmpleadoExpLaboral->anioInicio.'<br>
                                    <b><i class="fas fa-calendar-times"></i> Finalización: </b> '.$dataEmpleadoExpLaboral->mesFinalizacion.' - '.$dataEmpleadoExpLaboral->anioFinalizacion.'<br>
                                    <b><i class="fas fa-user-clock"></i> Tiempo de experiencia: </b> '.diferenciaFechas($fechaInicio, $fechaFin).'<br>
                                    <b><i class="fas fa-edit"></i> Motivo de retiro: </b> '.$dataEmpleadoExpLaboral->motivoRetiro.'
                                ';


                                echo '<tr><td>' . $n . '</td>
                                <td>' . $expLaboral . '</td>
                                <td>' . $periodo . '</td>
                                </tr>';
                            }
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane fade" id="ntab-content-4" role="tabpanel" aria-labelledby="ntab-4">
        <div class="row">
            <div class="col-lg-3 mb-4">
                <div class="nav flex-column nav-tabs text-center" id="v-tabs-tab" role="tablist" aria-orientation="vertical">
                    <a class="nav-link active" id="v-tabs-1-tab" data-mdb-toggle="tab" href="#v-tabs-1" role="tab" aria-controls="v-tabs-1" aria-selected="true">Idiomas</a>
                    <a class="nav-link" id="v-tabs-2-tab" data-mdb-toggle="tab" href="#v-tabs-2" role="tab" aria-controls="v-tabs-2" aria-selected="false">Conocimientos Informáticos</a>
                    <a class="nav-link" id="v-tabs-3-tab" data-mdb-toggle="tab" href="#v-tabs-3" role="tab" aria-controls="v-tabs-3" aria-selected="false">Conocimientos/Habilidades</a>
                    <a class="nav-link" id="v-tabs-4-tab" data-mdb-toggle="tab" href="#v-tabs-4" role="tab" aria-controls="v-tabs-4" aria-selected="false">Herramientas/Equipos</a>
                </div>
            </div>
            <div class="col-lg-9 mb-4">
                <?php
                    $dataEmpleadoHabilidades = $cloud->rows("
                        SELECT
                            prsHabilidadId, 
                            personaId, 
                            tipoHabilidad, 
                            habilidadPersona, 
                            nivelHabilidad
                        FROM th_personas_habilidades
                        WHERE personaId = ? AND flgDelete = '0'
                    ", [$_SESSION["personaId"]]);
                    $idioma = "";
                    $informatica = "";
                    $equipo = "";
                    $habilidades = "";
                    $n = 0;
                    foreach ($dataEmpleadoHabilidades as $dataEmpleadoHabilidades) {
                        $n += 1;
                
                        $habilidad = '
                            <b>'.$dataEmpleadoHabilidades->tipoHabilidad.': </b> '.$dataEmpleadoHabilidades->habilidadPersona.'<br>
                            <b><i class="fas fa-chart-line"></i> Nivel: </b> '.$dataEmpleadoHabilidades->nivelHabilidad.'
                        ';

                        switch ($dataEmpleadoHabilidades->tipoHabilidad){
                            case "Idioma":
                                $idioma .= '<tr><td>' . $n . '</td>
                                <td>' . $habilidad . '</td>
                                </tr>';
                            break;
                            case "Informática":
                                $informatica .= '<tr><td>' . $n . '</td>
                                <td>' . $habilidad . '</td>
                                </tr>';
                            break;
                            case "Equipo":
                                $equipo .= '<tr><td>' . $n . '</td>
                                <td>' . $habilidad . '</td>
                                </tr>';
                            break;
                            default:
                            $habilidades .= '<tr><td>' . $n . '</td>
                                <td>' . $habilidad . '</td>
                                </tr>';
                        }
                    }          
                    if ($idioma == ''){
                        $idioma = '<tr><td colspan="2">No se encontraron registros.</td>';
                    }
                    if ($informatica == ''){
                        $informatica = '<tr><td colspan="2">No se encontraron registros.</td>';
                    }
                    if ($equipo == ''){
                        $equipo = '<tr><td colspan="2">No se encontraron registros.</td>';
                    }
                    if ($habilidades == ''){
                        $habilidades = '<tr><td colspan="2">No se encontraron registros.</td>';
                    }
                ?>
                <div class="tab-content" id="vtabs-mainContent">
                    <div class="tab-pane fade show active" id="v-tabs-1" role="tabpanel" aria-labelledby="v-tabs-1-tab">
                        <div class="row">
                            <div class="col-lg-8 mb-4">
                                <h5><b>Idiomas</b></h5>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="tblEmpleadoHabIdiomas" class="table table-hover" style="width: 100%;">
                                <thead>
                                    <tr id="filterboxrow-hab-idioma">
                                        <th>#</th>
                                        <th>Idioma</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        echo $idioma;
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="v-tabs-2" role="tabpanel" aria-labelledby="v-tabs-2-tab">
                        <div class="row">
                            <div class="col-lg-8 mb-4">
                                <h5><b>Conocimientos Informáticos</b></h5>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="tblEmpleadoHabInformatico" class="table table-hover" style="width: 100%;">
                                <thead>
                                    <tr id="filterboxrow-hab-informatico">
                                        <th>#</th>
                                        <th>Conocimiento Informático</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        echo $informatica;
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="v-tabs-3" role="tabpanel" aria-labelledby="v-tabs-3-tab">
                        <div class="row">
                            <div class="col-lg-8 mb-4">
                                <h5><b>Conocimientos/Habilidades</b></h5>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="tblEmpleadoHabHabilidad" class="table table-hover" style="width: 100%;">
                                <thead>
                                    <tr id="filterboxrow-hab-habilidad">
                                        <th>#</th>
                                        <th>Conocimiento/Habilidad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        echo $habilidades;
                                    ?>
                                </tbody>
                            </table>
                        </div> 
                    </div>
                    <div class="tab-pane fade" id="v-tabs-4" role="tabpanel" aria-labelledby="v-tabs-4-tab">
                        <div class="row">
                            <div class="col-lg-8 mb-4">
                                <h5><b>Herramientas/Equipos</b></h5>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="tblEmpleadoHabEquipo" class="table table-hover" style="width: 100%;">
                                <thead>
                                    <tr id="filterboxrow-hab-equipo">
                                        <th>#</th>
                                        <th>Herramienta/Equipo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        echo $equipo;
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="ntab-content-5" role="tabpanel" aria-labelledby="ntab-5">
        <?php
            $dataEmpleadoLicencias = $cloud->rows("
            SELECT
                prsLicenciaId, 
                personaId, 
                categoriaLicencia, 
                tipoLicencia, 
                numLicencia, 
                fechaExpiracionLicencia,
                descripcionLicencia
            FROM th_personas_licencias
            WHERE personaId = ? AND flgDelete = '0'
            ", [$_SESSION["personaId"]]);

            $conducir = "";
            $armas = "";
            $n = 0;
            foreach ($dataEmpleadoLicencias as $dataEmpleadoLicencias) {
                if ($dataEmpleadoLicencias->categoriaLicencia == "Conducir") {
                    $conducir .= '<tr><td>
                        <b><i class="fas fa-list-ul"></i> Tipo de licencia: </b> '.$dataEmpleadoLicencias->tipoLicencia.'<br>
                        <b><i class="fas fa-address-card"></i> Número de licencia: </b> '.$dataEmpleadoLicencias->numLicencia.' (Expiración: '.$dataEmpleadoLicencias->fechaExpiracionLicencia.')
                        <br><b><i class="fas fa-edit"></i> Descripción de licencia: </b>' . $dataEmpleadoLicencias->descripcionLicencia . '
                        </td></tr>';
                } else {
                    $armas .= '<tr><td>
                        <b><i class="fas fa-list-ul"></i> Tipo de licencia: </b> '.$dataEmpleadoLicencias->tipoLicencia.'<br>
                        <b><i class="fas fa-address-card"></i> Número de licencia: </b> '.$dataEmpleadoLicencias->numLicencia.' (Expiración: '.$dataEmpleadoLicencias->fechaExpiracionLicencia.')
                        <br><b><i class="fas fa-edit"></i> Descripción de licencia: </b> -
                        </td></tr>';
                }
                
            }
            if ($conducir == ""){
                $conducir = "<tr><td>No se encontraron registros.</td></tr>";
            }
            if ($armas == ""){
                $armas = "<tr><td>No se encontraron registros.</td></tr>";
            }
        ?>
        <div class="row">
            <div class="col-lg-3 mb-4">
                <div class="nav flex-column nav-tabs text-center" id="v-tabs-tab-lic" role="tablist" aria-orientation="vertical">
                    <a class="nav-link active" id="v-tabs-1-tab-lic" data-mdb-toggle="tab" href="#v-tabs-1-lic" role="tab" aria-controls="v-tabs-1-lic" aria-selected="true">Conducir</a>
                    <a class="nav-link" id="v-tabs-2-tab-lic" data-mdb-toggle="tab" href="#v-tabs-2-lic" role="tab" aria-controls="v-tabs-2-lic" aria-selected="false">Armas</a>
                </div>
            </div>
            <div class="col-lg-9 mb-4">
                <div class="tab-content" id="vtabs-mainContent">
                    <div class="tab-pane fade show active" id="v-tabs-1-lic" role="tabpanel" aria-labelledby="v-tabs-1-tab-lic">
                        <div class="row">
                            <div class="col-lg-8 mb-4">
                                <h5><b>Licencias de Conducir</b></h5>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="tblEmpleadoLicConducir" class="table table-hover" style="width: 100%;">
                                <thead>
                                    <tr id="filterboxrow-lic-conducir">
                                        <th>Licencia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php echo $conducir; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="v-tabs-2-lic" role="tabpanel" aria-labelledby="v-tabs-2-tab-lic">
                        <div class="row">
                            <div class="col-lg-8 mb-4">
                                <h5><b>Licencias de Armas</b></h5>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="tblEmpleadoLicArma" class="table table-hover" style="width: 100%;">
                                <thead>
                                    <tr id="filterboxrow-lic-arma">
                                        <th>Licencia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php echo $armas; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="ntab-content-6" role="tabpanel" aria-labelledby="ntab-6">
        <?php
            $dataEmpleadoEnfermedades = $cloud->rows("
                SELECT
                    pe.prsEnfermedadId AS prsEnfermedadId, 
                    pe.catPrsEnfermedadId AS catPrsEnfermedadId,
                    cpe.tipoEnfermedad AS tipoEnfermedad,
                    cpe.nombreEnfermedad AS nombreEnfermedad
                FROM th_personas_enfermedades pe
                JOIN cat_personas_enfermedades cpe ON cpe.catPrsEnfermedadId = pe.catPrsEnfermedadId
                WHERE pe.personaId = ? AND pe.flgDelete = '0'
            ", [$_SESSION["personaId"]]);
            $enfermedad = '';
            $n = 0;
            if (empty($dataEmpleadoEnfermedades)){
                $enfermedad .= '<tr><td colspan="2">No se encontraron registros.</td></tr>';
            } else {
                foreach ($dataEmpleadoEnfermedades as $dataEmpleadoEnfermedades) {
                    $n += 1;
                        
                    $enfermedad .= '<tr><td>'.$n.'</td><td>
                        <b><i class="fas fa-syringe"></i> '.$dataEmpleadoEnfermedades->tipoEnfermedad.': </b> '.$dataEmpleadoEnfermedades->nombreEnfermedad.'
                        </td></tr>';
    
                }
            }
        ?>
        <div class="table-responsive">
            <table id="tblEmpleadoEnfermedades" class="table table-hover" style="width: 100%;">
                <thead>
                    <tr id="filterboxrow-enfermedad">
                        <th>#</th>
                        <th>Enfermedad/Alergia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php echo $enfermedad; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane fade" id="ntab-content-7" role="tabpanel" aria-labelledby="ntab-7">
        <div class="row">
            <div class="col-md-12">
                <div class="gallery-container">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function getAdjunto(personaId) {
        asyncDoDataReturn(
            '../app/modulos/rrhh/content/divs/getAdjuntos',
            {
                personaId: personaId
            },
            function(data) {
                $(".gallery-container").html(data);
            }
        );  
    }

    getAdjunto(<?php echo $_SESSION["personaId"]; ?>);

    function modalContactosEmpleado(tableData) {
        // id, nombrePersona
        let arrayData = tableData.split("^");
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: `Contactos del empleado: ${arrayData[1]}`,
                modalForm: '../../../mi-perfil/content/modal-forms/contactosEmp',
                formData: tableData,
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }
    function modalRelacionEmpleado(tableData) {
        // id, nombrePersona
        let arrayData = tableData.split("^");
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: `Núcleo familiar del empleado: ${arrayData[1]}`,
                modalForm: '../../../mi-perfil/content/modal-forms/nucleoFamEmp',
                formData: tableData,
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }
</script>

<?php 
    function diferenciaFechas($fechaInicio,$fechaFin) {
        $diff = '';
        $diferencia = ($fechaFin - $fechaInicio)/60/60/24;
        if($diferencia == 0) {
            $diff = 'Ninguno';
        } else if($diferencia == 1) {
            $diff = '1 día';
        } else if($diferencia < 31) {
            if($diferencia < 7) {
                $diff = $diferencia . ' días';
            } else if($diferencia < 14) {
                $diff = '1 semana';
            } else if($diferencia < 21) {
                $diff = '2 semanas';
            } else {
                $diff = '3 semanas';
            }
        } else if($diferencia < 365) {
            if($diferencia < 62) {
                $diff = '1 mes';
            } else {
                $diff = round($diferencia / 31) . ' meses';
            }
        } else {
            if($diferencia < 730) {
                $diff = '1 año';
            } else {
                $diff = round($diferencia / 365) . ' años';
            }
        }

        return $diff;
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

        if($anios >= 0) { // La fecha de publicacion era mayor que la actual (contratación a futuro)
            return $antiguiedad;
        } else {
            return "-";
        }
    }
?>