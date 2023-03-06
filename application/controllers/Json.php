<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Json extends CI_Controller {

    /*Classe para Respostas às requisições AJAX e outras do Frontend */

    

    function __construct() {
        parent::__construct();
        $this->load->library('consulta_ldap');
        $this->load->model("equipamento_model");
        $this->load->model("chamado_model");
        /* $this->load->library('encryption');
        $this->encryption->initialize(array('driver' => 'openssl')); */
    }

    public function solicitantes() {

        if (isset($_SESSION['id_usuario'])) {
            $termo = $this->input->get('q');
            /* $usr = $this->encryption->decrypt($_SESSION["usi"]);
            $pass = $this->encryption->decrypt($_SESSION["psi"]);   */        
            $ldap = new Consulta_LDAP($_SESSION["usi"],$_SESSION["psi"]);
            $lista = array();
            $lista = $ldap->buscaSolicitantes($termo);
            if (!empty($lista)) {
                $nova_lista = array();
                for($i = 1;$i<=count($lista); $i = $i+2) {
                    $nova_lista[] = $lista[$i];

                }
                header("Content-Type: application/json");
                echo json_encode($nova_lista);
            }


        } else
            die('Não autorizado!');
    }


    public function interacao() {

        if (isset($_SESSION['id_usuario'])) {

            $id_chamado = $this->input->get('id_chamado');
            $id_interacao = $this->input->get('id_interacao');
            $id_usuario = $this->input->get('id_usuario');

            $q_buscaInteracao = "select id_interacao from interacao where id_usuario_interacao = " . $id_usuario . 
            " and id_chamado_interacao = " . $id_chamado . " and id_interacao = " . $id_interacao;
        

            if ($this->db->query($q_buscaInteracao)->num_rows() == 0) {
                echo '1';
            } else {
    
                echo '0';
            }
        }

        else {
            die('Não autorizado!');
        }

        

    }

    public function interacoes() {

        if (isset($_SESSION['id_usuario'])) {

            $busca = NULL;

            $id_chamado = $this->input->post('id');

            $busca = "SELECT nome_usuario, id_interacao, tipo_interacao, " .
            "id_chamado_interacao, DATE_FORMAT(data_interacao, '%d/%m/%Y - %H:%i:%s') as data_interacao, " .
            "texto_interacao, nome_usuario, status_chamado  
            FROM interacao i, usuario u, chamado c 
            WHERE i.id_chamado_interacao = c.id_chamado
            AND c.id_chamado = " . $id_chamado .
            " AND i.id_usuario_interacao = u.id_usuario 
            ORDER BY i.data_interacao DESC";

            $r_interacao = $this->db->query($busca)->result_array();

            $busca_ultimo = "SELECT id_interacao FROM interacao WHERE id_chamado_interacao = " . $id_chamado . " ORDER BY id_interacao DESC limit 1";

            $r_ult_interacao = $this->db->query($busca_ultimo)->row_array();

            echo "<div class=\"mb-5\">";

            foreach ($r_interacao as $interacao) {

                $btn_desfazer = "<button id=\"btnDesfazer\" role=\"button\" onclick=\"removeInteracao(" . $interacao['id_interacao'] . 
                ", " . $interacao['id_chamado_interacao'] . ")\" class=\"m-2 float-right btn btn-sm btn-warning\">" .
                "<i class=\"fas fa-undo\"></i> Desfazer</button>";

                switch ($interacao['tipo_interacao']) {

                case 'ABERTURA':
                    echo "<div class=\"mb-3 p-1 table-secondary rounded\">";
                    echo "<span class=\"float-right\">" . $interacao['data_interacao'] . "</span>";
                    echo "<strong>" . $interacao['nome_usuario'] . "</strong>" . $interacao['texto_interacao'];
                    echo "</div>";
                break;

                case 'ATENDIMENTO':
                    echo "<div class=\"mb-3 p-1 table-primary rounded\">";
                    echo "<span class=\"float-right\">" . $interacao['data_interacao'] . "</span>";
                    echo "<strong>" . $interacao['nome_usuario'] . "</strong> realizou um <strong>atendimento</strong><hr class=\"m-0\" />";

                    if ($r_ult_interacao['id_interacao'] == $interacao['id_interacao']) 
                        echo $btn_desfazer;
                    echo $interacao['texto_interacao'];
                    echo "</div>";
                break;

                case 'ATENDIMENTO_INS':

                    echo "<div class=\"mb-3 p-1 table-primary rounded\">";
                    echo "<span class=\"float-right\">" . $interacao['data_interacao'] . "</span>";
                    echo "<strong>" . $interacao['nome_usuario'] . "</strong> realizou um <strong>atendimento</strong><hr class=\"m-0\" />";
                    
                    if ($r_ult_interacao['id_interacao'] == $interacao['id_interacao']) {
                        echo $btn_desfazer;
                    }
                    
                    echo "<a role=\"button\" class=\"btn btn-info mt-2 float-right\" href=\"" 
                    . base_url('chamado/gerar_laudo/' .$interacao['id_interacao']) . "\" download><i class=\"fas fa-file-download\"></i> Laudo Técnico</a>";
                    
                    echo $interacao['texto_interacao'];
        
                    echo "</div>";
                break;

                case 'ALT':
                    echo "<div class=\"mb-3 p-1 table-secondary rounded\">";
                    echo "<span class=\"float-right\">" . $interacao['data_interacao'] . "</span>";
                    echo "<strong>" . $interacao['nome_usuario'] . "</strong> alterou dados do chamado<hr class=\"m-0\" />";
                    echo $interacao['texto_interacao'];

                    echo "</div>";
                break;

                case 'ALT_FILA':
                    echo "<div class=\"mb-3 p-1 table-warning rounded\">";
                    echo "<span class=\"float-right\">" . $interacao['data_interacao'] . "</span>";
                    echo "<strong>" . $interacao['nome_usuario'] . "</strong> alterou a <strong>fila</strong><hr class=\"m-0\" />";
                    if ($r_ult_interacao['id_interacao'] == $interacao['id_interacao']) 
                        echo $btn_desfazer;
                    echo $interacao['texto_interacao'];

                    echo "</div>";
                break;

                case 'OBSERVACAO':
                    echo "<div class=\"mb-3 p-1 table-info rounded\">";
                    echo "<span class=\"float-right\">" . $interacao['data_interacao'] . "</span>";
                    echo "<strong>" . $interacao['nome_usuario'] . "</strong> fez uma <strong>observação</strong><hr class=\"m-0\" />";
                    if ($r_ult_interacao['id_interacao'] == $interacao['id_interacao']) 
                        echo $btn_desfazer;
                    echo $interacao['texto_interacao'];

                    echo "</div>";
                break;

                case 'FECHAMENTO':
                    echo "<div class=\"mb-3 p-1 table-success rounded\">";
                    echo "<span class=\"float-right\">" . $interacao['data_interacao'] . "</span>";
                    echo "<strong>" . $interacao['nome_usuario'] . "</strong> finalizou o chamado<hr class=\"m-0\" />";
                    if ($r_ult_interacao['id_interacao'] == $interacao['id_interacao']) 
                        echo $btn_desfazer;
                    echo $interacao['texto_interacao'];

                    echo "</div>";
                break;

                case 'FECHAMENTO_EQUIP': //fechamento com patrimonios
                    echo "<div class=\"mb-3 p-1 table-success rounded\">";
                    echo "<span class=\"float-right\">" . $interacao['data_interacao'] . "</span>";
                    echo "<strong>" . $interacao['nome_usuario'] . "</strong> finalizou o chamado<hr class=\"m-0\" />";
                    if ($r_ult_interacao['id_interacao'] == $interacao['id_interacao']) 
                        echo $btn_desfazer;
                    echo $interacao['texto_interacao'];

                    echo "</div>";
                break;

                case 'FECHAMENTO_INS': //fechamento com inserviveis
                    echo "<div class=\"mb-3 p-1 table-success rounded\">";
                    echo "<span class=\"float-right\">" . $interacao['data_interacao'] . "</span>";
                    echo "<strong>" . $interacao['nome_usuario'] . "</strong> finalizou o chamado<hr class=\"m-0\" />";
                    if ($r_ult_interacao['id_interacao'] == $interacao['id_interacao']) {

                        echo $btn_desfazer;
                    
                    }

                    echo "<a role=\"button\" class=\"btn btn-info mt-2 float-right\" href=\"" 
                    . base_url('chamado/gerar_laudo/' .$interacao['id_interacao']) . "\" download><i class=\"fas fa-file-download\"></i> Laudo Técnico</a>";
                    
                    echo $interacao['texto_interacao'];

                    echo "</div>";
                break;

                case 'ENTREGA':
                    echo "<div class=\"mb-3 p-1 table-success rounded\">";
                    echo "<span class=\"float-right\">" . $interacao['data_interacao'] . "</span>";
                    echo "<strong>" . $interacao['nome_usuario'] . "</strong> realizou a entrega<hr class=\"m-0\" />";
                    if ($r_ult_interacao['id_interacao'] == $interacao['id_interacao']) 
                        echo $btn_desfazer;
                    echo $interacao['texto_interacao'];
                    echo "</div>";

                break;

                case 'TENTATIVA_ENTREGA':
                    echo "<div class=\"mb-3 p-1 table-info rounded\">";
                    echo "<span class=\"float-right\">" . $interacao['data_interacao'] . "</span>";
                    echo "<strong>" . $interacao['nome_usuario'] . "</strong> registrou uma <b>tentativa</b> de entrega<hr class=\"m-0\" />";
                    if ($r_ult_interacao['id_interacao'] == $interacao['id_interacao']) 
                        echo $btn_desfazer;
                    echo $interacao['texto_interacao'];
                    echo "</div>";

                break;

                case 'FALHA_ENTREGA':
                    echo "<div class=\"mb-3 p-1 table-danger rounded\">";
                    echo "<span class=\"float-right\">" . $interacao['data_interacao'] . "</span>";
                    echo "<strong>" . $interacao['nome_usuario'] . "</strong> registrou uma <b>falha</b> na <b>entrega</b><hr class=\"m-0\" />";
                    if ($r_ult_interacao['id_interacao'] == $interacao['id_interacao']) 
                        echo $btn_desfazer;
                    echo $interacao['texto_interacao'];
                    echo "</div>";
                break;

                case 'ESPERA':
                    echo "<div class=\"mb-3 p-1 rounded\" style=\"background: #FFA789\">";
                    echo "<span class=\"float-right\">" . $interacao['data_interacao'] . "</span>";
                    echo "<strong>" . $interacao['nome_usuario'] . "</strong> deixou equipamentos em <b>espera</b><hr class=\"m-0\" />";
                    if ($r_ult_interacao['id_interacao'] == $interacao['id_interacao']) 
                        echo $btn_desfazer;
                    echo $interacao['texto_interacao'];
                    echo "</div>";
                break;

                case 'REM_ESPERA':
                    echo "<div class=\"mb-3 p-1 rounded\" style=\"background: #fff0fe\">";
                    echo "<span class=\"float-right\">" . $interacao['data_interacao'] . "</span>";
                    echo "<strong>" . $interacao['nome_usuario'] . "</strong> retirou equipamentos da <b>espera</b><hr class=\"m-0\" />";
                    if ($r_ult_interacao['id_interacao'] == $interacao['id_interacao']) 
                        echo $btn_desfazer;
                    echo $interacao['texto_interacao'];
                    echo "</div>";
                break;

                case 'ENC':
                    echo "<div class=\"mb-3 p-1 table-secondary rounded\">";
                    echo "<span class=\"float-right\">" . $interacao['data_interacao'] . "</span>";
                    echo "<strong>" . $interacao['nome_usuario'] . "</strong>" . $interacao['texto_interacao'];
                    echo "</div>";
                break;

                case 'ADC_EQUIP':
                    echo "<div class=\"mb-3 p-1 table-secondary rounded\">";
                    echo "<span class=\"float-right\">" . $interacao['data_interacao'] . "</span>";
                    echo "<strong>" . $interacao['nome_usuario'] . "</strong> adicionou equipamentos à solicitação<hr class=\"m-0\" />";
                    echo $interacao['texto_interacao'];
                    echo "</div>";
                break;

                }

            }

            echo "</div>";
        }
        else {
            header('HTTP/1.0 403 Forbidden');
        }

    }

    public function chamado_aberto() {

        if (isset($_SESSION['id_usuario'])) {

            $termo = $this->input->get('q');

            try {
            $result = $this->db->query("select id_chamado, num_equipamento from equipamento_chamado, chamado 
            where num_equipamento = " . $termo . 
            " and id_chamado_equipamento = chamado.id_chamado
            and chamado.status_chamado = 'ABERTO'")->result_array();
            
            header("Content-Type: application/json");

            echo json_encode($result);
                
            } catch(PDOException $e) {
                echo 'ERROR: ' . $e->getMessage();
            }
        }
        else {
            header('HTTP/1.0 403 Forbidden');
        }
    }

    

    public function chamado() {

        if (isset($_SESSION['id_usuario'])) {
            
            $id_chamado = $this->input->get('id_chamado');

   
            $q_buscaChamado = "select complemento_chamado, resumo_chamado, ticket_chamado, id_chamado, id_fila, status_chamado, nome_solicitante_chamado, nome_local, DATE_FORMAT(data_chamado, '%d/%m/%Y - %H:%i:%s') as data_chamado, telefone_chamado,
                (select usuario.nome_usuario from usuario where usuario.id_usuario = chamado.id_usuario_responsavel_chamado) as nome_responsavel,
                (select usuario.id_usuario from usuario where usuario.id_usuario = chamado.id_usuario_responsavel_chamado) as id_responsavel,  
                (select fila.nome_fila from fila where fila.id_fila = chamado.id_fila_chamado) as nome_fila_chamado, entrega_chamado
                from local, fila, chamado
                where local.id_local = chamado.id_local_chamado and
                fila.id_fila = chamado.id_fila_chamado and
                chamado.id_chamado = " . $id_chamado;

           $q_buscaStatusEquipamentos = "select status_equipamento_chamado from equipamento_chamado where equipamento_chamado.id_chamado_equipamento = " . $id_chamado;


            $result = $this->db->query($q_buscaChamado)->row_array();

            $result['nome_solicitante_chamado'] = str_replace(array('"', "'"), '', $result['nome_solicitante_chamado']);
            
            //$result['descricao_chamado'] = strip_tags($result['descricao_chamado'],$this->tags_permitidas);

            
            $result['status_equipamentos'] = $this->db->query($q_buscaStatusEquipamentos)->result_array();
            

            header("Content-Type: application/json");
                
            echo json_encode($result);
        }

        else {
            header('HTTP/1.0 403 Forbidden');
        }
    }
	
	public function triagem() {

        if (isset($_SESSION['id_usuario'])) {
			
			$result = array();
            
            $id_ticket = $this->input->get('id_ticket');

   
            //$q_buscaTriagem = "select ticket_triagem, nome_solicitante_triagem from triagem where id_triagem = " . $id_triagem;

            // $ticket_triagem =  $this->db->query("select ticket_triagem from triagem 
            // where id_triagem = " . $id_triagem)->row()->ticket_triagem;

            $chamado_existente = $this->db->query("select * from chamado 
                                where id_ticket_chamado = " 
                                . $id_ticket . " 
                                and status_chamado = 'ABERTO'");

            
            if ($chamado_existente->num_rows() > 0) {
                $result['chamado'] = $chamado_existente->row_array();
                $result['agrupamento'] = 1;
            }

            $db_otrs = $this->load->database('otrs', TRUE);

        
            $res = $db_otrs->query("SELECT aa.id, aa.filename FROM article_data_mime_attachment aa
            INNER JOIN article a ON (aa.article_id = a.id)
            WHERE(aa.disposition = 'attachment' OR aa.content_type LIKE 'image%') AND a.ticket_id = " .$id_ticket);
			
            $result['anexos_otrs'] = $res->result_array();


            header("Content-Type: application/json");
                
            echo json_encode($result);
        }

        
    }
	
	public function anexo_otrs($id_anexo) {

        if (isset($_SESSION['id_usuario'])) {

            $db_otrs = $this->load->database('otrs', TRUE);
		
				
			$q_buscaAnexoOTRS = "SELECT id, filename, content FROM article_data_mime_attachment
                                WHERE id = " . $id_anexo;

            $anexo = $db_otrs->query($q_buscaAnexoOTRS)->row_array();

            
			header("Content-Disposition: attachment; filename=" . $anexo['filename']);
			ob_clean();
			flush();
			
			
			echo $anexo['content'];
        }

        else {
            header('HTTP/1.0 403 Forbidden');
        }
    }

    public function anexos_chamado() {
        $id_chamado = $this->input->post("id_chamado");
        if (isset($_SESSION['id_usuario'])) {
            $anexos = array();
			$q_anexos_sigat = "select id_anexo_otrs from anexos_otrs where id_chamado_sigat = " . $id_chamado;
            $q = $this->db->query($q_anexos_sigat);
            if ($q->num_rows() >= 1) {
                $db_otrs = $this->load->database('otrs', TRUE);

                    foreach ($q->result() as $l) {
                        $q_buscaAnexoOTRS = "SELECT id as id_anexo_otrs, filename as nome_arquivo_otrs FROM article_data_mime_attachment
                                    WHERE disposition in ('attachment','inline') AND id = " . $l->id_anexo_otrs;
                        array_push($anexos,$db_otrs->query($q_buscaAnexoOTRS)->row());
                    }
                
               
                    

                
            }
            header("Content-Type: application/json");
            echo json_encode($anexos);
        }
        else {
            header('HTTP/1.0 403 Forbidden');
        }
    }


    public function desc_chamado() {

        if (isset($_SESSION['id_usuario'])) {
            $termo = $this->input->get('q');

            try {
                $busca = $this->db->query("SELECT descricao_chamado, DATE_FORMAT(data_chamado, '%d/%m/%Y - %H:%i:%s') as data_chamado FROM chamado WHERE id_chamado = " . $termo);
                
                
                $result = $busca->row_array();

                header("Content-Type: application/json");
                
                echo json_encode($result);
                
            } catch(PDOException $e) {
                echo 'ERROR: ' . $e->getMessage();
            }
        }
        else {
            header('HTTP/1.0 403 Forbidden');
        }
    }

    public function locais() {

        if (isset($_SESSION['id_usuario'])) {

            $termo = $this->input->get('q');
            $lista_nomes = array();
            $nova_lista = array();


            try {
                $busca = $this->db->query("SELECT nome_local FROM local WHERE nome_local LIKE '%". $termo . "%' order by id_local");
                
                $result = $busca->result_array();
                
                if ( count($result) ) { 
                    foreach($result as $row) {
                        array_push($lista_nomes, $row['nome_local']);
                        
                    }   
                    
                
                foreach ($lista_nomes as $nome) {
                    
                    $novo_nome = htmlentities($nome); //trocando " por &quot; para evitar problemas com o autocomplete
                    
                    array_push($nova_lista, $novo_nome);
                    }

                    header("Content-Type: application/json");
                    
                    echo json_encode($nova_lista);
                    

                } else {
                    echo "Nenhum resultado retornado.";
                }
            } catch(PDOException $e) {
                echo 'ERROR: ' . $e->getMessage();
            }
        } else {
            header('HTTP/1.0 403 Forbidden');
        }


    }

    public function desc_equipamento($e_desc,$json = TRUE) {

        if (isset($_SESSION['id_usuario'])) {

            $num_equip = $e_desc;

            $dados['descricao'] = NULL;

            $descEquipSigat = $this->equipamento_model->buscaDescEquipamento($num_equip);
          
            if ($descEquipSigat === NULL) {

                $res_sim = NULL;

                $res_sim = @file_get_contents($this->config->item('api_sim') . $num_equip);

                if ($res_sim !== FALSE && $res_sim !== "null") {

                    $descSim = json_decode($res_sim)->descrBem; //descricao do SIM

                    $dados['descricao'] = $descSim;
                }

                else {

                    $dados['descricao'] = NULL;

                }
            }  
            else {

                $dados['descricao'] = $descEquipSigat; //se estiver cadastrado, pega a desc do SIGAT
            }

            if ($json) {

                header("Content-Type: application/json");

                echo json_encode($dados);
            }

            else {
                return $dados;
            }
            

        } else {
            header('HTTP/1.0 403 Forbidden');
        }
    }

    public function status_equipamento() {
        $statusEquip = $this->equipamento_model->buscaStatusEquipamento($_POST['e_status']);
    
        header("Content-Type: application/json");
        echo json_encode($statusEquip);
    }

    

    public function equipamentos_pendentes() {
        if (isset($_SESSION['id_usuario'])) {

            $id_chamado = $this->input->post('id_chamado');
            $espera = $this->input->post('espera');

            $dados = array();
            
            $busca = $this->db->query("select id_fila from fila, chamado where id_fila = id_fila_chamado and id_chamado = " . $id_chamado);

            $result = $busca->row_array();

            $dados['id_fila'] = $result['id_fila'];

            $busca = $this->db->query("select * from fila");

            $result = $busca->result_array();

            $dados['filas'] = $result;


            if ($espera == "false") {

                $busca = $this->db->query("select num_equipamento_chamado from equipamento_chamado where id_chamado_equipamento = " . $id_chamado . 
                " and (status_equipamento_chamado = 'ABERTO' or status_equipamento_chamado = 'FALHA')");
            
            } else {
                
                $busca = $this->db->query("select num_equipamento_chamado from equipamento_chamado where id_chamado_equipamento = " . $id_chamado . 
                " and status_equipamento_chamado = 'ESPERA'");

            }

            $result = $busca->result_array();

            $dados['equipamentos'] = $result;
          
            header("Content-Type: application/json");

            echo json_encode($dados);
        } else {
            header('HTTP/1.0 403 Forbidden');
        }
    }

    public function listar_equipamentos_chamado($id_chamado) {
        if (isset($_SESSION['id_usuario'])) {
            
            $q_buscaEquipamentos = "select num_equipamento, descricao_equipamento, status_equipamento_chamado, tag_equipamento
            from equipamento, equipamento_chamado where equipamento_chamado.id_chamado_equipamento = " . $id_chamado .
            " and equipamento_chamado.num_equipamento_chamado = equipamento.num_equipamento order by status_equipamento_chamado";
          
            $linhas = $this->db->query($q_buscaEquipamentos)->result();
            
            header("Content-Type: application/json");

            echo json_encode($linhas);

        } else {
            header('HTTP/1.0 403 Forbidden');
        }
    }


    public function inserir_equipamento_chamado() {
        if (isset($_SESSION['id_usuario'])) {
            $dados = $this->input->post();

            $dados['item']['num_equipamento'] = preg_replace('/\s+/', '', $dados['item']['num_equipamento']); //removendo whitespaces
            $dados['item']['num_equipamento'] = mb_strtoupper($dados['item']['num_equipamento']);
            $dados['item']['descricao_equipamento'] = mb_strtoupper($dados['item']['descricao_equipamento']);
            
            $tag_equipamento = "(VAZIO)";

            if (isset($dados['item']['tag_equipamento']))
                $tag_equipamento = mb_strtoupper($dados['item']['tag_equipamento']);

           
            
            $existe_equip = $this->db->query("select * from equipamento where num_equipamento = '" . $dados['item']['num_equipamento'] ."'");

            if ($existe_equip->num_rows() == 1) {

                $dados['item']['descricao_equipamento'] = $existe_equip->row()->descricao_equipamento;
                $dados['item']['tag_equipamento'] = $existe_equip->row()->tag_equipamento;
            } 

            else {
                $ext_desc = NULL;
                $ext_desc = $this->desc_equipamento($dados['item']['num_equipamento'],FALSE);
                if ($ext_desc['descricao'] !== NULL) {
                    $dados['item']['descricao_equipamento'] = $ext_desc['descricao'];
                }

                $dados['item']['tag_equipamento'] = $tag_equipamento;

                $this->db->query("insert into equipamento 
                                values('" . $dados['item']['num_equipamento'] . "',
                                        '" . $dados['item']['descricao_equipamento'] . "', NOW(),
                                        '" . $tag_equipamento . "',NULL)");

            }

            $this->db->query("insert into equipamento_chamado 
                                values('" . $dados['item']['num_equipamento'] . "',
                                'ABERTO',NULL,NOW()," . $dados['g_id_chamado'] . ")");
            
            $dados['item']['status_equipamento_chamado'] = 'ABERTO';
            
            
            // ------------ LOG -------------------
            
            $nova_alteracao = array (
                'id_alteracao' => NULL,
                'data_alteracao' => date('Y-m-d H:i:s'),
                'texto_alteracao' => "adicionou <b>" . $dados['item']['num_equipamento'] . "</b> - <b>" . $dados['item']['descricao_equipamento'] . "</b> ao chamado" ,
                'id_chamado_alteracao' => $dados['g_id_chamado'],
                'id_usuario_alteracao' => $_SESSION['id_usuario'],
       
             ); 
             
             $this->db->insert('alteracao_chamado',$nova_alteracao);

             

             $log = array(
                'acao_evento' => 'ALTERAR_CHAMADO',
                'desc_evento' => 'ID CHAMADO: ' . $dados['g_id_chamado'],
                'id_usuario_evento' => $_SESSION['id_usuario']
            );
            
            $this->db->insert('evento', $log);

            // -------------- /LOG ----------------
            
            
            header("Content-Type: application/json");
            echo json_encode($dados['item']);

        } else {
            header('HTTP/1.0 403 Forbidden');
        }
    }


    public function atualizar_equipamento_chamado() {
        if (isset($_SESSION['id_usuario'])) {
            $dados = $this->input->post();
            
            $a = $dados['item_antigo'];
            $i = $dados['item'];
            
            $i['num_equipamento'] = preg_replace('/\s+/', '', $i['num_equipamento']); //removendo whitespaces
            $i['descricao_equipamento'] = mb_strtoupper($i['descricao_equipamento']);
            
            if (isset($i['tag_equipamento'])) {
                $i['tag_equipamento'] = mb_strtoupper($i['tag_equipamento']);
            }

            else {

                $i['tag_equipamento'] = $a['tag_equipamento'];
            }
                 


            $this->db->query("update equipamento 
                            set descricao_equipamento = '" . $i['descricao_equipamento'] .
                            "', num_equipamento = '"       . $i['num_equipamento'] .
                            "', tag_equipamento = '"       . $i['tag_equipamento'] .
                            "', data_alteracao_equipamento = NOW()" .
                            " where num_equipamento = '"   . $a['num_equipamento'] . "'"
                        
            );
            
            // ------------ LOG -------------------
            
            $nova_alteracao = array (
                'id_alteracao' => NULL,
                'data_alteracao' => date('Y-m-d H:i:s'),
                'texto_alteracao' =>    "alterou um equipamento. " . $a['num_equipamento'] . 
                                        ", " . $a['descricao_equipamento'] . ", " . $a['tag_equipamento'] . " => " .
                                        $i['num_equipamento'] . ", " . $i['descricao_equipamento'] . ", " . $i['tag_equipamento'],
                'id_chamado_alteracao' => $dados['g_id_chamado'],
                'id_usuario_alteracao' => $_SESSION['id_usuario'],
       
             ); 
             
             $this->db->insert('alteracao_chamado',$nova_alteracao);

             

             $log = array(
                'acao_evento' => 'ALTERAR_CHAMADO',
                'desc_evento' => 'ID CHAMADO: ' . $dados['g_id_chamado'],
                'id_usuario_evento' => $_SESSION['id_usuario']
            );
            
            $this->db->insert('evento', $log);

            // -------------- /LOG ----------------
            
            header("Content-Type: application/json");
            echo json_encode($i);
        } else {
            header('HTTP/1.0 403 Forbidden');
        }
    }

    public function remover_equipamento_chamado() {
        if (isset($_SESSION['id_usuario'])) {
            $dados = $this->input->post();
            $i = $dados['item'];

            $this->db->query("delete from equipamento_chamado where num_equipamento_chamado = '" . $i['num_equipamento'] . "' 
            and id_chamado_equipamento = " . $dados['g_id_chamado']);
            
            // ------------ LOG -------------------
            
            $nova_alteracao = array (
                'id_alteracao' => NULL,
                'data_alteracao' => date('Y-m-d H:i:s'),
                'texto_alteracao' => "removeu <b>" . $i['num_equipamento'] . "</b> - <b>" . $i['descricao_equipamento'] . "</b> do chamado" ,
                'id_chamado_alteracao' => $dados['g_id_chamado'],
                'id_usuario_alteracao' => $_SESSION['id_usuario'],
       
             ); 
             
             $this->db->insert('alteracao_chamado',$nova_alteracao);

             

             $log = array(
                'acao_evento' => 'ALTERAR_CHAMADO',
                'desc_evento' => 'ID CHAMADO: ' . $dados['g_id_chamado'],
                'id_usuario_evento' => $_SESSION['id_usuario']
            );
            
            $this->db->insert('evento', $log);

            // -------------- /LOG ----------------
            
            header("Content-Type: application/json");
            echo json_encode($i);
        } else {
            header('HTTP/1.0 403 Forbidden');
        }
    }

    

    public function usuarios() {
        if (isset($_SESSION['id_usuario'])) {
            
            $busca = $this->db->query("select * from usuario where status_usuario = 'ATIVO' order by nome_usuario");

            $result = $busca->result_array();

            header("Content-Type: application/json");
    
            echo json_encode($result);
            
        
        } else {
            header('HTTP/1.0 403 Forbidden');
        }
    }

    public function atualiza_responsavel() {

        if (isset($_SESSION['id_usuario'])) {

            $id_usuario = $this->input->post('id_usuario');
            $id_chamado = $this->input->post('id_chamado');
            $auto_usuario = $this->input->post('auto_usuario');
            $tipo = $this->input->post('tipo');

            $chamado = $this->db->query("select id_usuario_responsavel_chamado from chamado where id_chamado = " . $id_chamado);
            $id_responsavel = $chamado->row()->id_usuario_responsavel_chamado;
            

            if ($tipo == 'b') {

                //var_dump($id_responsavel);

                if ($id_responsavel === NULL) {
                    $this->db->query("UPDATE chamado set id_usuario_responsavel_chamado = " . $id_usuario .
                                             " WHERE id_chamado = " . $id_chamado);

                    // ------------ LOG -------------------
                    $log = array(
                        'acao_evento' => 'BLOQUEAR_CHAMADO',
                        'desc_evento' => 'ID CHAMADO: ' . $id_chamado,
                        'id_usuario_evento' => $_SESSION['id_usuario']
                    );
                    $this->db->insert('evento', $log);

                    // -------------- /LOG ----------------
                    echo NULL;
                }
                else {
                    $nome_responsavel = $this->db->query("select nome_usuario from usuario where id_usuario = " . $id_responsavel)->row()->nome_usuario;
                    echo $nome_responsavel;
                }
            } else {

                if ($id_responsavel === $id_usuario || $auto_usuario >= 3) {

                    $desbloqueio = $this->db->query("UPDATE chamado set id_usuario_responsavel_chamado = NULL WHERE id_chamado = " . $id_chamado . " and status_chamado = 'ABERTO'");
                    
                    if($desbloqueio) {

                        // ------------ LOG -------------------

                        $log = array(
                            'acao_evento' => 'DESBLOQUEAR_CHAMADO',
                            'desc_evento' => 'ID CHAMADO: ' . $id_chamado,
                            'id_usuario_evento' => $_SESSION['id_usuario']
                        );
                        
                        $this->db->insert('evento', $log);

                        // -------------- /LOG ----------------

                        header('HTTP/1.0 200 OK');
                    }
                    else {
                        header('HTTP/1.0 403 Forbidden');
                    }
                }
                
            }


        } else {
            header('HTTP/1.0 403 Forbidden');
        }


    }

    public function texto_ultima_interacao() {
        if (isset($_SESSION['id_usuario'])) {

            $id_chamado = $this->input->post('id_chamado');

            $dados = array();

            
            // id da fila atual do chamado
            
            $busca = $this->db->query("select texto_interacao,
            (select nome_usuario from usuario where id_usuario = id_usuario_interacao) as nome_usuario
            from interacao where id_chamado_interacao = " . $id_chamado ." order by data_interacao desc");

            $result = $busca->row_array();

            $result['texto_interacao'] = strip_tags($result['texto_interacao']);


            header("Content-Type: application/json");

            echo json_encode($result);
        } else {
            header('HTTP/1.0 403 Forbidden');
        }


        
    }

    public function filas() {
        if (isset($_SESSION['id_usuario'])) {


            $dados = array();
            
            $busca = $this->db->query("select id_fila, nome_fila from fila where status_fila = 'ATIVO'");

            $result = $busca->result();

            header("Content-Type: application/json");

            echo json_encode($result);
        } else {
            header('HTTP/1.0 403 Forbidden');
        }



    }

    public function carrega_historico($id_chamado) {
        if (isset($_SESSION['id_usuario'])) {
            $lista = array();      
            $historico = $this->chamado_model->buscaHistoricoChamado($id_chamado);
            foreach($historico as $linha) {
                array_push($lista,"<p class=\"border rounded p-2 my-3\"><span class=\"badge badge-info\">" .
                date("d/m/Y H:i:s",strtotime($linha->data_alteracao)) .
                "</span> <strong>" . $linha->nome_usuario . "</strong> " . $linha->texto_alteracao . "</p>");
            }
            header("Content-Type: text/html");
            echo implode(" ",$lista);
        } else {
            header('HTTP/1.0 403 Forbidden');
        }
    }

    public function endereco_local($id_chamado) {
        if (isset($_SESSION['id_usuario'])) {
            

            $endereco = $this->chamado_model->buscarEnderecoChamado($id_chamado);
            echo $endereco;
        } else {
            header('HTTP/1.0 403 Forbidden');
        }
    }

}


?>