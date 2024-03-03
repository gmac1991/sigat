<nav aria-label="breadcrumb">
   <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= base_url('admin'); ?>">Administração</a></li>
      <li class="breadcrumb-item active" aria-current="page">Usuário</li>
      <li class="breadcrumb-item active" aria-current="page"><?= $nomeUsuario ?></li>
      
   </ol>
</nav>
<div class="container py-4">

   <ul class="nav nav-tabs" id="myTab" role="tablist">
      <li class="nav-item">
            <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab" aria-controls="info" aria-selected="true"><i class="fas fa-info-circle"></i> Informações</a>
      </li>
      <li class="nav-item">
            <a class="nav-link" id="bloqueados-tab" data-toggle="tab" href="#bloqueados" role="tab" aria-controls="telefone" aria-selected="true"><i class="fas fa-user-lock"></i> Chamados bloqueados</a>
      </li>
   </ul>
    
   <div class="tab-content mb-5 mt-5">
      <div class="tab-pane active" id="info" role="tabpanel" aria-labelledby="info-tab">
            <div class="card">
            <div class="card-header">
               <h4><i class="fas fa-user-circle"></i> <?= $nomeUsuario ?></h4>
            </div>
            <div class="card-body">
               <blockquote class="blockquote mb-0">
                  <strong>Permissões</strong><br/>
                  <div class="form-check form-check-inline">
                     <input id_usuario="<?= $id_usuario?>" type="checkbox" class="form-check-input" id="CheckTriagem" <?= $triagem_usuario == 1 ? 'checked' : '' ?>>
                     <label class="form-check-label" for="CheckTriagem">Triagem</label>
                  </div><br/>
                  <div class="form-check form-check-inline">
                     <input id_usuario="<?= $id_usuario?>" type="checkbox" class="form-check-input" id="CheckEncerramento" <?= $encerramento_usuario == 1 ? 'checked' : '' ?>>
                     <label class="form-check-label" for="CheckEncerramento">Encerramento de chamados</label>
                  </div><br/>
                  <div class="form-check form-check-inline">
                     <input type="checkbox" class="form-check-input" id_usuario="<?= $id_usuario?>" id="CheckInserviveis" <?= $inservivel_usuario == 1 ? 'checked' : '' ?>>
                     <label class="form-check-label" for="CheckInserviveis">Controle de inservíveis</label>
                  </div><br/>
                  <strong>Login de rede: </strong> <?= $loginUsuario?><br/>
                  <strong>Autorização: </strong> <?= $autorizacao?><br/>
                  <strong>Interações nos últimos 30 dias: </strong> <?= $totalInteracoes->interacoes ?><br/>
                  <strong>Último acesso: </strong><?php if(isset($ultimo_login->data_evento)) { ?> <?= date("d/m/Y - H:i:s", strtotime(  $ultimo_login->data_evento)) ?><br/><?php } ?>

                  <div>
                  <canvas id="myChart"></canvas>
                  </div>
                  <?php  
                     $d = array();
                     for($i = 0; $i < 30; $i++) {
                        $d[] = date("d/m", strtotime('-'. 29 .' days+' . $i .'days'));
                     }
                     
                     $e = array();
                     for($i = 0; $i < 30; $i++) {
                        $e[] = date("d/m", strtotime('-'. 29 .' days+' . $i .'days'));
                     }

                     $f = array();
                     for($i = 0; $i < 30; $i++) {
                        $f[] = date("d/m", strtotime('-'. 29 .' days+' . $i .'days'));
                     }
                     $aux = null; //para evitar problema de variável não definida
                     for($i = 0; $i < 30; $i++){
                        $aux = $d[$i];
                        for($j = 0; $j < sizeof($produtividade); $j++){
                          if($aux == $produtividade[$j]['DATA']){
                              $d[$i] = array('DATA' => $aux, 'QTDE'=> $produtividade[$j]['QTDE']);
                              
                           }
                        }
                        if(!isset($d[$i]['DATA'])){
                           $d[$i] = array('DATA' => $aux, 'QTDE'=> 0);
                        }
                     }

                     $aux2 = null; //para evitar problema de variável não definida
                     for($i = 0; $i < 30; $i++){
                        $aux2 = $e[$i];
                        for($j = 0; $j < sizeof($chamadosAbertos); $j++){
                          if($aux2 == $chamadosAbertos[$j]['DATA']){
                              $e[$i] = array('DATA' => $aux2, 'QTDE'=> $chamadosAbertos[$j]['QTDE']);
                           }
                        }
                        if(!isset($e[$i]['DATA'])){
                           $e[$i] = array('DATA' => $aux2, 'QTDE'=> 0);
                        }
                     }

                     $aux3 = null; //para evitar problema de variável não definida
                     for($i = 0; $i < 30; $i++){
                        $aux3 = $f[$i];
                        for($j = 0; $j < sizeof($chamadosFechados); $j++){
                          if($aux3 == $chamadosFechados[$j]['DATA']){
                              $f[$i] = array('DATA' => $aux3, 'QTDE'=> $chamadosFechados[$j]['QTDE']);
                           }
                        }
                        if(!isset($f[$i]['DATA'])){
                           $f[$i] = array('DATA' => $aux3, 'QTDE'=> 0);
                        }
                     }
                     $datas = implode(',', array_column($d, 'DATA') );
                     $qtdes = implode(',', array_column($d, 'QTDE') );

                     $datasc = implode(',', array_column($e, 'DATA') );
                     $qtdesc = implode(',', array_column($e, 'QTDE') );

                     $datasf = implode(',', array_column($f, 'DATA') );
                     $qtdesf = implode(',', array_column($f, 'QTDE') );
                 
                     $datas = "'" . str_replace(",", "','", $datas) . "'";
                     
                  ?>  
                  
                  <script>

                   
                  const ctx = document.getElementById('myChart');

                  new Chart(ctx, {
                     type: 'line',
                     data: {
                        labels: [<?=$datas?>],
                        datasets: [{
                           label: 'Interações',
                           data: [<?=$qtdes?>],
                           borderWidth: 3
                        },
                        {
                           label: 'Abertura Chamados',
                           data: [<?=$qtdesc?>],
                           borderWidth: 3
                        },
                        {
                           label: 'Fechamento Chamados',
                           data: [<?=$qtdesf?>],
                           borderWidth: 3
                        }
                     ]
                     },
                     options: {
                        scales: {
                           y:{
                              ticks : {
                                 stepSize: 1
                              }
                           }
                        }
                     }
                  });
                  </script>


               </blockquote>
            </div>
         </div>
      </div>

      <div class="tab-pane" id="bloqueados" role="tabpanel" aria-labelledby="bloqueados-tab">
      
         <table class="table table-striped">
            <thead>
               <tr>
                  <th scope="col">Chamado</th>
                  <th scope="col">Status</th>
                  <th scope="col">Ticket</th>
                  <th scope="col"></th>
                  <th scope="col">Resumo</th>
                  <th scope="col">Data de abertura</th>
               </tr>
            </thead>
            <tbody>
            <?php if($chamadosBloqueados != NULL) { ?>
               <?php 
                  foreach ($chamadosBloqueados as $dados => $chamado) { ?>
                     <?php if($chamado['status_chamado'] == 'ABERTO') { ?>
                        <tr>
                        <th scope="row"><a href="<?php echo $this->config->item('base_url') . '/chamado//' .  $chamado['id_chamado']?>" target="_blank"><?php echo $chamado['id_chamado']?></a></th>
                        <td><?= $chamado['status_chamado']?></td>
                        <td><?= $chamado['ticket_chamado'];?></td>
                        <td><a style="font-size:medium" target="_blank" href="<?= $this->config->item('url_ticketsys') ?>index.pl?Action=AgentTicketZoom;TicketID=<?= $chamado['ticket_chamado']?>"> <i class="fas fa-external-link-alt"></i></a></td>
                        <td><?= $chamado['resumo_chamado']?></td>
                        <td><?= date("d/m/Y", strtotime($chamado['data_chamado']))?></td>
                     </tr>
                     <?php }?>
               <?php }?> 
            <?php } ?>  
            </tbody>
         </table>

      </div>
   </div>
</div>





	