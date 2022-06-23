
<!-- modal Lote -->
<div class="modal fade show" id="modalLote" tabindex="-1" role="dialog" aria-hidden="true">
     <div class="modal-dialog" role="document">
       <div class="modal-content">
         <div class="modal-header">
           <h5 class="modal-title"><i class="fas fa-list"></i> Inserção em lote</h5>
           
           <button type="button" class="close" data-dismiss="modal" aria-label="Close">
             <span aria-hidden="true">&times;</span>
           </button>
         </div>
         <div class="modal-body">
            <p><strong>Modo:</strong></p>

            <div class="form-check form-check-inline">
               <input class="form-check-input" type="radio" name="inlineRadioOptions" id="radLoteLista" checked>
               <label class="form-check-label" for="radLoteLista">Lista</label>
            </div>
            <div class="form-check form-check-inline">
               <input class="form-check-input" type="radio" name="inlineRadioOptions" id="radLoteFaixa">
               <label class="form-check-label" for="radLoteFaixa">Faixa</label>
            </div>
            <div class="form-group" id="divListaLote">
               <label for="txtListaLote"></label>
               <textarea class="form-control" id="txtListaLote" rows="10" placeholder="um item por linha..."></textarea>
            </div>
            <div class="form-group mt-3" id="divFaixaLote" style="display:none">
               <div class="input-group mb-3">
                  <div class="input-group-prepend">
                  <span class="input-group-text">Início</span>
                  </div>
                  <input type="text" class="form-control" id="txtInicioFaixaLote">
                  <div class="input-group-prepend">
                  <span class="input-group-text">Fim</span>
                  </div>
                  <input type="text" class="form-control" id="txtFimFaixaLote">
               </div>
            </div>
            <div class="progress my-3"><div id="pbLote" class="progress-bar" role="progressbar"></div></div>
         </div>
         <div class="modal-footer">
            
            <button type="button" class="btn btn-primary" id="btnInsereLote"><i class="fa fa-check"></i> Inserir</button>
         </div>
       </div>
     </div>
   </div>
<!-- fim modal -->  

<!-- modal Ocorrencias -->
   <div class="modal fade show" id="modalOcorrencias" tabindex="-1" role="dialog" aria-hidden="true">
     <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
         <div class="modal-header">
           <h5 class="modal-title"><i class="fas fa-exclamation-circle"></i> Ocorrências</h5>
           
           <button type="button" class="close" data-dismiss="modal" aria-label="Close">
             <span aria-hidden="true">&times;</span>
           </button>
         </div>
         <div class="modal-body">
         <p><strong>Atenção!</strong> Existem na lista equipamentos em atendimento ou inservíveis!</p>
         <div id="tblOcorrencias"></div>

         </div>
       </div>
     </div>
   </div>
<!-- fim modal -->  
  

<!-- modal Devolucao -->
   <div class="modal fade" id="modalDevolucao" tabindex="-1" role="dialog">
   <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-file-upload"></i> Devolução ao OTRS</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
         </div>
         <div class="modal-body">
            <form id="frmDevolveChamado">
               <div class="form-group mb-2">
                  <textarea class="form-control" id="txtDescDevo" placeholder="Descreva o motivo da devolução"></textarea>
               </div>
               <div class="text-right">
                  <button type="submit" class="btn btn-warning mb-2">Devolver</button>
               </div>
            </form>
         </div>
      </div>
   </div>
</div>

<!-- fim modal -->


<nav aria-label="breadcrumb">
   <ol class="breadcrumb">
      <li class="breadcrumb-item active"><a href="<?= base_url('painel?v=triagem'); ?>">Painel</a></li>
      <li class="breadcrumb-item" aria-current="page">Triagem #<?= $t_info->id; ?></li>
   </ol>
</nav>
<div class="container py-2">
   <div id="msg"></div>
