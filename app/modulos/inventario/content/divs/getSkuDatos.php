<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$inventarioCategoriaPrincipalId = $_POST['categoria'];
$marcaId = $_POST['marca'];

$last = $cloud->row("SELECT max(productoId) AS ultimo FROM prod_productos 
WHERE marcaId = ? AND inventarioCategoriaPrincipalId = ?", [$marcaId, $inventarioCategoriaPrincipalId]);

$categoriaA = $cloud->row("SELECT abreviaturaCategoria FROM cat_inventario_categorias
WHERE inventarioCategoriaId = ?", [$inventarioCategoriaPrincipalId]);

$marcaA = $cloud->row("SELECT abreviaturaMarca FROM cat_inventario_marcas
WHERE marcaId = ?", [$marcaId]);

$ultimoNum = $last && $last->ultimo ? (int) $last->ultimo : 0;
$siguiente = str_pad($ultimoNum + 1, 4, "0", STR_PAD_LEFT);

echo json_encode(array(
    "id" => $siguiente,
    "marca" => $marcaA->abreviaturaMarca ?? 'GN',
    "categoria" => $categoriaA->abreviaturaCategoria ?? 'GEN'
));
?>