<?php

$n = count($result["equip"]) + count($result["chamado"]) + count($result["triagem"]);

if ($n < 1): echo "Sem resultados." ;
else :
    if (count($result["equip"]) > 0):
?>
<p class="h5"><i class="fas fa-desktop"></i> Equipamentos</p> <!-- TABELA EQUIPS -->
<table id="tblEquipsBr" class="table table-hover table-sm">
    <thead>
        <th>num_equip</th>
        <th>desc_equip</th>
        <th>chamado_equip</th>
        <th>status_equip</th>
    </thead>
    <tbody>
    <?php
            foreach($result["equip"] as $equip): 
    ?>
        <tr>
            <td><?= $equip["num_equip"] ?></td>
            <td><?= $equip["desc_equip"] ?></td>
            <td><?= $equip["chamado_equip"] ?></td>
            <td><?= $equip["status_equip"] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
    endif;

    if (count($result["chamado"]) > 0):
?>
    <p class="h5"><i class="fas fa-info-circle"></i> Chamados</p> <!-- TABELA CHAMADOS -->
    <table id="tblChamadosBr"class="table table-hover table-sm">
        <thead>
            <th>id</th>
            <th>ticket</th>
            <th>nome_solicitante</th>
            <th>nome_local</th>
            <th>status</th>
            <th>responsavel</th>
        </thead>
        <tbody>
        <?php
                foreach($result["chamado"] as $chamado): 
        ?>
            <tr>
                <td><?= $chamado["id"] ?></td>
                <td><?= $chamado["ticket"] ?></td>
                <td><?= $chamado["nome_solicitante"] ?></td>
                <td><?= $chamado["nome_local"] ?></td>
                <td><?= $chamado["status"] ?></td>
                <td><?= $chamado["responsavel"] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php
    endif;
    if (count($result["triagem"]) > 0):
        ?>
            <p class="h5"><i class="fas fa-filter"></i> Triagem</p> <!-- TABELA TRIAGEM -->
            <table id="tblTriagemBr" class="table table-hover table-sm">
                <thead>
                    <th>id</th>
                    <th>ticket</th>
                    <th>nome_solicitante</th>
                    <th>data</th>
                </thead>
                <tbody>
                <?php
                        foreach($result["triagem"] as $triagem): 
                ?>
                    <tr>
                        <td><?= $triagem["id"] ?></td>
                        <td><?= $triagem["ticket"] ?></td>
                        <td><?= $triagem["nome_solicitante"] ?></td>
                        <td><?= $triagem["data"] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php
    endif;
endif;

