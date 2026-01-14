<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $dataEmpleado = $cloud->row("
        SELECT
            per.docIdentidad AS docIdentidad,
            per.numIdentidad AS numIdentidad, 
            per.fechaExpiracionIdentidad AS fechaExpiracionIdentidad,
            expedimuni.paisDepartamentoId AS paisDepartamentoIdExpedicion,
            expedidepto.departamentoPais AS departamentoExpedicion,
            per.paisMunicipioIdExpedicion AS paisMunicipioIdExpedicion,
            expedimuni.municipioPais AS municipioExpedicion,
            per.fechaExpedicionIdentidad AS fechaExpedicionIdentidad,
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
        LEFT JOIN cat_paises_municipios expedimuni ON expedimuni.paisMunicipioId = per.paisMunicipioIdExpedicion
        LEFT JOIN cat_paises_departamentos expedidepto ON expedidepto.paisDepartamentoId = expedimuni.paisDepartamentoId
        WHERE per.personaId = ?
    ", [$_POST["personaId"]]);

    // Este if es porque estos campos son nuevos y puede dar error
    if($dataEmpleado->fechaExpedicionIdentidad == "") {
        $lugarFechaExpedicion = "-";
    } else {
        $fechaExpedicionIdentidad = date("d/m/Y", strtotime($dataEmpleado->fechaExpedicionIdentidad));
        $lugarFechaExpedicion = $dataEmpleado->departamentoExpedicion . ", " . $dataEmpleado->municipioExpedicion . " (".$fechaExpedicionIdentidad.")";
    }

    if($dataEmpleado->estadoPersona == "Inactivo") {
        $disabledInactivo = "disabled";
        $urlVariables = "inactivos";
    } else {
        $disabledInactivo = "";
        $urlVariables = "activos";
    }   
?>
<h2>
    Perfil de Empleado: <?php echo $_POST["nombreCompleto"]; ?>
</h2>
<hr>
<div class="row mb-4">
    <div class="col-md-3">
        <button type="button" id="btnPageModulos" class="btn btn-secondary btn-block ttip">
            <i class="fas fa-chevron-circle-left"></i>
            Empleados
            <span class="ttiptext">Volver a Gestión de Empleados</span>
        </button>
    </div>
</div>
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
                    WHERE flgDelete = '0' AND descripcionPrsAdjunto = 'Actual' AND personaID =?"
                    , [$_POST["personaId"]]) ;

                    if ($dataPersImg->fotoPerfil == 1){
                        $dataUserImg = $cloud->row("
                        SELECT 
                        urlPrsAdjunto
                        FROM th_personas_adjuntos 
                        WHERE personaId = ? AND descripcionPrsAdjunto = 'Actual' AND flgDelete = '0'
                    ", [$_POST["personaId"]]);

                        $fotoPerfil = $dataUserImg->urlPrsAdjunto;
                    } else {
                        $fotoPerfil = "mi-perfil/user-default.jpg";
                    }
                ?>
                <div class="user-pic" style="background-image: url('../libraries/resources/images/<?php echo $fotoPerfil; ?>');">
                    <?php 
                        if($disabledInactivo == "disabled") {
                            echo '<button class="button-rounded" style="cursor: not-allowed;" disabled><i class="fas fa-camera "></i></button>';
                        } else {
                            echo '<button class="button-rounded" onclick="modalAdjunto(`fotoPerfil`);"><i class="fas fa-camera "></i></button>';
                        }
                    ?>
                </div>
                <div class="info-emp">
                    <?php 
                        if($disabledInactivo == "disabled") {
                            echo '<a role="button" class="link-secondary" style="cursor: not-allowed;"><i class="fas fa-pen"></i> Editar perfil</a>';
                        } else {
                            echo '<a role="button" class="link-primary" onclick="modalEmpleado();"><i class="fas fa-pen"></i> Editar perfil</a>';
                        }
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
                        <button type="button" class="btn btn-primary btn-sm" onclick="modalContactosEmpleado(`<?php echo $_POST['personaId'] . '^' . $_POST['nombreCompleto']; ?>`);">
                            <i class="fas fa-phone-square-alt"></i> Contactos
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="modalRelacionEmpleado(`<?php echo $_POST['personaId'] . '^' . $_POST['nombreCompleto']; ?>`);">
                            <i class="fas fa-users"></i> Núcleo familiar
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="row">
                    <div class="col-12">
                        <h3 class="display-5"><?php echo $_POST["nombreCompleto"]; ?></h3>
                        <hr>
                        <h4>Datos personales</h4>
                    </div>
                    <div class="row">
                        <div class="col-md-7">
                            <p>
                                <b></b>
                                <b><i class="fas fa-globe-americas"></i> País de origen:</b> 
                                <img src="../libraries/resources/images/<?php echo $dataEmpleado->iconBandera; ?>" alt="<?php echo $dataEmpleado->nacionalidad; ?>">
                                <?php echo $dataEmpleado->nacionalidad; ?>
                                <br>
                                <b><i class="fas fa-address-card"></i> <?php echo $dataEmpleado->docIdentidad; ?>:</b> <?php echo $dataEmpleado->numIdentidad . " (Expiración: " . str_replace("-", "/", $dataEmpleado->fechaExpiracionIdentidad) . ")"; ?><br>
                                <b><i class="fas fa-building"></i> Expedición: </b> <?php echo $lugarFechaExpedicion; ?><br>
                                <b><i class="fas fa-address-card"></i> NIT:</b> <?php echo (is_null($dataEmpleado->nit) || $dataEmpleado->nit == "" ? '-' : $dataEmpleado->nit); ?><br>
                                <b><i class="fas fa-money-check-alt"></i> NUP: </b> <?php echo $dataEmpleado->nup . " (" . $dataEmpleado->abreviaturaOrganizacionAFP . ")"; ?><br>
                                <b><i class="fas fa-ambulance"></i> ISSS: </b> <?php echo $dataEmpleado->numISSS; ?><br>
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
                                <b><i class="fas fa-syringe"></i> Tipo de sangre: </b> <?php echo (is_null($dataEmpleado->tipoSangre) || $dataEmpleado->tipoSangre == "" ? '-' : $dataEmpleado->tipoSangre); ?><br>
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
        <div class="row">
            <div class="col-lg-3 offset-lg-9 mb-4">
                <button type="button" class="btn btn-primary btn-block" onclick="modalEstudio('nuevo');" <?php echo $disabledInactivo; ?>>
                    <i class="fas fa-plus-circle"></i> Nuevo Estudio
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table id="tblEmpleadoEstudios" class="table table-hover" style="width: 100%;">
                <thead>
                    <tr id="filterboxrow-estudio">
                        <th>#</th>
                        <th>Estudio</th>
                        <th>Periodo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane fade" id="ntab-content-3" role="tabpanel" aria-labelledby="ntab-3">
        <div class="row">
            <div class="col-lg-3 offset-lg-9 mb-4">
                <button type="button" class="btn btn-primary btn-block" onclick="modalExpLaboral('nuevo');" <?php echo $disabledInactivo; ?>>
                    <i class="fas fa-plus-circle"></i> Nueva Exp. Laboral
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table id="tblEmpleadoExpLaboral" class="table table-hover" style="width: 100%;">
                <thead>
                    <tr id="filterboxrow-explab">
                        <th>#</th>
                        <th>Experiencia</th>
                        <th>Periodo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
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
                <div class="tab-content" id="vtabs-mainContent">
                    <div class="tab-pane fade show active" id="v-tabs-1" role="tabpanel" aria-labelledby="v-tabs-1-tab">
                        <div class="row">
                            <div class="col-lg-8 mb-4">
                                <h5><b>Idiomas</b></h5>
                            </div>
                            <div class="col-lg-4 mb-4">
                                <button type="button" class="btn btn-primary btn-block ttip" onclick="modalHabilidad('nuevo','idioma');" <?php echo $disabledInactivo; ?>>
                                    <i class="fas fa-plus-circle"></i> Idioma
                                    <span class="ttiptext">Nuevo Idioma</span>
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="tblEmpleadoHabIdiomas" class="table table-hover" style="width: 100%;">
                                <thead>
                                    <tr id="filterboxrow-hab-idioma">
                                        <th>#</th>
                                        <th>Idioma</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="v-tabs-2" role="tabpanel" aria-labelledby="v-tabs-2-tab">
                        <div class="row">
                            <div class="col-lg-8 mb-4">
                                <h5><b>Conocimientos Informáticos</b></h5>
                            </div>
                            <div class="col-lg-4 mb-4">
                                <button type="button" class="btn btn-primary btn-block ttip" onclick="modalHabilidad('nuevo','informática');" <?php echo $disabledInactivo; ?>>
                                    <i class="fas fa-plus-circle"></i> C. Informático
                                    <span class="ttiptext">Nuevo Conocimiento Informático</span>
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="tblEmpleadoHabInformatico" class="table table-hover" style="width: 100%;">
                                <thead>
                                    <tr id="filterboxrow-hab-informatico">
                                        <th>#</th>
                                        <th>Conocimiento Informático</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="v-tabs-3" role="tabpanel" aria-labelledby="v-tabs-3-tab">
                        <div class="row">
                            <div class="col-lg-8 mb-4">
                                <h5><b>Conocimientos/Habilidades</b></h5>
                            </div>
                            <div class="col-lg-4 mb-4">
                                <button type="button" class="btn btn-primary btn-block ttip" onclick="modalHabilidad('nuevo','habilidades');" <?php echo $disabledInactivo; ?>>
                                    <i class="fas fa-plus-circle"></i> Con./Habilidad
                                    <span class="ttiptext">Nuevo Conocimiento/Habilidad</span>
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="tblEmpleadoHabHabilidad" class="table table-hover" style="width: 100%;">
                                <thead>
                                    <tr id="filterboxrow-hab-habilidad">
                                        <th>#</th>
                                        <th>Conocimiento/Habilidad</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div> 
                    </div>
                    <div class="tab-pane fade" id="v-tabs-4" role="tabpanel" aria-labelledby="v-tabs-4-tab">
                        <div class="row">
                            <div class="col-lg-8 mb-4">
                                <h5><b>Herramientas/Equipos</b></h5>
                            </div>
                            <div class="col-lg-4 mb-4">
                                <button type="button" class="btn btn-primary btn-block ttip" onclick="modalHabilidad('nuevo','equipo');" <?php echo $disabledInactivo; ?>>
                                    <i class="fas fa-plus-circle"></i> Herr./Equipo
                                    <span class="ttiptext">Nueva Herramienta/Equipo</span>
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="tblEmpleadoHabEquipo" class="table table-hover" style="width: 100%;">
                                <thead>
                                    <tr id="filterboxrow-hab-equipo">
                                        <th>#</th>
                                        <th>Herramienta/Equipo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="ntab-content-5" role="tabpanel" aria-labelledby="ntab-5">
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
                            <div class="col-lg-4 mb-4">
                                <button type="button" class="btn btn-primary btn-block ttip" onclick="modalLicencia('nuevo','conducir');" <?php echo $disabledInactivo; ?>>
                                    <i class="fas fa-plus-circle"></i> Licencia
                                    <span class="ttiptext">Nueva Licencia</span>
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="tblEmpleadoLicConducir" class="table table-hover" style="width: 100%;">
                                <thead>
                                    <tr id="filterboxrow-lic-conducir">
                                        <th>#</th>
                                        <th>Licencia</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="v-tabs-2-lic" role="tabpanel" aria-labelledby="v-tabs-2-tab-lic">
                        <div class="row">
                            <div class="col-lg-8 mb-4">
                                <h5><b>Licencias de Armas</b></h5>
                            </div>
                            <div class="col-lg-4 mb-4">
                                <button type="button" class="btn btn-primary btn-block ttip" onclick="modalLicencia('nuevo','arma');" <?php echo $disabledInactivo; ?>>
                                    <i class="fas fa-plus-circle"></i> Licencia
                                    <span class="ttiptext">Nueva Licencia</span>
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="tblEmpleadoLicArma" class="table table-hover" style="width: 100%;">
                                <thead>
                                    <tr id="filterboxrow-lic-arma">
                                        <th>#</th>
                                        <th>Licencia</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="ntab-content-6" role="tabpanel" aria-labelledby="ntab-6">
        <div class="row">
            <div class="col-lg-4 offset-lg-8 mb-4">
                <button type="button" class="btn btn-primary btn-block" onclick="modalEnfermedad('nuevo');" <?php echo $disabledInactivo; ?>>
                    <i class="fas fa-plus-circle"></i> Nueva Enfermedad/Alergia
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table id="tblEmpleadoEnfermedades" class="table table-hover" style="width: 100%;">
                <thead>
                    <tr id="filterboxrow-enfermedad">
                        <th>#</th>
                        <th>Enfermedad/Alergia</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane fade" id="ntab-content-7" role="tabpanel" aria-labelledby="ntab-7">
        <div class="text-end mb-4">
            <button type="button" class="btn btn-primary ttip" onclick="modalAdjunto();" <?php echo $disabledInactivo; ?>>
                <i class="fas fa-paperclip"></i> Adjuntar archivo
            </button>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="gallery-container">
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function modalContactosEmpleado(tableData) {
        // id, nombrePersona
        let arrayData = tableData.split("^");
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: `Contactos del empleado: ${arrayData[1]}`,
                modalForm: 'contactosEmpleado',
                formData: tableData,
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }

    function verAdjuntoModal(data) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'xl',
                modalTitle: `Ver archivo`,
                modalForm: 'adjunto',
                formData: data,
                /* buttonAcceptShow: false,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save', */
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }
    function modalAdjunto(tipoAdjunto) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: `Adjuntar archivo`,
                modalForm: 'empleadoAdjunto',
                formData: '<?php echo $_POST["personaId"]; ?>^'+tipoAdjunto+'^<?php echo $_POST["nombreCompleto"]; ?>',
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }

    function modalEmpleado() {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'xl',
                modalTitle: `Editar Empleado`,
                modalForm: 'empleado',
                formData: 'editar^<?php echo $_POST["personaId"]; ?>',
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }
    function modalEstudio(tipo, tableData = "N/A") {
        let formData = ""; modalDev = "-1";
        if(tipo == "editar") {
            formData = 'editar^'+tableData + '^<?php echo $_POST["nombreCompleto"]; ?>';
            modalDev = "-1";
        } else {
            formData = 'nuevo^<?php echo $_POST["personaId"] . "^" . $_POST["nombreCompleto"]; ?>';
            modalDev = "-1";
        }

        loadModal(
            "modal-container",
            {
                modalDev: modalDev,
                modalSize: 'lg',
                modalTitle: `Estudio`,
                modalForm: 'empleadoEstudio',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function eliminarEstudio(tableData) {
        <?php 
            if($dataEmpleado->estadoPersona == "Inactivo") {
        ?>
                mensaje(
                    "Aviso:",
                    'No es posible eliminar la información de un empleado inactivo.',
                    "warning"
                );
        <?php 
            } else {
        ?>
                mensaje_confirmacion(
                    `¿Está seguro que desea eliminar este estudio?`, 
                    `Se eliminará del perfil de empleado.`, 
                    `warning`, 
                    function(param) {
                        asyncDoDataReturn(
                            '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                            {
                                typeOperation: `delete`,
                                operation: `empleado-estudio`,
                                id: tableData,
                                nombreCompleto: '<?php echo $_POST["nombreCompleto"]; ?>'
                            },
                            function(data) {
                                if(data == "success") {
                                    mensaje_do_aceptar(`Operación completada:`, `Estudio eliminado con éxito`, `success`, function() {
                                        $(`#tblEmpleadoEstudios`).DataTable().ajax.reload(null, false);
                                    });
                                } else {
                                    mensaje(
                                        "Aviso:",
                                        data,
                                        "warning"
                                    );
                                }
                            }
                        );
                    },
                    `Eliminar`,
                    `Cancelar`
                );
        <?php 
            }
        ?>
    }

    function delAdjunto(idAdjunto, idPersona) {
        <?php 
            if($dataEmpleado->estadoPersona == "Inactivo") {
        ?>
                mensaje(
                    "Aviso:",
                    'No es posible eliminar la información de un empleado inactivo.',
                    "warning"
                );
        <?php 
            } else {
        ?>
                let title = "Aviso:"
                let msj = "¿Esta seguro que quiere eliminar este registro?";
                let btnAccepTxt = "Confirmar";
                let msjDone = "Se eliminó correctamente el registro.";
                
                mensaje_confirmacion(
                    title, msj, `warning`, function(param) {
                        asyncDoDataReturn(
                            '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                            {
                                typeOperation: 'delete',
                                operation: 'delAdjunto',
                                idAdjunto: idAdjunto,
                                idPersona: idPersona
                            },
                            function(data) {
                                if(data == "success") {
                                    mensaje_do_aceptar(`Operación completada:`, msjDone, `success`, function() {

                                //$('#tblSucursal').DataTable().ajax.reload(null, false);
                                //$("#modal-container").modal("hide"); // para aprobar y rechazar se usa modal
                                getAdjunto(<?php echo $_POST["personaId"]; ?>);
                                });
                                } else {
                                    mensaje(
                                        "Aviso:",
                                        data,
                                        "warning"
                                    );
                                }
                            }
                        );
                    },
                    btnAccepTxt,
                    `Cancelar`
                );
        <?php 
            }
        ?>
    }

    function modalExpLaboral(tipo, tableData = "N/A") {
        let formData = ""; modalDev = "-1";
        if(tipo == "editar") {
            formData = 'editar^'+tableData + '^<?php echo $_POST["nombreCompleto"]; ?>';
            modalDev = "-1";
        } else {
            formData = 'nuevo^<?php echo $_POST["personaId"] . "^" . $_POST["nombreCompleto"]; ?>';
            modalDev = "-1";
        }
        loadModal(
            "modal-container",
            {
                modalDev: -1,
                modalSize: 'lg',
                modalTitle: `Experiencia Laboral`,
                modalForm: 'empleadoExpLaboral',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function eliminarExpLaboral(tableData) {
        <?php 
            if($dataEmpleado->estadoPersona == "Inactivo") {
        ?>
                mensaje(
                    "Aviso:",
                    'No es posible eliminar la información de un empleado inactivo.',
                    "warning"
                );
        <?php 
            } else {
        ?>
                mensaje_confirmacion(
                    `¿Está seguro que desea eliminar esta experiencia laboral?`, 
                    `Se eliminará del perfil de empleado.`, 
                    `warning`, 
                    function(param) {
                        asyncDoDataReturn(
                            '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                            {
                                typeOperation: `delete`,
                                operation: `empleado-experiencia-laboral`,
                                id: tableData,
                                nombreCompleto: '<?php echo $_POST["nombreCompleto"]; ?>'
                            },
                            function(data) {
                                if(data == "success") {
                                    mensaje_do_aceptar(`Operación completada:`, `Experiencia laboral eliminada con éxito`, `success`, function() {
                                        $(`#tblEmpleadoExpLaboral`).DataTable().ajax.reload(null, false);
                                    });
                                } else {
                                    mensaje(
                                        "Aviso:",
                                        data,
                                        "warning"
                                    );
                                }
                            }
                        );
                    },
                    `Eliminar`,
                    `Cancelar`
                );
        <?php 
            }
        ?>
    }

    function modalEnfermedad(tipo, tableData = "N/A") {
        let formData = ""; modalDev = "-1";
        if(tipo == "editar") {
            formData = 'editar^'+tableData + '^<?php echo $_POST["nombreCompleto"]; ?>';
            modalDev = "-1";
        } else {
            formData = 'nuevo^<?php echo $_POST["personaId"] . "^" . $_POST["nombreCompleto"]; ?>';
            modalDev = "-1";
        }
        loadModal(
            "modal-container",
            {
                modalDev: -1,
                modalSize: 'md',
                modalTitle: `Enfermedad/Alergia`,
                modalForm: 'empleadoEnfermedad',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function eliminarEnfermedad(tableData) {
        <?php 
            if($dataEmpleado->estadoPersona == "Inactivo") {
        ?>
                mensaje(
                    "Aviso:",
                    'No es posible eliminar la información de un empleado inactivo.',
                    "warning"
                );
        <?php 
            } else {
        ?>
                mensaje_confirmacion(
                    `¿Está seguro que desea eliminar esta enfermedad/alergia?`, 
                    `Se eliminará del perfil de empleado.`, 
                    `warning`, 
                    function(param) {
                        asyncDoDataReturn(
                            '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                            {
                                typeOperation: `delete`,
                                operation: `empleado-enfermedad`,
                                id: tableData,
                                nombreCompleto: '<?php echo $_POST["nombreCompleto"]; ?>'
                            },
                            function(data) {
                                if(data == "success") {
                                    mensaje_do_aceptar(`Operación completada:`, `Enfermedad/alergia eliminada con éxito`, `success`, function() {
                                        $(`#tblEmpleadoEnfermedades`).DataTable().ajax.reload(null, false);
                                    });
                                } else {
                                    mensaje(
                                        "Aviso:",
                                        data,
                                        "warning"
                                    );
                                }
                            }
                        );
                    },
                    `Eliminar`,
                    `Cancelar`
                );
        <?php 
            }
        ?>
    }

    function modalHabilidad(tipo, tipoHabilidad, tableData = "N/A") {
        let formData = ""; modalDev = "-1";
        if(tipo == "editar") {
            formData = 'editar^'+tableData + '^<?php echo $_POST["nombreCompleto"]; ?>^'+tipoHabilidad;
            modalDev = "-1";
        } else {
            formData = 'nuevo^<?php echo $_POST["personaId"] . "^" . $_POST["nombreCompleto"]; ?>^'+tipoHabilidad;
            modalDev = "-1";
        }

        loadModal(
            "modal-container",
            {
                modalDev: -1,
                modalSize: 'md',
                modalTitle: `Habilidad`,
                modalForm: 'empleadoHabilidad',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function eliminarHabilidad(tableData) {
        <?php 
            if($dataEmpleado->estadoPersona == "Inactivo") {
        ?>
                mensaje(
                    "Aviso:",
                    'No es posible eliminar la información de un empleado inactivo.',
                    "warning"
                );
        <?php 
            } else {
        ?>
                let arrayTblData = tableData.split("^");
                let tblHabilidad = "";

                if(arrayTblData[1] == "idioma") {
                    tblHabilidad = "tblEmpleadoHabIdiomas";
                    msjDelete = "Idioma";
                } else if(arrayTblData[1] == "informática") {
                    tblHabilidad = "tblEmpleadoHabInformatico";
                    msjDelete = "Conocimiento de informática";
                } else if(arrayTblData[1] == "equipo") {
                    tblHabilidad = "tblEmpleadoHabEquipo";
                    msjDelete = "Herramienta/Equipo";
                } else {
                    tblHabilidad = "tblEmpleadoHabHabilidad";
                    msjDelete = "Conocimiento/Habilidad";
                }

                mensaje_confirmacion(
                    `¿Está seguro que desea eliminar este ${msjDelete}?`, 
                    `Se eliminará del perfil de empleado.`, 
                    `warning`, 
                    function(param) {
                        asyncDoDataReturn(
                            '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                            {
                                typeOperation: `delete`,
                                operation: `empleado-habilidad`,
                                id: arrayTblData[0],
                                tipoHabilidad: arrayTblData[1],
                                nombreCompleto: '<?php echo $_POST["nombreCompleto"]; ?>'
                            },
                            function(data) {
                                if(data == "success") {
                                    mensaje_do_aceptar(`Operación completada:`, `${msjDelete} eliminada con éxito`, `success`, function() {
                                        $(`#${tblHabilidad}`).DataTable().ajax.reload(null, false);
                                    });
                                } else {
                                    mensaje(
                                        "Aviso:",
                                        data,
                                        "warning"
                                    );
                                }
                            }
                        );
                    },
                    `Eliminar`,
                    `Cancelar`
                );
        <?php 
            }
        ?>
    }

    function getAdjunto(personaId) {
        asyncDoDataReturn(
            '<?php echo $_SESSION["currentRoute"]; ?>content/divs/getAdjuntos',
            {
                personaId: personaId
            },
            function(data) {
                $(".gallery-container").html(data);
            }
        );  
    }
    
    function modalLicencia(tipo, categoriaLicencia, tableData = "N/A") {
        let formData = ""; modalDev = "-1";
        if(tipo == "editar") {
            formData = 'editar^'+tableData + '^<?php echo $_POST["nombreCompleto"]; ?>^'+categoriaLicencia;
            modalDev = "-1";
        } else {
            formData = 'nuevo^<?php echo $_POST["personaId"] . "^" . $_POST["nombreCompleto"]; ?>^'+categoriaLicencia;
            modalDev = "-1";
        }

        loadModal(
            "modal-container",
            {
                modalDev: -1,
                modalSize: 'md',
                modalTitle: `Licencia`,
                modalForm: 'empleadoLicencia',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function eliminarLicencia(tableData) {
        <?php 
            if($dataEmpleado->estadoPersona == "Inactivo") {
        ?>
                mensaje(
                    "Aviso:",
                    'No es posible eliminar la información de un empleado inactivo.',
                    "warning"
                );
        <?php 
            } else {
        ?>
                let arrayTblData = tableData.split("^");
                let tblLicencia = "";

                if(arrayTblData[1] == "conducir") {
                    tblLicencia = "tblEmpleadoLicConducir";
                    msjDelete = "Conducir";
                } else { // Arma
                    tblLicencia = "tblEmpleadoLicArma";
                    msjDelete = "Arma";
                }

                mensaje_confirmacion(
                    `¿Está seguro que desea eliminar esta Licencia de ${msjDelete}?`, 
                    `Se eliminará del perfil de empleado.`, 
                    `warning`, 
                    function(param) {
                        asyncDoDataReturn(
                            '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                            {
                                typeOperation: `delete`,
                                operation: `empleado-licencia`,
                                id: arrayTblData[0],
                                categoriaLicencia: arrayTblData[1],
                                nombreCompleto: '<?php echo $_POST["nombreCompleto"]; ?>'
                            },
                            function(data) {
                                if(data == "success") {
                                    mensaje_do_aceptar(`Operación completada:`, `Licencia de ${msjDelete} eliminada con éxito`, `success`, function() {
                                        $(`#${tblLicencia}`).DataTable().ajax.reload(null, false);
                                    });
                                } else {
                                    mensaje(
                                        "Aviso:",
                                        data,
                                        "warning"
                                    );
                                }
                            }
                        );
                    },
                    `Eliminar`,
                    `Cancelar`
                );
        <?php 
            }
        ?>
    }

    $(document).ready(function() {
        getAdjunto(<?php echo $_POST["personaId"]; ?>);

        $("#btnPageModulos").click(function() {
            // id 13 de tbl menus, no se puede usar changePage porque es exclusiva para "page-"
            asyncPage(13, 'submenu', '<?php echo $urlVariables; ?>');
        });

        // Tab: Estudios
        $('#tblEmpleadoEstudios thead tr#filterboxrow-estudio th').each(function(index) {
            if(index==1 || index == 2) {
                var title = $('#tblEmpleadoEstudios thead tr#filterboxrow-estudio th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}estudio" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}estudio">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblEmpleadoEstudios.column($(this).index()).search($(`#input${$(this).index()}estudio`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });
        
        let tblEstudio = $('#tblEmpleadoEstudios').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableEmpleadoEstudios",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "id": '<?php echo $_POST["personaId"]; ?>'
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                null,
                null,
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2, 3] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

        // Tab: Experiencia Laboral
        $('#tblEmpleadoExpLaboral thead tr#filterboxrow-explab th').each(function(index) {
            if(index==1 || index == 2) {
                var title = $('#tblEmpleadoExpLaboral thead tr#filterboxrow-explab th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}explab" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}explab">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblEmpleadoExpLaboral.column($(this).index()).search($(`#input${$(this).index()}explab`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });
        
        let tblEmpleadoExpLaboral = $('#tblEmpleadoExpLaboral').DataTable({
            "dom": 'lrtip',
            "autoWidth": false,
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableEmpleadoExpLaboral",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "id": '<?php echo $_POST["personaId"]; ?>'
                }
            },
            "columns": [
                null,
                null,
                null,
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2, 3] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

        // Tab: Habilidades
        // Idioma
        $('#tblEmpleadoHabIdiomas thead tr#filterboxrow-hab-idioma th').each(function(index) {
            if(index==1) {
                var title = $('#tblEmpleadoHabIdiomas thead tr#filterboxrow-hab-idioma th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}hab-idioma" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}hab-idioma">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblEmpleadoHabIdiomas.column($(this).index()).search($(`#input${$(this).index()}hab-idioma`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });
        
        let tblEmpleadoHabIdiomas = $('#tblEmpleadoHabIdiomas').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableEmpleadoHabilidades",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "id": '<?php echo $_POST["personaId"]; ?>',
                    "tipoHabilidad": 'Idioma'
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                null,
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

        // Informática
        $('#tblEmpleadoHabInformatico thead tr#filterboxrow-hab-informatico th').each(function(index) {
            if(index==1) {
                var title = $('#tblEmpleadoHabInformatico thead tr#filterboxrow-hab-informatico th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}hab-informatico" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}hab-informatico">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblEmpleadoHabInformatico.column($(this).index()).search($(`#input${$(this).index()}hab-informatico`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });
        
        let tblEmpleadoHabInformatico = $('#tblEmpleadoHabInformatico').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableEmpleadoHabilidades",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "id": '<?php echo $_POST["personaId"]; ?>',
                    "tipoHabilidad": 'Informática'
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                null,
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

        // Habilidad
        $('#tblEmpleadoHabHabilidad thead tr#filterboxrow-hab-habilidad th').each(function(index) {
            if(index==1) {
                var title = $('#tblEmpleadoHabHabilidad thead tr#filterboxrow-hab-habilidad th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}hab-habilidad" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}hab-habilidad">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblEmpleadoHabHabilidad.column($(this).index()).search($(`#input${$(this).index()}hab-habilidad`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });
        
        let tblEmpleadoHabHabilidad = $('#tblEmpleadoHabHabilidad').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableEmpleadoHabilidades",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "id": '<?php echo $_POST["personaId"]; ?>',
                    "tipoHabilidad": 'Habilidad'
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                null,
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
        // Herramienta-Equipo
        $('#tblEmpleadoHabEquipo thead tr#filterboxrow-hab-equipo th').each(function(index) {
            if(index==1) {
                var title = $('#tblEmpleadoHabEquipo thead tr#filterboxrow-hab-equipo th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}hab-equipo" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}hab-equipo">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblEmpleadoHabEquipo.column($(this).index()).search($(`#input${$(this).index()}hab-equipo`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });
        
        let tblEmpleadoHabEquipo = $('#tblEmpleadoHabEquipo').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableEmpleadoHabilidades",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "id": '<?php echo $_POST["personaId"]; ?>',
                    "tipoHabilidad": 'Equipo'
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                null,
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

        // Tab: Licencias
        // Licencia de Conducir
        $('#tblEmpleadoLicConducir thead tr#filterboxrow-lic-conducir th').each(function(index) {
            if(index==1) {
                var title = $('#tblEmpleadoLicConducir thead tr#filterboxrow-lic-conducir th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}lic-conducir" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}lic-conducir">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblEmpleadoLicConducir.column($(this).index()).search($(`#input${$(this).index()}lic-conducir`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });
        
        let tblEmpleadoLicConducir = $('#tblEmpleadoLicConducir').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableEmpleadoLicencias",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "id": '<?php echo $_POST["personaId"]; ?>',
                    "categoriaLicencia": 'Conducir'
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                null,
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

        // Licencia de armas
        $('#tblEmpleadoLicArma thead tr#filterboxrow-lic-arma th').each(function(index) {
            if(index==1) {
                var title = $('#tblEmpleadoLicArma thead tr#filterboxrow-lic-arma th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}lic-arma" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}lic-arma">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblEmpleadoLicArma.column($(this).index()).search($(`#input${$(this).index()}lic-arma`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });
        
        let tblEmpleadoLicArma = $('#tblEmpleadoLicArma').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableEmpleadoLicencias",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "id": '<?php echo $_POST["personaId"]; ?>',
                    "categoriaLicencia": 'Arma'
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                null,
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

        // Tab: Enfermedades/Alergias
        $('#tblEmpleadoEnfermedades thead tr#filterboxrow-enfermedad th').each(function(index) {
            if(index==1 || index == 2) {
                var title = $('#tblEmpleadoEnfermedades thead tr#filterboxrow-enfermedad th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}enfermedad" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}enfermedad">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblEmpleadoEnfermedades.column($(this).index()).search($(`#input${$(this).index()}enfermedad`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });
        
        let tblEmpleadoEnfermedades = $('#tblEmpleadoEnfermedades').DataTable({
            "dom": 'lrtip',
            "autoWidth": false,
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableEmpleadoEnfermedades",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "id": '<?php echo $_POST["personaId"]; ?>'
                }
            },
            "columns": [
                null,
                {"width": "70%"},
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>
<?php 
    function diferenciaFechasYMD($fechaPublicacion,$fechaActual) {
        if($fechaActual == "") {
            return "-";
        } else {        
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
    }
?>