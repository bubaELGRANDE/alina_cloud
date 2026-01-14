    <?php
    require_once ('../../../../../libraries/includes/logic/mgc/datos94.php');
    require '../../../../../libraries/packages/php/vendor/autoload-spreadsheet.php';
    @session_start();
    error_reporting(E_ALL & ~E_DEPRECATED);

    use PhpOffice\PhpSpreadsheet\IOFactory;
    use PhpOffice\PhpSpreadsheet\Spreadsheet;

    if (isset($_POST['partidaContableId']) && isset($_POST['partidaContaPeriodoId'])) {
        if (isset($_FILES['adjunto'])) {
            try {
                $fileTmpPath = $_FILES['adjunto']['tmp_name'];
                $spreadsheet = IOFactory::load($fileTmpPath);
                $sheet = $spreadsheet->getActiveSheet();

                $data = [];
                foreach ($sheet->getRowIterator(2) as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);

                    $rowData = [];
                    foreach ($cellIterator as $cell) {
                        $value = $cell->getCalculatedValue();
                        if ($value instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                            $rowData[] = $value->getPlainText();
                        } else {
                            $rowData[] = $value;
                        }
                    }
                    if (array_filter($rowData)) {
                        $data[] = $rowData;
                    }
                }

                $errores = [];
                $ultimoNCuentaId = 0;
                $itemsValidos = [];

                if (!empty($data) && isset($data[0][0]) && $data[0][0] > 0) {
                    foreach ($data as $index => $row) {
                        $nCuenta = isset($row[0]) ? trim((string) $row[0]) : '';

                        if ($nCuenta > 0) {
                            $cuentaId = $cloud->row('
                                SELECT cuentaContaId AS id 
                                FROM conta_cuentas_contables 
                                WHERE numeroCuenta = ? AND flgDelete = 0
                            ', [$nCuenta]);

                            if (!empty($cuentaId) && isset($cuentaId->id) && $cuentaId->id > 0) {
                                $ultimoNCuentaId = $cuentaId->id;
                            } else {
                                $errores[] = 'Fila #' . ($index + 2) . ": El número de cuenta $nCuenta no fue encontrado.";
                            }
                        }

                        if ($ultimoNCuentaId > 0) {
                            // Convertir sin redondear ni formatear
                            $cargo = isset($row[2]) && $row[2] !== '' ? floatval(str_replace([',', '$'], '', $row[2])) : 0.0;
                            $abono = isset($row[3]) && $row[3] !== '' ? floatval(str_replace([',', '$'], '', $row[3])) : 0.0;

                            if ($cargo > 0 && $abono > 0) {
                                $errores[] = 'Fila #' . ($index + 2) . ': Debe tener únicamente cargo o abono, no ambos.';
                            }

                            $itemsValidos[] = [
                                'partidaContableId' => $_POST['partidaContableId'],
                                'partidaContaPeriodoId' => $_POST['partidaContaPeriodoId'],
                                'cuentaContaId' => $ultimoNCuentaId,
                                'descripcionPartidaDetalle' => $row[1],
                                'cargos' => $cargo,
                                'abonos' => $abono,
                                'tipoDTEId' => 7,  //TIPO EXEL
                                'documentoId' => 0,
                                'numDocumento' => $row[4],
                            ];
                        }
                    }
                } else {
                    $errores[] = 'El primer ítem no tiene número de cuenta';
                }

                if (empty($errores)) {
                    foreach ($itemsValidos as $item) {
                        $cloud->insert('conta_partidas_contables_detalle', $item);
                    }

                    // Recalcular encabezado sin perder decimales
                    $result = $cloud->row('
                        SELECT SUM(cargos) AS cargos, SUM(abonos) AS abonos
                        FROM conta_partidas_contables_detalle
                        WHERE partidaContableId = ? AND flgDelete = 0
                    ', [$_POST['partidaContableId']]);

                    if ($result) {
                        $params = [
                            'cargoPartida' => (float) $result->cargos,
                            'abonoPartida' => (float) $result->abonos
                        ];
                        $cloud->update('conta_partidas_contables', $params, [
                            'partidaContableId' => $_POST['partidaContableId']
                        ]);
                    }

                    echo 'success';
                } else {
                    echo 'El archivo no contiene datos válidos:<br>' . implode('<br>', $errores);
                }
            } catch (Exception $e) {
                echo 'Error al leer el archivo Excel: ' . $e->getMessage();
            }
        } else {
            echo 'No se ha subido ningún archivo Excel.';
        }
    }
    ?>