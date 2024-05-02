<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Interacao_model extends CI_Model {

   public function registraInteracao($dados) {  

      $fila_ant_int = NULL;

      

      if (isset($dados['id_fila']) || isset($dados['equip_atendidos'])) {

         

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

         if ($dados['equip_atendidos'] != NULL || $dados['servicos_atendidos'] != NULL) {

            if($dados['tipo'] == 'ATENDIMENTO') {
               
               $dados['texto'] .= "<hr class=\"m-0\" /><p class=\"m-0\">Foram atendidos os seguintes equipamentos ou serviços:<br /><ul>";

               foreach ($dados['equip_atendidos'] as $num_equip) {
                  
                 if ($dados['id_fila'] == 3) { //se estiver na fila Manutenção de Hardware / id = 3
                     
                     $this->db->query("update equipamento_chamado set status_equipamento_chamado = 'ENTREGA', status_equipamento_chamado_ant = 'ABERTO',
                     ultima_alteracao_equipamento_chamado = NOW()
                     where num_equipamento_chamado = '" . $num_equip . "' and id_chamado_equipamento = " . $dados['id_chamado']);
                     
                     // ------------ LOG -------------------

                     $log = array(
                        'acao_evento' => 'ALTERAR_STATUS_EQUIP',
                        'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'] . " - NUM: " . $num_equip . " - NOVO STATUS: ENTREGA",
                        'id_usuario_evento' => $_SESSION['id_usuario']
                        );
      
                     $this->db->insert('evento', $log);
   
                     // -------------- /LOG ----------------
                  
                  } else { // se nao, marcar o equipamento como atendido

                     $this->db->query("update equipamento_chamado set status_equipamento_chamado = 'ATENDIDO', status_equipamento_chamado_ant = 'ABERTO',
                     ultima_alteracao_equipamento_chamado = NOW()
                     where num_equipamento_chamado = '" . $num_equip . "' and id_chamado_equipamento = " . $dados['id_chamado']);

                     // ------------ LOG -------------------

                     $log = array(
                        'acao_evento' => 'ALTERAR_STATUS_EQUIP',
                        'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'] . " - NUM: " . $num_equip . " - NOVO STATUS: ATENDIDO",
                        'id_usuario_evento' => $_SESSION['id_usuario']
                        );
      
                     $this->db->insert('evento', $log);
   
                     // -------------- /LOG ----------------

                  }
                  
                  $dados['texto'] .= "<li>" . $num_equip . "</li>"; 
               }
               $i = 0;
               foreach ($dados['servicos_atendidos'] as $servico) {
                
               $sql = "update servico_chamado set status_servico_chamado = 'FECHADO', 
               data_ult_alteracao_servico_chamado = NOW() WHERE id = '" . $dados['id_servicos_atendidos'][$i] . "'";
               
               
               $this->db->query($sql);
 
               // ------------ LOG -------------------
               $log = array(
                  'acao_evento' => 'ALTERAR_STATUS_SERVICO',
                  'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'] . " - SERVICO: " . $servico . " - NOVO STATUS: FECHADO", 
                  'id_usuario_evento' => $_SESSION['id_usuario']
               );
       
               $this->db->insert('evento', $log);
    
               // -------------- /LOG ----------------
               
               $dados['texto'] .= "<li>" . $dados['nome_servico'][$i] . "</li>"; 
               $i++;
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
               
               $equip_restantes = $this->db->query("select num_equipamento_chamado from equipamento_chamado where id_chamado_equipamento = " . $dados['id_chamado'] .
               " and (status_equipamento_chamado = 'ABERTO' or status_equipamento_chamado = 'ESPERA' 
               or status_equipamento_chamado = 'ENTREGA' or status_equipamento_chamado = 'FALHA' or status_equipamento_chamado = 'REPARO' or status_equipamento_chamado = 'GARANTIA')")->num_rows();

               $servicos_restantes = $this->db->query("SELECT status_servico_chamado FROM servico_chamado 
               WHERE id_chamado_servico = " . $dados['id_chamado'] . " AND status_servico_chamado = 'ABERTO'")->num_rows();
               
               if ($equip_restantes == 0 && $servicos_restantes == 0) { //se todos os patrimonios tiverem sido atendidos/entregues
                  
                  $dados['tipo'] = 'FECHAMENTO'; //marcar o chamado como fechado ***

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

               foreach ($dados['equip_atendidos'] as $num_equip) { //marcando os patrimonios escolhidos como ESPERA
                  $this->db->query("update equipamento_chamado set status_equipamento_chamado = 'ESPERA',
                  ultima_alteracao_equipamento_chamado = NOW()" .
                  " where num_equipamento_chamado = '" . $num_equip . 
                  "' and (status_equipamento_chamado = 'ABERTO' or status_equipamento_chamado = 'FALHA')" .
                  " and id_chamado_equipamento = " . $dados['id_chamado']);

                  // ------------ LOG -------------------

                  $log = array(
                     'acao_evento' => 'ALTERAR_STATUS_EQUIP',
                     'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'] . " - NUM: " . $num_equip . " - NOVO STATUS: ESPERA",
                     'id_usuario_evento' => $_SESSION['id_usuario']
                     );
   
                  $this->db->insert('evento', $log);

                  // -------------- /LOG ----------------

                  $dados['texto'] .= "<li>" . $num_equip . "</li>";
               }

               $dados['texto'] .= "</ul></p>";
               
            }

            if($dados['tipo'] == 'REM_ESPERA') {

               $dados['texto'] .= "<hr class=\"m-0\" /><p class=\"m-0\">Foram removidos da espera os equipamentos:<br /><ul>";

               foreach ($dados['equip_atendidos'] as $num_equip) { //marcando os patrimonios escolhidos como ABERTO
                  $this->db->query("update equipamento_chamado set status_equipamento_chamado = 'ABERTO',
                  ultima_alteracao_equipamento_chamado = NOW()" .
                  " where num_equipamento_chamado = '" . $num_equip . 
                  "' and status_equipamento_chamado = 'ESPERA'" .
                  " and id_chamado_equipamento = " . $dados['id_chamado']);

                  // ------------ LOG -------------------

                  $log = array(
                     'acao_evento' => 'ALTERAR_STATUS_EQUIP',
                     'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'] . " - NUM: " . $num_equip . " - NOVO STATUS: ABERTO",
                     'id_usuario_evento' => $_SESSION['id_usuario']
                     );
   
                  $this->db->insert('evento', $log);

                  // -------------- /LOG ----------------

                  $dados['texto'] .= "<li>" . $num_equip . "</li>";
               }

               $dados['texto'] .= "</ul></p>";

               
            }

            if($dados['tipo'] == 'INSERVIVEL_REPARO' || $dados['tipo'] == 'INSERVIVEL') {
               $status = '';
               $num_equip = $dados['equip_atendidos'][0];

               if ($dados['tipo'] == 'INSERVIVEL_REPARO')
                  $status = 'REMESSA';
               else
                  $status = 'INSERVIVEL';
               
               $this->db->query("update equipamento_chamado set status_equipamento_chamado = '{$status}', status_equipamento_chamado_ant = 'REPARO',
               ultima_alteracao_equipamento_chamado = NOW()
               where num_equipamento_chamado = '" . $num_equip  . "' and id_chamado_equipamento = " . $dados['id_chamado']);

               // ------------ LOG -------------------

               $log = array(
                  'acao_evento' => 'ALTERAR_STATUS_EQUIP',
                  'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'] . " - NUM: " . $num_equip  . " - NOVO STATUS: {$status}",
                  'id_usuario_evento' => $_SESSION['id_usuario']
               );

               $this->db->insert('evento', $log);

               // -------------- /LOG ----------------

               
               $patri_restantes = $this->db->query("select num_equipamento_chamado from equipamento_chamado where id_chamado_equipamento = " . $dados['id_chamado'] .
               " and (status_equipamento_chamado = 'ABERTO' or status_equipamento_chamado = 'ESPERA' or status_equipamento_chamado = 'FALHA' or status_equipamento_chamado = 'REPARO' or status_equipamento_chamado = 'GARANTIA')")->num_rows();

               $equip_entrega = $this->db->query("select num_equipamento_chamado from equipamento_chamado where id_chamado_equipamento = " . $dados['id_chamado'] .
               " and status_equipamento_chamado = 'ENTREGA'")->num_rows();
               
               if ($patri_restantes == 0 && $equip_entrega == 0) { //se nao houver patrimonios marcados como ENTREGA ou ABERTO...

                  //$dados['tipo'] = 'FECHAMENTO_INS';

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

               
               // }
               // elseif($equip_entrega > 0) { //se houver patrimonios marcados como ENTREGA...

               //    $dados['tipo'] = 'ATENDIMENTO_INS';

                  
               //    $dados['texto'] .= 'O chamado foi sinalizado para <b>Entrega</b>';

               //    $this->db->query("update chamado set entrega_chamado = 1 where id_chamado = " . $dados['id_chamado'] ); 

               //    // ------------ LOG -------------------

               //       $log = array(
               //       'acao_evento' => 'SINALIZAR_ENTREGA_CHAMADO',
               //       'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'],
               //       'id_usuario_evento' => $_SESSION['id_usuario']
               //       );
                     
               //       $this->db->insert('evento', $log);
   
               //    // -------------- /LOG ----------------



               // }
               // else { //se não, o tipo é ATENDIMENTO_INS

               //    $dados['tipo'] = 'ATENDIMENTO_INS';
               // }

            }  
            
         }

      }

      else {

         $dados['equip_atendidos'] = array();
         $dados['texto'] = "";


      }

      if($dados['tipo'] == 'ENTREGA') {
         if (isset($dados['nome_termo_entrega'])):
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

               $dados['texto'] .= "<a role=\"button\" class=\"btn btn-sm btn-primary float-right mt-2 ml-2\" href=\"/termos/" .
               $dados['nome_termo_responsabilidade'] . "\" download><i class=\"fas fa-file-download\"></i> Termo de Responsabilidade</a>";
            }
            
            
            $dados['texto'] .= "<a role=\"button\" class=\"btn btn-sm btn-primary float-right mt-2\" href=\"/termos/" . 
            $dados['nome_termo_entrega'] . "\" download><i class=\"fas fa-file-download\"></i> Termo de Entrega</a>";

            

            $equip_entregues = $this->db->query("select * from equipamento_chamado where id_chamado_equipamento = " . $dados['id_chamado'] .
            " and status_equipamento_chamado = 'ENTREGA'")->result_array();

            $equip_restantes = $this->db->query("select num_equipamento_chamado from equipamento_chamado where id_chamado_equipamento = " . $dados['id_chamado'] .
                  " and (status_equipamento_chamado = 'ABERTO' or status_equipamento_chamado = 'ESPERA' or status_equipamento_chamado = 'FALHA' or status_equipamento_chamado = 'REPARO' or status_equipamento_chamado = 'GARANTIA')")->num_rows();

            

            if(!empty($equip_entregues)) { // verificando se existem equipamentos sem patrimonio que foram entregues

               $dados['texto'] .= "Os equipamentos foram entregues:<ul>";

         

            for ($i = 0; $i < count($equip_entregues); $i++) {

            
               $this->db->query("update equipamento_chamado set status_equipamento_chamado = 'ENTREGUE', ultima_alteracao_equipamento_chamado = NOW()
                  where num_equipamento_chamado = '" . $equip_entregues[$i]['num_equipamento_chamado'] . "' and id_chamado_equipamento = " . $dados['id_chamado']);

                  array_push($dados['equip_atendidos'],$equip_entregues[$i]['num_equipamento_chamado']);
                  
                  // ------------ LOG -------------------

                  $log = array(
                     'acao_evento' => 'ALTERAR_STATUS_EQUIP',
                     'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'] . 
                     " - EQUIPAMENTO: " . $equip_entregues[$i]['num_equipamento_chamado'] . " - NOVO STATUS: ENTREGUE",
                     'id_usuario_evento' => $_SESSION['id_usuario']
                     );

                  $this->db->insert('evento', $log);

                  // -------------- /LOG ----------------

               $dados['texto'] .= "<li>" . $equip_entregues[$i]['num_equipamento_chamado'] . "</li>";
            
            }
               $dados['texto'] .= "</ul>";
            }

            $dados['texto'] .= "<p>Recebido por: <strong>" . $dados['nome_recebedor'] ."</strong></p>"; 

            if ($equip_restantes == 0) {
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

         endif;   
      }

      if ($dados['tipo'] == 'FALHA_ENTREGA') {

         $this->db->query('update chamado set entrega_chamado = 0 where id_chamado = ' . $dados['id_chamado']); //tirando o sinal de entrega.. entrega_chamado = 0
         
         $equip_entrega = $this->db->query("select num_equipamento_chamado from equipamento_chamado where id_chamado_equipamento = " . $dados['id_chamado'] .
         " and status_equipamento_chamado = 'ENTREGA'")->result_array();

         $dados['texto'] .= $dados['txtFalhaEntrega'];

         $dados['texto'] .= "<hr><p class=\"m-0\">Os seguintes equipamentos retornaram:</p><ul>";

                           
         if(!empty($equip_entrega)) {
            foreach ($equip_entrega as $equip) {
               $this->db->query("update equipamento_chamado set status_equipamento_chamado = 'FALHA', status_equipamento_chamado_ant = 'ENTREGA',
               ultima_alteracao_equipamento_chamado = NOW()
                  where num_equipamento_chamado = '" . $equip['num_equipamento_chamado'] . "' and id_chamado_equipamento = " . $dados['id_chamado']);
               
                  $dados['texto'] .= '<li>' . $equip['num_equipamento_chamado'] . '</li>';

                  array_push($dados['equip_atendidos'],$equip['num_equipamento_chamado']);
                
                  // ------------ LOG -------------------

                $log = array(
                  'acao_evento' => 'ALTERAR_STATUS_EQUIP',
                  'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'] . " - NUM: " . $equip['num_equipamento_chamado'] . " - NOVO STATUS: FALHA",
                  'id_usuario_evento' => $_SESSION['id_usuario']
                  );

               $this->db->insert('evento', $log);

               // -------------- /LOG ----------------
                  
            }
            $dados['texto'] .= '</ul>';

         }
   

      }

      if ($dados['tipo'] == 'TENTATIVA_ENTREGA') {
         $equip_entrega = $this->db->query(
         "select num_equipamento_chamado from equipamento_chamado 
         where id_chamado_equipamento = " . $dados['id_chamado'] .
         " and status_equipamento_chamado = 'ENTREGA'")->result_array();

         $dados['texto'] .= $dados['txtFalhaEntrega'];
         $dados['texto'] .= "<hr>";
         $dados['texto'] .= "<p class=\"m-0\">Os seguintes equipamentos retornaram:</p><ul>";

         if(!empty($equip_entrega)) {
            foreach ($equip_entrega as $equip) {
               
               
                  $dados['texto'] .= '<li>' . $equip['num_equipamento_chamado'] . '</li>';

                  array_push($dados['equip_atendidos'],$equip['num_equipamento_chamado']);
                
               // ------------ LOG -------------------

                $log = array(
                  'acao_evento' => 'ALTERAR_STATUS_EQUIP',
                  'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'] . " - NUM: " . $equip['num_equipamento_chamado'] . " - NOVO STATUS: FALHA",
                  'id_usuario_evento' => $_SESSION['id_usuario']
                  );

               $this->db->insert('evento', $log);

               // -------------- /LOG ----------------
                  
            }
            $dados['texto'] .= '</ul>';

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

      if ($dados['tipo'] == 'FECHAMENTO_MAN') {

       

         $dados['texto'] = "O chamado foi finalizado de forma manual.<br><br>";
        

         // ------------ LOG -------------------

         $log = array(
            'acao_evento' => 'FINALIZAR_CHAMADO_MANUAL',
            'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'],
            'id_usuario_evento' => $_SESSION['id_usuario']
            );

         $this->db->insert('evento', $log);

         // -------------- /LOG ----------------


      }

      $equips = NULL;
      $servicos = NULL;
    

      if ($dados['tipo'] != 'FECHAMENTO_MAN') {

         $equips = implode("::",$dados['equip_atendidos']);

         if (isset($dados['id_servicos_atendidos']))
         {
            $servicos = implode("::",$dados['id_servicos_atendidos']);
         }

        
      }

     
      

      $nova_interacao = array (
         'id_interacao' => NULL,
         'tipo_interacao' => $dados['tipo'],
         'texto_interacao' => $dados['texto'],
         'id_chamado_interacao' => $dados['id_chamado'],
         'id_usuario_interacao' => $dados['id_usuario'],
         'id_fila_ant_interacao' => $fila_ant_int,
         'pool_equipamentos' => $equips,
         'pool_servicos' => $servicos,

      ); 
      //aqui

     

      if ($dados['tipo'] == 'ENTREGA') { // checando se foi enviado o arquivo PDF do termo de entrega

         if (isset($dados['nome_termo_entrega'])) {
            
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

        
      }

      else {
        $inserir = $this->db->insert('interacao',$nova_interacao);
      }
      

      // ------------ LOG -------------------

      $log = array(
      'acao_evento' => 'REGISTRAR_INTERACAO',
      'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'] . " - TIPO: " . $dados['tipo'],
      'id_usuario_evento' => $_SESSION['id_usuario']
      );

      $log = $this->db->insert('evento', $log);
   }
    
   public function buscaInteracoes($id_chamado) {

      $this->db->from('interacao');
      $this->db->where(array('id_chamado_interacao' => $id_chamado));
      $this->db->order_by('data_interacao DESC');

      $result = $this->db->get()->result();

      return $result;
   }
   
   public function removeInteracao($p_id_interacao) {
      $interacao = $this->db->get_where('interacao', array('id_interacao' => $p_id_interacao))->row();

      $chamado = $this->db->get_where('chamado', array('id_chamado' => $interacao->id_chamado_interacao))->row();
	  
	   $pool_equips = explode("::",$interacao->pool_equipamentos);
      $pool_servicos = explode("::", $interacao->pool_servicos);

      switch ($interacao->tipo_interacao) {

         case 'ATENDIMENTO':

            //var_dump(count($pool_equips));
		 
            if (count($pool_equips) >= 1) {
               foreach ($pool_equips as $num_equip) { 
                  $this->db->query("update equipamento_chamado set status_equipamento_chamado_ant = status_equipamento_chamado, 
                  status_equipamento_chamado = 'ABERTO',
                  ultima_alteracao_equipamento_chamado = NOW()
                  where id_chamado_equipamento = " . $interacao->id_chamado_interacao . " and
                  num_equipamento_chamado = '" . $num_equip . "'");

                  // ------------ LOG -------------------

                  $log = array(
                     'acao_evento' => 'ALTERAR_STATUS_EQUIP',
                     'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . " - NUM: " . $num_equip . " - NOVO STATUS: ABERTO",
                     'id_usuario_evento' => $_SESSION['id_usuario']
                     );

                  $this->db->insert('evento', $log);

                  // -------------- /LOG ----------------
               }

               $entregas_pendentes = $this->db->query("select * from equipamento_chamado
                  where id_chamado_equipamento = " . $interacao->id_chamado_interacao . " and
                  status_equipamento_chamado = 'ENTREGA'")->num_rows();

               if ($chamado->entrega_chamado == '1' && $entregas_pendentes == 0) {

                  $this->db->set('entrega_chamado', '0');
                  $this->db->where('id_chamado', $interacao->id_chamado_interacao);
                  $this->db->update('chamado');
               }
            

            
               

               // ------------ LOG -------------------

               $log = array(
                  'acao_evento' => 'CANCELAR_ENTREGA_CHAMADO',
                  'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao,
                  'id_usuario_evento' => $_SESSION['id_usuario']
                  );

               $this->db->insert('evento', $log);

               // -------------- /LOG ----------------
            
            }
            if(count($pool_servicos) >= 1){
               
               foreach($pool_servicos as $servico){
                  if($servico != NULL || $servico != ''){
                     $sql = "UPDATE servico_chamado
                     SET status_servico_chamado = 'ABERTO', data_ult_alteracao_servico_chamado= NOW() WHERE id = " . $servico . "";

                     $this->db->query($sql);

                        // ------------ LOG -------------------

                     $log = array(
                        'acao_evento' => 'ALTERAR_STATUS_SERVICO',
                        'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . " - ID_SERVICO: " . $servico . " - NOVO STATUS: ABERTO",
                        'id_usuario_evento' => $_SESSION['id_usuario']
                        );

                     $this->db->insert('evento', $log);

                     // -------------- /LOG ----------------
                  }
                  
               }

               
            }
         
         break;

         case 'ATENDIMENTO_INS':

            foreach ($pool_equips as $num_equip) { 
               $this->db->query("update equipamento_chamado set status_equipamento_chamado_ant = status_equipamento_chamado, 
               status_equipamento_chamado = 'ABERTO',
               ultima_alteracao_equipamento_chamado = NOW()
               where id_chamado_equipamento = " . $interacao->id_chamado_interacao . " and
               num_equipamento_chamado = '" . $num_equip . "'");

               // ------------ LOG -------------------

               $log = array(
                  'acao_evento' => 'ALTERAR_STATUS_EQUIP',
                  'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . " - NUM: " . $num_equip . " - NOVO STATUS: ABERTO",
                  'id_usuario_evento' => $_SESSION['id_usuario']
                  );

               $this->db->insert('evento', $log);

               // -------------- /LOG ----------------
            }

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

         case 'FECHAMENTO':

            foreach ($pool_equips as $num_equip) { 
               $this->db->query("update equipamento_chamado set status_equipamento_chamado_ant = status_equipamento_chamado, 
               status_equipamento_chamado = 'ABERTO',
               ultima_alteracao_equipamento_chamado = NOW()
               where id_chamado_equipamento = " . $interacao->id_chamado_interacao . " and
               num_equipamento_chamado = '" . $num_equip . "'");

               // ------------ LOG -------------------

               $log = array(
                  'acao_evento' => 'ALTERAR_STATUS_EQUIP',
                  'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . " - NUM: " . $num_equip . " - NOVO STATUS: ABERTO",
                  'id_usuario_evento' => $_SESSION['id_usuario']
                  );

               $this->db->insert('evento', $log);

               // -------------- /LOG ----------------
            }

            foreach($pool_servicos as $servico){
               if($servico != NULL || $servico != '')
               {
                  $sql = "UPDATE servico_chamado
                  SET status_servico_chamado = 'ABERTO', data_ult_alteracao_servico_chamado= NOW() WHERE id = " . $servico . "";

                  $this->db->query($sql);
               
                  // ------------ LOG -------------------

               $log = array(
                  'acao_evento' => 'ALTERAR_STATUS_SERVICO',
                  'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . " - ID_SERVICO: " . $servico . " - NOVO STATUS: ABERTO",
                  'id_usuario_evento' => $_SESSION['id_usuario']
                  );

               $this->db->insert('evento', $log);
               
                  // -------------- /LOG ----------------
               }                  
            
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

            foreach ($pool_equips as $num_equip) { 
               $this->db->query("update equipamento_chamado set status_equipamento_chamado_ant = status_equipamento_chamado, 
               status_equipamento_chamado = 'ABERTO',
               ultima_alteracao_equipamento_chamado = NOW()
               where id_chamado_equipamento = " . $interacao->id_chamado_interacao . " and
               num_equipamento_chamado = '" . $num_equip . "'");

               // ------------ LOG -------------------

               $log = array(
                  'acao_evento' => 'ALTERAR_STATUS_EQUIP',
                  'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . " - NUM: " . $num_equip . " - NOVO STATUS: ABERTO",
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

            if (!empty($pool_equips)) {
               foreach ($pool_equips as $num_equip) { 
                  $this->db->query("update equipamento_chamado set status_equipamento_chamado = 'ENTREGA',
                  ultima_alteracao_equipamento_chamado = NOW()" .
                  " where num_equipamento_chamado = '" . $num_equip . 
                  "' and status_equipamento_chamado = 'ENTREGUE'" .
                  " and id_chamado_equipamento = " . $interacao->id_chamado_interacao);

                  // ------------ LOG -------------------

                  $log = array(
                     'acao_evento' => 'DESFAZER_ENTREGA_EQUIP',
                     'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . " - NUM: " . $num_equip,
                     'id_usuario_evento' => $_SESSION['id_usuario']
                     );

                  $this->db->insert('evento', $log);

                  // -------------- /LOG ----------------
               }

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

            $apagar_termo = unlink($this->config->item('caminho_termos') . $termo_e->nome_termo);

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

               $apagar_termo = unlink($this->config->item('caminho_termos') . $termo_r->nome_termo);

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

            if (!empty($pool_equips)) {
               foreach ($pool_equips as $num_equip) { //patrimonios[0] é o vetor com a lista de patrimonios da interacao
                  $this->db->query("update equipamento_chamado set status_equipamento_chamado = 'ENTREGA', " .
                  "status_equipamento_chamado_ant = 'FALHA',
                  ultima_alteracao_equipamento_chamado = NOW()" .
                  " where num_equipamento_chamado = '" . $num_equip . 
                  "' and status_equipamento_chamado = 'FALHA'" .
                  " and id_chamado_equipamento = " . $interacao->id_chamado_interacao);

                  // ------------ LOG -------------------

                  $log = array(
                     'acao_evento' => 'DESFAZER_FALHA_ENTREGA_EQUIP',
                     'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . " - NUM: " . $num_equip,
                     'id_usuario_evento' => $_SESSION['id_usuario']
                     );

                  $this->db->insert('evento', $log);

                  // -------------- /LOG ----------------
               }
            }

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

         case 'TENTATIVA_ENTREGA':

            if (!empty($pool_equips)) {
               foreach ($pool_equips as $num_equip) { //patrimonios[0] é o vetor com a lista de patrimonios da interacao

                  // ------------ LOG -------------------

                  $log = array(
                     'acao_evento' => 'DESFAZER_TENTATIVA_ENTREGA_EQUIP',
                     'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . " - NUM: " . $num_equip,
                     'id_usuario_evento' => $_SESSION['id_usuario']
                     );

                  $this->db->insert('evento', $log);

                  // -------------- /LOG ----------------
               }
            }

         break;

         case 'ESPERA':

            foreach ($pool_equips as $num_equip) { //patrimonios[0] é o vetor com a lista de patrimonios da interacao
               $this->db->query("update equipamento_chamado set status_equipamento_chamado = 'ABERTO',
               ultima_alteracao_equipamento_chamado = NOW()" .
               " where num_equipamento_chamado = " . $num_equip . 
               " and status_equipamento_chamado = 'ESPERA'" .
               " and id_chamado_equipamento = " . $interacao->id_chamado_interacao);

                // ------------ LOG -------------------

                $log = array(
                  'acao_evento' => 'DESFAZER_ESPERA_equipamento',
                  'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . " - NUM: " . $num_equip . " - NOVO STATUS: ABERTO",
                  'id_usuario_evento' => $_SESSION['id_usuario']
                  );

               $this->db->insert('evento', $log);

               // -------------- /LOG ----------------
            }

         break;

         case 'REM_ESPERA':

            foreach ($pool_equips as $num_equip) { //patrimonios[0] é o vetor com a lista de patrimonios da interacao
               $this->db->query("update equipamento_chamado set status_equipamento_chamado = 'ESPERA',
               ultima_alteracao_equipamento_chamado = NOW()" .
               " where num_equipamento_chamado = " . $num_equip . 
               " and status_equipamento_chamado = 'ABERTO'" .
               " and id_chamado_equipamento = " . $interacao->id_chamado_interacao);

               // ------------ LOG -------------------

               $log = array(
                  'acao_evento' => 'DESFAZER_REM_ESPERA_PATRI',
                  'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . " - NUM: " . $num_equip . " - NOVO STATUS: ESPERA",
                  'id_usuario_evento' => $_SESSION['id_usuario']
                  );

               $this->db->insert('evento', $log);

               // -------------- /LOG ----------------
            }

         break;

         case 'FECHAMENTO_MAN':

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
    
   
   public function buscaInteracaoChamado($id_interacao) {

      $query = $this->db->query("SELECT DATE_FORMAT(data_interacao, '%d/%m/%Y - %H:%i:%s') AS data_interacao, 
      id_interacao, texto_interacao, nome_usuario, id_chamado_interacao, pool_equipamentos, ticket_chamado, 
      DATE_FORMAT(data_chamado, '%d/%m/%Y - %H:%i:%s') AS data_chamado, nome_local, nome_solicitante_chamado
      FROM interacao i
      INNER JOIN usuario u ON(u.id_usuario = i.id_usuario_interacao)
      INNER JOIN chamado c ON(i.id_chamado_interacao = c.id_chamado)
      INNER JOIN local l ON(c.id_local_chamado = l.id_local)
      WHERE i.id_interacao = " . $id_interacao);
      
      $result = $query->row();

      return $result;
   }

   public function buscaInteracao($id_interacao) {

      $this->db->select();
      $this->db->from('interacao');
      $this->db->where(array('id_interacao' => $id_interacao));
      $result = $this->db->get()->row();
      return $result;
   }

   public function logEnviarEmail($id_chamado) {
      // ------------ LOG -------------------
      $log = array(
         'acao_evento' => 'ENVIAR_EMAIL',
         'desc_evento' => 'ID CHAMADO: ' . $id_chamado ,
         'id_usuario_evento' => $_SESSION['id_usuario']
      );
   
      $this->db->insert('evento', $log);

      // -------------- /LOG ----------------
   }

   public function buscaUltimaInteracao($id_chamado) {

      $query = $this->db->query("SELECT data_interacao, DATE_FORMAT(data_interacao, '%d/%m/%Y - %H:%i:%s') AS data_interacao_br, id_interacao, texto_interacao, nome_usuario,
      tipo_interacao
      FROM interacao i
      INNER JOIN usuario u ON(u.id_usuario = i.id_usuario_interacao)
      WHERE i.id_chamado_interacao = " . $id_chamado . " ORDER BY data_interacao desc");
      
      $result = $query->row();
      return $result;
   }

   public function buscarAnexoOtrs($otobo, $tipos_arquivos) {
      $db_otrs = $this->load->database('otrs', TRUE);

      $db_otrs->from('article_data_mime_attachment');
      $db_otrs->select('id as id_anexo_otrs, filename as nome_arquivo_otrs');
      $db_otrs->where('article_id', $otobo->ArticleID);
      $db_otrs->where_in('content_type', $tipos_arquivos);

      return $db_otrs->get()->result();
   }

   public function buscaSolicitanteOtobo($id_ticket_chamado) {
      $db_otrs = $this->load->database('otrs', TRUE);

      $db_otrs->select('customer_user_id');
      $db_otrs->from('ticket');
      $db_otrs->where('id', $id_ticket_chamado);

      return $db_otrs->get()->row()->customer_user_id;
   }

   public function buscarEmailSolicitanteOtobo($ticked_id, $article_sender_type = true) {
      $db_otrs = $this->load->database('otrs', TRUE);

      $db_otrs->select('
         article_data_mime.a_from,
         article_data_mime.a_reply_to,
         article_data_mime.a_to,
         article_data_mime.a_cc,
         article_data_mime.a_bcc
      ');
      $db_otrs->from('article');
      $db_otrs->join('article_data_mime', 'article.id = article_data_mime.article_id', 'INNER');
      $db_otrs->where('ticket_id', $ticked_id);
      $db_otrs->where('is_visible_for_customer', "1");

      if ($article_sender_type) {
         $db_otrs->where('article_sender_type_id', 3);
      }

      return $db_otrs->get()->result();
   }

   public function insereAnexoSigat($id_chamado_sigat, $id_article, $file_name) {
      $this->db->set('id_chamado_sigat', $id_chamado_sigat);
      $this->db->set('id_anexo_otrs', $id_article);
      $this->db->set('id_triagem_sigat', 0);
      $this->db->set('nome_arquivo_otrs', $file_name);
      $this->db->set('id_triagem_sigat', 0);
      $this->db->insert('anexos_otrs');

      // ------------ LOG -------------------

      $log = array(
         'acao_evento' => 'ADICIONAR_ANEXO_EMAIL',
         'desc_evento' => 'ID ANEXO: ' . $id_article,
         'id_usuario_evento' => $_SESSION['id_usuario']
      );
   
      $this->db->insert('evento', $log);

      // -------------- /LOG ----------------
   }
}

?>