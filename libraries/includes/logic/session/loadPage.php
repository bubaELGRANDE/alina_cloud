<?php 
	@session_start();
	require_once("../mgc/datos94.php");

	$fhActual = date("Y-m-d H:i:s");

    if(isset($_SESSION["inactividad"])) {
        if(isset($_SESSION["usuarioId"])) {
            if($_SESSION["flgPassword"] == "1" && basename($_SERVER['PHP_SELF']) != "index.php") {
                header("Location: /portal/app/"); // redirect a cloud, cambiar nombre en server de alina-cloud a cloud
            } else {
                // Mantener sesión
                // Permisos
                $dataPermisosUsuario = $cloud->rows("
                    SELECT
                        ps.menuPermisoId
                    FROM conf_permisos_usuario ps
                    JOIN conf_menus_permisos mp ON mp.menuPermisoId = ps.menuPermisoId
                    WHERE ps.usuarioId = ? AND ps.flgDelete = '0' AND mp.flgDelete = '0'
                ", [$_SESSION["usuarioId"]]);

                $_SESSION["arrayPermisos"] = array();

                foreach ($dataPermisosUsuario as $dataPermisosUsuario) {
                    $_SESSION["arrayPermisos"][] = $dataPermisosUsuario->menuPermisoId;
                }

                // Verificar si la persona continua activa (en caso que se dé de baja y tenga su sesión abierta)
                $dataEstadoPersona = $cloud->row("
                    SELECT
                        estadoPersona
                    FROM th_personas
                    WHERE personaId = ?
                ", [$_SESSION['personaId']]);

                // Verificar si su usuario sigue activo (en caso que se suspenda su acceso desde admin. usuarios)
                $dataEstadoUsuario = $cloud->row("
                	SELECT
                		flgMensaje,
                		estadoUsuario
                	FROM conf_usuarios
                	WHERE usuarioId = ?
                ", [$_SESSION['usuarioId']]);

                if($dataEstadoPersona->estadoPersona == "Activo" && $dataEstadoUsuario->estadoUsuario == "Activo") {
                    // Mantener su sesión
					$modulosRoute = "../../../../app/modulos";
					$_SESSION["loginStart"] = 1;

					if(!isset($_SESSION["currentPage"])) {
						$_SESSION["currentPage"] = 1;
						$_SESSION["currentToken"] = "principal";
					} else {
					}

					// Validar F5
					$pageId = ($_POST["token"] == "start-app") ? $_SESSION["currentPage"] : $_POST["page"];
					$token = ($_POST["token"] == "start-app") ? $_SESSION["currentToken"] : $_POST["token"];

					$flg404 = 0; $flgModulo = 0; $whereGetPage = ""; $moduloId = 0;
					if($token == "principal" || $token == "institucional") {
						$moduloId = ($token == "institucional") ? 2 : 1;
						$whereGetPage = "WHERE sub.moduloId = '$moduloId' AND sub.menuId = ?";
					} else if($token == "modulo") { // No se debe de poder, debe existir menú siempre por los permisos
						$flgModulo = 1;
					} else if($token == "submenu" || $token == "sub-submenu") {
						$whereGetPage = "WHERE sub.menuId = ?";
					} else {
						$flg404 = 1;
					}

					if($flg404 == 0 && $flgModulo == 0) {
						$existePage = $cloud->count("
							SELECT
								md.moduloId AS moduloId,
								md.modulo AS modulo,
								md.urlModulo AS urlModulo,
								sub.menuId AS menuId,
								sub.menu AS menu,
								sub.menuSuperior AS menuSuperior,
								sub.urlMenu AS urlMenu,
								sub.estadoMenu AS estadoMenu
							FROM conf_menus sub
							JOIN conf_modulos md ON md.moduloId = sub.moduloId
							$whereGetPage			
						",[$pageId]);

						if($existePage > 0) {
							$_SESSION["currentToken"] = $token;
							$_SESSION["currentPage"] = $pageId;
							$dataGetPage = $cloud->row("
								SELECT
									md.moduloId AS moduloId,
									md.modulo AS modulo,
									md.urlModulo AS urlModulo,
									sub.menuId AS menuId,
									sub.menu AS menu,
									sub.menuSuperior AS menuSuperior,
									sub.urlMenu AS urlMenu,
									sub.estadoMenu AS estadoMenu
								FROM conf_menus sub
								JOIN conf_modulos md ON md.moduloId = sub.moduloId
								$whereGetPage
							",[$pageId]);

							$moduloId = $dataGetPage->moduloId;

							$route = $modulosRoute . "/" . $dataGetPage->urlModulo . "/";
							$page = $dataGetPage->urlMenu . ".php"; // content/views/escritorio

							$_SESSION["currentRoute"] = "modulos/" . $dataGetPage->urlModulo . "/";

							$file = $route . "main.php";

							// SESSION para la class active en caso de F5
							$_SESSION["pageActive"] = ($moduloId == 0) ? "" : "m" . $moduloId . "-" . $pageId . "^" . $moduloId . "^";
							$flgMenuSuperior = (is_null($dataGetPage->menuSuperior) || $dataGetPage->menuSuperior == "") ? "N/A" : $dataGetPage->menuSuperior;
							$_SESSION["pageActive"] .= $flgMenuSuperior;

							if($dataEstadoUsuario->flgMensaje == "Cambio-expediente") {
								// Se actualizó el expediente desde RRHH, no cargar la página
								echo '
									<script>
				                        loadModal(
				                            "modal-container",
				                            {
				                                modalDev: "-1",
				                                modalSize: "lg",
				                                modalTitle: "Movimiento de expediente - Recursos Humanos",
				                                modalForm: "cambioExpediente",
				                                formData: "",
				                                buttonCancelActionShow: true,
				                                buttonCancelActionIcon: "check-circle",
				                                buttonCancelActionText: "Aceptar"
				                            }
				                        );
									</script>
								';
							} else {
								if(file_exists($file) && $dataGetPage->estadoMenu == "Disponible") {
									// Tag para limpiar div comunes en las paginas
									echo '<script>$("#side-carrito").html("");</script>';
								   include($file); // los permisos se validarán en el main de cada modulo
								} else if($dataGetPage->estadoMenu == "Mantenimiento") {
									// Registrar en bitácora
									$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Error 503 - En Mantenimiento (página: ".$_POST["page"].", token de acceso: ".$_POST["token"].", tipo: menú en mantenimiento), ");
								   header("Location: ../../../../app/modulos/escritorio/content/views/503");									
								} else { // hemos creado el menú pero el archivo en la carpeta del módulo no 
									// Registrar en bitácora
									$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Error 404 - Página no encontrada (página: ".$_POST["page"].", token de acceso: ".$_POST["token"].", tipo: menú no creado), ");
								   header("Location: ../../../../app/modulos/escritorio/content/views/404");
								}
							}
						} else { // la única forma que suceda esto es manejando la consola del navegador o borrando un id manual
							$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y visualizar otra interfaz (página: ".$_POST["page"].", token de acceso: ".$_POST["token"].", tipo: no existe código), ");
							header("Location: ../../../../app/modulos/escritorio/content/views/403/");
						} 
					} else if($flgModulo == 1) { // La única forma que suceda esto es que no hayamos agregado menús a un módulo o manejando la consola
						// Registrar en bitácora
						$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y visualizar otra interfaz (página: ".$_POST["page"].", token de acceso: ".$_POST["token"].", tipo: módulo), ");
						header("Location: ../../../../app/modulos/escritorio/content/views/403");
					} else { // la única forma que suceda esto es manejando la consola del navegador
						// Registrar en bitácora
						$cloud->writeBitacora("movInterfaces", "(" . $fhActual . ") Intentó saltarse los permisos asignados y visualizar otra interfaz (página: ".$_POST["page"].", token de acceso: ".$_POST["token"].", tipo: consola de navegador), ");
						header("Location: ../../../../app/modulos/escritorio/content/views/403");
					}
                } else {
                    // Cerrar su sesión
                    echo '
                        <script>
                            location.href = "../libraries/includes/logic/session/logout?flg=baja-emp";
                        </script>
                    ';
                }
            }
        } else {
            if($_SESSION["inactividad"] == 0) {
            } else {
                header("Location: /alina-cloud/cierre-inactividad"); // cambiar nombre en server de alina-cloud a cloud
            }
        }
    } else {
        header("Location: /alina-cloud/"); // cambiar nombre en server de alina-cloud a cloud
    }
?>