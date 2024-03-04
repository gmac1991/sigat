        <div class="modal fade" id="modalRegistro" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-asterisk"></i> Nova Interação</h5>
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
                        <h5 class="modal-title"><i class="fas fa-file-signature"></i> Registrar Entrega</h5>
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

        <div class="modal fade" id="modalEndereco" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-map-marker-alt"></i> <?= $chamado->nome_local?> <a href="<?= base_url('local') . '/' . $chamado->id_local?>" target="_blank"><i class="fas fa-external-link-alt"></i></a></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h6><?= $chamado->endereco_local?></h6>
                        <?php if($telefones != null) {?>
                            <table class="table table-borderless">
                            <thead>
                                <tr>
                                <th scope="col">Telefone</th>
                                <th scope="col">Setor</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($telefones as $key => $telefone) { ?>
                                <tr>
                                <td><?= $telefone['telefone']?></td>
                                <td><?= $telefone['setor']?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                        <?php }?>
                    </div>
                  
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalIniciarReparo" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-wrench"></i> Iniciar reparo</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                    <form id="frmIniciarReparo">
                        <div class="form-group">
                            <label>Equipamento</label>
                            <select id="listaEquipReparo" class="form-control">     
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Bancada</label>
                            <select id="listaBancadas" class="form-control">   
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">    
                        <button type="submit" id="btnIniciarReparo" class="btn btn-success"><i class="fas fa-check"></i><span></span></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalReparo" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                    
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" id="btnLaudoGarantiaEquip"
                        data-toggle="modal" data-target="#modalLaudoGarantiaEquip" disabled>
                        <i class="fas fa-cloud-upload-alt"></i> Enviar Laudo
                        </button>
                        <button class="btn btn-primary" id="btnGarantiaReparo"
                        data-toggle="modal" data-target="#modalGarantiaReparo" disabled>
                        <i class="fas fa-box"></i> Garantia
                        </button>
                        <button class="btn btn-warning" id="btnLaudoInservivelEquip"
                        data-toggle="modal" data-target="#modalLaudoInservivelEquip" disabled>
                        <i class="fas fa-ban"></i> Inservível
                        </button>
                        <button class="btn btn-success" id="btnEncerrarReparo">
                        <i class="fas fa-check-circle" disabled></i> Encerrar Reparo
                        </button>
                        <button class="btn btn-danger" id="btnJustificativaCancelamento" data-toggle="modal" data-target="#modalJustificativaCancelamento" disabled>
                        <i class="fas fa-times-circle"></i> Cancelar Reparo
                        </button>
                    </div>
                    <div class="container modal-historico">
                        
                        <h5 class="title"><i class="fas fa-scroll"></i> Histórico</h5>
                        <div id="conteudo-historico-modal">
                            
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalJustificativaCancelamento" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-times-circle"></i> Cancelar Reparo</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="input-group mb-3">
                            <textarea id="txtJustificativaCancelamento" class="form-control" placeholder="Escreva o motivo do cancelamento..."></textarea>
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button" id="btnCancelarReparo"><i class="far fa-arrow-alt-circle-right"></i> Enviar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalGarantiaReparo" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-box"></i> Escalonamento para garantia</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="container">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Ticket da garantia:</span>
                                </div>
                                <input type="text" id="txtTicketGarantia" class="form-control" placeholder="Informe o ticket devolvido pela empresa... ">
                            </div>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Justificativa:</span>
                                </div>
                                <textarea id="txtJustificativaGarantia" class="form-control" placeholder="Escreva o motivo do escalonamento para garantia..."></textarea>
                            </div>
                          
                            <div class="text-right">
                                <button class="btn btn-primary mt-3" id="btnGarantiaEquip">
                                    <i class="fas fa-check"></i> Registrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalLaudoGarantiaEquip" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-file-upload"></i> Garantia do Equipamento</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="container">
                            <form method="post" enctype="multipart/form-data" id="frmLaudoGarantiaEquip">
                                <p>Laudo garantia: </p>
                                <input type="file" id="fileLaudo" name="laudoGarantia" placeholder="Ticket#00001" accept="application/pdf">
                                <br>
                                <button class="btn btn-primary mt-3" id="#" type="submit">
                                <i class="fas fa-upload"></i> Enviar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalLaudoInservivelEquip" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="slctListaModeloLaudo">Modelos de texto:</label>
                            <select class="form-control" id="slctListaModeloLaudo">
                                <option value="">Escolha...</option>
                            </select>
                        </div>
                   
                        <div class="form-group">
                            <label for="txtLaudoInservivelEquip">Laudo técnico:</label>
                            <textarea rows="10" id="txtLaudoInservivelEquip" class="form-control"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-primary" type="button" id="btnInservivelEquip">
                            <i class="far fa-arrow-alt-circle-right"></i> Enviar</button>
                    </div>
                </div>
            </div>
        </div>
     
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active"><a href="<?= base_url('painel'); ?>">Painel</a></li>
                <li class="breadcrumb-item" aria-current="page">Ver chamado</li>
                <li class="breadcrumb-item" aria-current="page">#<?= $chamado->id_chamado ?></li>
            </ol>
        </nav>
        
        <div class="container py-2">
            <div>
                <div class="float-right">
                    <div class="form-check d-inline" id="divPrioridade">
                    <?php if($usuario->autorizacao_usuario > 3): ?>
                    <input class="form-check-input mt-2" type="checkbox" value="" id_chamado="<?= $chamado->id_chamado ?>" id="chkPrioridade" <?= $chamado->prioridade_chamado == 1 ? 'checked' : '' ?>>
                    <label class="form-check-label" style="font-size: 0.9rem;">
                        Prioridade
                    </label>
                    <?php endif;?>
                    </div>
                    <button id="btnBloquearChamado" class="btn btn-sm btn-primary" style="display:none"><i class="fas fa-lock"></i> Bloquear</button>
                    <button id="btnDesbloquearChamado" class="btn btn-sm btn-primary" style="display:none"><i class="fas fa-unlock"></i> Desbloquear</button>                </div>
                <h3 id="headerChamado">
                <i class="<?= $icone ?>"></i>
                
                <?= $chamado->ticket_chamado ?> 
                    <a style="font-size:medium" target="_blank" 
                    href="<?= $this->config->item('url_ticketsys') ?>index.pl?Action=AgentTicketZoom;TicketID=<?= $chamado->id_ticket_chamado?>">
                    <i class="fas fa-external-link-alt"></i></a> <small>(#<?= $chamado->id_chamado ?>)&nbsp;
                    <button class="btn btn-secondary btn-sm d-inline" id="btnImprimirChamado" data-chamado="<?= $chamado->id_chamado ?>"><i class="fas fa-print"></i></button>
                    <span class="text-warning" id="estrela_prioridade" style="display:<?= $chamado->prioridade_chamado == 1 ? 'inline' : 'none' ?>"><i class="fas fa-star"></i></span>
                    <div class="spinner-border spinner-border-sm" role="status" id="spnStatusChamado" style="display: none;">
                    <span class="sr-only">Loading...</span></div></small>
                    
                    
                    
                </h3>
              
			   
					
                  
            </div>
            
            
            <hr />
            
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab" aria-controls="info" aria-selected="true"><i class="fas fa-info-circle"></i> Informações</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" id="comunicacao-tab" data-toggle="tab" href="#comunicacao" role="tab" aria-controls="descricao" aria-selected="false"><i class="fas fa-exchange-alt"></i></i> Comunicação</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" id="atendimento-tab" data-toggle="tab" href="#atendimento" role="tab" aria-controls="atendimento" aria-selected="false"><i class="far fa-hand-pointer"></i> Atendimento</a>
                </li>
                
                <li class="nav-item">
                <a class="nav-link" id="equip-tab" data-toggle="tab" href="#equip" role="tab" aria-controls="equip" aria-selected="false"><i class="fas fa-desktop"></i> Equipamentos</a>
                <li class="nav-item">
                <a class="nav-link" id="infraestrutura-tab" data-toggle="tab" href="#infra" role="tab" aria-controls="servicos" aria-selected="false"><i class="fas fa-broadcast-tower"></i></i> Infraestrutura</a>
                </li>
				
                <li class="nav-item">
                <a class="nav-link" id="reparos-tab" data-toggle="tab" href="#reparos" role="tab" aria-controls="descricao" aria-selected="false"><i class="fas fa-wrench"></i> Reparos</a>
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
                                <label for="local">Local <a href="#" id="btnVerEndereco" data-toggle="modal" data-chamado="" data-target="#modalEndereco" class="badge badge-danger"><i class="fas fa-map-marker-alt"></i></a></label>
                                <input type="text" class="form-control" name="nome_local" aria-describedby="" placeholder=""  disabled />
                            </div>
                            <div class="form-group col">
                                <label for="complemento">Complemento</label>
                                <input type="text" class="form-control" name="complemento" aria-describedby="" placeholder=""  disabled />
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="form-group col">
                                <p class="h5"><i class="fas fa-paperclip"></i> Anexos</p>
                                <hr />
                                <div id="tblAnexosChamado" class="jsgrid"></div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="tab-pane" id="atendimento" role="tabpanel" aria-labelledby="atendimento-tab">
                    <div class="content mt-3">
                        <div id="botoesAtendimento" class="text-right"></div>
                        <div class="mt-4" id="interacoes"></div>        
                    </div>
                </div>
                <div class="tab-pane" id="comunicacao" role="tabpanel" aria-labelledby="comunicacao-tab">
                    <div class="col-0 my-3 mb-5">
                        <div class="accordion" id="accordionArticles">
                            <div class="d-flex justify-content-end">
                                <!-- 
                                    <button class="btn btn-primary mb-3 ms-auto" type="button" data-toggle="modal" data-target="#modalEmail" id="btnModalEmail"><i class="fa fa-envelope" aria-hidden="true"></i> Enviar e-mail</button>
                                 -->
                                <button class="btn btn-primary mb-3 ms-auto" type="button" disabled><i class="fa fa-envelope" aria-hidden="true"></i> Enviar e-mail</button>
                            </div>
                            <?php $count = count($ticket['t_articles']); ?>
                        <?php for($i = 0; $i < $count; $i++): ?>
                        <?php $sigat = strripos($ticket['t_articles'][$i]->a_from, 'SIGAT'); $otobo = strripos($ticket['t_articles'][$i]->a_from, 'OTOBO');?>
                        <div class="card">
                            <div class="card-header">
                                <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#a_<?= $ticket['t_articles'][$i]->article_id ?>">
                                <i class="fas fa-user-circle"></i>
                                <?php if($i < $chamado->email_nao_lido_chamado && $sigat === false&& $otobo === false) { ?>
                                    <strong>
                                        <?= preg_replace("/\s{1}<.+>/","",$ticket['t_articles'][$i]->a_from,1); ?>
                                    </strong>
                                <?php }?>
                                <?php if($i >= $chamado->email_nao_lido_chamado || $sigat > 0 || $otobo > 0) { ?>
                                        <?= preg_replace("/\s{1}<.+>/","",$ticket['t_articles'][$i]->a_from,1); ?>
                                <?php }?>
                                
                                
                                <?php
                                
                                
                                $date = date_create($ticket['t_articles'][$i]->create_time); 
                                $interval = DateInterval::createFromDateString('3 hours');
                                
                                ?>
                                <div class="float-right"><i class="fas fa-calendar-alt"></i> <?= date_format(date_sub($date,$interval),"d/m/y - H:i:s"); ?></div>
                                </button>
                                </h2>
                            </div>
                            <div id="a_<?= $ticket['t_articles'][$i]->article_id ?>" class="collapse <?php echo $i == 0 ? 'show' : '' ?>" data-parent="#accordionArticles">
                                <div class="card-body">
                                    <pre><?= htmlspecialchars($ticket['t_articles'][$i]->a_body, ENT_QUOTES, 'UTF-8');?></pre>
                                
                                </div>
                            </div>
                        </div>
                        <?php endfor; ?>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="equip" role="tabpanel" aria-labelledby="equip-tab">
                    <div class="content mt-3 mb-5">
                        <div id="tblEquipamentosChamado" class="jsgrid"></div>   
                    </div>
                </div>
               
                <div class="tab-pane" id="infra" role="tabpanel" aria-labelledby="infraestrutura-tab">
                    <div class="content mt-3 mb-5">
                        <div id="tblServicosInfraChamado" class="jsgrid"></div>   
                    </div>
                </div>

                <div class="tab-pane" id="reparos" role="tabpanel" aria-labelledby="reparos-tab">
                    <div class="content mt-3 mb-5">
                        <div id="botoesAtendimentoReparo" class="text-right"></div>  
                        <table id="tblReparosChamado" class="table my-3">
                            <thead>
                                <tr>
                                    <th scope="col">Equipamento</th>
                                    <th scope="col">Bancada</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Iniciado</th>
                                    <th scope="col">Finalizado</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>Lista vazia.</td></tr>
                            </toby>
                        </table>
                    </div>
                </div>
                
               
                
                <div class="tab-pane" id="historico" role="tabpanel" aria-labelledby="historico-tab">
                    <div class="col-0 my-3 mb-5" id="historico">
                        
                        
                    </div>
                </div>
            </div>
        </div>

<!-- Modal -->
<div class="modal fade" id="modalEmail" tabindex="-1" role="dialog" aria-labelledby="modalEmailLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEmailLabel"><i class="fas fa-envelope"></i> Novo e-mail</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="frmEmail" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div id="conteudo_form">
                        <!-- <div class="row mb-3">
                            <div class="col">
                                <h5>Titulo: </h5>
                                <input type="text" class="form-control" id="titleEmail" disabled>
                            </div>
                        </div> -->
                        <div class="row mb-3">
                            <div class="col">
                                <textarea name="txtEmail"></textarea>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <h5><i class="fas fa-paperclip"></i> Anexos</h5>
                                    <input type="file" name="anexoEmail[]" id="fileAnexosEmail" aria-describedby="inputGroupFileAddon01" multiple>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-times-circle"></i> Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnEnviarEmail"><i class="fas fa-arrow-circle-right"></i> Enviar</button>
                </div>
            </form>
        </div>
    </div>
</div>