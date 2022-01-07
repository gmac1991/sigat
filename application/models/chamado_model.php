<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Chamado_model extends CI_Model {

    public function registraChamado($dados) {
        $msg = NULL;

        $this_model = new self; //re-instancia a classe Chamado_model para utilizar seus métodos na função 'registrar'
        
        if (!isset($dados['erros_upload'])) { //se não houver erros com o upload
                       
            $q_buscaIdLocal = "select id_local from local where nome_local = '". addslashes($dados['nome_local']) . "'";

            //$r_id_solicitante = $this->db->query($q_buscaIdSolicitante);
            $r_id_local = $this->db->query($q_buscaIdLocal);

            if ($r_id_local->num_rows() > 0) { // validando local

                
                $id_local = $r_id_local->row()->id_local;

                $q_registraChamado = "insert into chamado values(NULL, " . $id_local . ",'" 
                . $dados['nome_solicitante'] . "', '" . $dados['descricao'] . "', '" . $dados['telefone'] . "'," . $dados['id_usuario'] . ", NULL, 'ABERTO'," 
                . $dados['id_fila'] . ",NOW(),0,NULL,NULL)"; 

                $nome_fila = $this->db->query('select nome_fila from fila where id_fila = ' . $dados['id_fila'])->row()->nome_fila;

                // removida a verficação do solicitante, conforme solicitado

                $anexo = FALSE;

                if(isset($dados['nome_anexo'])) {

                    $q_registraAnexo = "insert into anexo values(NULL, '" . $dados['nome_anexo'] . "', NOW(), ";
                    $anexo = TRUE;
                }

                // $requerPatrimonio = $this->db->query("select * from fila where id_fila = " . $dados['id_fila'] 
                // . " and requer_patrimonio_fila = 1");

                // ------------ FUNCAO PARA REGISTRAR ------------------ 

                function registrar($inst,$sql_insert,$p_nome_fila,$p_id_usuario) {

                    $inst->db->query($sql_insert); //registrando o chamado

                    $id_novo_chamado = $inst->db->insert_id(); // buscando o ID do chamado recem aberto

                    $inst->db->query('insert into interacao values(NULL, \'ABERTURA\', NOW(), \' abriu o chamado na fila <b>' //criando a interacao de abertura
                    . $p_nome_fila . '</b>\',' . $id_novo_chamado . ',' . $p_id_usuario . ',NULL)'); 

                    echo "<div id=\"alerta\" class=\"alert alert-success alert-dismissible\">";
                    echo "<a href=\"#\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">&times;</a>";
                    echo "<small class=\"float-right\">". date('G:i:s') . "</small>";
                    echo "Foi aberto o chamado n. " 
                    . $id_novo_chamado . "<br /><a href=". base_url() . "chamado/". 
                    $id_novo_chamado .">Ver agora -></a>";
                    echo "</div>"; 

                    return $id_novo_chamado;

                    
                }

                // ------------------------------------------------------


                // if ($requerPatrimonio->num_rows() == 1 && $dados['id_fila'] != 6) { //checando se a fila precisa de patrimonio (bypass na fila Sol. Equip.)

                    preg_match_all("/[1-9]\d{5}/",$dados['listaPatrimonios'], $patrimonios);  // separando os itens da lista em um array (6 digitos consecutivos)

                    $lista_abrir_chamado = array();

                    foreach ($patrimonios[0] as $patrimonio) { //patrimonios[0] é o vetor com a lista de patrimonios informados

                        array_push($lista_abrir_chamado,$patrimonio);

                    }

                    if (!empty($lista_abrir_chamado)) {

                        $id_chamado = registrar($this_model,$q_registraChamado,$nome_fila,$dados['id_usuario']);

                        foreach($lista_abrir_chamado as $patrimonio) { //registrando na tabela patrimonio_chamado

                            $busca_tag = $this->db->query("select ultima_tag_patrimonio from patrimonio_chamado where ultima_tag_patrimonio <> NULL 
                                                            and num_patrimonio = " . $patrimonio .
                                                            " order by data_registro_patrimonio asc limit 1");

                            if ($busca_tag->num_rows() == 1) {
                                $this->db->query("insert into patrimonio_chamado values('" . $patrimonio . 
                                                    "', " . $id_chamado . ", 'ABERTO', NULL," . $busca_tag->ultima_tag_patrimonio . ",NOW())");
                            }

                            else {
                                $this->db->query("insert into patrimonio_chamado values('" . $patrimonio . "', " . $id_chamado . ", 'ABERTO',NULL,NULL,NOW())");

                            }

                            
                        }

                        if ($anexo) { $this->db->query($q_registraAnexo . $id_chamado . ")"); } // registrando anexo

                        // ------------ LOG -------------------

                        $log = array(
                            'acao_evento' => 'INSERIR_CHAMADO',
                            'desc_evento' => 'ID CHAMADO: ' . $id_chamado ,
                            'id_usuario_evento' => $_SESSION['id_usuario']
                        );
                        
                        $this->db->insert('evento', $log);

                        // -------------- /LOG ----------------
                    
                    }

                // } else {

                    // $id_chamado = registrar($this_model,$q_registraChamado,$nome_fila,$dados['id_usuario']); //registrando o chamado

                    if ($anexo) { $this->db->query($q_registraAnexo . $id_chamado . ")"); } // registrando anexo


                    // ------------ LOG -------------------

                    // $log = array(
                    //     'acao_evento' => 'INSERIR_CHAMADO',
                    //     'desc_evento' => 'ID CHAMADO: ' . $id_chamado ,
                    //     'id_usuario_evento' => $_SESSION['id_usuario']
                    // );
                    
                    // $this->db->insert('evento', $log);

                    // -------------- /LOG ----------------
                // }
                
            } else {

                $msg .= "<div id=\"alerta\" class=\"alert alert-warning alert-dismissible\">" .
                "<a href=\"#\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">&times;</a>" .
                "Local inválido!" .
                "</div>";

                exit($msg);
            }
            

                       
                        
        } else {
            
            $msg .= "<div id=\"alerta\" class=\"alert alert-warning alert-dismissible\">";
            $msg .= "<a href=\"#\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">&times;</a>";
            $msg .= "Ocorreram o(s) seguinte(s) problema(s) com o anexo:";
            $msg .= "<ul>";
            
            foreach ($dados['erros_upload'] as $erro) {
                $msg .= "<li>" . $erro . "</li>";
            }
            
            $msg .= "</ul>";
            $msg .= "</div>";

            echo $msg;
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

        

        $texto_alteracao = NULL;


        $chamado_original = $this->db->query('select id_usuario_responsavel_chamado, 
        (select nome_usuario from usuario where id_usuario = id_usuario_responsavel_chamado) as nome_responsavel, 
        nome_solicitante_chamado, telefone_chamado, 
        id_local_chamado, (select nome_local from local where id_local = id_local_chamado) as nome_local from chamado
        where id_chamado = ' . $dados['id_chamado'])->row();

        if ($dados['id_responsavel'] != NULL) { //se foi enviado algum id_responsavel...
            $q_alteraChamado = 
            "update chamado set nome_solicitante_chamado = '" . $dados['nome_solicitante'] . 
            "', telefone_chamado = " . $dados['telefone'] . ", id_local_chamado = " . $id_local . ", id_usuario_responsavel_chamado = "
            . $dados['id_responsavel'] . " where id_chamado = " . $dados['id_chamado'];


        } else { //se nao...
            $q_alteraChamado = 
            "update chamado set nome_solicitante_chamado = '" . $dados['nome_solicitante'] . 
            "', telefone_chamado = " . $dados['telefone'] . ", id_local_chamado = " . $id_local . 
            " where id_chamado = " . $dados['id_chamado'];

            
        }

        $this->db->query($q_alteraChamado); //executa a alteracao

        //removido inserção de interacao

        //inserindo na tabela alteracao

        if ($chamado_original->id_local_chamado != $id_local) {

            $novo_nome_local = $this->db->query('select nome_local from local where id_local = ' . $id_local)->row()->nome_local;

            $texto_alteracao .= '<p>Foi alterado o local <strong>' . $chamado_original->nome_local . '</strong>';
            $texto_alteracao .= ' para <strong>' . $novo_nome_local . '</strong></p>';
        }

        if ($chamado_original->telefone_chamado != $dados['telefone']) {

            $texto_alteracao .= '<p>Foi alterado o telefone de <strong>' . $chamado_original->telefone_chamado . '</strong>';
            $texto_alteracao .= ' para <strong>' . $dados['telefone'] . '</strong></p>';
        }

        if ($chamado_original->nome_solicitante_chamado != $dados['nome_solicitante']) {

            $texto_alteracao .= '<p>Foi alterado o solicitante de <strong>' . $chamado_original->nome_solicitante_chamado . '</strong>';
            $texto_alteracao .= ' para <strong>' . $dados['nome_solicitante'] . '</strong></p>';
        }

        if ($dados['id_responsavel'] != NULL) { 

            if ($chamado_original->id_usuario_responsavel_chamado != $dados['id_responsavel']) {
                $novo_nome_responsavel = $this->db->query('select nome_usuario from usuario where id_usuario = ' . $dados['id_responsavel'])->row()->nome_usuario;

                if ($chamado_original->id_usuario_responsavel_chamado != NULL) { 
                    $texto_alteracao .= '<p>Foi alterado o responsável de <strong>' . $chamado_original->nome_responsavel . '</strong>';
                    $texto_alteracao .= ' para <strong>' . $novo_nome_responsavel . '</strong></p>';

                }

                else { //se a alteracao do responsavel for de NULL para algum valor...

                    $texto_alteracao .= '<p>Foi alterado o responsável';
                    $texto_alteracao .= ' para <strong>' . $novo_nome_responsavel . '</strong></p>';
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

        

        $auto_usuario = $this->db->query('select autorizacao_usuario from usuario where id_usuario = ' 
                                    . $dados['id_usuario'])->row()->autorizacao_usuario;

        if($auto_usuario == 4) {
            
            $q_encerraChamado = $this->db->query("update chamado set status_chamado = 'ENCERRADO' where id_chamado = " . $dados['id_chamado']);

            if($q_encerraChamado) {

                $this->db->query("insert into interacao values(NULL, 'ENC', NOW(), ' encerrou o chamado'," 
                . $dados['id_chamado'] . "," . $dados['id_usuario'] . " ,NULL)"); //inserindo a interacao


                echo '1';

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

            return FALSE;
        }
        
        
    }

    public function buscaChamado($id_chamado, $status = '') {

        $chamado = $this->db->query("select * from chamado where id_chamado = ". $id_chamado . " and id_fila_chamado is NULL")->row();


        // removida a verficaçao do solicitante, conforme solicitado

		

	   $q_buscaChamado = "select id_chamado, id_fila, nome_solicitante_chamado, nome_local, DATE_FORMAT(data_chamado, '%d/%m/%Y - %H:%i:%s') as data_chamado, descricao_chamado, telefone_chamado,
        (select usuario.id_usuario from usuario where usuario.id_usuario = chamado.id_usuario_responsavel_chamado) as id_responsavel, 
        (select fila.nome_fila from fila where fila.id_fila = chamado.id_fila_chamado) as nome_fila_chamado
        from local, fila, chamado
        where local.id_local = chamado.id_local_chamado and
        fila.id_fila = chamado.id_fila_chamado and
        chamado.id_chamado = " . $id_chamado;

        $q_buscaPatrimonios = "select num_patrimonio from patrimonio_chamado where id_chamado_patrimonio = " . $id_chamado . 
        " and status_patrimonio_chamado = '" . $status . "'";

        $q_buscaEquipamentos = "select * from equipamento_chamado where id_chamado_equipamento = " . $id_chamado . 
        " and status_equipamento = '" . $status . "'";
        
        $q_buscaAnexos = "select nome_anexo from anexo where id_chamado_anexo = " . $id_chamado;
        

        $result['patrimonios'] = $this->db->query($q_buscaPatrimonios)->result();
        
        $result['equipamentos'] = $this->db->query($q_buscaEquipamentos)->result();
        
        $result['anexos'] = $this->db->query($q_buscaAnexos)->result();
    

        $result['chamado'] = $this->db->query($q_buscaChamado)->row();

        return $result;
    }

    public function buscaAnexo($id_chamado) {

        $q_buscaAnexos = "select nome_anexo from anexo where id_chamado_anexo = " . $id_chamado;
        
        return $this->db->query($q_buscaAnexos)->row();
    }

}

?>