</div>
<div id="divTriagem" class="container py-2">
   <div class="row">
      <div class="col-8">
         <h3 >Ticket#<?= $t_info->tn; ?> <a style="font-size:medium" target="_blank" href="<?= $this->config->item('url_ticketsys') ?>index.pl?Action=AgentTicketZoom;TicketID=<?= $t_info->id ?>"><i class="fas fa-external-link-alt"></i></a></h3>
      </div>
      <div class="col-4 text-right">
         <button type="button" class="btn btn-warning" id="btnDevolveChamado" data-toggle="modal" data-target="#modalDevolucao"><i class="fas fa-file-upload"></i> Devolver ao OTRS</button>
      </div>
   </div>
   <hr id="header_triagem" />
   <form enctype="multipart/form-data" method="post" id="frmImportarChamado" class="mb-5">
      <div class="row">
         <div class="form-group col">
            <div class="accordion" id="accordionArticles">
               <?php $count = count($t_articles); ?>
               <?php for($i = 0; $i < $count; $i++): ?>
               <div class="card">
                  <div class="card-header">
                     <h2 class="mb-0">
                     <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#a_<?= $t_articles[$i]->article_id ?>">
                     <?= preg_replace("/\s{1}<.+>/","",$t_articles[$i]->a_from,1); ?> 
                       <?php $date = date_create($t_articles[$i]->create_time); ?>
                       <div class="float-right"><?= date_format($date,"d/m/y - H:i:s",); ?></div>
                     </button>
                     </h2>
                  </div>
                  <div id="a_<?= $t_articles[$i]->article_id ?>" class="collapse <?php echo $i == 0 ? 'show' : '' ?>" data-parent="#accordionArticles">
                     <div class="card-body">
                        <pre><?= $t_articles[$i]->a_body ?></pre>
                     
                     </div>
                  </div>
               </div>
               <?php endfor; ?>
               </div>
         </div>
         
      </div>
      <div class="row">
         <div class="form-group col">
            <p class="h5"><i class="fas fa-desktop"></i> Equipamentos</p>
            <hr>
            <div class="progress my-3"><div id="pbEquips" class="progress-bar" role="progressbar"></div></div>
            <div id="tblEquips" class="jsgrid"></div>
            <div class="text-right my-3">
               <button id="btnValidaEquip" type="button" class="btn btn-success" disabled><i class="fa fa-check"></i> Confirmar</button>
               <button type="button" id="btnAlteraEquip" class="btn btn-info" disabled><i class="fas fa-pencil-alt"></i> Alterar</button>
               <button type="button" id="btnLoteEquip" class="btn btn-info" disabled><i class="fas fa-list"></i> Lote</button>
            </div>
         </div>
      </div>
      
      <div class="row">
         <div class="form-group col">
            <p class="h5"><i class="fas fa-paperclip"></i> Anexos</p>
            <hr />
            <div id="tblAnexos" class="jsgrid"></div>
         </div>
      </div>
      
  
      <div class="row" id="linhaInfoTriagem">
         <div class="form-group col">
            <p class="h5"><i class="fas fa-info-circle"></i> Informações</p>
            <hr>
            <label for="resumo_solicitacao">Resumo da solicitação</label>
            <input type="text" class="form-control col-7" name="resumo_solicitacao" id="listaResumos">
            <br />
            <label for="nome_solicitante">Solicitante</label>
            <input type="text" class="form-control col-5" name="nome_solicitante" id="listaSolicitantes" value="<?= preg_replace("/\s{1}<.+>/","",$t_info->a_from,1); ?> ">
            <br />
            <label for="telefone">Telefone</label>
            <input type="text" maxlength="15" class="form-control col-4" name="telefone" aria-describedby="" placeholder="">
            <br />
            <label for="local">Local</label>
            <input type="text" class="form-control col-8" name="nome_local" id="listaLocais" data-toggle="popover">
            <br />
            <label for="comp_local">Complemento</label>
            <input type="text" class="form-control col-4" name="comp_local" id="listaComplementos">
         </div>
      </div>
      <div class="row">
        
         <div class="form-group col text-right">
            <button type="submit" id="btnImportarChamado" class="btn btn-success"><i class="fas fa-file-import"></i> Importar Chamado</button>
         </div>
      </div>
   </form>
</div>

