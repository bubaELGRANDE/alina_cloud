<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    // personaId ^ nombreCompleto ^ tab activo
    $arrayFormData = explode("^", $_POST["arrayFormData"]);

    ?>

<ul class="nav nav-tabs mb-3" id="ntab" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active" id="ntab-modal-1" data-mdb-toggle="pill" href="#ntab-modal-content-1" role="tab" aria-controls="ntab-modal-content-1" aria-selected="true">
            Familia
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="ntab-modal-2" data-mdb-toggle="pill" href="#ntab-modal-content-2" role="tab" aria-controls="ntab-modal-content-2" aria-selected="false">
            Empresa
        </a>
    </li>
</ul>
<div class="tab-content" id="ntab-content">
    <div class="tab-pane fade show active" id="ntab-modal-content-1" role="tabpanel" aria-labelledby="ntab-modal-1">
        <div class="table-responsive">
            <table id="tblNucleoFamilia" class="table table-hover" style="width: 100%;">
                <thead>
                    <tr id="filterboxrow-familia">
                        <th>#</th>
                        <th>Relación familiar</th>
                        <th>Beneficiario</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $dataRelaciones = $cloud->rows("
                        SELECT 
                            pf.prsFamiliaId AS prsFamiliaId, 
                            pf.catPrsRelacionId AS catPrsRelacionId,
                            rel.tipoPrsRelacion AS parentesco, 
                            pf.nombreFamiliar AS nombreFamiliar, 
                            pf.fechaNacimiento AS fechaNacimiento, 
                            pf.flgBeneficiario AS flgBeneficiario, 
                            pf.porcentajeBeneficiario AS porcentajeBeneficiario,
                            per.estadoPersona AS estadoPersona
                        FROM th_personas_familia pf
                        JOIN th_personas per ON per.personaId = pf.personaId
                        JOIN cat_personas_relacion rel ON rel.catPrsRelacionId = pf.catPrsRelacionId
                        WHERE pf.personaId = ? AND pf.flgDelete = 0
                    ", [$arrayFormData[0]]);
                    $n = 0;
                    if (empty($dataRelaciones)){
                        echo '<tr>
                                <td colspan="3">No se encontraron registros.</td>
                            </tr>';
                    } else {

                        foreach ($dataRelaciones as $dataRelaciones) {
                            $n += 1;
                            
                                $relacionFamiliar = '
                                <b><i class="fas fa-user"></i> Nombre del familiar: </b> '.$dataRelaciones->nombreFamiliar.'<br>
                                <b><i class="fas fa-people-arrows"></i> Parentesco: </b> '.$dataRelaciones->parentesco.'<br>
                                <b><i class="fas fa-calendar"></i> F. nacimiento: </b> '.date("d/m/Y", strtotime($dataRelaciones->fechaNacimiento)).'
                            ';
    
                            if($dataRelaciones->flgBeneficiario == "Sí") {
                                $beneficiario = '
                                    <b><i class="fas fa-user-circle"></i> Edad: </b> '.date_diff(date_create($dataRelaciones->fechaNacimiento), date_create(date("Y-m-d")))->format("%y").' años<br>
                                    <b><i class="fas fa-hand-holding-heart"></i> Beneficiario: </b> '.$dataRelaciones->flgBeneficiario.'<br>
                                    <b><i class="fas fa-hand-holding-usd"></i> Porcentaje del beneficiario: </b> '.number_format($dataRelaciones->porcentajeBeneficiario, 2, '.', ',').'%
                                ';
                            } else {
                                $beneficiario = '
                                    <b><i class="fas fa-user-circle"></i> Edad: </b> '.date_diff(date_create($dataRelaciones->fechaNacimiento), date_create(date("Y-m-d")))->format("%y").' años<br>
                                    <b><i class="fas fa-hand-holding-heart"></i> Beneficiario: </b> '.$dataRelaciones->flgBeneficiario.'
                                ';
                            }
    
                            echo '<tr>
                                <td>'.$n.'</td>
                                <td>'.$relacionFamiliar.'</td>
                                <td>'.$beneficiario.'</td>
                            </tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane fade" id="ntab-modal-content-2" role="tabpanel" aria-labelledby="ntab-modal-2">
        <div class="table-responsive">
            <table id="tblRelacionesEmpleados" class="table table-hover" style="width: 100%;">
                <thead>
                    <tr id="filterboxrow-empresa">
                        <th>#</th>
                        <th>Empleado</th>
                        <th>Tipo de relación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $dataRelaciones = $cloud->rows("
                        SELECT 
                            thpr.prsRelacionId as prsRelacionId,
                            thpr.personaId2 as personaId2,
                            CONCAT(
                                IFNULL(per.apellido1, '-'),
                                ' ',
                                IFNULL(per.apellido2, '-'),
                                ', ',
                                IFNULL(per.nombre1, '-'),
                                ' ',
                                IFNULL(per.nombre2, '-')
                            ) AS nombreCompleto,
                            per.estadoPersona AS estadoPersona,
                            pr.tipoPrsRelacion as personaRelacion
                            FROM ((th_personas_relacion thpr
                            JOIN th_personas per ON per.personaId = thpr.personaId2)
                            JOIN cat_personas_relacion pr ON pr.catPrsRelacionId = thpr.catPrsRelacionId)
                            WHERE thpr.personaId1 = ? AND thpr.flgDelete = '0'
                        ", [$arrayFormData[0]]);
                        if (empty($dataRelaciones)){
                            echo '<tr>
                                    <td colspan="3">No se encontraron registros.</td>
                                </tr>';
                        } else {
                            foreach ($dataRelaciones as $dataRelaciones) {
                                $n += 1;
                                echo '<tr>
                                <td>'.$n.'</td>
                                <td>'.$dataRelaciones->nombreCompleto.'</td>
                                <td>'.$dataRelaciones->personaRelacion.'</td>
                                </tr>';
                            }
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>