<nav aria-label="breadcrumb">
      <ol class="breadcrumb">
         <li class="breadcrumb-item active"><a href="<?= base_url('inservivel'); ?>">Remessas</a></li>
         <li class="breadcrumb-item" aria-current="page">Remessa de inservíveis / #<?= $remessa_inservivel->id_remessa_inservivel ?></li>
      </ol>
   </nav>
<div class="content p-3 mb-5" id="content-inservivel">
   <div class="row mb-2">
      <div class="col">
         <div class="float-right text-right">
            <?php if (!$data_fechamento):?>
               <button type="button" class="btn btn-primary" id="fechar_lista"><i class="fas fa-check"></i> Fechar lista</button>
            <?php endif; ?>
            <?php if (!$data_entrega && $data_fechamento):?>
               <button type="button" data-toggle="modal" data-target="#modalEntrega" class="btn btn-success" id="realizar-entrega" disabled><i class="far fa-check-circle mr-2"></i>Registrar entrega</button>
            <?php endif; ?>
            <?php if ($data_entrega && $remessa_inservivel->id_termo):?>
               <a href="/termos/<?= $remessa_inservivel->nome_termo ?>" target="_blank"><button type="button" class="btn btn-success"><i class="fas fa-file-download"></i> Lista de remessa assinada</button></a>
               <p>Recebido por <strong><?= $remessa_inservivel->nome_recebedor ?></strong></p>
            <?php endif; ?>
         </div>
         <h2 class="align-center">Remessa de inservíveis #<?= $remessa_inservivel->id_remessa_inservivel ?> - 
            <small>
               Status:
               <strong><?=
                  $remessa_inservivel->data_fechamento == null ? "Aberta" : (($remessa_inservivel->data_entrega != null) ? "Entregue" : ($remessa_inservivel->falha_envio == 1 ? "Erro" : "Fechada"));
               ?></strong>
            </small>
         </h2>
         
      </div>
   </div>            
   <table id="painel-inservivel"></table>
   <div id="button-imprimir" class="text-right">
      <?php if (!$data_entrega && $data_fechamento):?>
         <button class="btn btn-primary m-3" id="btn-impressao" disabled><i class="fas fa-print"></i> Imprimir</button>
      <?php endif; ?>
   </div>
</div>
   <!-- Modal -->
   <div class="modal fade" id="modalEntrega" tabindex="-1" role="dialog" aria-labelledby="modalEntregaTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="modalEntregaLongTitle"><i class="fas fa-file-signature"></i> Registrar entrega da remessa</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
               </button>
            </div>

            <div class="modal-body">
               <div class="container">
                  <form method="post" enctype="multipart/form-data" id="frm-remessa-entrega">
                     <p>Todos equipamentos foram entregues no almoxarifado?</p>
                     <div class="custom-control custom-radio custom-control-inline">
                        <div class="form-check mb-3">
                           <input type="radio" value="true" id="rdSim" name="confirma" class="custom-control-input">
                           <label class="custom-control-label mr-3" for="rdSim">Sim</label>
                        </div>
                        <div class="form-check">
                           <input type="radio" value="false" id="rdNao" name="confirma" class="custom-control-input">
                           <label class="custom-control-label" for="rdNao" checked>Não</label>
                        </div>
                     </div>
                     <div id="formEntregaInservivel"></div>
                  </form>
               </div>
            </div>
            <!-- <div class="modal-footer" id="modal-footer-entrega"></div> -->
         </div>
      </div>
   </div>
</div>

<div id="impressao">
   <div class="row text-center">
      <div class="col">
         <img id="img-logo" src="<?= base_url("img/logo_pms.png") ?>" width="70" height="70" class="d-inline-block align-top mr-5 mb-3 " alt="">
      </div>
      <div class="col-8 mt-3">
         <h4>Remessa de inservível #<?= $id_remessa ?></h4>
      </div>
      <div class="col">
         <img id="img-logo" src="<?= base_url("img/logo-cgti-preto.png") ?>" width="70" height="70" class="d-inline-block align-top mb-3" alt="">
      </div>
   </div>

   <div id="tabela-impressao" class="text-center">
      <table class="table table-sm table-responsive-md">
         <thead>
               <tr>
                  <th>Núm. equip.</th>
                  <th>Descrição</th>
                  <th>Local</th>
                  <th>Laudo</th>
               </tr>
         </thead>
         <tbody id="tbody-impressao"></tbody>
      </table>
   </div>

   <div class="row">
      <div class="col-12">
         <div class="conferido">
            <label for="conferido">Conferido por: <?= $nome_usuario ?></label>
         </div>
      </div>
   </div>
   <div class="row">
      <div class="col-6">
         <div class="recebido">
            <label for="recebido">Recebido por:</label>
         </div>
      </div>
      <div class="col-6">
         <div class="data">
            <label for="recebido"><span class="m-5">Data de recebimento:</span><span class="mr-5">/</span>/</label>
         </div>
      </div>
   </div>
</div>