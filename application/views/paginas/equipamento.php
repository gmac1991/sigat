<nav aria-label="breadcrumb">
   <ol class="breadcrumb">
      <li class="breadcrumb-item active"><a href="<?= base_url('painel'); ?>">Painel</a></li>
      <li class="breadcrumb-item" aria-current="page">Equipamento <?= $id; ?></li>
   </ol>
</nav>
<div class="container py-2">
<div>
                <div class="text-center">
                    <h4 id="txtEquipamento"><i class="fas fa-microchip"></i> 
                    <?php echo $id . " - ". $descricao ?></h4>           
                </div>
                 
                

                <hr />

            <ul class="nav nav-tabs mb-5" id="myTab" role="tablist">
                <li class="nav-item">
                <a class="nav-link active" id="descricao-tab" data-toggle="tab" href="#info" role="tab" aria-controls="info" aria-selected="true"><i class="fas fa-info-circle"></i> Informações</a>
                </li>
                <?php if($bg_info == true) { ?>
                <li class="nav-item">
                <a class="nav-link" id="acessos-tab" data-toggle="tab" href="#acessos" role="tab" aria-controls="atendimento" aria-selected="false"><i class="fas fa-users"></i> Últimos acessos</a>
                </li>
                <?php } ?>
                <?php if(!empty($dados_equipamento)) { ?>
                <li class="nav-item">
                <a class="nav-link" id="chamados-tab" data-toggle="tab" href="#chamados" role="tab" aria-controls="atendimento" aria-selected="false"><i class="fas fa-history"></i> Últimos chamados</a>
                </li>
                <?php } ?>
            </ul>
            
            <div class="tab-content mb-5">
                
                <div class="tab-pane active" id="info" role="tabpanel" aria-labelledby="descricao-tab">

                    <div class="card">
                        
                        <div class="card-body">
                        <?php if($bg_info == true) { ?>
                        

                            <button type="button" class="btn btn-primary float-right" id="btnPingEquipamento">
                            <i class="fas fa-wifi"></i>     
                            Ping</button>
                           
                            <p class="card-text">
                                <strong>Processador: </strong>
                                <?php echo $info_equipamento['CPU']?>
                                <br/>
                                <strong>Endereço IP: </strong>
                                <?php echo $info_equipamento['IP_2']?>
                                <br/>
                                <strong>Acessado por último por: </strong>
                                <?php echo $info_equipamento['User_Name']?>
                                <br/>
                                <strong>Ligado pela última vez em: </strong>
                                <?php echo date("d/m/Y - H:i:s", strtotime($info_equipamento['Time_Stamp']));?>
                                <br/>
                                <strong>Memória RAM: </strong>
                                <?php echo $info_equipamento['Memory']?>
                                <br/>
                                <strong>Sistema Operacional: </strong><?php echo $info_equipamento['OS_Version']?>
                                <br/>
                                <h5>Armazenamento</h5>
                                <?php foreach($armazenamento as $unidade) { ?>
                                <strong><?php echo $unidade['unidade']?>: </strong>
                                <?php echo $unidade['espaco_total']?>
                                <br/>
                                <strong>Espaço livre: </strong> <?php echo $unidade['livre']?>
                                <br/>
                            </p>
                            <div class="progress">
                                <div class="progress-bar <?php if($unidade['percentagem'] <= 60) echo 'bg-success'?><?php if($unidade['percentagem'] > 60 && $unidade['percentagem'] <= 85) echo 'bg-warning'?><?php if($unidade['percentagem'] > 85) echo 'bg-danger'?>" role="progressbar" style="width: <?php echo $unidade['percentagem']?>%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">Espaço ocupado <?php echo $unidade['percentagem']?>%</div>
                            </div>
                            <?php } ?>
                        
                        
                        <?php } ?>
                        </div>
                    </div>

                </div>

                <div class="tab-pane" id="acessos" role="tabpanel" aria-labelledby="acessos-tab">

                <table class="table table-hover mb-5">
                    <thead>
                        <tr>
                            <th scope="col">Usuário</th>
                            <th scope="col">Último acesso</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($usuarios_equip as $usuario) { ?>
                        <tr>
                            <td><?php echo $usuario['User_Name']?></td>
                            <td><?php echo date("d/m/Y - H:i:s", strtotime($usuario['Time_Stamp']));?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                    </table>

                </div>

                <div class="tab-pane" id="chamados" role="tabpanel" aria-labelledby="chamados-tab">

                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Chamado</th>
                            <th scope="col">Status</th>
                            <th scope="col">Ticket</th>
                            <th scope="col">Resumo</th>
                            <th scope="col">Data de abertura</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($dados_equipamento as $equipamento) { ?>
                        <tr>
                            <th scope="row">
                                <a href="<?php echo $this->config->item('base_url') . '/chamado/' .  $equipamento['id_chamado']?>" target="_blank"><?php echo $equipamento['id_chamado']?></a>
                            </th>
                            <td><?php echo $equipamento['status_equipamento_chamado']?></td>
                            <td>
                            <?php echo $equipamento['ticket_chamado'];?><a style="font-size:medium" target="_blank" href="<?= $this->config->item('url_ticketsys') ?>index.pl?Action=AgentTicketZoom;TicketID=<?= $equipamento['ticket_chamado']?>">            <i class="fas fa-external-link-alt"></i></a>
                            </td>
                            <td><?php echo $equipamento['resumo_chamado']?></td>
                            <td>
                                <?php echo date("d/m/Y - H:i:s", strtotime($equipamento['data_chamado']));?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>

                </div>

            </div>
            
</div>



	