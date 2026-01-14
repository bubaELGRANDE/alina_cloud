<?php 
    @session_start();
?>
<h2>
    Gestión de jefaturas
</h2>
<hr>
<div class="row">
    <div class="col text-end">
        <button type="button" class="btn btn-primary ttip" onclick="modalJefatura('nuevo^0');">
            <i class="fas fa-plus-circle"></i> Jefatura
            <span class="ttiptext">Nueva jefatura</span>
        </button>
    </div>
</div>
<div class="table-responsive">
    <table id="tblJefatura" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow">
                <th>#</th>
                <th>Jefatura</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script>
    function modalJefatura(formData) {
        loadModal(
            "modal-container",
            {
                modalDev: "-1",
                modalSize: 'lg',
                modalTitle: `Jefatura`,
                modalForm: 'expedienteJefatura',
                formData: formData,
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }

    function eliminarJefatura(id, flg, jefeId){
        let txtAsk = '', txtDescrip = '', txtSuccess = '';
        if(flg == "empleado") {
            txtAsk = '¿Está seguro que desea eliminar al empleado de la jefatura?';
            txtDescrip = 'Se eliminará al empleado de la jefatura.';
            txtSuccess = 'Empleado eliminado de la jefatura con éxito';
        } else {
            txtAsk = '¿Está seguro que desea eliminar esta jefatura?';
            txtDescrip = 'Se eliminará la jefatura de los empleados que fueron asignados a esta.';
            txtSuccess = 'Jefatura eliminada con éxito';
        }
        mensaje_confirmacion(
            `${txtAsk}`,
            `${txtDescrip}`,
            `warning`,
            (param) => {
                asyncDoDataReturn(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation',
                    {
                        typeOperation:`delete`,
                        operation: `expediente-jefatura`,
                        id: id,
                        flg: flg,
                        jefeId: jefeId
                    },
                    (data) => {
                        if (data=="success") {
                            mensaje_do_aceptar(
                                `Operación completada`,
                                `${txtSuccess}`,
                                `success`,() => {
                                    $(`#tblJefatura`).DataTable().ajax.reload(null,false);
                                    if(flg == "empleado") {
                                        cargarJefaturaEmpleados();
                                    } else {
                                        // Se elimino jefe
                                    }
                                }
                            );
                        }else{
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
        )
    }

    function jefeInmediato(id, flg) {
        asyncDoDataReturn(
            '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation',
            {
                typeOperation:`update`,
                operation: `expediente-jefatura-inmediata`,
                id: id,
                flg: flg
            },
            (data) => {
                if (data=="success") {
                    // No es necesario volver a llamar la function, si se hace, se pierde la paginación y esta function se dispara solo con input switch
                    //cargarJefaturaEmpleados();
                } else {
                    mensaje(
                        "Aviso:",
                        data,
                        "warning"
                    );
                }
            }
        );
    }

    function heredarEmpleados(id, flg) {
        asyncDoDataReturn(
            '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation',
            {
                typeOperation:`update`,
                operation: `expediente-jefatura-heredar`,
                id: id,
                flg: flg
            },
            (data) => {
                if (data=="success") {
                    $(`#tblJefatura`).DataTable().ajax.reload(null,false);
                    // No es necesario volver a llamar la function, si se hace, se pierde la paginación y esta function se dispara solo con input switch
                    //cargarJefaturaEmpleados();
                } else {
                    mensaje(
                        "Aviso:",
                        data,
                        "warning"
                    );
                }
            }
        );
    }

    $(document).ready(function() {
        $('#tblJefatura thead tr#filterboxrow th').each(function(index) {
            if(index==1) {
                var title = $('#tblJefatura thead tr#filterboxrow th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblJefatura.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });
        
        let tblJefatura = $('#tblJefatura').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableJefaturas",
                "data": { 
                    "tipoJefatura": 'jefeId'
                }
            },
            "autoWidth": false,
            "columns": [
                null,
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