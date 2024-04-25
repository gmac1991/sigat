<nav aria-label="breadcrumb">
   <ol class="breadcrumb">
      <li class="breadcrumb-item active"><a href="<?= base_url('admin'); ?>">Administração</a></li>
      <li class="breadcrumb-item" aria-current="page">Local</li
      ><li class="breadcrumb-item" aria-current="page"><?= $local['nome_local']; ?></li>
   </ol>
</nav>



<div class="container py-2">

   <div class="float-right">
      <div class="form-check d-inline">
         <input id="chkLocal" class="form-check-input mt-2" id_local="<?= $local['id_local'] ?>" type="checkbox" value=""  <?= $local['status_local'] == 1 ? 'checked' : '' ?> <?php if($usuario->autorizacao_usuario < 3) echo 'disabled' ?>>
         <label class="form-check-label" style="font-size: 0.9rem;">
               Ativo
         </label>
      </div>
   </div>
   <h3><i class="fas fa-home"></i> <?= $local['nome_local']?></h3>



   <ul class="nav nav-tabs" id="myTab" role="tablist">
   <li class="nav-item">
         <a class="nav-link <?php if ($tel != true) echo 'active'?>" id="info-tab" data-toggle="tab" href="#info" role="tab" aria-controls="info" aria-selected="true"><i class="fas fa-info-circle"></i> Informações</a>
   </li>
   <li class="nav-item">
         <a class="nav-link <?php if ($tel == true) echo 'active'?>" id="telefone-tab" data-toggle="tab" href="#telefone" role="tab" aria-controls="telefone" aria-selected="true"><i class="fas fa-phone"></i> Telefones</a>
   </li>
</ul>

   <div class="tab-content mb-5">

      <div class="tab-pane <?php if ($tel != true) echo 'active'?>" id="info" role="tabpanel" aria-labelledby="info-tab">

         <form method="post" id="frmEditarLocal" class="mt-3">
            <input type="hidden" name="id_local" value="<?= $local['id_local']?>">
            <input type="hidden" name="status_local" value="<?= $local['status_local']?>">
            <div class="my-3 text-right" id="botoesLocal">
               <button type="button" id="btnEditarLocal" class="btn btn-info" <?php if($usuario->autorizacao_usuario < 3) echo 'style="display:none"'?>><i class="fas fa-edit"></i> Editar</button>
               <button id="btnSalvarLocal" type="submit" class="btn btn-success"><i class="fas fa-check"></i> Salvar</button>
               <button type="button" id="btnCancelarEdicaoLocal" type="button"  class="btn btn-danger" hidden><i class="fas fa-ban"></i> Cancelar</button>
            </div>
            <div class="row">
               <div id="alerta" class="col-md-auto"></div>

            </div>
            <div class="form-group col-6 mt-4">
               <h5>
                  <input class="form-check-input" type="checkbox" name="infovia" <?php if($local['infovia'] == true) echo 'checked'?> disabled>
                  <label class="form-check-label" for="infovia">
                  <i class="fas fa-network-wired"></i>
                     Acessa a Infovia
                  </label>
               </h5>
            </div>
            <div class="row">
               <div class="form-group col">
                  <label for="nome_local">Local</label>
                  <input type="text" class="form-control" name="nome_local" id="nome_local" value="<?= $local['nome_local']?>" disabled />
               </div>
            </div>
            <div class="row">
               <div class="form-group col-6">
                  <label for="secretaria_local" >Secretaria</label>
                  <select name="secretaria_local" id="secretaria_local" class="form-control" disabled>
                        <?php foreach ($secretarias as $secretaria) { ?>
                           <?php if ($secretaria['id_secretaria'] == $local['secretaria_local']) { ?>
                              <option value="<?= $secretaria['id_secretaria']?>" selected><?= $secretaria['nome_secretaria']?></option>
                           <?php } else { ?>
                              <option value="<?= $secretaria['id_secretaria']?>"><?= $secretaria['nome_secretaria'] ?></option>
                           <?php } ?>
                       <?php } ?>
                  </select>
            </div>
            <div class="form-group col-6">
               <label for="endereco_local">Endereço</label>
               <input type="text" class="form-control" name="endereco_local" id="endereco_local" value="<?= $local['endereco_local'] ?>" disabled>
            </div>
            
            <div class="form-group col-6">
                  <label for="regiao_local" class="text-bold">Região</label>
                  <select name="regiao_local" id="regiao_local" class="form-control" disabled>
                        <?php foreach ($regioes as $regiao) { ?>
                           <?php if ($regiao == $local['regiao_local']) { ?>
                              <option value="<?= $local['regiao_local']?>" selected><?= $local['regiao_local']?></option>
                           <?php } else { ?>
                              <option value="<?= $regiao?>"><?= $regiao ?></option>
                           <?php } ?>
                       <?php } ?>
                  </select>
            </div>
         </form>
      </div>
   </div>

   <div class="tab-pane <?php if ($tel == true) echo 'active'?>" id="telefone" role="tabpanel" aria-labelledby="chamados-tab">
      <input type="hidden" id="IdLocal" value="<?= $id?>"/>   
      <table id="tabela_telefones" class="table table-borderless mt-4">
            <thead>
               <tr>
                  <th>Telefone</th>
                  <th>Setor</th>
                  <?php if($usuario->autorizacao_usuario > 3) { ?>
                     <th scope="col">Editar</th>
                  <?php } ?>
               </tr>
            </thead>
            <tbody>
            <?php if(!empty($telefones)) { ?>   
               <?php foreach($telefones as $valor => $telefone) { ?>
               <tr>
                  <td id="tel<?= $telefone['id']?>"><?= $telefone['telefone']?></td>
                  <td id="set<?= $telefone['id']?>"><?= $telefone['setor']?></td>
                  <?php if($usuario->autorizacao_usuario > 3) { ?>
                  <td id="edit<?= $telefone['id']?>">
                  <button type="button" onclick="editarTelefone(<?= $telefone['id']?>)" class="btn btn-info mr-2 mb-2"><i class="fas fa-edit"></i> Editar</button>
                  <button type="button" onclick="excluirTelefone(<?= $telefone['id']?>)" class="btn btn-danger mr-2 mb-2"><i class="fas fa-trash"></i> Excluir</button>
                  </td>
                  <?php } ?>
               </tr>
               <?php }?>
            <?php } ?>
         </tbody>
      </table>
      
      <?php if($usuario->autorizacao_usuario > 3) { ?>
      <button type="button" id="addTelLocal" class="btn btn-info"><i class="fas fa-plus"></i> Adicionar</button>
      <?php } ?>
   </div> 

</div>
