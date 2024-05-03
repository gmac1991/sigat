<?php

defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('America/Sao_Paulo');

class Chamado_model extends CI_Model {

    public function importaChamado($dados) {
        $msg = NULL;

        $this_model = new self; //re-instancia a classe Chamado_model para utilizar seus métodos na função 'registrar'

        // ------------ FUNCAO PARA IMPORTAR------------------ 

        function importar($inst,$sql_insert,$p_nome_fila,$p_id_usuario) {

            $id_novo_chamado = FALSE;

            $inst->db->trans_start();

            $inst->db->query($sql_insert); //registrando o chamado
            
            $query = $inst->db->query('SELECT id_chamado from chamado order by data_chamado desc LIMIT 1');
            $linha = $query->row_array();

            $id_novo_chamado = $linha['id_chamado'];
            
            $inst->db->trans_complete();
             
           
            
            //$id_novo_chamado = $inst->db->insert_id(); // buscando o ID do chamado recem aberto

            $inst->db->query("insert into alteracao_chamado values(NULL," . $id_novo_chamado  . "," . //criando historico de alteracao
            $p_id_usuario .", ' abriu o chamado na fila <b>" . 
            $p_nome_fila . "</b>', NOW())"); 

            

            return $id_novo_chamado;
        
        }
                       
        $q_buscaIdLocal = "select id_local from local where nome_local = '". addslashes($dados['nome_local']) . "'";

        $r_id_local = $this->db->query($q_buscaIdLocal);

            if ($r_id_local->num_rows() > 0) { // validando local

                $id_local = $r_id_local->row()->id_local;

                $complementoM = mb_strtoupper($dados['comp_local'],'UTF-8');
                $resumoM = mb_strtoupper($dados['resumo_solicitacao'],'UTF-8');

                //$id_ticket_otrs = $this->db->query("select id_ticket_triagem from triagem where id_triagem = " .$dados['id_triagem'])->row()->id_ticket_triagem;

                $q_insereChamado = 
                "INSERT INTO `db_sigat`.`chamado` (`id_local_chamado`, `nome_solicitante_chamado`, `telefone_chamado`, `celular_chamado`,
                `id_usuario_abertura_chamado`, `status_chamado`, `id_fila_chamado`, `data_chamado`, `ticket_chamado`, 
                `id_ticket_chamado`,`complemento_chamado`, `resumo_chamado`, `data_encerramento_chamado`) values(" . 
                $id_local . ",'" .
                $dados['nome_solicitante'] . "','" .
                $dados['telefone'] . "','" .
                $dados['celular'] . "'," .
                $dados['id_usuario'] . ", 'ABERTO', ".
                $dados['id_fila'] .", NOW(),'" .
                $dados['num_ticket'] . "'," .
                $dados['id_ticket'] . ",'" .
                $complementoM . "','" .
                $resumoM . "',NULL)";

                if (strlen($resumoM) > 6)
                    $this->db->query("insert resumo values(NULL,'" . $resumoM . "')"); // cadastrando resumos
                if (strlen($complementoM) > 6)
                    $this->db->query("insert complemento values(NULL,'" . $complementoM . "')"); // cadastrando complementos

            

                $nome_fila = $this->db->query("select nome_fila from fila where id_fila = 1")->row()->nome_fila;
                    // if (!empty($dados['listaEquipamentos']) || $dados['serv_tel'] == 1 || $dados['serv_rede'] == 1) {
                if (!empty($dados['listaEquipamentos']) || !empty($dados['listaServicos']) ) {

                    $novo_id = importar($this_model,$q_insereChamado,$nome_fila,$dados['id_usuario']);

                    if( $novo_id !== FALSE) {

                        // ------------ LOG -------------------

                        $log = array(
                            'acao_evento' => 'INSERIR_CHAMADO',
                            'desc_evento' => 'ID CHAMADO: ' . $novo_id ,
                            'id_usuario_evento' => $_SESSION['id_usuario']
                        );
                        
                        $this->db->insert('evento', $log);

                        // -------------- /LOG ----------------

                        if (!empty($dados['listaEquipamentos'])) {
                            foreach($dados['listaEquipamentos'] as $equip) { //registrando nas tabelas equipamento_chamado e, se necessario, na tabela equipamento
                                $busca_equip = $this->db->query("select * from equipamento where num_equipamento = '". $equip->Número ."'");
                                if ($busca_equip->num_rows() == 0) { //equipamento novo
                                    $this->db->query("insert into equipamento values('". $equip->Número ."','". $equip->Descrição . "',NOW(),NULL,NULL)");
                                }
                                $this->db->query("insert into equipamento_chamado values('" . $equip->Número."','ABERTO', NULL, NOW(),". $novo_id .")");
                            }

                        }


                        if (!empty($dados['listaServicos'])) {
                            foreach($dados['listaServicos'] as $servico) {
            
                                $this->db->query("insert into servico_chamado
                                (id_chamado_servico, id_servico_chamado, status_servico_chamado, data_ult_alteracao_servico_chamado, quantidade) values(" . $novo_id . "," . $servico->id_servico . 
                                ",'ABERTO', NOW(), " . $servico->quantidade .");");
                            }

                        }
                

                        foreach($dados['anexos'] as $anexo) {
                            $this->db->query("insert into anexos_otrs(id_chamado_sigat,id_anexo_otrs) values(".$novo_id.",". $anexo->id_arquivo.")");
        
                        }

                    //$this->db->query("delete from anexos_otrs where id_chamado_sigat is NULL and id_triagem_sigat = " . $dados['id_triagem']); //deletando anexos descartados
                    //$this->db->query("update triagem set triado_triagem = 1 where id_triagem = " . $dados['id_triagem']); //marcando triagem como realizada
                    
                
                    $msg = "";
                    $msg = "<div id=\"alerta\" class=\"alert alert-success\">";
                    $msg .= "<small class=\"float-right\">". date('G:i:s') . "</small>";
                    $msg .= "Importação concluída! Chamado n. "; 
                    $msg .= $novo_id . "<br /><a href=". base_url('/painel?v=triagem') . ">Voltar para o painel</a>";
                    $msg .= "</div>"; 

                    return array("novo_id" => $novo_id, "msg" => $msg);
                }          
                else {
                    die($novo_id);
                }
            } 
            else {

                $msg .= "<div id=\"alerta\" class=\"alert alert-warning alert-dismissible\">" .
                "<a href=\"#\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">&times;</a>" .
                "Local inválido!" .
                "</div>";

                exit($msg);
            }
        }
    }

    public function alteraChamado($dados) {

        $msg = NULL;

        $q_buscaIdLocal = "select id_local from local where nome_local = '". addslashes($dados['nome_local']) . "'";

        $r_id_local = $this->db->query($q_buscaIdLocal);

        if ($r_id_local->num_rows() > 0) { // validando local
            $id_local = $r_id_local->row()->id_local;
        } else {


            $msg .= "<div id=\"alerta\" class=\"alert alert-warning alert-dismissible\">" .
                "<a href=\"#\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">&times;</a>" .
                "Local inválido!" .
                "</div>";

            
            exit($msg);

        }
        
        // ------ checando alteracoes no chamado ---------

        

        $texto_alteracao = "";


        $chamado_original = $this->db->query('select id_usuario_responsavel_chamado, 
        (select nome_usuario from usuario where id_usuario = id_usuario_responsavel_chamado) as nome_responsavel, 
        nome_solicitante_chamado, telefone_chamado, celular_chamado,
        id_local_chamado, (select nome_local from local where id_local = id_local_chamado) as nome_local from chamado
        where id_chamado = ' . $dados['id_chamado'])->row();

        if ($dados['id_responsavel'] != NULL) { //se foi enviado algum id_responsavel...
            $q_alteraChamado = 
            "update chamado set nome_solicitante_chamado = '" . $dados['nome_solicitante'] . 
            "', telefone_chamado = '" . $dados['telefone'] . 
            "', celular_chamado = '" . $dados['celular'] . "', id_local_chamado = " . $id_local . ", id_usuario_responsavel_chamado = "
            . $dados['id_responsavel'] . " where id_chamado = " . $dados['id_chamado'];


        } else { //se nao...
            $q_alteraChamado = 
            "update chamado set nome_solicitante_chamado = '" . $dados['nome_solicitante'] . 
            "', telefone_chamado = '" . $dados['telefone'] . 
            "', celular_chamado = '" . $dados['celular'] . "', id_local_chamado = " . $id_local . 
            " where id_chamado = " . $dados['id_chamado'];

            
        }

        $this->db->query($q_alteraChamado); //executa a alteracao

        //removido inserção de interacao

        //inserindo na tabela alteracao

        if ($chamado_original->id_local_chamado != $id_local) {

            $novo_nome_local = $this->db->query('select nome_local from local where id_local = ' . $id_local)->row()->nome_local;

            $texto_alteracao .= 'alterou o local de <strong>' . $chamado_original->nome_local . '</strong>';
            $texto_alteracao .= ' para <strong>' . $novo_nome_local . '</strong>; ';
        }

        if ($chamado_original->telefone_chamado != $dados['telefone']) {

            $texto_alteracao .= 'alterou o telefone de <strong>' . $chamado_original->telefone_chamado . '</strong>';
            $texto_alteracao .= ' para <strong>' . $dados['telefone'] . '</strong>; ';
        }

        if ($chamado_original->celular_chamado != $dados['celular']) {
            $texto_alteracao .= 'alterou o celular de <strong>' . $chamado_original->celular_chamado . '</strong>';
            $texto_alteracao .= ' para <strong>' . $dados['celular'] . '</strong>; ';
        }

        if ($chamado_original->nome_solicitante_chamado != $dados['nome_solicitante']) {

            $texto_alteracao .= 'alterou o solicitante de <strong>' . $chamado_original->nome_solicitante_chamado . '</strong>';
            $texto_alteracao .= ' para <strong>' . $dados['nome_solicitante'] . '</strong>; ';
        }

        if ($dados['id_responsavel'] != NULL) { 

            if ($chamado_original->id_usuario_responsavel_chamado != $dados['id_responsavel']) {
                $novo_nome_responsavel = $this->db->query('select nome_usuario from usuario where id_usuario = ' . $dados['id_responsavel'])->row()->nome_usuario;

                if ($chamado_original->id_usuario_responsavel_chamado != NULL) { 
                    $texto_alteracao .= 'alterou o responsável de <strong>' . $chamado_original->nome_responsavel . '</strong>';
                    $texto_alteracao .= ' para <strong>' . $novo_nome_responsavel . '</strong>; ';

                }

                else { //se a alteracao do responsavel for de NULL para algum valor...

                    $texto_alteracao .= 'alterou o responsável';
                    $texto_alteracao .= ' para <strong>' . $novo_nome_responsavel . '</strong>; ';
                }
                
                
            }

        }

        

        if($this->db->query($q_alteraChamado)) {

            if ($texto_alteracao != NULL) {

                $nova_alteracao = array (
                    'id_alteracao' => NULL,
                    'data_alteracao' => date('Y-m-d H:i:s'),
                    'texto_alteracao' => $texto_alteracao,
                    'id_chamado_alteracao' => $dados['id_chamado'],
                    'id_usuario_alteracao' => $dados['id_usuario'],
           
                 ); 
                 
                 $this->db->insert('alteracao_chamado',$nova_alteracao);

                 // ------------ LOG -------------------

                 $log = array(
                    'acao_evento' => 'ALTERAR_CHAMADO',
                    'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'],
                    'id_usuario_evento' => $_SESSION['id_usuario']
                );
                
                $this->db->insert('evento', $log);

                // -------------- /LOG ----------------

            }
        //     exit($msg);
        }  
            
    }

    public function encerraChamado($dados) {

        

        $usuario = $this->db->query('select autorizacao_usuario, encerramento_usuario from usuario where id_usuario = ' 
                                    . $dados['id_usuario']);


        if($usuario->row()->autorizacao_usuario >= 3 && $usuario->row()->encerramento_usuario == 1) {
            
            $q_encerraChamado = $this->db->query("update chamado set status_chamado = 'ENCERRADO', data_encerramento_chamado = NOW() where id_chamado = " . $dados['id_chamado']);

            if($q_encerraChamado) {

                $sql = "INSERT INTO interacao
                (id_interacao, tipo_interacao, data_interacao, texto_interacao, id_chamado_interacao, id_usuario_interacao, id_fila_ant_interacao, pool_equipamentos, pool_servicos)
                VALUES(NULL, 'ENC', NOW(), ' encerrou o chamado', " 
                . $dados['id_chamado'] . ", " . $dados['id_usuario'] . ", NULL, NULL, NULL)";
                
                $this->db->query($sql); //inserindo a interacao

                $nova_alteracao = array (
                    'id_alteracao' => NULL,
                    'data_alteracao' => date('Y-m-d H:i:s'),
                    'texto_alteracao' => 'Encerrou o chamado',
                    'id_chamado_alteracao' => $dados['id_chamado'],
                    'id_usuario_alteracao' => $dados['id_usuario'],
           
                 ); 
                 
                 $this->db->insert('alteracao_chamado',$nova_alteracao);

                // ------------ LOG -------------------

                $log = array(
                    'acao_evento' => 'ENCERRAR_CHAMADO',
                    'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'],
                    'id_usuario_evento' => $_SESSION['id_usuario']
                );
                
                $this->db->insert('evento', $log);

                // -------------- /LOG ----------------

                
    
    
            }

            
        } else {

            header("HTTP/1.1 403 Forbidden");
        }
        
        
    }

    public function reabreChamado($dados) {

        

        $usuario = $this->db->query('select autorizacao_usuario, encerramento_usuario from usuario where id_usuario = ' 
                                    . $dados['id_usuario']);


        if($usuario->row()->autorizacao_usuario >= 3 && $usuario->row()->encerramento_usuario == 1) {
            
            $q_reabreChamado = $this->db->query("update chamado set status_chamado = 'ABERTO', data_encerramento_chamado = null where id_chamado = " . $dados['id_chamado']);

            if($q_reabreChamado) {

                $sql = "INSERT INTO interacao
                (id_interacao, tipo_interacao, data_interacao, texto_interacao, id_chamado_interacao, id_usuario_interacao, id_fila_ant_interacao, pool_equipamentos, pool_servicos)
                VALUES(NULL, 'ENC', NOW(), ' reabriu o chamado', " 
                . $dados['id_chamado'] . ", " . $dados['id_usuario'] . ", NULL, NULL, NULL)";
                
                $this->db->query($sql); //inserindo a interacao


                $nova_alteracao = array (
                    'id_alteracao' => NULL,
                    'data_alteracao' => date('Y-m-d H:i:s'),
                    'texto_alteracao' => 'Reabriu o chamado',
                    'id_chamado_alteracao' => $dados['id_chamado'],
                    'id_usuario_alteracao' => $dados['id_usuario'],
           
                 ); 
                 
                 $this->db->insert('alteracao_chamado',$nova_alteracao);

                // ------------ LOG -------------------

                $log = array(
                    'acao_evento' => 'REABRIR_CHAMADO',
                    'desc_evento' => 'ID CHAMADO: ' . $dados['id_chamado'],
                    'id_usuario_evento' => $_SESSION['id_usuario']
                );
                
                $this->db->insert('evento', $log);

                // -------------- /LOG ----------------

                
    
    
            }

            
        } else {

            header("HTTP/1.1 403 Forbidden");
        }
        
        
    }

    public function devolveChamado($p_id_chamado) {

        $ticket = $this->buscaTicketTriagem($p_id_chamado);
        //$this->db->query("delete from triagem where id_triagem = " . $p_id_chamado);
        $this->db->query("delete from anexos_otrs where id_chamado_sigat = " . $p_id_chamado);

        $this->db->query("insert into alteracao_chamado ".
                         "values(NULL," . $p_id_chamado . 
                         "," . $_SESSION['id_usuario'] .
                         ",'<b>devolveu o ticket ".  $ticket . " para o Nível 0</b>',NOW())");

         // ------------ LOG -------------------

         $log = array(
            'acao_evento' => 'DEVOLVER_TICKET',
            'desc_evento' => 'ID CHAMADO: ' . $p_id_chamado . ' - TICKET: ' . $ticket,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );
        
        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------


    }

    public function finalizaManualChamado($p_id_chamado) {

        $this->db->query("update chamado set status_chamado = 'FECHADO' where id_chamado = " . $p_id_chamado);
        

         // ------------ LOG -------------------

         $log = array(
            'acao_evento' => 'FECHAMENTO_MANUAL',
            'desc_evento' => 'ID CHAMADO: ' . $p_id_chamado,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );
        
        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------


    }

    public function buscaTicketTriagem($id_triagem) {

        $result = $this->db->query("select ticket_triagem from triagem where id_triagem = " . $id_triagem);

        return $result->row()->ticket_triagem;
    }

    public function buscaChamado($id_chamado, $status = "") {

	   $q_buscaChamado = "select status_chamado, complemento_chamado, id_ticket_chamado, ticket_chamado, id_chamado, id_fila, nome_solicitante_chamado, id_local, endereco_local, nome_local, regiao_local, infovia, DATE_FORMAT(data_chamado, '%d/%m/%Y - %H:%i:%s') as data_chamado, DATE_FORMAT(data_encerramento_chamado, '%d/%m/%Y - %H:%i:%s') as data_encerramento_chamado, telefone_chamado, celular_chamado,
        (select usuario.id_usuario from usuario where usuario.id_usuario = chamado.id_usuario_responsavel_chamado) as id_responsavel, 
        (select fila.nome_fila from fila where fila.id_fila = chamado.id_fila_chamado) as nome_fila_chamado, prioridade_chamado, resumo_chamado, email_nao_lido_chamado
        from local, fila, chamado
        where local.id_local = chamado.id_local_chamado and
        fila.id_fila = chamado.id_fila_chamado and
        chamado.id_chamado = " . $id_chamado;

        $q_buscaEquipamentos = "SELECT e.num_equipamento, e.descricao_equipamento, e.tag_equipamento
        FROM equipamento AS e, equipamento_chamado
        WHERE equipamento_chamado.id_chamado_equipamento = " . $id_chamado .
        " AND equipamento_chamado.num_equipamento_chamado = e.num_equipamento";
        
        if ($status !== "") {
            $q_buscaEquipamentos .= " AND status_equipamento_chamado in (" . $status . ")";
        }
        //$this->dd->dd($q_buscaEquipamentos);

        $q_buscaServicos = "SELECT s.nome_servico, s.unidade_medida, sc.quantidade, sc.data_ult_alteracao_servico_chamado, sc.status_servico_chamado
        FROM servico s INNER JOIN servico_chamado sc on s.id_servico = sc.id_servico_chamado
        WHERE sc.id_chamado_servico = " . $id_chamado;
        
        if ($status !== "") {
            $q_buscaServicos .= " AND status_servico_chamado not in (" . $status . ")";
        }
        
        
        $result['equipamentos'] = $this->db->query($q_buscaEquipamentos)->result();

        $result['servicos'] = $this->db->query($q_buscaServicos)->result();

        $result['chamado'] = $this->db->query($q_buscaChamado)->row();
        
        if (isset($result['chamado'])) {
            $result['icone'] = $this->db->query(
                "SELECT icone_fila from fila f 
                INNER JOIN chamado c ON(f.id_fila = c.id_fila_chamado) 
                WHERE id_chamado = ". $id_chamado)->row()->icone_fila;
        }
        

        return $result;
    }

    public function buscaHistoricoChamado($id_chamado) {
        $q_buscaHistorico = "SELECT u.nome_usuario, a.texto_alteracao, a.data_alteracao FROM alteracao_chamado AS a, usuario AS u
        WHERE u.id_usuario = a.id_usuario_alteracao AND id_chamado_alteracao =". $id_chamado . " ORDER BY a.data_alteracao DESC LIMIT 50";

        return $this->db->query($q_buscaHistorico)->result();   


       
    }

    public function buscaInteracoes($id_chamado){
        
        $result = null;

        $sql = "SELECT nome_usuario, id_interacao, tipo_interacao, " .
        "id_chamado_interacao, DATE_FORMAT(data_interacao, '%d/%m/%Y - %H:%i:%s') as data_interacao, " .
        "texto_interacao, nome_usuario, status_chamado, pool_equipamentos, pool_servicos  
        FROM interacao i, usuario u, chamado c 
        WHERE i.id_chamado_interacao = c.id_chamado
        AND c.id_chamado = " . $id_chamado .
        " AND i.id_usuario_interacao = u.id_usuario 
        ORDER BY i.data_interacao DESC";

       $result = $this->db->query($sql)->result_array();

        return $result;
    
    }

    public function priorizaChamado($id_chamado) {
        
        $prioridade = $this->db->query("SELECT prioridade_chamado from chamado WHERE id_chamado = " . $id_chamado)->row()->prioridade_chamado;
        $nova_prioridade = $prioridade == 1 ? 0 : 1;
        $this->db->query("update chamado set prioridade_chamado = " . $nova_prioridade . " WHERE id_chamado = " . $id_chamado);

        

        switch($nova_prioridade) {
            case 1:
                $texto_alteracao .= "posicionou o chamado como <b>prioridade</b>.";
                break;
            case 0:
                $texto_alteracao .= "</b> removeu a prioridade do chamado.";
                break;
        }

        
        
        $nova_alteracao = array (
            'id_alteracao' => NULL,
            'data_alteracao' => date('Y-m-d H:i:s'),
            'texto_alteracao' => $texto_alteracao,
            'id_chamado_alteracao' => $id_chamado,
            'id_usuario_alteracao' => $_SESSION['id_usuario'],
   
         ); 
         
         $this->db->insert('alteracao_chamado',$nova_alteracao);

         // ------------ LOG -------------------

         $log = array(
            'acao_evento' => 'PRIORIDADE_CHAMADO',
            'desc_evento' => 'ID CHAMADO: ' . $id_chamado . " - PRIORIDADE = " . $nova_prioridade,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );
        
        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------
    }

    public function buscarEnderecoChamado($id_chamado) {
    
    
        $endereco = $this->db->query("select endereco_local as end from local l 
        inner join chamado c on l.id_local = c.id_local_chamado where c.id_chamado = " . $id_chamado)->row()->end;
        
        return $endereco;
    }

    public function listaEncerrados() {

        $q = "SELECT id_chamado,ticket_chamado, nome_solicitante_chamado, 
        (SELECT nome_local FROM local WHERE id_local = id_local_chamado) AS nome_local, 
        data_chamado, data_encerramento_chamado
        FROM chamado
        WHERE status_chamado = 'ENCERRADO' order by data_encerramento_chamado desc limit 500";

        return $this->db->query($q)->result();
    }

    public function temEquipEspera($id_chamado) {

        $out = 0;

        $this->db->select("status_equipamento_chamado");
        $this->db->from("equipamento_chamado");
        $this->db->where("status_equipamento_chamado =  'ESPERA'");
        $this->db->where("id_chamado_equipamento = " . $id_chamado);

        $out = $this->db->get()->num_rows();

        return $out;
    }

    public function listaChamados($id_fila = NULL,$id_usuario) {

        //removida verificacao do solicitante

        $nivel_usuario = $this->db->query('select autorizacao_usuario from usuario where id_usuario = ' . $id_usuario)->row()->autorizacao_usuario;


        $q = "SELECT id_chamado, ticket_chamado, id_fila_chamado, nome_solicitante_chamado, data_chamado, prioridade_chamado, resumo_chamado,
        (
       SELECT nome_local
       FROM local
       WHERE id_local = id_local_chamado) AS nome_local, 

       (
       SELECT regiao_local
       FROM local
       WHERE id_local = id_local_chamado) AS regiao_local, 
               data_chamado, 
        (
       SELECT usuario.nome_usuario
       FROM usuario
       WHERE usuario.id_usuario = c.id_usuario_responsavel_chamado) AS nome_responsavel, 
       status_chamado, entrega_chamado,
       (
        SELECT data_interacao FROM interacao
        WHERE id_chamado_interacao = c.id_chamado
        ORDER BY data_interacao DESC
        LIMIT 1
        ) AS data_ultima_interacao
       FROM chamado c where";

        $q .= ' status_chamado <> \'ENCERRADO\' and';

        if ($id_fila > 0) {

            if ($id_fila == 99) { // fila de Entrega (virtual)  

                $q .= " entrega_chamado = 1";
            } else {

                $q .= " id_fila_chamado = " . $id_fila;
            }
            
        } else {

            $q .= " id_fila_chamado > 0 ";
        }

        //$q .= ' order by data_chamado';

        return $this->db->query($q)->result();
    }

    public function listaTicketChamado($id_chamado) {
        $this->db->select('ticket_chamado');
        $this->db->where('id_chamado', $id_chamado);
        $query = $this->db->get('chamado');

        return $query->row();
    }

    public function buscarChamadosBloqueados($id_usuario){
        $sql = "SELECT id_chamado, status_chamado, ticket_chamado, resumo_chamado, data_chamado 
        FROM chamado WHERE id_usuario_responsavel_chamado = " . $id_usuario;

        $busca = $this->db->query($sql);

        if($busca->num_rows() >= 1) {

            return $busca->result_array();

        }

        else{
            return NULL;
        }
    }

    public function listar_servicos_chamado($id_chamado){
        
        $q_buscaServicos = "SELECT s.id_servico, s.nome_servico, s.unidade_medida, sc.quantidade, sc.status_servico_chamado as status_servico, sc.id from servico s inner join servico_chamado sc on s.id_servico = sc.id_servico_chamado             where sc.id_chamado_servico = " . $id_chamado;

          
        $linhas = $this->db->query($q_buscaServicos)->result();

        if(sizeof($linhas) >= 1){
            return $linhas;
        }

        else{
            return NULL;
        }

    }

    public function listar_servicos($id_fila){

        $sql = "SELECT id_servico, nome_servico, unidade_medida from servico
        where id_fila_servico = {$id_fila} and status_servico = true";

        $linhas = $this->db->query($sql)->result();

        if(sizeof($linhas) >= 1){
            return $linhas;
        }

        else{
            return NULL;
        }

        return $linhas;
    }

    public function listar_servico($id){
        $sql = "SELECT nome_servico from servico
        where id_servico = {$id}";
        $linha = $this->db->query($sql)->result_array();
        
        if($linha != NULL && $linha != ''){
            return $linha;
        }

        else{
            return NULL;
        }

     }
    

    public function inserir_servicos($servico){
        $sql = "INSERT INTO servico_chamado
        (id_chamado_servico, id_servico_chamado, status_servico_chamado, quantidade)
        VALUES({$servico['id_chamado']}, {$servico['id_servico']}, '{$servico['status_servico']}', {$servico['quantidade']})";

        $this->db->query($sql);
    
        // ------------ LOG -------------------

        $nova_alteracao = array (
            'id_alteracao' => NULL,
            'data_alteracao' => date('Y-m-d H:i:s'),
            'texto_alteracao' => "adicionou serviço: <b>" . $servico['nome_servico'] . "</b> ao chamado" ,
            'id_chamado_alteracao' => $servico['id_chamado'],
            'id_usuario_alteracao' => $_SESSION['id_usuario'],
   
         ); 
         
         $this->db->insert('alteracao_chamado',$nova_alteracao);


        $log = array(
            'acao_evento' => 'ADICIONAR_SERVICO',
            'desc_evento' => 'ID CHAMADO: ' . $servico['id_chamado'],
            'id_usuario_evento' => $_SESSION['id_usuario']
        );
        
        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------
    
    }

    public function excluir_servico($servico){
        $sql = "DELETE FROM servico_chamado
        WHERE id = " . $servico['id'] . "";

        $this->db->query($sql);

        // ------------ LOG -------------------

        $nova_alteracao = array (
            'id_alteracao' => NULL,
            'data_alteracao' => date('Y-m-d H:i:s'),
            'texto_alteracao' => "removeu serviço: <b>" . $servico['nome_servico'] . "</b> do chamado" ,
            'id_chamado_alteracao' => $servico['id_chamado'],
            'id_usuario_alteracao' => $_SESSION['id_usuario'],
   
         ); 
         
         $this->db->insert('alteracao_chamado',$nova_alteracao);


        $log = array(
            'acao_evento' => 'REMOVER_SERVICO',
            'desc_evento' => 'ID CHAMADO: ' . $servico['id_chamado'],
            'id_usuario_evento' => $_SESSION['id_usuario']
        );
        
        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------
    }

    public function atualizar_servicos($servico){
        $sql = "UPDATE db_sigat.servico_chamado
        SET  quantidade={$servico['quantidade']}
        WHERE id={$servico['id']};";

        $this->db->query($sql);
    
        // ------------ LOG -------------------

        $nova_alteracao = array (
            'id_alteracao' => NULL,
            'data_alteracao' => date('Y-m-d H:i:s'),
            'texto_alteracao' => "atualizou serviço: <b>" . $servico['nome_servico'] . "</b> ao chamado" ,
            'id_chamado_alteracao' => $servico['id_chamado'],
            'id_usuario_alteracao' => $_SESSION['id_usuario'],
   
         ); 
         
         $this->db->insert('alteracao_chamado',$nova_alteracao);


        $log = array(
            'acao_evento' => 'ATUALIZAR_SERVICO',
            'desc_evento' => 'ID CHAMADO: ' . $servico['id_chamado'],
            'id_usuario_evento' => $_SESSION['id_usuario']
        );
        
        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------
    
    }

    public function zerar_chamados($chamado){
        $sql = "UPDATE db_sigat.chamado
        SET email_nao_lido_chamado=0
        WHERE id_chamado={$chamado}";
        
        $this->db->query($sql);
    }

    public function buscaTermoEntrega($id_chamado){
        $sql = "SELECT id_termo, nome_termo, tipo_termo, data_termo, id_chamado_termo
        FROM db_sigat.termo
        WHERE id_chamado_termo = {$id_chamado}";

        $result = $this->db->query($sql)->result_array();

        return $result;
    }

}

?>