       <div class="modal fade" id="modalRegistro" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Nova Interação</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="frmInteracao" method="post">
                        <div id="conteudo_form"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success" id="btnRegistrarInteracao">
                        <i class="fas fa-check"></i> Registrar</button>
                    </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalEquipamentos" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Adicionar Equipamentos</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        
                        <form id="frmEquipamentos" enctype="multipart/form-data"> <!-- INICIO FORM -->
                            
                            <div class="form-group" id="listaPatrimoniosEquip">
                                <label for="listaPatrimoniosEquip">Patrimônio(s)</label>
                                <div id="msgPatrEquip"></div>
                                <textarea placeholder="Insira os números aqui" class="form-control" id="txtPatrimoniosEquip" name="listaPatrimoniosEquip" rows="5"></textarea>
                                <div class="text-right mt-3">
                                    <button type="button" class="btn btn-primary" id="btnVerificaPatrimoniosEquip">
                                    <i class="fas fa-search"></i> Verificar</button>

                                </div>
                                
                            </div>

                            <!-- TABELAS DE PATRIMONIOS -->
                            <div class="row" id="divTabelaInserviveisEquip" style="display: none">
                                <div class="col">
                                <div class="alert alert-danger">Atenção! Existem equipamentos inservíveis na lista!</div>
                                <table class="table table-sm" id="tblInserviveisEquip">
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
                            <div class="row" id="divTabelaChamadosAbertosEquip" style="display: none">
                                <div class="col">
                                <div class="alert alert-warning">Atenção! Os seguintes itens já estão sendo atendidos:</div>
                                <table class="table table-sm" id="tblEquipAbertos">
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
                            <button type="button" class="btn btn-primary btn-sm mb-3" id="btnRemovePatrimoniosEquip" style="display: none">Remover da lista</button>
                            <div class="row" id="divTabelaPatrimoniosEquip" style="display: none">
                                <div class="col">
                                <div class="my-3"><h5 class="d-inline">Lista de patrimônios <button type="button" class="btn btn-primary btn-sm" id="btnAlteraPatrimoniosEquip">Alterar</button></h5></div>
                                <table class="table table-sm" id="tblPatrimoniosEquip">
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
                            

                            <!-- FIM TABELAS PATRIMONIO -->

                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="chkEquipamentos" onclick="$('#listaEquipamentos').toggle()">
                                <label class="form-check-label" for="chkEquipamentos">Equipamentos sem patrimônio</label>
                            </div>

                            <div id="listaEquipamentos" style="display:none">
                                <div class="form-row align-items-center">
                                    <div class="col-auto">
                                    <label class="sr-only" for="inlineFormInput">Núm. de série</label>
                                    <input type="text" class="form-control mb-2" id="txtNumSerieEquip" placeholder="Núm. de série">
                                    </div>
                                    <div class="col-auto">
                                    <label class="sr-only" for="inlineFormInputGroup">Descrição</label>
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" id="txtDescEquip" placeholder="Descrição">
                                    </div>
                                    </div>
                                    <div class="col-auto">
                                    <button type="button" class="btn btn-primary mb-2" id="btnAdcEquip"><i class="fas fa-plus"></i></button>
                                    <button type="button" class="btn btn-primary mb-2" id="btnLimpaEquip"><i class="fas fa-eraser"></i> Limpar lista</button>
                                    </div>
                                </div>
                                
                                <div class="row" id="divTabelaEquipamentos">
                                    <div class="col">
                                        <div id="msgEquip"></div>
                                        
                                            
                                        <table class="table table-sm" id="tblEquipamentos">
                                        <thead>
                                            <tr>
                                                <th>Núm. de série</th>
                                                <th>Descrição</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>                   
                            </div>
                            
                            
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success" id="btnAdicionarEquipamentos">
                            <i class="fas fa-check"></i> Adicionar</button>
                        </div>
                        <input type="hidden" value="<?= $chamado->id_chamado ?>" name="id_chamado">
                    </form> <!-- FIM FORM -->
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalRegistroEntrega" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Registrar entrega</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="frmRegistroEntrega" method="post" enctype="multipart/form-data">
                            <div id="conteudo_form_entrega"></div>
                            
                    </div>
                    <div class="modal-footer">    
                        <button type="submit" style="display: none" id="btnRegistrarEntrega" class="btn btn-success"><i class="fas fa-check"></i><span></span></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
     
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><a href="<?= base_url('painel'); ?>">Painel</a></li>
                <li class="breadcrumb-item" aria-current="page">Ver chamado</li>
            </ol>
        </nav>
        
        <div class="container py-2">

            <h3><?= $chamado->ticket_chamado ?> <small>(#<?= $chamado->id_chamado ?>)</small></h3>
            
            <hr />
            
            <div id="msg"></div>
            
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab" aria-controls="info" aria-selected="true">Informações</a>
                </li>
				<li class="nav-item">
                <a class="nav-link" id="descricao-tab" data-toggle="tab" href="#descricao" role="tab" aria-controls="descricao" aria-selected="false">Descrição</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" id="equip-tab" data-toggle="tab" href="#equip" role="tab" aria-controls="equip" aria-selected="false">Equipamentos</a>
                </li>
                
                <li class="nav-item">
                <a class="nav-link" id="atendimento-tab" data-toggle="tab" href="#atendimento" role="tab" aria-controls="atendimento" aria-selected="false">Atendimento</a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane active" id="info" role="tabpanel" aria-labelledby="info-tab">

                    <form method="post" id="frmEditarChamado" class="mt-3">
                    <input type="hidden" name="id_chamado" value="<?= $chamado->id_chamado ?>">
                        <div class="my-3 text-right" id="botoesChamado">
                            <button id="btnEditarChamado" class="btn btn-info" style="display:none"><i class="fas fa-edit"></i> Editar</button>
                            <button type="submit" class="btn btn-success" hidden><i class="fas fa-check"></i> Salvar</button>
                            <button type="button" id="btnCancelarEdicao" class="btn btn-danger" hidden><i class="fas fa-ban"></i> Cancelar</button>
                            <button id="btnBloquearChamado" class="btn btn-primary" style="display:none"><i class="fas fa-lock"></i> Bloquear</button>
                            <button id="btnDesbloquearChamado" class="btn btn-primary" style="display:none"><i class="fas fa-unlock"></i> Desbloquear</button>     
                            <hr>
                        </div>
                    
                        

                        <div class="row">
                            <div id="alerta" class="col-md-auto"></div>

                        </div>

                        

                        <div class="row">
                            <div class="form-group col-3">
                            <label for="status" >Status</label>
                            <input type="text" class="form-control" name="status" disabled>
                            </div>
                            <div class="form-group col-3">
                            <label for="fila">Fila</label>
                            <input type="text" class="form-control" name="fila" disabled>
                            </div>
                            <div class="form-group col-3">
                            <label for="data_chamado">Data de abertura</label>
                            <input type="text" class="form-control" name="data_chamado" disabled>
                            </div>
                            <div class="form-group col-3">
                            <label for="id_responsavel" class="text-bold">Responsável</label>
                            <select name="id_responsavel" class="form-control" disabled>
                            </select>
                            </div>

                        </div>

                        <div class="row">
                            <div class="form-group col-8">
                            <label for="nome_solicitante">Solicitante</label>
                            <input type="text" class="form-control" name="nome_solicitante" aria-describedby="" placeholder="" disabled>
                            </div>

                            <div class="form-group col-3">
                            <label for="telefone">Telefone</label>
                            <input type="text" class="form-control" name="telefone" aria-describedby="" placeholder=""  disabled>
                        </div>

                        </div> 

                        <div class="row">
                            <div class="form-group col-10">
                                <label for="local">Local</label>
                                <input type="text" class="form-control" name="nome_local" aria-describedby="" placeholder=""  disabled />
                            </div>
                        </div>

                    

                    </form>

                    
                </div>

                <div class="tab-pane" id="equip" role="tabpanel" aria-labelledby="equip-tab">
                    <div class="mb-3 mt-3">
                        <div id="listaPatrimoniosChamado">
                            <table class="table" id="tblPatrimonios">
                                <thead>
                                    <tr>
                                    <th>Patrimônio</th>
                                    <th>Descrição</th>
                                    <th>Etiqueta</th>
                                    <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php if ($chamado->id_fila == 7 || $chamado->id_fila == 3): ?>
                    <div id="listaEquipamentos">
                        <table class="table" id="tblEquipamentosChamado">
                            <thead>
                                <tr>
                                <th>Núm. de série</th>
                                <th>Descrição</th>
                                <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                        <?php endif; ?>
                    </div>

                    
                
                <div class="tab-pane" id="atendimento" role="tabpanel" aria-labelledby="atendimento-tab">
                    <div class="content mt-3">
                        <div id="botoesAtendimento" class="text-right"></div>
                        <div class="mt-4" id="interacoes"></div>        
                    </div>
                </div>

                <div class="tab-pane" id="descricao" role="tabpanel" aria-labelledby="descricao-tab">
                    <div class="col-0 my-3">
                        <div name="descricao" class="border rounded p-2 overflow-auto" style="max-height: 450px;"></div>
                    </div>
                    <?php if ($anexos != NULL): ?>
                    <h4>Anexo</h4>
                    <ul>
                    <?php foreach($anexos as $anexo): ?>
                    <li><a href="<?= base_url('anexos/') . $anexo->nome_anexo ?>" download><?=$anexo->nome_anexo?></a></li>
                    <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                    </div>
                </div>
            
            </div>
        </div>
    
