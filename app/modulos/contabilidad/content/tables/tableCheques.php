<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $n = 0;

    $dataCheques = $cloud->rows("
        SELECT
            chequeId, 
            numCheque,
            fechaCheque,
            aFavorDe,
            conceptoCheque,
            montoCheque,
            fechaRetiroCheque,
            personaRetiroCheque,
            fhAnular,
            obsAnular
        FROM conta_cheques 
        WHERE flgDelete = ? AND estadoCheque = ?
        ",[0, $_POST['estadoCheque']]);

    foreach($dataCheques as $dataCheques){
        $n += 1;
        $datosCheque = '
            <b><i class="fas fa-list-ol"></i> N° cheque: </b>'.($dataCheques->numCheque).'<br>
            <b><i class="fas fa-calendar-alt"></i> Fecha cheque: </b>'.(date("d/m/Y", strtotime($dataCheques->fechaCheque))).'<br>
            <b><i class="fas fa-user-tie"></i> A favor de: </b>'.($dataCheques->aFavorDe).'<br>
        ';

        $concepto = '
            <b><i class="fas fa-edit"></i> Concepto: </b>'.($dataCheques->conceptoCheque).'<br>
        ';

        $monto = ' 
            <b><i class="fas fa-dollar-sign"></i> Monto : </b> $ '.($dataCheques->montoCheque).'<br>
        ';

        $jsonAnular = [
            "numCheque"       => $dataCheques->numCheque,
            "montoCheque"     => $dataCheques->montoCheque,
            "chequeId"        => $dataCheques->chequeId
        ];

        $funcionAnular = htmlspecialchars(json_encode($jsonAnular));

        if($_POST["estadoCheque"] == "Emitido") {
            $acciones = '
                <button type="button" class="btn btn-primary btn-sm ttip" onclick="">
                    <i class="fas fa-folder-open"></i>
                    <span class="ttiptext">Ver documento</span>    
                </button>

                <button type="button" class="btn btn-primary btn-sm ttip" onclick="">
                    <i class="fas fa-hand-holding-usd"></i>
                    <span class="ttiptext">Entrega cheque</span>
                </button>

                <button type="button" class="btn btn-danger btn-sm ttip" onclick="modalAnularCheque('.$funcionAnular.')">
                    <i class="fas fa-ban"></i>
                    <span class="ttiptext">Anular cheque</span>
                </button>
            ';
        } else {
            $acciones = '
                <button type="button" class="btn btn-primary btn-sm ttip" onclick="">
                    <i class="fas fa-folder-open"></i>
                    <span class="ttiptext">Ver documento</span>    
                </button>
            ';
        }

        if($_POST["estadoCheque"] == "Entregado" || $_POST["estadoCheque"] == "Anulado") {
            if($_POST["estadoCheque"] == "Entregado") {
                $columnaExtra = '
                <b><i class="fas fa-calendar-alt"></i> Fecha: </b>'.(date("d/m/Y", strtotime($dataCheques->fechaRetiroCheque))).'<br>
                <b><i class="fas fa-user-alt"></i> Persona: </b>'.($dataCheques->personaRetiroCheque).'<br>
            ';
            } else {
                // Anulado
                $columnaExtra = '
                <b><i class="fas fa-calendar-alt"></i> Fecha: </b>'.(date("d/m/Y", strtotime($dataCheques->fhAnular))).'<br>
                <b><i class="fas fa-user-edit"></i> Motivo: </b>'.($dataCheques->obsAnular).'<br>
            ';
            }

            $output['data'][] = array(
                $n, // es #, se dibuja solo en el JS de datatable
                $datosCheque,
                $concepto,
                $monto,
                $columnaExtra,
                $acciones
            );
        } else {
            $output['data'][] = array(
                $n, // es #, se dibuja solo en el JS de datatable
                $datosCheque,
                $concepto,
                $monto,
                $acciones
            );
        }
    }


    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>''));
    }

?>