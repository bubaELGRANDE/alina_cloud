<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$dataBit = $cloud->rows("SELECT 
  m.bitMayorizacionId,
  CONCAT(p.nombre1, ' ', p.apellido1) AS nombre,
  m.descripcionMayorizacion, 
  CONCAT(ci.mesNombre, ' ', ci.anio) AS fechaMayorizacionIncio, 
  CONCAT(cf.mesNombre, ' ', cf.anio) AS fechaMayorizacionFinal
FROM bit_mayorizacion m
JOIN th_personas p ON m.personaId = p.personaId
JOIN conta_partidas_contables_periodos ci ON ci.partidaContaPeriodoId = m.fechaMayorizacionIncio
JOIN conta_partidas_contables_periodos cf ON cf.partidaContaPeriodoId = m.fechaMayorizacionFinal
WHERE m.flgDelete = ?", [0]);
$n = 0;

foreach ($dataBit as $row) {
    $n++;

    $fecha = '<b>Inicio: </b> ' . $row->fechaMayorizacionIncio . '<br><b>Final: </b> ' . $row->fechaMayorizacionFinal . '<br>';
    $desc = '<b>Descripción: </b>' . $row->descripcionMayorizacion . '<br><b>Generada por : </b> ' . $row->nombre;
    $acciones = '';


    $output['data'][] = array(
        $n, //? es #, se dibuja solo en el JS de datatable
        $fecha,
        $desc,
        $acciones
    );
}


if ($n > 0) {
    echo json_encode($output);
} else {
    //! No retornar nada para evitar error "null"
    echo json_encode(array('data' => ''));
}

?>