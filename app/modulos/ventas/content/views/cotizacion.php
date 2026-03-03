<?php
@session_start();
?>

<h2>Cotizaciones</h2>
<hr>

<div class="row mb-3">
    <div class="col text-end">
        <button type="button" class="btn btn-primary" onclick="nuevaCotizacion()">
            <i class="fas fa-plus-circle"></i> Nueva cotización
        </button>
    </div>
</div>

<div class="table-responsive">
    <table id="tblCotizaciones" class="table table-hover" style="width:100%;">
        <thead>
            <tr id="filterboxrow">
                <th>#</th>
                <th>Cotización</th>
                <th>Cliente</th>
                <th>Sucursal</th>
                <th>Fecha</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
    function nuevaCotizacion() {
        changePage(`<?php echo $_SESSION['currentRoute']; ?>`, 'cotizacion', 'cotizacionId=0');
    }

    function abrirCotizacion(frmData) {
        changePage(`<?php echo $_SESSION['currentRoute']; ?>`, 'cotizacion', `cotizacionId=${frmData.cotizacionId}`);
    }

    function anularCotizacion(frmData) {
        mensaje_confirmacion(
            '¿Anular cotización?',
            `Se anulará la cotización #${frmData.cotizacionId}.`,
            'warning',
            () => {
                asyncData(
                    `<?php echo $_SESSION['currentRoute']; ?>transaction/operation`,
                    {
                        typeOperation: 'update',
                        operation: 'cotizacion-anular',
                        cotizacionId: frmData.cotizacionId
                    },
                    (data) => {
                        if(data === 'success') {
                            mensaje_do_aceptar('Operación completada', 'Cotización anulada con éxito.', 'success', () => {
                                $('#tblCotizaciones').DataTable().ajax.reload(null, false);
                            });
                        } else {
                            mensaje('Aviso:', data, 'warning');
                        }
                    }
                );
            },
            'Anular',
            'Cancelar'
        );
    }

    $(document).ready(function() {
        let tbl = $('#tblCotizaciones').DataTable({
            dom: 'lrtip',
            ajax: {
                method: 'POST',
                url: `<?php echo $_SESSION['currentRoute']; ?>content/tables/tableCotizaciones`
            },
            autoWidth: false,
            columns: [
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null
            ],
            columnDefs: [
                { orderable: false, targets: [7] }
            ],
            language: {
                url: '../libraries/packages/js/spanish_dt.json'
            }
        });
    });
</script>
