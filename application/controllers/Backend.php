<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Backend extends CI_Controller {

    /*Classe para Respostas às requisições AJAX e outras do Frontend */

    private $tags_permitidas = "<p><br><div><span><style>";

    function __construct() {
        parent::__construct();
        $this->load->library('consulta_ldap');
        $this->load->model("equipamento_model");
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
                    . base_url('chamado/gerar_laudo/' .$id_chamado) . "\" download>Baixar Laudo Técnico</a>";
                    
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

                case 'FECHAMENTO_PATRI': //fechamento com patrimonios
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
                    . base_url('chamado/gerar_laudo/' .$id_chamado) . "\" download>Baixar Laudo Técnico</a>";
                    
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
            die("Não autorizado!");
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
            die("Não autorizado!");
        }
    }

    

    public function chamado() {

        if (isset($_SESSION['id_usuario'])) {
            
            $id_chamado = $this->input->get('id_chamado');

   
            $q_buscaChamado = "select ticket_chamado, id_chamado, id_fila, status_chamado, nome_solicitante_chamado, nome_local, DATE_FORMAT(data_chamado, '%d/%m/%Y - %H:%i:%s') as data_chamado, descricao_chamado, telefone_chamado,
                (select usuario.nome_usuario from usuario where usuario.id_usuario = chamado.id_usuario_responsavel_chamado) as nome_responsavel,
                (select usuario.id_usuario from usuario where usuario.id_usuario = chamado.id_usuario_responsavel_chamado) as id_responsavel,  
                (select fila.nome_fila from fila where fila.id_fila = chamado.id_fila_chamado) as nome_fila_chamado, entrega_chamado
                from local, fila, chamado
                where local.id_local = chamado.id_local_chamado and
                fila.id_fila = chamado.id_fila_chamado and
                chamado.id_chamado = " . $id_chamado;

            $q_buscaPatrimonios = "select * from equipamento_chamado where id_chamado_equipamento = " . $id_chamado;

            $q_buscaEquipamentos = "select * from equipamento_chamado where id_chamado_equipamento = " . $id_chamado;
            
            //$q_buscaAnexos = "select nome_anexo from anexo where id_chamado_anexo = " . $id_chamado;

            $result = $this->db->query($q_buscaChamado)->row_array();

            $result['nome_solicitante_chamado'] = str_replace(array('"', "'"), '', $result['nome_solicitante_chamado']);
            
            $result['descricao_chamado'] = strip_tags($result['descricao_chamado'],$this->tags_permitidas);

            if ($result['id_fila'] == 3 && $result['entrega_chamado'] == 1) {
                $result['nome_fila_chamado'] = 'Entrega';
            }

            $result['patrimonios'] = $this->db->query($q_buscaPatrimonios)->result_array();
            
            $result['equipamentos'] = $this->db->query($q_buscaEquipamentos)->result_array();
            
            //$result['anexo'] = $this->db->query($q_buscaAnexos)->result_array();

            header("Content-Type: application/json");
                
            echo json_encode($result);
        }

        else {
            die("Não autorizado!");
        }
    }
	
	public function triagem() {

        if (isset($_SESSION['id_usuario'])) {
			
			$result = array();
            
            $id_triagem = $this->input->get('id_triagem');

   
            $q_buscaTriagem = "select * from triagem where id_triagem = " . $id_triagem;
				
			$q_buscaAnexosOTRS = "select id_anexo_otrs, nome_arquivo_otrs from anexos_otrs
			where id_chamado_sigat = " . $id_triagem;

            

            $result['triagem'] = $this->db->query($q_buscaTriagem)->row_array();

            $result['triagem']['descricao_triagem'] = 
            strip_tags($result['triagem']['descricao_triagem'],$this->tags_permitidas);
            
            
            $result['anexos_otrs'] = $this->db->query($q_buscaAnexosOTRS)->result_array();
			
			

           

            header("Content-Type: application/json");
                
            echo json_encode($result);
        }

        else {
            die("Não autorizado!");
        }
    }
	
	public function anexo_otrs($id_anexo) {

        if (isset($_SESSION['id_usuario'])) {
		
				
			$q_buscaAnexoOTRS = "select * from anexos_otrs where id_anexo_otrs = " . $id_anexo;

            $anexo = $this->db->query($q_buscaAnexoOTRS)->row_array();

            
			header("Content-Disposition: attachment; filename=" . $anexo['nome_arquivo_otrs']);
			ob_clean();
			flush();
			
			
			echo $anexo['conteudo_anexo_otrs'];
        }

        else {
            die("Não autorizado!");
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
            die("Não autorizado!");
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
            die("Não autorizado!");
        }


    }

    public function desc_equipamento() {

        if (isset($_SESSION['id_usuario'])) {

            $num_equip = $this->input->post('e');

            $dados['descricao'] = NULL;

            $descEquipSigat = $this->equipamento_model->buscaDescEquipamento($num_equip);

          
            if ($descEquipSigat === NULL) {

                $res_sim = NULL;

                $res_sim = file_get_contents($this->config->item('api_sim') . $num_equip);

                if ($res_sim != 'null') {

                    $descSim = json_decode($res_sim)->descrBem; //descricao do SIM

                    $dados['descricao'] = $descSim;
                }
            }  
            else {

                $dados['descricao'] = $descEquipSigat; //se estiver cadastrado, pega a desc do SIGAT
            }

            header("Content-Type: application/json");

            echo json_encode($dados);

        } else {
            die("Não autorizado!");
        }
    }

    public function status_equipamento() {

        //if (isset($_SESSION['id_usuario'])) {


            $statusEquip = $this->equipamento_model->buscaStatusEquipamento($this->input->post('e'));

            if($statusEquip == NULL)
                echo FALSE;

            else {

                header("Content-Type: application/json");

                echo json_encode($statusEquip);
            }
           
            
        // } else {
        //     die("Não autorizado!");
        // }
    }

    public function patrimonios() {
        if (isset($_SESSION['id_usuario'])) {

            $id_chamado = $this->input->post('id_chamado');
            $espera = $this->input->post('espera');

            $dados = array();
            
            // removido verificaçao da fila

            // $requer_patri = FALSE;

            //id da fila atual do chamado
            
            $busca = $this->db->query("select id_fila from fila, chamado where id_fila = id_fila_chamado and id_chamado = " . $id_chamado);

            $result = $busca->row_array();

            $dados['id_fila'] = $result['id_fila'];

            $busca = $this->db->query("select * from fila");

            $result = $busca->result_array();

            $dados['filas'] = $result;

            // if ($result['requer_equipamento_fila'] == 1) { // filas

            //     $requer_patri = TRUE;

            //     $busca = $this->db->query("select id_fila, nome_fila from fila where requer_equipamento_fila = 1");

            //     $result = $busca->result_array();

            //     $dados['filas'] = $result;

            // } else {

            //     $busca = $this->db->query("select id_fila, nome_fila from fila where requer_equipamento_fila = 0");

            //     $result = $busca->result_array();

            //     $dados['filas'] = $result;
            // }

            // patrimonios do chamado

            // if ( $requer_patri ) { //se a fila requer patrimonio

                //var_dump($espera);

                if ($espera == 'false') {

                    $busca = $this->db->query("select num_equipamento from equipamento_chamado where id_chamado_equipamento = " . $id_chamado . 
                    " and (status_equipamento_chamado = 'ABERTO' or status_equipamento_chamado = 'FALHA')");
                
                } else {
                    
                    $busca = $this->db->query("select num_equipamento from equipamento_chamado where id_chamado_equipamento = " . $id_chamado . 
                    " and status_equipamento_chamado = 'ESPERA'");

                }

                $result = $busca->result_array();

                $dados['patrimonios'] = $result;
            // }
            // else {

            //     $dados['patrimonios'] = NULL;
            // }

            header("Content-Type: application/json");

            echo json_encode($dados);
        } else {
            die("Não autorizado!");
        }
    }

    public function usuarios() {
        if (isset($_SESSION['id_usuario'])) {
            
            $busca = $this->db->query("select * from usuario where status_usuario = 'ATIVO' order by nome_usuario");

            $result = $busca->result_array();

            header("Content-Type: application/json");
    
            echo json_encode($result);
            
        
        } else {
            die("Não autorizado!");
        }
    }

    // public function requer_equipamento() {

    //     $id_fila = $this->input->get('id_fila');

    //     if (isset($_SESSION['id_usuario'])) {

    //         try {
    //             $busca = $this->db->query("SELECT * FROM fila WHERE id_fila = " . $id_fila . " and requer_equipamento_fila = 1");
                
    //             $result = $busca->result_array();
                
    //             if ( count($result) ) { 
              
    //               echo '1';
                  
    //             }
    //           } catch(PDOException $e) {
    //               echo 'ERROR: ' . $e->getMessage();
    //           }
        
        
    //     } else {
    //         die("Não autorizado!");
    //     }
        

    // }

    public function atualiza_responsavel() {

        if (isset($_SESSION['id_usuario'])) {

            $id_usuario = $this->input->post('id_usuario');
            $id_chamado = $this->input->post('id_chamado');
            $tipo = $this->input->post('tipo');

        

            if ($tipo == 'b') {

                $busca = $this->db->query("UPDATE chamado set id_usuario_responsavel_chamado = " . $id_usuario .
                " WHERE id_chamado = " . $id_chamado);

                // ------------ LOG -------------------

                $log = array(
                    'acao_evento' => 'BLOQUEAR_CHAMADO',
                    'desc_evento' => 'ID CHAMADO: ' . $id_chamado,
                    'id_usuario_evento' => $_SESSION['id_usuario']
                );
                
                $this->db->insert('evento', $log);

                // -------------- /LOG ----------------

            } else {

                $busca = $this->db->query("UPDATE chamado set id_usuario_responsavel_chamado = NULL WHERE id_chamado = " . $id_chamado . " and status_chamado = 'ABERTO'");
                
                // ------------ LOG -------------------

                $log = array(
                    'acao_evento' => 'DESBLOQUEAR_CHAMADO',
                    'desc_evento' => 'ID CHAMADO: ' . $id_chamado,
                    'id_usuario_evento' => $_SESSION['id_usuario']
                );
                
                $this->db->insert('evento', $log);

                // -------------- /LOG ----------------
            }

            if (!$busca) {
                echo FALSE;
            }

        } else {
            die("Não autorizado!");
        }


    }

    public function equipamentos() {

        if (isset($_SESSION['id_usuario'])) {

            $termo = $this->input->post('id_chamado');

            try {
            $result['equipamentos'] = $this->db->query("select * from equipamento_chamado where id_chamado_equipamento = " 
            . $termo . " and status_equipamento = 'ABERTO'")->result_array();
            
            header("Content-Type: application/json");

            echo json_encode($result);
                
            } catch(PDOException $e) {
                echo 'ERROR: ' . $e->getMessage();
            }
        }
        else {
            die("Não autorizado!");
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

           // $result['texto_interacao'] = strip_tags($result['texto_interacao']);


            header("Content-Type: application/json");

            echo json_encode($result);
        } else {
            die("Não autorizado!");
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
            die("Não autorizado!");
        }



    }

   

}


?>