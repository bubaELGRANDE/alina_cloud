<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $colConsulta = '';
    $ultimo = array_key_last($_POST["columnasDatos"]);
    $joinsConsulta = '';
    foreach ($_POST["columnasDatos"] as $num => $columnas){ 
        $col = explode("^", $columnas);

        if ($col[1] == "nombreCompleto"){
            $colConsulta .= "CONCAT(IFNULL(per.apellido1, '-'),' ',IFNULL(per.apellido2, '-'),', ',IFNULL(per.nombre1, '-'),' ',IFNULL(per.nombre2, '-')) AS ";
        } else if($col[1] == "numeroCuenta") {
            $colConsulta .= "(
                SELECT
                    numeroCuenta
                FROM th_personas_cbancaria cb
                WHERE flgCuentaPlanilla = '1' AND flgDelete = '0' AND per.personaId = cb.personaId
                LIMIT 1
            ) AS ";
        } else if($col[1] == "sucursal") {
            $colConsulta .= "
                (
                    SELECT s.sucursal
                    FROM th_expediente_personas exp
                    JOIN cat_sucursales_departamentos sd ON sd.sucursalDepartamentoId = exp.sucursalDepartamentoId
                    JOIN cat_sucursales s ON s.sucursalId = sd.sucursalId
                    WHERE exp.personaId = per.personaId AND exp.estadoExpediente = 'Activo' AND exp.flgDelete = 0
                    LIMIT 1
                ) AS
            ";
        } else if($col[1] == "cargoPersona") {
            $colConsulta .= "
                (
                    SELECT cp.cargoPersona
                    FROM th_expediente_personas exp
                    JOIN cat_personas_cargos cp ON cp.prsCargoId = exp.prsCargoId
                    WHERE exp.personaId = per.personaId AND exp.estadoExpediente = 'Activo' AND exp.flgDelete = 0
                ) AS
            ";
        } else if ($col[1] == "fechaInicioCargo") {
            $colConsulta .= "
                (
                    SELECT exp.fechaInicio
                    FROM th_expediente_personas exp
                    WHERE exp.personaId = per.personaId AND exp.estadoExpediente = 'Activo' AND exp.flgDelete = 0
                ) AS
            ";
        } else {
            // Otra columna
        }

        if($num == $ultimo){
            $colConsulta .= $col[1];
        } else {
            $colConsulta .= $col[1] .',';
        }
    }

   /*  $arrayAFP = preg_grep('/^nameafp$/i', $_POST["columnasDatos"]);
    var_dump($_POST["columnasDatos"]);
    var_dump($arrayAFP);
    if (!empty($arrayAFP)){
        echo "simon :v";
        $joins .= "LEFT JOIN cat_nombres_organizaciones nameafp ON nameafp.nombreOrganizacionId = per.nombreOrganizacionIdAFP";
    }
    // echo $joins;
    $query = "SELECT $colConsulta FROM th_personas per $joins"; */
    // echo $query;
    $datosEmp = $cloud->rows("SELECT $colConsulta FROM th_personas per WHERE per.flgDelete = '0' AND per.estadoPersona = ? $joinsConsulta", ["Activo"]);
    
?>
<div class="text-center mb-3">
    <h3>Listado de empleados <br>
        <small>Departamento: Recursos Humanos</small>
    </h3>
</div>
<div class="row justify-content-end">
    <div class="col-md-3">
        <div class="input-group">
            <input type="text" class="form-control" id="buscar" placeholder="Buscar">
        </div>
    </div>
