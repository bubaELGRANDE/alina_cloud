<?php
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();

    $dataParametrizacion = $cloud->rows("
        SELECT
        	cp.lineaId AS lineaId,
     		CONCAT('(', l.abreviatura, ') ', l.linea) AS nombreLinea
        FROM conta_comision_porcentaje_lineas cp
        JOIN temp_cat_lineas l ON l.lineaId = cp.lineaId
        WHERE cp.flgDelete = ?
        GROUP BY cp.lineaId
    ", ['0']);
    $n = 0;
    foreach ($dataParametrizacion as $dataParametrizacion) {
    	$n += 1;

    	$dataCondiciones = $cloud->rows("
    		SELECT
    			comisionPorcentajeLineaId, 
    			rangoPorcentajeInicio, 
    			rangoPorcentajeFin,
    			porcentajePago
    		FROM conta_comision_porcentaje_lineas
    		WHERE lineaId = ? AND flgDelete = '0'
    		ORDER BY rangoPorcentajeInicio
    	",[$dataParametrizacion->lineaId]);

		$condiciones = '
			<div class="accordion accordion-flush" id="accordionCondiciones'.$n.'">
			    <div class="accordion-item">
			        <div class="accordion-header" id="flush-item-'.$n.'">
			            <button class="accordion-button collapsed" type="button" data-mdb-toggle="collapse" data-mdb-target="#flush-collapse-'.$n.'" aria-expanded="false" aria-controls="flush-collapse-'.$n.'">
			                <b><i class="fas fa-clipboard"></i> Condiciones</b>
			            </button>
			        </div>
			        <div id="flush-collapse-'.$n.'" class="accordion-collapse collapse" aria-labelledby="flush-item-'.$n.'" data-mdb-parent="#accordionCondiciones'.$n.'">
			            <div class="accordion-body">
			            	<table class="table table-hover table-bordered">
			            		<thead>
									<th>#</th>	  
									<th>Condición</th>    
									<th>Porcentaje de pago</th>       		
			            		</thead>
			            		<tbody>
		';

		$x = 0;
    	foreach($dataCondiciones as $dataCondiciones) {
    		$x += 1;
    		$condiciones .= '
			    <tr>
			    	<td>'.$x.'</td>
			    	<td>'.$dataCondiciones->rangoPorcentajeInicio.' % - '.$dataCondiciones->rangoPorcentajeFin.' % de descuento</td>
			    	<td>'.$dataCondiciones->porcentajePago.' % sobre venta</td>
			    </tr>
			';
    	}

    	$condiciones .= '
    							</tbody>
    						</table>
						</div>
			        </div>
    			</div>
    		</div>
    	';

    	$parametrizacion = '
    		<b><i class="fas fa-tag"></i> Línea: </b> '.$dataParametrizacion->nombreLinea.'<br>
    		'.$condiciones.'
    	';

	    $acciones = '
			<button type="button" class="btn btn-primary btn-sm ttip" onclick="modalParametrizacion(`editar^'.$dataParametrizacion->lineaId.'`);">
				<i class="fas fa-pencil-alt"></i>
                <span class="ttiptext">Editar</span>
			</button>
	    ';

	    $output['data'][] = array(
	        $n, // es #, se dibuja solo en el JS de datatable
	        $parametrizacion,
	        $acciones
	    );
	} // foreach

    if($n > 0) {
        echo json_encode($output);
    } else {
        // No retornar nada para evitar error "null"
        echo json_encode(array('data'=>'')); 
    }
?>