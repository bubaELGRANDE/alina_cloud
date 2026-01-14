<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
	@session_start();
?>
<select id="menuId" name="menuId[]" style="width: 100%;" multiple="multiple" required>
    <?php 
	  	if($_POST["moduloId"] > 0) { // No se ha seleccionado módulo
	        if(in_array(10, $_SESSION["arrayPermisos"])) { // Mostrar todos los menús
	            $dataModuloxMenu = $cloud->rows("
					SELECT
						menuId,
						menu,
						urlMenu
					FROM conf_menus
					WHERE moduloId = ? AND flgDelete = '0'
				    ORDER BY menu
				",[$_POST["moduloId"]]);
	        } else { // No permitir que un usuario seleccione un menú al que no tiene acceso/no tiene asignado
	            $dataModuloxMenu = $cloud->rows("
	                SELECT
	                    m.menuId AS menuId,
	                    m.menu AS menu,
	                    m.urlMenu AS urlMenu
	                FROM conf_permisos_usuario perm
	                JOIN conf_menus_permisos mp ON mp.menuPermisoId = perm.menuPermisoId
	                JOIN conf_menus m ON m.menuId = mp.menuId
	                JOIN conf_modulos md ON md.moduloId = m.moduloId
	                WHERE md.moduloId = ? AND perm.usuarioId = ? AND
	                (perm.flgDelete = '0' AND mp.flgDelete = '0' AND m.flgDelete = '0' AND md.flgDelete = '0')
	                GROUP BY m.menuId
	            ",[$_POST["moduloId"], $_SESSION["usuarioId"]]);
	        }
			$n = 0;
			foreach ($dataModuloxMenu as $dataModuloxMenu) {
		        $existeSubMenu = $cloud->count("
		            SELECT
		                menuId
		            FROM conf_menus
		            WHERE menuSuperior = ? AND flgDelete = '0'
		            ORDER BY numOrdenMenu  
		        ", [$dataModuloxMenu->menuId]);
		        if($existeSubMenu == 0) {
					$n += 1;
					echo '<option value="' . $dataModuloxMenu->menuId . '">' . $dataModuloxMenu->menu . '</option>';
		        } else {
		        	// Es dropdown, no mostrarlo
		        }
			}
			if($n == 0) {
				echo '<option value="0" selected>No se encontraron menús...</option>';
			} else {
				// Se dibujaron todos los option
			}
	  	} else {
	  		echo '<option value="0" selected>Seleccione módulo...</option>';
	  	}
    ?>
</select>
<script>
	$(document).ready(function() {
		$("#menuId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Menú(s)'
		});
	});
</script>