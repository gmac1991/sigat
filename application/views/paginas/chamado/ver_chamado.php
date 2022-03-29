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
            <div>
                <div class="float-right">
                    <button id="btnBloquearChamado" class="btn btn-sm btn-primary" style="display:none"><i class="fas fa-lock"></i> Bloquear</button>
                    <button id="btnDesbloquearChamado" class="btn btn-sm btn-primary" style="display:none"><i class="fas fa-unlock"></i> Desbloquear</button>
                </div>
                 
                <h3><?= $chamado->ticket_chamado ?> <small>(#<?= $chamado->id_chamado ?>)&nbsp;<div class="spinner-border spinner-border-sm" role="status" id="spnStatusChamado" style="display: none;"><span class="sr-only">Loading...</span></div></small></h3>
                
                  
            </div>
            
            
            <hr />
            
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab" aria-controls="info" aria-selected="true"><i class="fas fa-info-circle"></i> Informações</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" id="atendimento-tab" data-toggle="tab" href="#atendimento" role="tab" aria-controls="atendimento" aria-selected="false"><i class="far fa-hand-pointer"></i> Atendimento</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" id="equip-tab" data-toggle="tab" href="#equip" role="tab" aria-controls="equip" aria-selected="false"><i class="fas fa-desktop"></i> Equipamentos</a>
                </li>
				<li class="nav-item">
                <a class="nav-link" id="descricao-tab" data-toggle="tab" href="#descricao" role="tab" aria-controls="descricao" aria-selected="false"><i class="fas fa-scroll"></i> Descrição completa</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" id="historico-tab" data-toggle="tab" href="#historico" role="tab" aria-controls="historico" aria-selected="false"><i class="fas fa-history"></i> Histórico</a>
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
                        </div>
                        <div class="row">
                            <div id="alerta" class="col-md-auto"></div>

                        </div>
                        <div class="row">
                            <div class="form-group col">
                                <label for="resumo">Resumo</label>
                                <input type="text" class="form-control" name="resumo" aria-describedby="" placeholder=""  disabled />
                            </div>
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
                            <div class="form-group col">
                                <label for="telefone">Telefone <a href="#" id="sipLink" class="badge badge-info"><i class="fas fa-phone"></i></a></label>
                                <input type="text" class="form-control" name="telefone" aria-describedby="" placeholder=""  disabled>
                                
                            </div>
                        </div> 
                        <div class="row">
                            <div class="form-group col-7">
                                <label for="local">Local</label>
                                <input type="text" class="form-control" name="nome_local" aria-describedby="" placeholder=""  disabled />
                            </div>
                            <div class="form-group col">
                                <label for="complemento">Complemento</label>
                                <input type="text" class="form-control" name="complemento" aria-describedby="" placeholder=""  disabled />
                            </div>
                        </div>
                    </form>
                </div>
                <div class="tab-pane" id="equip" role="tabpanel" aria-labelledby="equip-tab">
                    <div class="content mt-3">
                        <div id="tblEquipamentosChamado" class="jsgrid"></div>   
                    </div>
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
                </div>
                <div class="tab-pane" id="historico" role="tabpanel" aria-labelledby="historico-tab">
                    <div class="col-0 my-3" id="historico">
                        
                        
                    </div>
                </div>
            </div>
        </div>
    
