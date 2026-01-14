<?php
@session_start();

// Evitar que los "deprecated" y "notice" rompan las cabeceras
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
require_once("../../../../../libraries/packages/php/vendor/Spout/Autoloader/autoload.php");

// Usa las clases necesarias
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;

// Nombre del archivo

$nombre_archivo = "plantillaProductos-" . date("YmdHis") . ".xlsx";

// Cabeceras de descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
header('Cache-Control: max-age=0');

// Crear un nuevo escritor XLSX
$writer = WriterEntityFactory::createXLSXWriter();
$writer->openToBrowser($nombre_archivo);

// Definir encabezados de la tabla
$headers = [
    'SKU',
    'NOMBRE',
    'CATEGORIA',
    'MARCA',
    'PUREZA',
    'PESO',
    'ANCHO',
    'LARGO',
    'DIAMETRO',
    'DESCRIPCION',
    'CANTIDAD',
    'UBICACION',
    'OBSERVACIONES'
];

// Escribir los encabezados en la primera fila
$headerRow = WriterEntityFactory::createRowFromArray($headers);
$writer->addRow($headerRow);

// Cerrar el archivo (forzar descarga)
$writer->close();
exit;
?>