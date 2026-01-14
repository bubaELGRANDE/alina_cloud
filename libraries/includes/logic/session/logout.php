<?php
@session_start();
include("../mgc/datos94.php");

if (isset($_SESSION["usuarioId"])) { // Para evitar back-button después de cerrar sesión
    if (isset($_REQUEST["flg"])) {
        if ($_REQUEST['flg'] == "baja-emp") { // Session finalizada desde header
            // Se le dió de baja al empleado y su sesión seguía abierta
            $fhActual = date("Y-m-d H:i:s");
            $cloud->writeBitacora('movInterfaces', "(" . $fhActual . ") Sesión finalizada automáticamente por cambio de estado (RRHH).");

            $update = [
                'fhLogout' => $fhActual,
            ];
            $where = ['loginUsuarioId' => $_SESSION["loginUsuarioId"]];
            $cloud->update('bit_login_usuarios', $update, $where);

            $update = [
                'enLinea' => 0,
            ];
            $where = ['usuarioId' => $_SESSION["usuarioId"]];
            $cloud->update('conf_usuarios', $update, $where);

            unset($_SESSION['usuarioId']);
            session_destroy();
            session_start();

            $_SESSION["inactividad"] = 0;

            header("Location: /alina-cloud/");
        } else { // Inactividad
            $fhActual = date("Y-m-d H:i:s");
            $cloud->writeBitacora('movInterfaces', "(" . $fhActual . ") Sesión finalizada por inactividad.");

            $update = [
                'fhLogout' => $fhActual,
            ];
            $where = ['loginUsuarioId' => $_SESSION["loginUsuarioId"]];
            $cloud->update('bit_login_usuarios', $update, $where);

            $update = [
                'enLinea' => 0,
            ];
            $where = ['usuarioId' => $_SESSION["usuarioId"]];
            $cloud->update('conf_usuarios', $update, $where);

            unset($_SESSION['usuarioId']);
            session_destroy();
            session_start();

            $_SESSION["inactividad"] = 1;

            header("Location: /alina-cloud/cierre-inactividad");
        }
    } else {
        $fhActual = date("Y-m-d H:i:s");
        $cloud->writeBitacora('movInterfaces', "(" . $fhActual . ") Cerró sesión.");

        $update = [
            'fhLogout' => $fhActual,
        ];
        $where = ['loginUsuarioId' => $_SESSION["loginUsuarioId"]];
        $cloud->update('bit_login_usuarios', $update, $where);

        $update = [
            'enLinea' => 0,
        ];
        $where = ['usuarioId' => $_SESSION["usuarioId"]];
        $cloud->update('conf_usuarios', $update, $where);

        unset($_SESSION["usuarioId"]);
        session_destroy();

        header("Location: /alina-cloud/");
    }
} else {
    header("Location: /alina-cloud/");
}
