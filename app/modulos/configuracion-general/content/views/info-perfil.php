<?php 
	@session_start();
?>
<h2>
    Información de perfil de empleado
</h2>
<hr>
<div class="row">
    <div class="col-md-3">
        <div class="nav flex-column nav-tabs text-center" id="v-tabs-tab" role="tablist" aria-orientation="vertical">
            <a class="nav-link active" id="v-tabs-1-tab" data-mdb-toggle="tab" href="#v-tabs-1" role="tab" aria-controls="v-tabs-1" aria-selected="true">Áreas de estudio</a>
            <a class="nav-link" id="v-tabs-2-tab" data-mdb-toggle="tab" href="#v-tabs-2" role="tab" aria-controls="v-tabs-2" aria-selected="false">Áreas de experiencia</a>
            <a class="nav-link" id="v-tabs-3-tab" data-mdb-toggle="tab" href="#v-tabs-3" role="tab" aria-controls="v-tabs-3" aria-selected="false">Programas informáticos</a>
            <a class="nav-link" id="v-tabs-4-tab" data-mdb-toggle="tab" href="#v-tabs-4" role="tab" aria-controls="v-tabs-4" aria-selected="false">Herramientas y Equipos</a>
            <a class="nav-link" id="v-tabs-5-tab" data-mdb-toggle="tab" href="#v-tabs-5" role="tab" aria-controls="v-tabs-5" aria-selected="false">Enfermedades y Alergias</a>
            <a class="nav-link" id="v-tabs-6-tab" data-mdb-toggle="tab" href="#v-tabs-6" role="tab" aria-controls="v-tabs-6" aria-selected="false">Nombres de relación</a>
        </div>
    </div>  
    <div class="col-md-9">
        <div class="tab-content" id="vtabs-mainContent">
            <div class="tab-pane fade show active" id="v-tabs-1" role="tabpanel" aria-labelledby="v-tabs-1-tab">
                <div class="row">
                    <div class="col text-end">
                        <?php 
                            if(in_array(5, $_SESSION["arrayPermisos"]) || in_array(15, $_SESSION["arrayPermisos"])) { 
                        ?>
                                <button id="btnNuevaArea" type="button" class="btn btn-primary" onclick="modalAreaHabilidad('nuevo','estudio');"><i class="fas fa-plus-circle"></i> Nueva área</button>
                        <?php 
                            } 
                        ?>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="tblEstudio" class="table table-hover" style="width: 100%;">
                        <thead>
                            <tr id="filterboxrow-estudio">
                                <th>#</th>
                                <th>Área de estudio</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="v-tabs-2" role="tabpanel" aria-labelledby="v-tabs-2-tab">
                <div class="row">
                    <div class="col text-end">
                        <?php 
                            if(in_array(5, $_SESSION["arrayPermisos"]) || in_array(15, $_SESSION["arrayPermisos"])) { 
                        ?>
                                <button id="btnNuevaExperiencia" type="button" class="btn btn-primary" onclick="modalAreaHabilidad('nuevo','experiencia');">
                                    <i class="fas fa-plus-circle"></i> Nueva área
                                </button>
                        <?php 
                            } 
                        ?>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="tblExperiencia" class="table table-hover" style="width: 100%;">
                        <thead>
                            <tr id="filterboxrow-exp">
                                <th>#</th>
                                <th>Área de experiencia</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="v-tabs-3" role="tabpanel" aria-labelledby="v-tabs-3-tab">
                <div class="row">
                    <div class="col text-end">
                        <?php 
                            if(in_array(5, $_SESSION["arrayPermisos"]) || in_array(15, $_SESSION["arrayPermisos"])) { 
                        ?>
                                <button id="btnNuevoSoft" type="button" class="btn btn-primary" onclick="modalAreaHabilidad('nuevo','software');">
                                    <i class="fas fa-plus-circle"></i> Nuevo Programa
                                </button>
                        <?php 
                            } 
                        ?>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="tbProgramas" class="table table-hover" style="width: 100%;">
                        <thead>
                            <tr id="filterboxrow-software">
                                <th>#</th>
                                <th>Nombre del programa</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="v-tabs-4" role="tabpanel" aria-labelledby="v-tabs-4-tab">
                <div class="row">
                    <div class="col text-end">
                        <?php 
                            if(in_array(5, $_SESSION["arrayPermisos"]) || in_array(15, $_SESSION["arrayPermisos"])) { 
                        ?>
                                <button id="btnNuevaHerraEqu" type="button" class="btn btn-primary" onclick="modalAreaHabilidad('nuevo','herraEqu');">
                                    <i class="fas fa-plus-circle"></i> Nueva Herramienta/Equipo
                                </button>
                        <?php 
                            } 
                        ?>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="tbHerraEq" class="table table-hover" style="width: 100%;">
                        <thead>
                            <tr id="filterboxrow-equipo">
                                <th>#</th>
                                <th>Nombre de la herramienta o equipo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="v-tabs-5" role="tabpanel" aria-labelledby="v-tabs-5-tab">
                <div class="row">
                    <div class="col text-end">
                        <?php 
                            if(in_array(5, $_SESSION["arrayPermisos"]) || in_array(15, $_SESSION["arrayPermisos"])) { 
                        ?>
                                <button id="btnNuevaEnfermedad" type="button" class="btn btn-primary" onclick="modalEnfermedad('nuevo');">
                                    <i class="fas fa-plus-circle"></i> Nueva Enfermedad/Alergia
                                </button>
                        <?php 
                            } 
                        ?>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="tblEnfermedades" class="table table-hover" style="width: 100%;">
                        <thead>
                            <tr id="filterboxrow-enfermedad">
                                <th>#</th>
                                <th>Nombre de la enfermeda/alergia</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="v-tabs-6" role="tabpanel" aria-labelledby="v-tabs-6-tab">
                <div class="row">
                    <div class="col text-end">
                        <?php 
                            if(in_array(5, $_SESSION["arrayPermisos"]) || in_array(15, $_SESSION["arrayPermisos"])) { 
                        ?>
                                <button id="btnNuevaEnfermedad" type="button" class="btn btn-primary" onclick="modalRelacion('nuevo');">
                                    <i class="fas fa-plus-circle"></i> Nuevo nombre de relación
                                </button>
                        <?php 
                            } 
                        ?>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="tblRelaciones" class="table table-hover" style="width: 100%;">
                        <thead>
                            <tr id="filterboxrow-enfermedad">
                                <th>#</th>
                                <th>Nombre de relación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function modalAreaHabilidad(tipo, tipoHabilidad, tableData = "N/A") {
        let formData = ""; modalDev = "-1";
        if(tipo == "editar") {
            formData = 'editar^'+tipoHabilidad+'^'+tableData;
            //modalDev: '9^16';
            modalDev: -1;
        } else {
            formData = 'nuevo^'+tipoHabilidad;
            //modalDev: "9^15";
            modalDev: -1;
        }

        let titulo = (tipoHabilidad == "estudio") ? "Nueva área de estudio" : (tipoHabilidad == "experiencia") ? "Nueva área de experiencia" : (tipoHabilidad == "software") ? "Nuevo programa informático" : (tipoHabilidad == "herraEqu") ? "Nueva herramienta o equipo" : "Agregar nuevo";

        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'md',
                modalTitle: titulo,
                modalForm: 'ModalArea-Habilidad',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }
    
    
    function delArea(idArea){
        let title = "Aviso:"
        let msj = "¿Esta seguro que quiere eliminar este registro?";
        let btnAccepTxt = "Confirmar";
        let msjDone = "Se eliminó correctamente el registro.";
        
        let arrayData = idArea.split("^");
        
        mensaje_confirmacion(
			title, msj, `warning`, function(param) {
				asyncDoDataReturn(
					'<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
					{
						typeOperation: 'delete',
						operation: arrayData[0],
						idArea: arrayData[1],
					},
					function(data) {
						if(data == "success") {
							mensaje_do_aceptar(`Operación completada:`, msjDone, `success`, function() {
                                var tablaUpd = arrayData[0];
                                if (tablaUpd == "estudio"){
                                    var idTabla = "#tblEstudio";
                                } else if (tablaUpd == "experiencia") {
                                    var idTabla = "#tblExperiencia";
                                } else if (tablaUpd == "software") {
                                    var idTabla = "#tbProgramas";
                                } else if (tablaUpd == "herraEqu") {
                                    var idTabla = "#tbHerraEq";
                                }
                                $(idTabla).DataTable().ajax.reload(null, false);
                                $("#modal-container").modal("hide"); // para aprobar y rechazar se usa modal
                            });
						} else {
                            mensaje(
                                "Aviso:",
                                data,
                                "warning"
                            );
						}
					}
				);
			},
			btnAccepTxt,
			`Cancelar`
		);
    }

    function modalEnfermedad(tipo, tableData = "N/A") {
        let formData = ""; modalDev = "-1";
        if(tipo == "editar") {
            formData = 'editar^'+tableData;
            modalDev = "-1";
        } else {
            formData = 'nuevo';
            modalDev = "-1";
        }
        loadModal(
            "modal-container",
            {
                modalDev: -1,
                modalSize: 'md',
                modalTitle: `Enfermedad/Alergia`,
                modalForm: 'empleadoEnfermedadAlergia',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function eliminarEnfermedad(tableData) {
        mensaje_confirmacion(
            `¿Está seguro que desea eliminar esta enfermedad/alergia?`, 
            `Se eliminará del catálogo.`, 
            `warning`, 
            function(param) {
                asyncDoDataReturn(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                    {
                        typeOperation: `delete`,
                        operation: `enfermedad-alergia`,
                        id: tableData
                    },
                    function(data) {
                        if(data == "success") {
                            mensaje_do_aceptar(`Operación completada:`, `Enfermedad/alergia eliminada con éxito`, `success`, function() {
                                $(`#tblEnfermedades`).DataTable().ajax.reload(null, false);
                            });
                        } else {
                            mensaje(
                                "Aviso:",
                                data,
                                "warning"
                            );
                        }
                    }
                );
            },
            `Eliminar`,
            `Cancelar`
        );
    }

    function modalRelacion(tipo, tableData = "N/A") {
        let formData = ""; modalDev = "-1";
        if(tipo == "editar") {
            formData = 'editar^'+tableData;
            modalDev = "-1";
        } else {
            formData = 'nuevo';
            modalDev = "-1";
        }
        loadModal(
            "modal-container",
            {
                modalDev: -1,
                modalSize: 'md',
                modalTitle: `Relación`,
                modalForm: 'modalRelacion',
                formData: formData,
                buttonAcceptShow: true,
                buttonAcceptText: 'Guardar',
                buttonAcceptIcon: 'save',
                buttonCancelShow: true,
                buttonCancelText: 'Cancelar'
            }
        );
    }

    function eliminarRelacion(tableData) {
        mensaje_confirmacion(
            `¿Está seguro que desea eliminar esta relación?`, 
            `Se eliminará del catálogo.`, 
            `warning`, 
            function(param) {
                asyncDoDataReturn(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                    {
                        typeOperation: `delete`,
                        operation: `tipoRelacion`,
                        id: tableData
                    },
                    function(data) {
                        if(data == "success") {
                            mensaje_do_aceptar(`Operación completada:`, `Relación eliminada con éxito`, `success`, function() {
                                $(`#tblRelaciones`).DataTable().ajax.reload(null, false);
                            });
                        } else {
                            mensaje(
                                "Aviso:",
                                data,
                                "warning"
                            );
                        }
                    }
                );
            },
            `Eliminar`,
            `Cancelar`
        );
    }

    $(document).ready(function() {
        //Estudio
        $('#tblEstudio thead tr#filterboxrow-estudio th').each(function(index) {
            if(index==1 ) {
                var title = $('#tblEstudio thead tr#filterboxrow-estudio th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}estudio" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}estudio">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblEstudio.column($(this).index()).search($(`#input${$(this).index()}estudio`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });
        
        let tblEstudio = $('#tblEstudio').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableListaAreas-Habilidades",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "tipoSolicitud": 'Estudio'
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                {"width": "70%"},
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
   
    
    //experiencia
    
        $('#tblExperiencia thead tr#filterboxrow-exp th').each(function(index) {
            if(index==1 ) {
                var title = $('#tblExperiencia thead tr#filterboxrow-exp th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}exp" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}exp">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblExperiencia.column($(this).index()).search($(`#input${$(this).index()}exp`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });
        
        let tblExperiencia = $('#tblExperiencia').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableListaAreas-Habilidades",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "tipoSolicitud": 'Experiencia'
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                {"width": "70%"},
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

    //software
        $('#tbProgramas thead tr#filterboxrow-software th').each(function(index) {
            if(index==1 ) {
                var title = $('#tbProgramas thead tr#filterboxrow-software th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}software" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}software">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tbProgramas.column($(this).index()).search($(`#input${$(this).index()}software`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });
        
        let tbProgramas = $('#tbProgramas').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableListaAreas-Habilidades",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "tipoSolicitud": 'Software'
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                {"width": "70%"},
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    //Herramientas y equipo
        $('#tbHerraEq thead tr#filterboxrow-equipo th').each(function(index) {
            if(index==1 ) {
                var title = $('#tbHerraEq thead tr#filterboxrow-equipo th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}equipo" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}equipo">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tbHerraEqu.column($(this).index()).search($(`#input${$(this).index()}equipo`).val()).draw();
                });
	            document.querySelectorAll('.form-outline').forEach((formOutline) => {
	                new mdb.Input(formOutline).init();
	            });
            } else {
            }
        });
        
        let tbHerraEqu = $('#tbHerraEq').DataTable({
            "dom": 'lrtip',
            "ajax": {
            	"method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableListaAreas-Habilidades",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "tipoSolicitud": 'HerraEqu'
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                {"width": "70%"},
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });

        // Enfermedad o Alergia
        $('#tblEnfermedades thead tr#filterboxrow-enfermedad th').each(function(index) {
            if(index==1 ) {
                var title = $('#tblEnfermedades thead tr#filterboxrow-enfermedad th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}enfermedad" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}enfermedad">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblEnfermedades.column($(this).index()).search($(`#input${$(this).index()}enfermedad`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });
        
        let tblEnfermedades = $('#tblEnfermedades').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableListaEnfermedadesAlergias",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "x": ''
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                {"width": "70%"},
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
        // Relaciones

        $('#tblRelaciones thead tr#filterboxrow-enfermedad th').each(function(index) {
            if(index==1 ) {
                var title = $('#tblRelaciones thead tr#filterboxrow-enfermedad th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}enfermedad" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}enfermedad">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblRelaciones.column($(this).index()).search($(`#input${$(this).index()}enfermedad`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });
        
        let tblRelaciones = $('#tblRelaciones').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableRelaciones",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "x": ''
                }
            },
            "autoWidth": false,
            "columns": [
                null,
                {"width": "70%"},
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>
