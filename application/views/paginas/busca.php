<?php

$n = count($result["equip"]) + count($result["chamado"]);

if ($n < 1): echo "Sem resultados." ;
else :
    if (count($result["equip"]) > 0):
?>
<p class="h5"><i class="fas fa-desktop"></i> Equipamentos</p> <!-- TABELA EQUIPS -->
<table id="tblEquipsBr" class="table table-hover table-sm">
    <thead>
        <th>ID</th>
        <th>Descrição</th>
        <th>Último lacre</th>
        <th>Núm. chamado</th>
        <th>Status</th>
    </thead>
    <tbody>
    <?php
            foreach($result["equip"] as $equip): 
    ?>
        <tr>
            <td><?= $equip["num_equip"] ?></td>
            <td><?= $equip["desc_equip"] ?></td>
            <td><?= $equip["tag_equip"] ?></td>
            <td><?= $equip["chamado_equip"] ?> <i class="fas fa-external-link-alt"></i></td>
            <td><?= $equip["status_equip"] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
    endif;

    if (count($result["chamado"]) > 0):
?>
    <p class="h5"><i class="fas fa-headset"></i> Chamados</p> <!-- TABELA CHAMADOS -->
    <table id="tblChamadosBr"class="table table-hover table-sm">
        <thead>
            <th>ID</th>
            <th>Ticket</th>
            <th>Solicitante</th>
            <th>Local</th>
            <th>Status</th>
            <th>Responsável</th>
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
endif;

