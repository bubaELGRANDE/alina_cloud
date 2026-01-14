<?php

function getMarcaIdByName($nombreMarca, $cloud)
{
    $dataUDM = $cloud->row("SELECT marcaId FROM cat_inventario_marcas
    WHERE flgDelete = 0 AND nombreMarca = '$nombreMarca'");
    return $dataUDM ? $dataUDM->marcaId : 7; // Retorna 7 (General) si no se encuentra la marca
}

function getCategoriaIdByName($nombreCategoria, $cloud)
{
    $dataUDM = $cloud->row("SELECT inventarioCategoriaId FROM cat_inventario_categorias
    WHERE flgDelete = 0 AND flgPrincipal = 1 AND nombreCategoria = '$nombreCategoria'");
    return $dataUDM ? $dataUDM->inventarioCategoriaId : 0; // Retorna 7 (General) si no se encuentra la marca
}

function getPrecioOroActual($cloud)
{
    $dataUDM = $cloud->row("SELECT precioOro FROM inv_precios_oro
    WHERE flgDelete = 0 ORDER BY fechaRegistro DESC LIMIT 1");
    return $dataUDM ? $dataUDM->precioOro : 0; // Retorna 0 si no se encuentra el precio
}

function httpGet($url, $apiKey)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-API-KEY: ' . $apiKey]);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // seguir redirecciones 301/302

    $output = curl_exec($ch);

    if ($output === false) {
        // En desarrollo puedes ver el error así:
        // echo 'Error cURL: ' . curl_error($ch);
        curl_close($ch);
        return null;
    }

    curl_close($ch);
    return $output;
}
?>