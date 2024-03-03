<?php



if (!isset($result["equip"])  && count($result["chamado"]) == 0 ): 
    echo "Sem resultados." ;
else :
    if (count($result["equip"]) > 0):
?>
        <p class="h5"><i class="fas fa-desktop"></i> Equipamentos</p> <!-- TABELA EQUIPS -->
        <table id="tblEquipsBr" class="table table-hover table-sm">
            <thead>
                <th>Núm.</th>
                <th>Descrição</th>
                <th>Último lacre</th>
            </thead>
            <tbody>

<?php
        if (count($result["equip"]) == 1):
?>
        <tr>
            <td><?= $result["equip"][0]["num_equipamento"] ?></td>
            <td><?= $result["equip"][0]["descricao_equipamento"] ?></td>
            <td><?= $result["equip"][0]["tag_equipamento"] ?></td>
            
        </tr>
<?php
        else:
    
            foreach($result["equip"] as $equip):
?>
            <tr>
            <td><?= $equip["num_equipamento"] ?></td>
            <td><?= $equip["descricao_equipamento"] ?></td>
            <td><?= $equip["tag_equipamento"] ?></td>
            </tr>
<?php 
            
            endforeach;

        endif;
    endif;
?>
    
    </tbody>
</table>
<?php
    
    if (isset($result["chamados_equip"])):
?>

<p><i class="fas fa-history"></i> <strong>Últimos chamados de <?= $result["equip"][0]["num_equipamento"] ?></strong></p> <!-- TABELA CHAMADOS EQUIP -->
<table id="tblChamadosEquipBr" class="table table-hover table-sm">
    <thead>
        <th>Chamado</th>
        <th>Solicitante</th>
        <th>Local</th>
        <th>Status do equip.</th>
        <th>Status do chamado</th>
        <th>Última alteração</th>
    </thead>
    <tbody>
  
    <?php
                foreach($result["chamados_equip"] as $chamado): 
        ?>
            <tr>
                <td><?= $chamado["id_chamado_equipamento"] ?></td>
                <td><?= $chamado["nome_solicitante_chamado"] ?></td>
                <td><?= $chamado["nome_local"] ?></td>
                <td><?= $chamado["status_equipamento_chamado"] ?></td>
                <td><?= $chamado["status_chamado"] ?></td>
                <td><?= $chamado["data_ultima_alteracao"] ?></td>
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

