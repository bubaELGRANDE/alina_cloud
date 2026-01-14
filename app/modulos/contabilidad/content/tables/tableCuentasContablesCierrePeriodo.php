<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();


$getPeriodosContables = $cloud->rows("
    SELECT partidaContaPeriodoId,concat(mesNombre,' ',anio) as periodo,fechaCierrePeriodo,estadoPeriodoPartidas
    FROM desarrollo_cloud.conta_partidas_contables_periodos
    WHERE flgDelete = ?
    ORDER BY anio ASC, mes ASC", [0]);

$n = 0; //? Contador total

foreach ($getPeriodosContables as $periodosContable) {
    $n++;

    //TODOS: Funcion para volver abrir el periodo
    $jsonAbrir = array(
        "typeOperation" => "update",
        "operation" => "abrir-periodo-contable",
        "id" => $periodosContable->partidaContaPeriodoId
    );
    $funcionAbrir = htmlspecialchars(json_encode($jsonAbrir));

    //TODOS: Funcion para eliminar el periodo
    $jsonDelete = array(
        "typeOperation" => "delete",
        "operation" => "periodo-contable",
        "id" => $periodosContable->partidaContaPeriodoId
    );
    $funcionDel = htmlspecialchars(json_encode($jsonDelete));

    //TODOS: Funcion para volver cerrar el periodo
    $jsonCerrar = array(
        "typeOperation" => "update",
        "operation" => "cerrar-periodo-contable",
        "id" => $periodosContable->partidaContaPeriodoId
    );
    $funcionCerrar = htmlspecialchars(json_encode($jsonCerrar));

    $acciones = "";
    //! Validacion de permisos
    if ($periodosContable->estadoPeriodoPartidas === 'Activo') {
        if (
            in_array(249, $_SESSION["arrayPermisos"]) ||
            in_array(309, $_SESSION["arrayPermisos"]) ||
            in_array(348, $_SESSION["arrayPermisos"]) ||
            in_array(349, $_SESSION["arrayPermisos"])
        ) {
            $acciones = '
		<button type="button" class="btn btn-info btn-sm ttip" onclick="cerrarPeriodo(' . $funcionCerrar . ')">
			<i class="fas fa-lock"></i>
			<span class="ttiptext">Cerrar período</span>
		</button>';

            if (
                in_array(249, $_SESSION["arrayPermisos"]) ||
                in_array(309, $_SESSION["arrayPermisos"]) ||
                in_array(348, $_SESSION["arrayPermisos"]) ||
                in_array(349, $_SESSION["arrayPermisos"])
            ) {

                /*$acciones .= '<button type="button" class="btn btn-danger btn-sm ttip" onclick="delPeriodo(' . $funcionDel . ');">
				<i class="fas fa-trash-alt"></i> 
				<span class="ttiptext">Eliminar Período</span>
			</button>';*/
            }
        }

    } else {
        if (
            in_array(249, $_SESSION["arrayPermisos"]) ||
            in_array(309, $_SESSION["arrayPermisos"]) ||
            in_array(348, $_SESSION["arrayPermisos"]) ||
            in_array(349, $_SESSION["arrayPermisos"])
        ) {
            $acciones = '
		<button type="button" class="btn btn-secondary btn-sm ttip" onclick="abrirPeriodo(' . $funcionAbrir . ')">
			<i class="fas fa-lock-open"></i>
			<span class="ttiptext">Abrir período</span>
		</button>';
        }
    }




    $output['data'][] = array(
        $n, //? es #, se dibuja solo en el JS de datatable
        $periodosContable->periodo,
        $periodosContable->estadoPeriodoPartidas,
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