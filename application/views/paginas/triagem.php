<!-- modal
<div class="modal fade show" id="modalDescChamado" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><br><span id="data_chamado"></span></h5>
        
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body"></div>
    </div>
  </div>
</div>

-- fim modal -->

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

<nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item active"><a href="<?= base_url('painel?v=triagem'); ?>">Painel</a></li>
        <li class="breadcrumb-item" aria-current="page">Triagem #<?= $triagem->id_triagem; ?></li>
      </ol>
</nav>





<div class="container py-2">
  <div id="msg"></div>
</div>
<div id="divTriagem" class="container py-2">
<div class="row">
    <div class="col-8">
      <h3><?= $triagem->ticket_triagem; ?></h3> 
    </div>
    <div class="col-4 text-right">
      <button type="button" class="btn btn-warning" id="btnDevolveChamado" data-toggle="modal" data-target="#modalDevolucao"><i class="fas fa-file-upload"></i> Devolver ao OTRS</button>
    </div>
  </div>
  

  <form enctype="multipart/form-data" method="post" id="frmImportarChamado" class="mb-5">
  
      
	  
	  <div class="row">
        <div class="form-group col">
            <div name="descricao_triagem" class="border rounded p-2 overflow-auto" 
            style="max-height: 450px;"></div>
        
			
		</div>
	  
	  </div>
	  
	  <div class="row">
      <div class="form-group w-100 p-3">
      

          <p class="h5">Equipamentos</p>
          <hr>
          <div id="msgPatr"></div>
          <div class="text-right">
            <!-- <input type="checkbox" class="form-check-input align-middle" id="chkSoSelecaoTriagem"> -->
            <!-- <label class="form-check-label" for="chkSoSelecaoTriagem">Somente seleção</label>
            
            <button type="button" class="btn btn-primary" id="btnVerificaPatrimoniosTriagem"><i class="fas fa-search"></i> Busca automática</button> -->
            <!-- <button type="button" class="btn btn-info" id="btnInsManualPatrimoniosTriagem"><i class="fas fa-pencil-alt"></i> Inserção manual</button> -->
            <!-- <button type="button" class="btn btn-secondary" id="btnInsertManualTriagem"><i class="fas fa-pencil-alt"></i> Inserção manual</button> -->
        </div>
        <div class="progress mt-3">
          <div id="pbEquips" class="progress-bar" role="progressbar"></div>
        </div>
      
      </div>
	</div>
    <!--
    <div id="divInsercaoManual">
      <h6>Inserção manual</h6>
      <div class="form-inline">
        <input class="form-control" type="text" placeholder="número" id="txtNumEquip">&nbsp;
        <input class="form-control" type="text" placeholder="descrição" id="txtDescEquip">&nbsp;
        <button id="btnAddNovoEquip" class="btn btn-primary"><i class="fas fa-plus"></i></button>
      </div>
    </div> -->
	  <div class="row" id="divTabelaInserviveis" style="display: none">
        <div class="col">
          <div class="alert alert-danger">Atenção! Existem equipamentos inservíveis na lista!</div>
          <table class="table table-sm" id="tblInserviveis">
            <thead>
              <tr>
                <th>Patrimônio</th>
                <th>Descrição</th>
                <th>Chamado</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
      <div class="row" id="divTabelaChamadosAbertos" style="display: none">
        <div class="col">
          <div class="alert alert-warning">Atenção! Os seguintes itens já estão sendo atendidos:</div>
          <table class="table table-sm" id="tblPatrimoniosAbertos">
            <thead>
              <tr>
                <th>Patrimônio</th>
                <th>Descrição</th>
                <th>Chamado aberto</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
	  <button type="button" class="btn btn-primary btn-sm mb-3" id="btnRemovePatrimoniosTriagem" style="display: none">Fechar</button>
      <div id="tblEquips" class="jsgrid">
      </div>
	  
	    <hr />
	<div class="row">
	  
		<div class="col-4">
		
			<p class="h5">Anexos</p>
			<table id="listaAnexosOTRS" class="table table-sm">
				
			</table>

		</div>
	</div>
  <hr>
  <div class="row">
      <div class="form-group col-4">
        <label for="fila">Fila</label>
            <select class="form-control" name="id_fila" id="id_fila">
      <option value="">Selecione...</option>
      <?php foreach ($filas as $fila) : ?>
                <option value="<?= $fila['id_fila'] ?>"><?= $fila['nome_fila']?></option>
            <?php endforeach; ?>
            
            </select>
        </div>
        
    </div>

    <div class="row">
        
        <div class="form-group col">
            <label for="nome_solicitante">Solicitante</label>
            <input type="text" class="form-control col-7" name="nome_solicitante" id="listaSolicitantes">
            <br />
            <label for="telefone">Telefone</label>
            <input type="text" maxlength="15" class="form-control col-4" name="telefone" aria-describedby="" placeholder="">
            <br />
            <label for="local">Local</label>
            <input type="text" class="form-control col-8" name="nome_local" id="listaLocais" data-toggle="popover">
        </div>

    </div>

  
    <div class="row">
      <input type="hidden" name="id_usuario" value=""/>
      <input type="hidden" name="id_chamado" value=""/>
      <div class="form-group col text-right">
        <button type="submit" id="btnImportarChamado" class="btn btn-success"><i class="fas fa-file-import"></i> Importar Chamado</button>
      </div>
    </div>
  </form>
</div>
