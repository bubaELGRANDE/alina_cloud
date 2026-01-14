<?php 
    date_default_timezone_set('America/El_Salvador');
    $url = "https://bitpay.com/api/rates";
    $json = json_decode(file_get_contents($url));
    foreach($json as $obj) {
        if($obj->code == "USD") {
            echo '<div class="card-header"><h5 class="card-title">Precio actual:<br>1 BTC = $ '.number_format($obj->rate, 2, '.', ',').'</h5></div>';
            echo '<div class="card-body">
            ';
            $precioActual = $obj->rate;
            $arrayEquivalentes = array(1, 5, 10, 20, 50, 100);
            echo  "<table class='table table-sm table-bordered table-hover'>
                    <thead>
                        <tr class='text-center'>
                            <th><b>USD</b></td>
                            <th><b>SAT</b></td>
                            <th><b>BTC</b></td>
                        </tr>
                    </thead>
                    <tbody>";
            for ($i=0; $i < count($arrayEquivalentes); $i++) { 
                $equivalenteBTC = number_format($arrayEquivalentes[$i] / $obj->rate, 10, '.', ',');
                $equivalenteSAT = number_format($equivalenteBTC / 0.00000001, 0, '.', ',');
                echo  "
                            <tr>
                                <td class='text-end'>$ ".number_format($arrayEquivalentes[$i], 2, '.', ',')."</td>
                                <td class='text-end'>$equivalenteSAT</td>
                                <td class='text-end'>$equivalenteBTC</td>
                            </tr>
                    ";            
                }
            
            echo '</tbody></table>
                </div>
            <div class="card-footer text-muted">
                <small><i class="fas fa-check-circle"></i> Ultima actualización: '.date('d-m-Y H:i:s').'</small>
            </div>
            ';
        } else {
            // Es el equivalente en otra moneda
        }
    }
?>