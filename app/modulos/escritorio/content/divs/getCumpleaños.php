<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();
    // PENDIENTE: nombre3, apellido3 (aCasada - reemplazar por apellido2 si lo tiene)
    $L = new DateTime(); 
    $dataEmpleados = $cloud->rows("
        SELECT
        personaId, 
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
        DATE_FORMAT(fechaNacimiento, '%d de %M') as fechaNac
        FROM th_personas
        WHERE prsTipoId = ? AND flgDelete = ? AND estadoPersona = ?
        AND WEEKOFYEAR(fechaNacimiento) = WEEKOFYEAR(NOW())
        AND personaId <> 145
        ORDER BY fechaNac ASC
    ", [1, 0, 'Activo']);
    $n = 0;
    if(empty($dataEmpleados)){ 
        echo '
                <div class="row align-items-center">
                    <div class="col-md-3 border-end text-center">
                        <h2><i class="fas fa-frown"></i></h2>
                    </div>
                    <div class="col-md-9">
                        No hay cumpleañeros este mes.
                    </div>
                </div>
            ';
    }else{
        $cumpleaneros = '';
        $cumplesPasados = '';
        foreach ($dataEmpleados as $dataEmpleados) {
            
            $n += 1;
            $arrayFechaNacimiento = explode("-", $dataEmpleados->fechaNacimiento);
            $birthday = diferenciaFechas(strtotime(date("Y-m-d")), strtotime(date("Y") . $arrayFechaNacimiento[1] . $arrayFechaNacimiento[2]));
    
            $calcularEdad = date_diff(date_create($dataEmpleados->fechaNacimiento), date_create(date("Y-m-d")));

            $checkCumplesPasados = explode(" ", $birthday);

            $dias = array("Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado");
            $diaSem = $dias[date("w", strtotime(date("Y") . $arrayFechaNacimiento[1] . $arrayFechaNacimiento[2]))];

            if ($checkCumplesPasados[0] == "Hace" || $checkCumplesPasados[0] == "Ayer"){
                $cumplesPasados .= '
                    <div class="row align-items-center mb-2">
                        <div class="col-md-3 border-end text-center">
                        <small>'. $diaSem.'</small> <br> <b class="fs-4">'.date("d", strtotime($dataEmpleados->fechaNacimiento)) .'</b>
                        </div>
                        <div class="col-md-9">
                            <b><i class="fas fa-user"></i> ' . $dataEmpleados->nombreCompleto . '</b><br>
                            <b><i class="fas fa-birthday-cake"></i> Cumpleaños: </b> '.$birthday.'<br>
                        </div>
                    </div>
                ';

            } else {
                if ($checkCumplesPasados[0] == "¡Hoy"){
                    $esHoy = "cumpleHoy";
                } else {
                    $esHoy = "";
                }

                $cumpleaneros .= '
                    <div class="row align-items-center mb-2 '.$esHoy.'">
                        <div class="col-md-3 border-end text-center">
                            <small>'. $diaSem .'</small> <br> <b class="fs-4">'.date("d", strtotime($dataEmpleados->fechaNacimiento)) .'</b>
                        </div>
                        <div class="col-md-9">
                            <b><i class="fas fa-user"></i> ' . $dataEmpleados->nombreCompleto . '</b><br>
                            <b><i class="fas fa-birthday-cake"></i> Cumpleaños: </b> '.$birthday.'<br>
                        </div>
                    </div>
                ';
            }
                    
        }
        /* echo '<div class="alert alert-info mb-2" role="alert">'.$cumpleaneros.'</div>';
        echo '<h5>Cumpleaños anteriores</h5><div class="alert alert-secondary mb-2" role="alert">'.$cumplesPasados.'</div>'; */
        ?>
        <!-- Tabs navs -->
<ul class="nav nav-tabs nav-fill mb-3" id="ex1" role="tablist">
  <li class="nav-item" role="presentation">
    <a class="nav-link active" id="ex2-tab-1" data-mdb-toggle="tab" href="#ex2-tabs-1" role="tab" aria-controls="ex2-tabs-1" aria-selected="true">Proximos</a>
  </li>
  <li class="nav-item" role="presentation">
    <a class="nav-link" id="ex2-tab-2" data-mdb-toggle="tab" href="#ex2-tabs-2" role="tab" aria-controls="ex2-tabs-2" aria-selected="false">Anteriores</a>
  </li>
</ul>

<div class="tab-content" id="ex2-content">
    <div class="tab-pane fade show active" id="ex2-tabs-1" role="tabpanel" aria-labelledby="ex2-tab-1">
        <div class="cumple container-fluid">
            <?php echo $cumpleaneros; ?>
        </div>
    </div>
    <div class="tab-pane fade" id="ex2-tabs-2" role="tabpanel" aria-labelledby="ex2-tab-2">
        <div class="cumpleAnteriores container-fluid">
            <?php if (empty($cumplesPasados)){
                echo '
                    <div class="row align-items-center">
                        <div class="col-md-3 border-end text-center">
                            <h2><i class="fas fa-calendar-alt"></i></h2>
                        </div>
                        <div class="col-md-9">
                            Aún no hay cumpleaños pasados.
                        </div>
                    </div>
                ';
            } else {
                echo $cumplesPasados; 
            }
            ?>
        </div>
    </div>
</div>

<?php
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