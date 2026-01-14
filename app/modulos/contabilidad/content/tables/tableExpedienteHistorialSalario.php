<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    /*
        POST:
        prsExpedienteId
    */

    $dataExpedienteSalarios = $cloud->rows("
        SELECT
            expedienteSalarioId, 
            prsExpedienteId, 
            tipoSalario, 
            fechaInicioVigencia, 
            salario, 
            descripcionSalario, 
            estadoSalario
        FROM th_expediente_salarios
        WHERE prsExpedienteId = ? AND flgDelete = '0'
        ORDER BY expedienteSalarioId DESC
    ", [$_POST["prsExpedienteId"]]);
    $n = 0;
    foreach ($dataExpedienteSalarios as $dataExpedienteSalarios) {
        $n += 1;

        switch($dataExpedienteSalarios->tipoSalario) {
            case "Aumento":
                $tipoSalario = '<span class="text-success fw-bold">Aumento <i class="fas fa-sort-up"></i></span>';
            break;

            case "Reducción":
                $tipoSalario = '<span class="text-danger fw-bold">Reducción <i class="fas fa-sort-down"></i></span>';
            break;

            default:
                $tipoSalario = '<span class="fw-bold">Inicial</span>';
            break;
        }

        switch($dataExpedienteSalarios->estadoSalario) {
            case "Activo":
                $estadoSalario = '<span class="text-success fw-bold">Activo</span>';
            break;

            case "Pendiente":
                $estadoSalario = '<span class="text-warning fw-bold">Pendiente (Aún no se ha alcanzado la fecha de inicio de vigencia de este salario)</span>';
            break;

            default:
                $estadoSalario = '<span class="text-danger fw-bold">Inactivo</span>';
            break;
        }

        $salario = '
            <b>Tipo de salario: </b> '.$tipoSalario.'<br>
            <b>Fecha de inicio de vigencia: </b> '.date("d/m/Y", strtotime($dataExpedienteSalarios->fechaInicioVigencia)).'<br>
            <b>Salario: </b> $ '.$dataExpedienteSalarios->salario.'<br>
            <b>Descripción: </b> '.$dataExpedienteSalarios->descripcionSalario.'<br>
            <b>Estado: </b> '.$estadoSalario.'
        ';

        $output['data'][] = array(
            $n, // es #, se dibuja solo en el JS de datatable
            $salario
        );
    } // foreach

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>