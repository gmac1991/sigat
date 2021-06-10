<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Interacao_model extends CI_Model {

   public function registraInteracao($dados) {  

      $fila_ant_int = NULL;
      
      if (isset($dados['id_fila']) || isset($dados['patri_atendidos'])) {

         if(($dados['id_fila'] != $dados['id_fila_ant']) && $dados['tipo'] == 'ALT_FILA' ) {

            $this->db->where('id_chamado',$dados['id_chamado']);
            $this->db->update('chamado',array(
               'id_fila_chamado' => $dados['id_fila'],
               'id_usuario_responsavel_chamado' => NULL
            ));

            $fila_ant = $this->db->query("select nome_fila from fila where id_fila = " . $dados['id_fila_ant'] )->row()->nome_fila;
            $fila_nova = $this->db->query("select nome_fila from fila where id_fila = " . $dados['id_fila'] )->row()->nome_fila;
            
            $fila_ant_int = $dados['id_fila_ant'];
            
            $dados['texto'] .= "<hr class=\"m-0\" /><p class=\"m-0\">A fila foi alterada: <strong>" . $fila_ant . "</strong> para <strong>" . $fila_nova . "</strong></p>";
         
            //header("Location: " . base_url('painel'));
         }

         if ($dados['patri_atendidos'] != NULL) { //verificando se foi antendido algum equipamento patrimoniado

            if($dados['tipo'] == 'ATENDIMENTO') {
               
               $dados['texto'] .= "<hr class=\"m-0\" /><p class=\"m-0\">Foram atendidos os seguintes equipamentos:<br /><ul>";

               foreach ($dados['patri_atendidos'] as $patrimonio) {
                  
                 if ($dados['id_fila'] == 3) { //se estiver na fila Manutenção de Hardware / id = 3
                     
                     $this->db->query("update patrimonio_chamado set status_patrimonio_chamado = 'ENTREGA', status_patrimonio_chamado_ant = 'ABERTO'
                     where num_patrimonio = " . $patrimonio);
                     
                     // ------------ LOG -------------------

                     $log = array(
                        'acao_evento' => 'ALTERAR_STATUS_PATRIMONIO',
                        'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'] . " - PATRIMONIO: " . $patrimonio . " - NOVO STATUS: ENTREGA",
                        'id_usuario_evento' => $_SESSION['id_usuario']
                        );
      
                     $this->db->insert('evento', $log);
   
                     // -------------- /LOG ----------------
                  
                  } else { // se nao, marcar o equipamento como atendido

                     $this->db->query("update patrimonio_chamado set status_patrimonio_chamado = 'ATENDIDO', status_patrimonio_chamado_ant = 'ABERTO'
                     where num_patrimonio = " . $patrimonio);

                     // ------------ LOG -------------------

                     $log = array(
                        'acao_evento' => 'ALTERAR_STATUS_PATRIMONIO',
                        'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'] . " - PATRIMONIO: " . $patrimonio . " - NOVO STATUS: ATENDIDO",
                        'id_usuario_evento' => $_SESSION['id_usuario']
                        );
      
                     $this->db->insert('evento', $log);
   
                     // -------------- /LOG ----------------

                  }
                  
                  $dados['texto'] .= "<li>" . $patrimonio . "</li>"; 
               }
            
               $dados['texto'] .= "</ul>";

               if ($dados['id_fila'] == 3) { //se estiver na fila Manutenção de Hardware / id = 3 ***

                  $dados['texto'] .= '<hr class="m-0 p-0">O chamado foi sinalizado para <b>Entrega</b>';
   
                  $this->db->query("update chamado set entrega_chamado = 1 where id_chamado = " . $dados['id_chamado']); 
   
                  // ------------ LOG -------------------

                  $log = array(
                  'acao_evento' => 'SINALIZAR_ENTREGA_CHAMADO',
                  'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'],
                  'id_usuario_evento' => $_SESSION['id_usuario']
                  );
                  
                  $this->db->insert('evento', $log);

                  // -------------- /LOG --------------
                 
               }

               $patri_restantes = $this->db->query("select num_patrimonio from patrimonio_chamado where id_chamado_patrimonio = " . $dados['id_chamado'] .
               " and (status_patrimonio_chamado = 'ABERTO' or status_patrimonio_chamado = 'ESPERA' or status_patrimonio_chamado = 'ENTREGA')")->num_rows();
            
               if ($patri_restantes == 0) { //se todos os patrimonios tiverem sido atendidos/entregues
                  
                  $dados['tipo'] = 'FECHAMENTO_PATRI'; //marcar o chamado como fechado ***

                  $this->db->query("update chamado set status_chamado = 'FECHADO', entrega_chamado = 0 where id_chamado = " . $dados['id_chamado']);
                  
                  // ------------ LOG -------------------

                  $log = array(
                  'acao_evento' => 'FINALIZAR_CHAMADO',
                  'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'],
                  'id_usuario_evento' => $_SESSION['id_usuario']
                  );

                  $this->db->insert('evento', $log);

                  // -------------- /LOG ----------------            
               
               
                }

            }

            if($dados['tipo'] == 'ESPERA') {

              

               $dados['texto'] .= "<hr class=\"m-0\" /><p class=\"m-0\">Foram deixados em espera os equipamentos:<br /><ul>";

               foreach ($dados['patri_atendidos'] as $patrimonio) { //marcando os patrimonios escolhidos como ESPERA
                  $this->db->query("update patrimonio_chamado set status_patrimonio_chamado = 'ESPERA'" .
                  " where num_patrimonio = " . $patrimonio . 
                  " and (status_patrimonio_chamado = 'ABERTO' or status_patrimonio_chamado = 'FALHA')" .
                  " and id_chamado_patrimonio = " . $dados['id_chamado']);

                  // ------------ LOG -------------------

                  $log = array(
                     'acao_evento' => 'ALTERAR_STATUS_PATRIMONIO',
                     'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'] . " - PATRIMONIO: " . $patrimonio . " - NOVO STATUS: ESPERA",
                     'id_usuario_evento' => $_SESSION['id_usuario']
                     );
   
                  $this->db->insert('evento', $log);

                  // -------------- /LOG ----------------

                  $dados['texto'] .= "<li>" . $patrimonio . "</li>";
               }

               $dados['texto'] .= "</ul></p>";
               
            }

            if($dados['tipo'] == 'REM_ESPERA') {

               $dados['texto'] .= "<hr class=\"m-0\" /><p class=\"m-0\">Foram removidos da espera os equipamentos:<br /><ul>";

               foreach ($dados['patri_atendidos'] as $patrimonio) { //marcando os patrimonios escolhidos como ABERTO
                  $this->db->query("update patrimonio_chamado set status_patrimonio_chamado = 'ABERTO'" .
                  " where num_patrimonio = " . $patrimonio . 
                  " and status_patrimonio_chamado = 'ESPERA'" .
                  " and id_chamado_patrimonio = " . $dados['id_chamado']);

                  // ------------ LOG -------------------

                  $log = array(
                     'acao_evento' => 'ALTERAR_STATUS_PATRIMONIO',
                     'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'] . " - PATRIMONIO: " . $patrimonio . " - NOVO STATUS: ABERTO",
                     'id_usuario_evento' => $_SESSION['id_usuario']
                     );
   
                  $this->db->insert('evento', $log);

                  // -------------- /LOG ----------------

                  $dados['texto'] .= "<li>" . $patrimonio . "</li>";
               }

               $dados['texto'] .= "</ul></p>";

               
            }

            if($dados['tipo'] == 'INSERVIVEL') {

               
               $dados['texto'] .= "<hr class=\"m-0\" /><p class=\"m-0\">Foram classificados como <span class=\"text-danger font-weight-bold\">INSERVÍVEL</span> os equipamentos:<br /><ul>";

               foreach ($dados['patri_atendidos'] as $patrimonio) {
                  $this->db->query("update patrimonio_chamado set status_patrimonio_chamado = 'INSERVIVEL', status_patrimonio_chamado_ant = 'ABERTO'
                  where num_patrimonio = " . $patrimonio . " and id_chamado_patrimonio = " . $dados['id_chamado']);

                  // ------------ LOG -------------------

                  $log = array(
                     'acao_evento' => 'ALTERAR_STATUS_PATRIMONIO',
                     'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'] . " - PATRIMONIO: " . $patrimonio . " - NOVO STATUS: INSERVIVEL",
                     'id_usuario_evento' => $_SESSION['id_usuario']
                     );
   
                  $this->db->insert('evento', $log);

                  // -------------- /LOG ----------------

                  $dados['texto'] .= "<li>" . $patrimonio . "</li>";
               }
            
               $dados['texto'] .= "</ul></p>";

               $patri_restantes = $this->db->query("select num_patrimonio from patrimonio_chamado where id_chamado_patrimonio = " . $dados['id_chamado'] .
               " and status_patrimonio_chamado = 'ABERTO'")->num_rows();

               $patri_entrega = $this->db->query("select num_patrimonio from patrimonio_chamado where id_chamado_patrimonio = " . $dados['id_chamado'] .
               " and status_patrimonio_chamado = 'ENTREGA'")->num_rows();
               
               if ($patri_restantes == 0 && $patri_entrega == 0) { //se nao houver patrimonios marcados como ENTREGA ou ABERTO...

                  $dados['tipo'] = 'FECHAMENTO_INS';

                  $this->db->query("update chamado set status_chamado = 'FECHADO' where id_chamado = " . $dados['id_chamado']);

                  // ------------ LOG -------------------

                  $log = array(
                     'acao_evento' => 'FINALIZAR_CHAMADO',
                     'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'],
                     'id_usuario_evento' => $_SESSION['id_usuario']
                     );
   
                  $this->db->insert('evento', $log);

                  // -------------- /LOG ----------------
               
               }
               elseif($patri_entrega > 0) { //se houver patrimonios marcados como ENTREGA...

                  $dados['tipo'] = 'ATENDIMENTO_INS';

                  
                  $dados['texto'] .= 'O chamado foi sinalizado para <b>Entrega</b>';

                  $this->db->query("update chamado set entrega_chamado = 1 where id_chamado = " . $dados['id_chamado'] ); 

                  // ------------ LOG -------------------

                     $log = array(
                     'acao_evento' => 'SINALIZAR_ENTREGA_CHAMADO',
                     'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'],
                     'id_usuario_evento' => $_SESSION['id_usuario']
                     );
                     
                     $this->db->insert('evento', $log);
   
                  // -------------- /LOG ----------------



               }
               else { //se não, o tipo é ATENDIMENTO_INS

                  $dados['tipo'] = 'ATENDIMENTO_INS';
               }

            }  
            
         }

         if ($dados['equip_atendidos'] != NULL) { //verificando se foi antendido algum equipamento sem patrimonio
               
            $dados['texto'] .= "<hr class=\"m-0\" /><p>Equipamentos sem patrimônio:</p><ul>";

            var_dump($dados['equip_atendidos']);
            
            for ($i = 1; $i < count($dados['equip_atendidos']); $i++) {
               
               if ($dados['id_fila'] == 3) { //se estiver na fila Manutenção de Hardware / id = 3
                  
                  $this->db->query("update equipamento_chamado set status_equipamento = 'ENTREGA'
                  where num_equipamento = '" . $dados['equip_atendidos'][$i][0] . "'");

                  // ------------ LOG -------------------

                  $log = array(
                     'acao_evento' => 'ALTERAR_STATUS_EQUIPAMENTO',
                     'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'] . 
                     " - EQUIPAMENTO: " . $dados['equip_atendidos'][$i][0] . " - " . $dados['equip_atendidos'][$i][1] . " - NOVO STATUS: ENTREGA",
                     'id_usuario_evento' => $_SESSION['id_usuario']
                     );
   
                  $this->db->insert('evento', $log);

                  // -------------- /LOG ----------------

                  
                  $this->db->query("update chamado set entrega_chamado = 1 where id_chamado = " . $dados['id_chamado']);

                  // ------------ LOG -------------------

                  $log = array(
                     'acao_evento' => 'SINALIZAR_ENTREGA_CHAMADO',
                     'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'],
                     'id_usuario_evento' => $_SESSION['id_usuario']
                     );
                     
                     $this->db->insert('evento', $log);
   
                  // -------------- /LOG ----------------
               
               }                   
               $dados['texto'] .= "<li>" . $dados['equip_atendidos'][$i][0] . " - " . $dados['equip_atendidos'][$i][1] .  "</li>"; 
            }
         
            $dados['texto'] .= "</ul>";

            if ($dados['id_fila'] == 3) { //se estiver na fila Manutenção de Hardware / id = 3 ***

               //sinalizar para entrega / chamado_entrega = 1

               $dados['texto'] .= '<hr class="m-0 p-0">O chamado foi sinalizado para <b>Entrega</b>';

               $this->db->query("update chamado set entrega_chamado = 1 where id_chamado = " . $dados['id_chamado']); 

               // ------------ LOG -------------------

               $log = array(
                  'acao_evento' => 'SINALIZAR_ENTREGA_CHAMADO',
                  'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'],
                  'id_usuario_evento' => $_SESSION['id_usuario']
                  );
                  
                  $this->db->insert('evento', $log);

               // -------------- /LOG ----------------
            
               
            }
         
         }

      }


      if($dados['tipo'] == 'ENTREGA') {

         $patri_entregues = $this->db->query("select num_patrimonio from patrimonio_chamado where id_chamado_patrimonio = " . $dados['id_chamado'] .
         " and status_patrimonio_chamado = 'ENTREGA'")->result();

         $patri_restantes = $this->db->query("select num_patrimonio from patrimonio_chamado where id_chamado_patrimonio = " . $dados['id_chamado'] .
               " and (status_patrimonio_chamado = 'ABERTO' or status_patrimonio_chamado = 'ESPERA')")->num_rows();

         $equip_entregues = $this->db->query("select * from equipamento_chamado where id_chamado_equipamento = " . $dados['id_chamado'] .
         " and status_equipamento = 'ENTREGA'")->result();

         $equip_restantes = $this->db->query("select num_equipamento from equipamento_chamado where id_chamado_equipamento = " . $dados['id_chamado'] .
               " and status_equipamento = 'ABERTO'")->num_rows();

         
         $this->db->query("insert into termo values(NULL,'" . $dados['nome_termo_entrega'] . "', 'E', NOW()," .  $dados['id_chamado'] . ")");  // registrando o Termo de Entrega
         
         // ------------ LOG -------------------

            $log = array(
            'acao_evento' => 'REGISTRAR_TERMO_ENTREGA',
            'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'] . " - " . $dados['nome_termo_entrega'],
            'id_usuario_evento' => $_SESSION['id_usuario']
            );
            
            $this->db->insert('evento', $log);

         // -------------- /LOG ----------------

         if (isset($dados['nome_termo_responsabilidade'])) { // registrando o Termo de Responsabilidade
            
            $this->db->query("insert into termo values(NULL,'" . $dados['nome_termo_responsabilidade'] . "', 'R', NOW()," .  $dados['id_chamado'] . ")");  

         
            // ------------ LOG -------------------

            $log = array(
               'acao_evento' => 'REGISTRAR_TERMO_RESPONSABILIDADE',
               'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'] . " - " .  $dados['nome_termo_responsabilidade'],
               'id_usuario_evento' => $_SESSION['id_usuario']
               );
               
               $this->db->insert('evento', $log);

            // -------------- /LOG ----------------

            $dados['texto'] .= "<a role=\"button\" class=\"btn btn-info float-right mt-2\" href=\"" . base_url('termos/' .
            $dados['nome_termo_responsabilidade']) . "\" download><i class=\"fas fa-file-download\"></i> Baixar Termo de Responsabilidade</a>";
         }
         
         
         $dados['texto'] .= "<a role=\"button\" class=\"btn btn-info float-right mt-2\" href=\"" . base_url('termos/' . 
         $dados['nome_termo_entrega']) . "\" download><i class=\"fas fa-file-download\"></i> Baixar Termo de Entrega</a>";

         $dados['texto'] .= "<p class=\"m-0\">Os equipamentos foram entregues:</p>";

         if(!empty($patri_entregues)) {  

            $dados['texto'] .= "<ul>";
            
            foreach ($patri_entregues as $patrimonio) {
               $this->db->query("update patrimonio_chamado set status_patrimonio_chamado = 'ENTREGUE', status_patrimonio_chamado_ant = 'ENTREGA'
                  where num_patrimonio = " . $patrimonio->num_patrimonio . " and id_chamado_patrimonio = " . $dados['id_chamado']);
               $dados['texto'] .= "<li>" . $patrimonio->num_patrimonio . "</li>";

               // ------------ LOG -------------------

               $log = array(
                  'acao_evento' => 'ALTERAR_STATUS_PATRIMONIO',
                  'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'] . 
                  " - PATRIMONIO: " . $patrimonio->num_patrimonio . " - NOVO STATUS: ENTREGUE",
                  'id_usuario_evento' => $_SESSION['id_usuario']
                  );

               $this->db->insert('evento', $log);

               // -------------- /LOG ----------------
            }

            $dados['texto'] .= "</ul>";
         }

         if(!empty($equip_entregues)) { // verificando se existem equipamentos sem patrimonio que foram entregues

            $dados['texto'] .= "<p>Equipamentos sem patrimônio:</p><ul>";

            foreach ($equip_entregues as $equip) {
               $this->db->query("update equipamento_chamado set status_equipamento = 'ENTREGUE', ult_alt_equipamento = NOW()
                  where num_equipamento = '" . $equip->num_equipamento . "' and id_chamado_equipamento = " . $dados['id_chamado']);

                  // ------------ LOG -------------------

                  $log = array(
                     'acao_evento' => 'ALTERAR_STATUS_EQUIPAMENTO',
                     'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'] . 
                     " - EQUIPAMENTO: " . $equip->num_equipamento . " - " . $equip->desc_equipamento . " - NOVO STATUS: ENTREGUE",
                     'id_usuario_evento' => $_SESSION['id_usuario']
                     );
   
                  $this->db->insert('evento', $log);

                  // -------------- /LOG ----------------

               $dados['texto'] .= "<li>" . $equip->num_equipamento . " - " . $equip->desc_equipamento . "</li>";
            }

            $dados['texto'] .= "</ul>";


         }

         $dados['texto'] .= "<p>Recebido por: <strong>" . $dados['nome_recebedor'] ."</strong></p>"; 

         if ($patri_restantes == 0 && $equip_restantes == 0) {
            $dados['texto'] .= "<hr class=\"m-0 p-0\">O chamado foi <strong>fechado</strong>"; 
            $this->db->query("update chamado set status_chamado = 'FECHADO' where id_chamado = " . $dados['id_chamado']); //fechando o chamado

            // ------------ LOG -------------------

            $log = array(
               'acao_evento' => 'FINALIZAR_CHAMADO',
               'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'],
               'id_usuario_evento' => $_SESSION['id_usuario']
               );

            $this->db->insert('evento', $log);

            // -------------- /LOG ----------------
         
         }

         $this->db->query("update chamado set entrega_chamado = 0 where id_chamado = " . $dados['id_chamado']); // tirando o flag de Entrega

         // ------------ LOG -------------------

         $log = array(
            'acao_evento' => 'REMOVER_SINAL_ENTREGA',
            'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'],
            'id_usuario_evento' => $_SESSION['id_usuario']
            );
            
            $this->db->insert('evento', $log);

         // -------------- /LOG ----------------

      }

      if ($dados['tipo'] == 'FALHA_ENTREGA') {

         $this->db->query('update chamado set entrega_chamado = 0 where id_chamado = ' . $dados['id_chamado']); //tirando o sinal de entrega.. entrega_chamado = 0
         
         $patri_entrega = $this->db->query("select num_patrimonio from patrimonio_chamado where id_chamado_patrimonio = " . $dados['id_chamado'] .
         " and status_patrimonio_chamado = 'ENTREGA'")->result();

         $equip_entrega = $this->db->query("select * from equipamento_chamado where id_chamado_equipamento = " . $dados['id_chamado'] .
         " and status_equipamento = 'ENTREGA'")->result();
                           
         if(!empty($patri_entrega)) {
            foreach ($patri_entrega as $patrimonio) {
               $this->db->query("update patrimonio_chamado set status_patrimonio_chamado = 'FALHA', status_patrimonio_chamado_ant = 'ENTREGA'
                  where num_patrimonio = " . $patrimonio->num_patrimonio . " and id_chamado_patrimonio = " . $dados['id_chamado']);
               
                // ------------ LOG -------------------

                $log = array(
                  'acao_evento' => 'ALTERAR_STATUS_PATRIMONIO',
                  'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'] . " - PATRIMONIO: " . $patrimonio->num_patrimonio . " - NOVO STATUS: FALHA",
                  'id_usuario_evento' => $_SESSION['id_usuario']
                  );

               $this->db->insert('evento', $log);

               // -------------- /LOG ----------------
                  
               }

            $patri_falha = $this->db->query("select num_patrimonio from patrimonio_chamado where id_chamado_patrimonio = " . $dados['id_chamado'] .
               " and status_patrimonio_chamado = 'FALHA'")->result();

            $dados['texto'] .= "<hr class=\"m-0\" /><p class=\"m-0\">Os seguintes equipamentos retornaram:</p>";
            $dados['texto'] .= '<ul>';
               
            foreach ($patri_falha as $patrimonio) {

               $dados['texto'] .= '<li>' . $patrimonio->num_patrimonio . '</li>';
   

            }

            $dados['texto'] .= '</ul>';

         }
         

         if(!empty($equip_entrega)) { // verificando se existem equipamentos sem patrimonio que foram entregues

            $dados['texto'] .= "<p>Equipamentos sem patrimônio:</p><ul>";

            foreach ($equip_entrega as $equip) {
               $this->db->query("update equipamento_chamado set status_equipamento = 'ABERTO', ult_alt_equipamento = NOW()
                  where num_equipamento = '" . $equip->num_equipamento . "' and id_chamado_equipamento = " . $dados['id_chamado']);

                   // ------------ LOG -------------------

                   $log = array(
                     'acao_evento' => 'ALTERAR_STATUS_EQUIPAMENTO',
                     'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'] . 
                     " - EQUIPAMENTO: " . $equip->num_equipamento . " - " . $equip->desc_equipamento . " - NOVO STATUS: ABERTO",
                     'id_usuario_evento' => $_SESSION['id_usuario']
                     );
   
                  $this->db->insert('evento', $log);

                  // -------------- /LOG ----------------
               
               $dados['texto'] .= "<li>" .  $equip->num_equipamento . " - " .  $equip->desc_equipamento . "</li>";
            }

            $dados['texto'] .= "</ul>";


         }

      }

      if ($dados['tipo'] == 'FECHAMENTO') {

         $this->db->query("update chamado set status_chamado = 'FECHADO' where id_chamado = " . $dados['id_chamado']);

         // ------------ LOG -------------------

         $log = array(
            'acao_evento' => 'FINALIZAR_CHAMADO',
            'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'],
            'id_usuario_evento' => $_SESSION['id_usuario']
            );

         $this->db->insert('evento', $log);

         // -------------- /LOG ----------------


      }

      if ($dados['tipo'] == 'ADC_EQUIP') {

         $dados['texto'] = "<ul>";

         if(!empty($dados['patrimonios'])) {

            $patrimonios = array();

            preg_match_all("/[1-9]\d{5}/",$dados['patrimonios'], $patrimonios);
            
            if (!empty($patrimonios)) {

               foreach($patrimonios[0] as $patrimonio) {

                  $this->db->query("insert into patrimonio_chamado values(" . //inserindo na tabela patrimonio_chamado
                  $patrimonio . "," . $dados['id_chamado'] . ",'ABERTO',NULL)");

                  // ------------ LOG -------------------

               $log = array(
                  'acao_evento' => 'ADC_PATRIMONIO_SOLICITACAO',
                  'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'] . " - NUM: " . $patrimonio,
                  'id_usuario_evento' => $_SESSION['id_usuario']
                  );

               $this->db->insert('evento', $log);

            // -------------- /LOG ----------------

                  $dados['texto'] .= '<li>' . $patrimonio . '</li>';
               }
            }
         }

         if(!empty($dados['json_equip'])) {

            $equip = json_decode($dados['json_equip']);

            //$equip[x][0] -> num de serie
            //$equip[x][1] -> descricao

            
            for ($i = 1; $i < count($equip); $i++) {

               $this->db->query("insert into equipamento_chamado values('" . //inserindo na tabela equipamento_chamado
               $equip[$i][0] . "','" .  $equip[$i][1] . "'," . $dados['id_chamado'] . ",'ABERTO')");

               $log = array(
                  'acao_evento' => 'ADC_EQUIP_SOLICITACAO',
                  'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'] . " - S/N: " .  $equip[$i][0] . " - DESC: " . $equip[$i][1],
                  'id_usuario_evento' => $_SESSION['id_usuario']
                  );

               $this->db->insert('evento', $log);

               $dados['texto'] .= '<li>' . $equip[$i][0] . " - " .  $equip[$i][1] . '</li>';
            }

         }

         $dados['texto'] .= '</ul>';

         


      }

      $nova_interacao = array (
         'id_interacao' => NULL,
         'tipo_interacao' => $dados['tipo'],
         //'data_interacao' => date('Y-m-d H:i:s'),
         'texto_interacao' => $dados['texto'],
         'id_chamado_interacao' => $dados['id_chamado'],
         'id_usuario_interacao' => $dados['id_usuario'],
         'id_fila_ant_interacao' => $fila_ant_int

      ); 
      
      $this->db->insert('interacao',$nova_interacao);

       // ------------ LOG -------------------

       $log = array(
         'acao_evento' => 'REGISTRAR_INTERACAO',
         'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'] . " - TIPO: " . $dados['tipo'],
         'id_usuario_evento' => $_SESSION['id_usuario']
         );

      $this->db->insert('evento', $log);

      // -------------- /LOG ----------------

      

   }
    
   public function buscaInteracoes($id_chamado) {

      $this->db->from('interacao');
      $this->db->where(array('id_chamado_interacao' => $id_chamado));
      $this->db->order_by('data_interacao DESC');

      $result = $this->db->get()->result();

      return $result;
   }

   public function removeInteracao($p_id_interacao) {
      $buscaInteracao = $this->db->get_where('interacao', array('id_interacao' => $p_id_interacao));
      
      $interacao = $buscaInteracao->row();

      $buscaChamado = $this->db->get_where('chamado', array('id_chamado' => $interacao->id_chamado_interacao));

      $chamado = $buscaChamado->row();
	  
	   preg_match_all("/[1-9]\d{5}/",$interacao->texto_interacao, $patrimonios); 

      switch ($interacao->tipo_interacao) {

         case 'ATENDIMENTO':
		 
		 
            foreach ($patrimonios[0] as $patrimonio) { //patrimonios[0] é o vetor com a lista de patrimonios da interacao
               $this->db->query("update patrimonio_chamado set status_patrimonio_chamado_ant = status_patrimonio_chamado, 
               status_patrimonio_chamado = 'ABERTO'
               where id_chamado_patrimonio = " . $interacao->id_chamado_interacao . " and
               num_patrimonio = " . $patrimonio);

               // ------------ LOG -------------------

               $log = array(
                  'acao_evento' => 'ALTERAR_STATUS_PATRIMONIO',
                  'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . " - PATRIMONIO: " . $patrimonio . " - NOVO STATUS: ABERTO",
                  'id_usuario_evento' => $_SESSION['id_usuario']
                  );

               $this->db->insert('evento', $log);

               // -------------- /LOG ----------------
            }

            if ($chamado->entrega_chamado == '1') {

               $this->db->set('entrega_chamado', '0');
               $this->db->where('id_chamado', $interacao->id_chamado_interacao);
               $this->db->update('chamado');

               // ------------ LOG -------------------

               $log = array(
                  'acao_evento' => 'CANCELAR_ENTREGA_CHAMADO',
                  'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao,
                  'id_usuario_evento' => $_SESSION['id_usuario']
                  );

               $this->db->insert('evento', $log);

               // -------------- /LOG ----------------
            
            }
         
         break;

         case 'ATENDIMENTO_INS':

            $this->db->query("update patrimonio_chamado set status_patrimonio_chamado = status_patrimonio_chamado_ant,
            status_patrimonio_chamado_ant = status_patrimonio_chamado 
            where id_chamado_patrimonio = " . $interacao->id_chamado_interacao . " and
            status_patrimonio_chamado = 'INSERVIVEL'");

            // ------------ LOG -------------------

            $log = array(
               'acao_evento' => 'DESFAZER_INSERVIVEL_CHAMADO',
               'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . ' - TODOS INSERVIVEIS DO CHAMADO FORAM REVERTIDOS PARA O ESTADO ANTERIOR',
               'id_usuario_evento' => $_SESSION['id_usuario']
               );

            $this->db->insert('evento', $log);

            // -------------- /LOG ----------------

         break;

         case 'ALT_FILA':
         
            $this->db->where('id_chamado', $interacao->id_chamado_interacao);
            $this->db->set('id_fila_chamado', $interacao->id_fila_ant_interacao);
            $this->db->set('id_usuario_responsavel_chamado', $_SESSION['id_usuario']);
            $this->db->update('chamado');

            // ------------ LOG -------------------

            $log = array(
               'acao_evento' => 'ALTERAR_FILA_CHAMADO',
               'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . " - NOVA FILA: " . $interacao->id_fila_ant_interacao,
               'id_usuario_evento' => $_SESSION['id_usuario']
               );

            $this->db->insert('evento', $log);

            // -------------- /LOG ----------------
         
         break;

         case 'OBSERVACAO':

            // ------------ LOG -------------------

            $log = array(
               'acao_evento' => 'REMOVER_OBSERVACAO',
               'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao,
               'id_usuario_evento' => $_SESSION['id_usuario']
               );

            $this->db->insert('evento', $log);

            // -------------- /LOG ----------------
         
		 
		   break;

         case 'FECHAMENTO_PATRI':

            foreach ($patrimonios[0] as $patrimonio) { //patrimonios[0] é o vetor com a lista de patrimonios da interacao
               $this->db->query("update patrimonio_chamado set status_patrimonio_chamado_ant = status_patrimonio_chamado, 
               status_patrimonio_chamado = 'ABERTO'
               where id_chamado_patrimonio = " . $interacao->id_chamado_interacao . " and
               num_patrimonio = " . $patrimonio);

               // ------------ LOG -------------------

               $log = array(
                  'acao_evento' => 'ALTERAR_STATUS_PATRIMONIO',
                  'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . " - PATRIMONIO: " . $patrimonio . " - NOVO STATUS: ABERTO",
                  'id_usuario_evento' => $_SESSION['id_usuario']
                  );

               $this->db->insert('evento', $log);

               // -------------- /LOG ----------------
            }


            $this->db->set('status_chamado', 'ABERTO');
            $this->db->where('id_chamado', $interacao->id_chamado_interacao);
            $this->db->update('chamado');

            // ------------ LOG -------------------

            $log = array(
               'acao_evento' => 'ALTERAR_STATUS_CHAMADO',
               'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . " - NOVO STATUS: ABERTO",
               'id_usuario_evento' => $_SESSION['id_usuario']
               );

            $this->db->insert('evento', $log);

            // -------------- /LOG ----------------

         break;

         case 'FECHAMENTO_INS':

            $this->db->query("update patrimonio_chamado set status_patrimonio_chamado = status_patrimonio_chamado_ant,
            status_patrimonio_chamado_ant = status_patrimonio_chamado 
            where id_chamado_patrimonio = " . $interacao->id_chamado_interacao . " and
            status_patrimonio_chamado = 'INSERVIVEL'");
            
            // ------------ LOG -------------------

            $log = array(
               'acao_evento' => 'DESFAZER_INSERVIVEL_CHAMADO',
               'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . ' - TODOS INSERVIVEIS DO CHAMADO FORAM REVERTIDOS PARA O ESTADO ANTERIOR',
               'id_usuario_evento' => $_SESSION['id_usuario']
               );

            $this->db->insert('evento', $log);

            // -------------- /LOG ----------------

            $this->db->set('status_chamado', 'ABERTO');
            $this->db->where('id_chamado', $interacao->id_chamado_interacao);
            $this->db->update('chamado');

            // ------------ LOG -------------------

            $log = array(
               'acao_evento' => 'ALTERAR_STATUS_CHAMADO',
               'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . " - NOVO STATUS: ABERTO",
               'id_usuario_evento' => $_SESSION['id_usuario']
               );

            $this->db->insert('evento', $log);

            // -------------- /LOG ----------------

         break;

         case 'FECHAMENTO':

            $this->db->set('status_chamado', 'ABERTO');
            $this->db->where('id_chamado', $interacao->id_chamado_interacao);
            $this->db->update('chamado');

            // ------------ LOG -------------------

            $log = array(
               'acao_evento' => 'ALTERAR_STATUS_CHAMADO',
               'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . " - NOVO STATUS: ABERTO",
               'id_usuario_evento' => $_SESSION['id_usuario']
               );

            $this->db->insert('evento', $log);

            // -------------- /LOG ----------------

         break;

         case 'ENTREGA':

            if (!empty($patrimonios[0])) {
               foreach ($patrimonios[0] as $patrimonio) { //patrimonios[0] é o vetor com a lista de patrimonios da interacao
                  $this->db->query("update patrimonio_chamado set status_patrimonio_chamado = 'ENTREGA'" .
                  " where num_patrimonio = " . $patrimonio . 
                  " and status_patrimonio_chamado = 'ENTREGUE'" .
                  " and id_chamado_patrimonio = " . $interacao->id_chamado_interacao);

                  // ------------ LOG -------------------

                  $log = array(
                     'acao_evento' => 'DESFAZER_ENTREGA_PATRIMONIO',
                     'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . " - NUM: " . $patrimonio,
                     'id_usuario_evento' => $_SESSION['id_usuario']
                     );

                  $this->db->insert('evento', $log);

                  // -------------- /LOG ----------------
               }

            }
            
            //equipamentos sem patrimonio
            $this->db->query("update equipamento_chamado set status_equipamento = 'ENTREGA'" .
            " where status_equipamento = 'ENTREGUE'" .
            " and id_chamado_equipamento = " . $interacao->id_chamado_interacao);

            if ($this->db->affected_rows() > 0) { // se equip. sem patrimonio foram modificados..
               
               // ------------ LOG -------------------

               $log = array(
                  'acao_evento' => 'DESFAZER_ENTREGA_EQUIPAMENTOS',
                  'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . ' - TODOS EQUIPS. DO CHAMADO FORAM REVERTIDOS PARA ENTREGA',
                  'id_usuario_evento' => $_SESSION['id_usuario']
                  );

               $this->db->insert('evento', $log);

               // -------------- /LOG ----------------

            }
           
            
            $this->db->set('entrega_chamado', '1');
            $this->db->set('status_chamado', 'ABERTO');
            $this->db->where('id_chamado', $interacao->id_chamado_interacao);
            $this->db->update('chamado');

            // ------------ LOG -------------------

            $log = array(
               'acao_evento' => 'SINALIZAR_ENTREGA_CHAMADO',
               'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao,
               'id_usuario_evento' => $_SESSION['id_usuario']
               );
               
               $this->db->insert('evento', $log);


            $log = array(
               'acao_evento' => 'ALTERAR_STATUS_CHAMADO',
               'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . " - NOVO STATUS: ABERTO",
               'id_usuario_evento' => $_SESSION['id_usuario']
               );

            $this->db->insert('evento', $log);

            // -------------- /LOG ----------------

            $buscaTermoEntrega = $this->db->get_where('termo', array('id_chamado_termo' => $interacao->id_chamado_interacao, 'tipo_termo' => 'E'));

            $termo_e = $buscaTermoEntrega->row();

            $apagar_termo = unlink('C:\xampp\htdocs\sigat\termos\\' . $termo_e->nome_termo);

            if ($apagar_termo) {

               $this->db->where('id_chamado_termo', $interacao->id_chamado_interacao);
               $this->db->where('tipo_termo', 'E');
               $this->db->delete('termo');

               // ------------ LOG -------------------

               $log = array(
                  'acao_evento' => 'REMOVER_TERMO_ENTREGA',
                  'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . ' - TERMO: ' . $termo_e->nome_termo,
                  'id_usuario_evento' => $_SESSION['id_usuario']
                  );

               $this->db->insert('evento', $log);

               // -------------- /LOG ----------------

            }

            $buscaTermoResp = $this->db->get_where('termo', array('id_chamado_termo' => $interacao->id_chamado_interacao, 'tipo_termo' => 'R'));

            $termo_r = $buscaTermoResp->row();

            if(!empty($termo_r)) {

               $apagar_termo = unlink('C:\xampp\htdocs\sigat\termos\\' . $termo_r->nome_termo);

               if ($apagar_termo) {

                  $this->db->where('id_chamado_termo', $interacao->id_chamado_interacao);
                  $this->db->where('tipo_termo', 'E');
                  $this->db->delete('termo');

                  // ------------ LOG -------------------

                  $log = array(
                     'acao_evento' => 'REMOVER_TERMO_RESPONSABILIDADE',
                     'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . ' - TERMO: ' . $termo_r->nome_termo,
                     'id_usuario_evento' => $_SESSION['id_usuario']
                     );

                  $this->db->insert('evento', $log);

               // -------------- /LOG ----------------

               }


            }
            
            
         
         break;

         case 'FALHA_ENTREGA':

            if (!empty($patrimonios[0])) {
               foreach ($patrimonios[0] as $patrimonio) { //patrimonios[0] é o vetor com a lista de patrimonios da interacao
                  $this->db->query("update patrimonio_chamado set status_patrimonio_chamado = 'ENTREGA', " .
                  "status_patrimonio_chamado_ant = 'FALHA'" .
                  " where num_patrimonio = " . $patrimonio . 
                  " and status_patrimonio_chamado = 'FALHA'" .
                  " and id_chamado_patrimonio = " . $interacao->id_chamado_interacao);

                  // ------------ LOG -------------------

                  $log = array(
                     'acao_evento' => 'DESFAZER_FALHA_ENTREGA_PATRI',
                     'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . " - NUM: " . $patrimonio,
                     'id_usuario_evento' => $_SESSION['id_usuario']
                     );

                  $this->db->insert('evento', $log);

                  // -------------- /LOG ----------------
               }
            }

            //equipamentos sem patrimonio
            $this->db->query("update equipamento_chamado set status_equipamento = 'ENTREGA'" .
            " where status_equipamento = 'ABERTO'" .
            " and id_chamado_equipamento = " . $interacao->id_chamado_interacao);

            if ($this->db->affected_rows() > 0) { // se equip. sem patrimonio foram modificados..
               
               // ------------ LOG -------------------

               $log = array(
                  'acao_evento' => 'DESFAZER_FALHA_ENTREGA_EQUIP',
                  'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . ' - TODOS EQUIPS. DO CHAMADO FORAM REVERTIDOS PARA ENTREGA',
                  'id_usuario_evento' => $_SESSION['id_usuario']
                  );

               $this->db->insert('evento', $log);

               // -------------- /LOG ----------------

            }
            

            $this->db->set('entrega_chamado', '1');
            $this->db->where('id_chamado', $interacao->id_chamado_interacao);
            $this->db->update('chamado');

             // ------------ LOG -------------------

             $log = array(
               'acao_evento' => 'SINALIZAR_ENTREGA_CHAMADO',
               'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao,
               'id_usuario_evento' => $_SESSION['id_usuario']
               );
               
               $this->db->insert('evento', $log);

            // -------------- /LOG ----------------

         break;

         case 'ESPERA':

            foreach ($patrimonios[0] as $patrimonio) { //patrimonios[0] é o vetor com a lista de patrimonios da interacao
               $this->db->query("update patrimonio_chamado set status_patrimonio_chamado = 'ABERTO'" .
               " where num_patrimonio = " . $patrimonio . 
               " and status_patrimonio_chamado = 'ESPERA'" .
               " and id_chamado_patrimonio = " . $interacao->id_chamado_interacao);

                // ------------ LOG -------------------

                $log = array(
                  'acao_evento' => 'DESFAZER_ESPERA_PATRIMONIO',
                  'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . " - PATRIMONIO: " . $patrimonio . " - NOVO STATUS: ABERTO",
                  'id_usuario_evento' => $_SESSION['id_usuario']
                  );

               $this->db->insert('evento', $log);

               // -------------- /LOG ----------------
            }

         break;

            case 'REM_ESPERA':

            foreach ($patrimonios[0] as $patrimonio) { //patrimonios[0] é o vetor com a lista de patrimonios da interacao
               $this->db->query("update patrimonio_chamado set status_patrimonio_chamado = 'ESPERA'" .
               " where num_patrimonio = " . $patrimonio . 
               " and status_patrimonio_chamado = 'ABERTO'" .
               " and id_chamado_patrimonio = " . $interacao->id_chamado_interacao);

               // ------------ LOG -------------------

               $log = array(
                  'acao_evento' => 'DESFAZER_REM_ESPERA_PATRI',
                  'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . " - PATRIMONIO: " . $patrimonio . " - NOVO STATUS: ESPERA",
                  'id_usuario_evento' => $_SESSION['id_usuario']
                  );

               $this->db->insert('evento', $log);

               // -------------- /LOG ----------------
            }

         break;
      }
	 
	   $this->db->where('id_interacao', $interacao->id_interacao);
      $this->db->delete('interacao');

      // ------------ LOG -------------------

      $log = array(
         'acao_evento' => 'REMOVER_INTERACAO',
         'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . " - ID INTERACAO: " . $interacao->id_interacao,
         'id_usuario_evento' => $_SESSION['id_usuario']
         );

      $this->db->insert('evento', $log);

      // -------------- /LOG ----------------

   }
    
   
   public function buscaInteracao($id_chamado,$tipo = array()) {

      $this->db->select('DATE_FORMAT(data_interacao, \'%d/%m/%Y - %H:%i:%s\') as data_interacao, texto_interacao, nome_usuario');
      $this->db->from('interacao');
      $this->db->join('usuario','usuario.id_usuario = interacao.id_usuario_interacao');
      $this->db->where(array('id_chamado_interacao' => $id_chamado));
      $this->db->where(array('tipo_interacao' => $tipo[0]));
      $i = count($tipo) - 1;
      while ($i > 0) {

         $this->db->or_where(array('tipo_interacao' => $tipo[$i]));
         $i--;

      }
      $this->db->order_by('data_interacao DESC');

      $result = $this->db->get()->row();

      return $result;
   }
    
    
}

?>