</div>
<div class="table-responsive">
    <table id="tableReport" class="table table-hover table-sm" >
        <thead>
            <tr id="filterboxrow">
            <?php foreach ($_POST["columnasDatos"] as $num => $columnas){
                $col = explode("^", $columnas);
                echo '<th>'.$col[0].'</th>';
            } ?>

            </tr>
        </thead>
        <tbody>
            <?php 
           
                foreach ($datosEmp as $empleado){
                    echo '<tr>';
                    foreach ($_POST["columnasDatos"] as $num => $columnas){
                        $col = explode("^", $columnas);
                        $nombreCol = $col[1];
                        
                        $colExp = "";
                        $column = "";

                        switch($nombreCol) {
                            case "nombreCompleto":
                                echo '<td>'.$empleado->$nombreCol.'</td>';
                            break;
                            case "per.fechaNacimiento":
                                $colExp = explode(".", $nombreCol);
                                $column = $colExp[1];
                                $fecha = date_create($empleado->$column);
                                echo '<td>'.date_format($fecha, "d/m/Y").'</td>';
                            break;
                            case "per.fechaExpiracionIdentidad":
                                $colExp = explode(".", $nombreCol);
                                $column = $colExp[1];
                                echo '<td>'.date('m/Y', strtotime('01-'.$empleado->$column)).'</td>';
                            break;
                            case "numeroCuenta":
                                echo '<td>'.($empleado->$nombreCol == "" || is_null($empleado->$nombreCol) ? '-' : $empleado->$nombreCol).'</td>';
                            break;
                            case "per.fechaInicioLabores":
                                $colExp = explode(".", $nombreCol);
                                $column = $colExp[1];
                                $fecha = date_create($empleado->$column);
                                echo '<td>'.date_format($fecha, "d/m/Y").'</td>';
                            break;
                            case "fechaInicioCargo":
                                $fechaInicioCargo = "fechaInicioCargo";
                                //echo '<td>'.($empleado->$fechaInicioCargo == "" || is_null($empleado->$fechaInicioCargo) ? '-' : $empleado->$fechaInicioCargo).'</td>';
                                $fecha = date_create($empleado->$fechaInicioCargo);
                                echo '<td>'.date_format($fecha, "d/m/Y").'</td>';
                            break;
                            case "sucursal":
                                echo '<td>'.($empleado->$nombreCol == "" || is_null($empleado->$nombreCol) ? '-' : $empleado->$nombreCol).'</td>';
                            break;
                            case "cargoPersona":
                                echo '<td>'.($empleado->$nombreCol == "" || is_null($empleado->$nombreCol) ? '-' : $empleado->$nombreCol).'</td>';
                            break;
                            case "Sexo":
                                echo '<td>'.($empleado->$sexo == "" || is_null($empleado->$sexo) ? '-' : $empleado->$sexo).'</td>';
                            break;
                            default:
                                $colExp = explode(".", $nombreCol);
                                $column = $colExp[1];
                                echo '<td>'.$empleado->$column.'</td>';
                        }

                    }
                    echo '</tr>';
                }
            ?>
        </tbody>
        <tfoot></tfoot>
    </table>
</div>

