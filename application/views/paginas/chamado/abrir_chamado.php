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

<nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item active"><a href="<?= base_url('painel'); ?>">Painel</a></li>
        <li class="breadcrumb-item" aria-current="page">Abrir chamado</li>
      </ol>
</nav>
<div class="container py-2">
  <h2>Abertura de chamado</h2>
  <hr />
  <div id="msg"></div>

  <form enctype="multipart/form-data" method="post" id="frmAbrirChamado" class="mb-5">
  
      <div class="row">
        <div class="form-group col-4">
          <label for="fila">Fila</label>
              <select class="form-control" name="id_fila" id="id_fila" onchange="precisaPatrimonio(this.value)">
              <?php foreach ($filas as $fila) : ?>
                  <option value="<?= $fila['id_fila'] ?>"><?= $fila['nome_fila']?></option>
              <?php endforeach; ?>
              
              </select>
          </div>
          
      </div>

      <div class="row">
          <div class="form-group col-3 border rounded ml-3 p-3" id="listaPatrimonios">
              <label for="listaPatrimonios">Patrimônio(s)</label>
              <div id="msgPatr"></div>
              <textarea class="form-control" id="txtPatrimonios" name="listaPatrimonios" rows="5"></textarea>
              <br>
              <button type="button" class="btn btn-primary" id="btnVerificaPatrimonios"><i class="fas fa-search"></i> Verificar</button>
            </div>
          
          <div class="form-group col">
              <label for="nome_solicitante">Solicitante</label>
              <input type="text" class="form-control col-7" name="nome_solicitante" id="listaSolicitantes">
              <br />
              <label for="telefone">Telefone</label>
              <input type="text" maxlength="15" class="form-control col-4" name="telefone" aria-describedby="" placeholder="">
			  <br />
			  <label for="local">Local</label>
			  <input type="text" class="form-control col-8" name="nome_local" id="listaLocais">
        </div>

      </div>
	  
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
      <button type="button" class="btn btn-primary btn-sm mb-3" id="btnRemovePatrimonios" style="display: none">Remover da lista</button>
      <div class="row" id="divTabelaPatrimonios" style="display: none">
        <div class="col">
        <div class="my-3"><h5 class="d-inline">Lista de patrimônios <button type="button" class="btn btn-primary btn-sm" id="btnAlteraPatrimonios">Alterar</button></h5></div>
          <table class="table table-sm" id="tblPatrimonios">
          <thead>
              <tr>
                <th>Patrimônio</th>
                <th>Descrição</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
    <div class="row">
        <div class="form-group col">
              <label for="descricao">Descrição</label>
              <textarea class="form-control" name="descricao" rows="5"></textarea>
            </div>
      </div>
      <div class="row">
        <div class="form-group col" id="divAnexo">
              <label for="anexo">Anexo <input type="checkbox" name="temAnexo" id="chkAnexo"></label>
            </div>
      </div>
      <input type="hidden" id="flagPrecisaPatrimonio" value=""/>
      <input type="hidden" name="id_usuario" value=""/>
      <div class="row">
       <div class="col text-right">
       <button type="submit" id="btnAbrirChamado" class="btn btn-success"><i class="fas fa-check"></i> Abrir Chamado</button>
       </div>

      </div>
      
  </form>
</div>
