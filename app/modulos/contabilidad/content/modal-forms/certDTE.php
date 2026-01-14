<?php
	@session_start();
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");

    $datosCert = $cloud->row('SELECT facturaCertificacionId, numeroControl, codigoGeneracion, estadoCertificacion, descripcionMsg
                            FROM fel_factura_certificacion 
                            WHERE facturaId = ? AND flgDelete = 0
                            ORDER BY facturaCertificacionId DESC', [$_POST['facturaId']]);

?>

<div class="row">
    <div class="col-md-4">
        <b>Número de control: </b><br><?php echo $datosCert->numeroControl; ?>
    </div>
    <div class="col-md-4">
        <b>Código generación: </b><br><?php echo $datosCert->codigoGeneracion; ?>
    </div>
    <div class="col-md-4">
        <b>Estado: </b><br><span class="badge rounded-pill bg-success"><?php echo $datosCert->estadoCertificacion; ?></span>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <b>Descripción: </b><br><?php echo $datosCert->descripcionMsg; ?>
    </div>
</div>

<script>
    $(document).ready(function() {
        
    });
</script>