<script>
    $(document).ready(function() {
        var now    = new Date();
        var jsDate = now.getDate()+'-'+(now.getMonth()+1)+'-'+now.getFullYear();
        var hora   = now.getHours() + ':' + now.getMinutes() + ':' + now.getSeconds();
        var fechaIni = $("#fechaInicio").val();
        var fechaFin = $("#fechaFin").val();
        var impresoPor = '<?php echo $_SESSION["nombrePersona"] ?>';
        
        
        
        let tableReport = $('#tableReport').DataTable({
            "dom": 'lBrtip',
            // "sDom": '<"search-box"r><"H"lf>t<"F"ip>',
            "paging": true,
            "autoWidth": false,
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            },
            "buttons":[
                {
                    extend: 'excelHtml5',
                    className: 'btn btn-success',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    title: () => {
                        return `Listado de empleados`
                    }
                },
                {
                    extend: 'pdfHtml5',
                    className: 'btn btn-info',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    title: () => {
                        return `Listado de empleados`
                    },
                    pageSize: 'LETTER',
                    messageTop: () => {
                        return 'Departamento: Recursos Humanos';
                        
                    },
                    customize: (doc) => {
                        
                        doc.content.splice(1,0,{
                            margin: [0,-40,0,12],
                            alignment: 'left',
                            image: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAPAAAABQCAMAAAAQlwhOAAAAk1BMVEUAAAAASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASocASofwOT3rAAAAMHRSTlMA4dAC+rAGDvFB1U8w9hgJ7FvciCY7E6LANcy2kWtlvJyBfFYe6KpIcZYijXdgKsakRi97AAAIQUlEQVR42uTZ63KiQBAF4ObOiHJXBBQQCAqI9Ps/3ToYU2MUN6tuSvT7SVmWx+k5OAjDZbjE2jpJEQe2qqoiUuqHHcRevdHGMrwMISdRLfEmXiXGzfAzC5WWFiMRf4gjMFyyHyXBBP/RFoZI2EVlIOJNGhgYhaShit9NuCD0FqttZGmdZrkvr5jDc6YCw6FoTiue5ByFSdoQty+EsnZG+E0Gw2Cs64BJyhdORHIB/s6X8EQKz0/w07mIB2qbbNZVb1JZyatdpSvsCzR1UIF1yzOREnlvu87hEkH3s81CCuyvaCLXlo0OB2Q4Iz1Og26C2zLyZbhgNm0cqfdGHPrQKfCLOINnJZMFhyi2ZbMT4Jwyjcq5adptmNSrDa3nNdnTrCgtw69+toBq8EsCz0nJChVtj67rZXKjTd2ZAD3ypY2UDVSGR6oOT0ixQrV1NB3uYcRdQKBWz/y7w7CSYjuV4W7dsvJdp43wUwnPZ+bCY6S4Z51MtCTA65qZx4pSPo55h3887CfTLbygKyoUeOC9cl4jROQ0oBw8WL3yPFcBmlsDqC12VA1eWKYGkQFs3raCFxalLhwINVLi6pW3L8MokBr58B4qHvcm6Zss7+c5OHzp3cswFrhnr+FN+CNENDfvMs3ySkScOEN6QnmXHY8olk959P0fDEfcx83hXRD7IXFTieXCrVxPYozh0XQPJ7UOl8mlxEjgiilSD3j0N0eWC48lLFUz7f9wGbI4uMJDVgG3cpE1h8fyeTsyfvxtr6CfMkEWgVvVyLLgsTJNuFreeCKHfktk2QLcSDaRYcrwqxbICuGKGFkbuFWGrBp+lWEiS4Mr8jHDhZvFZ5X1iyxkcQLca5dtNhE568icZNZ6rMBe9bDKEty1lZEcLlB2RCOE+K4u31pZRsJ35jzV0h7UY55RALg1hwc8W0XT5ON4eSmDc1ZZ7vF95hSdMhLzzKWqu3vyjBRAziQVO6Ps+59vIceWTTLtqyxR/3Fl8bC3QtbiT/dmtuQmEEPRNl7AYMPYGBKzeBvwNl74/69LMYlTtE4HSKXykOgtKghYun0kNT3T1Lg2o2fT/Xw4OrJQ7mZKDZdNh2NDiuPgXDb+GTZSe1tWsG1hRtZYtZirXZrXihporvoCLs7JR9VmIcqdJb/lVjtK8XsKWSLt/dx8VKkwIuvUu8ua10E/Va22+VxmY/gRlRyhPGqeNXoV2EV9mldqXpafRc/KCtl9qV3jHj/41n7NFtqpQxmPmp7k+0Y6DOtn2HaRr2p79j7pEY8goaKCITG2AzdO00QI5VkPHHoVmifWJy36xFl/ZJ2FFoCs0rM93TFEgo/XWBdKSWRFMuVOgF7FeY+n+qvXK+yupdwvEhF+hOTYH1m3OuilyLlIVYZrlhOlDh3IcpHyC3uVu1IFVqjvCPxqtJwHNbKc3sNARC14WIwXpOqOkmtpniuRxSoFZLlDpXKofidvSlCA7r+BLGohldEKHDRtltTvA4npQtaTyFrU9JGTeTES7duKI5ku8vOfIMsC8vcop3sMhl4PZN2N49UKd10kC0OxyIGs+E+QtcA6u6JuFfWbQwRfJFqGXcgKQZ9IqXgutGS/QUsXROlXNnShheFABB0zgfSkEMGhR5eVscu6yobJVQKGe4RkwRIZtW0BUgsnBJ0pD6Hfr0BfV5f1qFcjA4e7yiYIfGWHFXSS6+RTLXbs6rKuSHlg0u8D6DMhC8MjF/4Tbe5q9tOi9SadV+zGLUTpV+bryIrg2qJKZUb9ZkBfhPe6QSgM3KxlMc72ibHN4jDwa7tRCwcEPYUSod/AAfqIrKUQCugP+qwbJ4zD+nbYkpV114aspWkwFFAsgKwHJs870Bdr0kuAiw/VHTjr9ZpRhtP+fBSjRJtSCx6CTmRlmDwToI9TwhHD4wqB2xgW4yzDZIhHMUpmG1MLKQpsKasU9TsjslByfQOyGDgkwNbj5G7FoyDTTSuyoAV27h6UuMG0setA1gVsILLqwK2RgFhn/XlywaOmFTaNTEY+JdzLOiHlK+jX51TEKQHImgBZDNxaPt1dQV1YKpc2ZA04jZeyTlwxOa0hqhwcwZSAdgbIimXDlEh5DWKprjNl2va98kQtvKPAhtjG2EEELjjCKWFMZKHkcjBM5KMsPOoGmfZEFqfxkf+Zctm6z6UIOBVxSijQznBKUJZMQCRvilqQ1XkivqAWWCc4zeSoW9zI4ZSwh1ACIIugG8sEH4GsEzpro5FPT3Od4GDoSk88ArIwJQRlB7IOCNxKLM9yWDv0IEGmWSuyoIUAre2KyIInRLPAvdiNAVn8eVJeB+ikC1mPtoN70AKDzjGQw+h1jmYhl/VmsoRQHujiWZNcsVTsNyBr3//zXUotPIGsN+y8LkUI/ITzzVbQNrhUHchyJ6IdTxGTqbJTImvQ+8RBUUELMyILgLpWupUjTmvK0X2DeUVkiWvmb9zqz9v/mylk2nLigFrAaHZCa1CYPzoBWTb9EMoGbrbjO7iBrLT3iYNJCS1gLxZVqvsH5+YfTGQl7T8mRp8Au1Gmq96HMd4Y9IOhSnH2rBzTZoMY54Tqr2B9mgidFMr0QUlHlg+mbtsPYzDoRBYkJMJS5ifMN1h8u7N4LRLZd5uPDoMfmNGIlJwGGh0pU0+1mDVoWOLXwNNcYZ2GpsOdvjrw5JXO9H2i7s1rvvqvkuf+TFZ4VbfmNdbMtINrZy+PdYsbn/zdV3J3axW7zVf+7MaXg4b9vb/Dm+Vhtl+s25A4nJ0/svDs+cpouWHhe4ePbP8ubygWhyzM1//632DpU/3/f8xbjlf/vcnx6r83p8nknfrL9g2KAAcVBiUUWQAAAABJRU5ErkJggg==',
                            width: 91,
                            height: 30
                        });
                        doc.styles.message = {
                            alignment: 'center'
                        };
                        doc['footer'] = (page,pages) =>{
                            return {
                                columns:[
                                    {
                                        alignment: 'left',
                                        text: ['Impreso el: <?php echo date("d/m/Y H:i:s");?>\n Generado por: ', {text: impresoPor}],
                                        fontSize: 7,
                                        margin: [40,0]
                                    },
                                    {
                                        alignment: 'right',
                                        text: ['Página ', {text: page.toString() }, ' de ', {text: pages.toString()}],
                                        fontSize: 7,
                                        margin: [40,0]
                                    }
                                ]
                            }
                        };
                        doc.defaultStyle.fontSize = 9; 
                        doc.styles.tableHeader.fontSize = 9;
                        doc.styles.tableBodyOdd.fillColor = '#fff';
                    },
                    
                }
            ]
        });

        tableReport = $('#tableReport').DataTable();   //pay attention to capital D, which is mandatory to retrieve "api" datatables' object, as @Lionel said
        $('#buscar').keyup(function(){
            tableReport.search($(this).val()).draw() ;
        })
    });
</script>