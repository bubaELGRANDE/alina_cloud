<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$datMarca = $cloud->rows("
        SELECT
            marcaId,nombreMarca,abreviaturaMarca,urlLogoMarca,estadoMarca
        FROM cat_inventario_marcas
        WHERE flgDelete = 0 ORDER BY nombreMarca
    ");

$n = 0;
foreach ($datMarca as $dMarca) {
    $n += 1;
    $estado = ($dMarca->estadoMarca == "Activa") ? '<span class="text-success fw-bold">Activa</span>' : '<span class="text-danger fw-bold">Inactiva</span>';
    $udn = '<b><i class="fas fa-trademark"></i> Marca: </b>' . $dMarca->nombreMarca . '
    <br><i clas="fas fa-thumbtack"></i> <b>Abreviatura: </b> ' . $dMarca->abreviaturaMarca . '
    <br><i class="fas fa-check"></i> <b>Estado: </b> ' . $estado;

    if ($dMarca->urlLogoMarca != "") {
        $logo = '<img src="../libraries/resources/images/' . $dMarca->urlLogoMarca . '" class ="img-fluid" alt="' . $dMarca->nombreMarca . '">';
    } else {
        $logo = '';
    }


    if ($dMarca->estadoMarca == "Inactiva") {
        $acciones = '<button type="button" class="btn btn-info btn-sm ttip" onClick="habilitarMarca(`' . $dMarca->marcaId . '^' . $dMarca->nombreMarca . '`);">
                        <i class="fas fa-check"></i>
                        <span class="ttiptext">Habilitar UDN</span>
                    </button>';
    } else {
        $acciones = '<button type="button" class="btn btn-primary btn-sm ttip" onClick="modalMarca(`update^' . $dMarca->marcaId . '`);">
                        <i class="fas fa-pen"></i>
                        <span class="ttiptext">Editar</span>
                    </button>
                    <button type="button" class="btn btn-danger btn-sm ttip" onClick="eliminarMarca(`' . $dMarca->marcaId . '`);">
                        <i class="fas fa-trash-alt"></i>
                        <span class="ttiptext">Eliminar</span>
                    </button>';
    }

    $output['data'][] = array(
        $n, // es #, se dibuja solo en el JS de datatable
        $udn,
        $logo,
        $acciones
    );
} // foreach

if ($n > 0) {
    echo json_encode($output);
} else {
    // No retornar nada para evitar error "null"
    echo json_encode(array('data' => ''));
}