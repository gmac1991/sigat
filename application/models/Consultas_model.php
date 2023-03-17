<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Consultas_model extends CI_Model {

   
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
    --    (
    --    SELECT COUNT(*)
    --    FROM equipamento_chamado
    --    WHERE id_chamado_equipamento = c.id_chamado) AS total_equips,
    --    (
    --    SELECT COUNT(*)
    --    FROM equipamento_chamado
    --    WHERE id_chamado_equipamento = c.id_chamado AND status_equipamento_chamado IN('ATENDIDO','ENTREGUE','INSERVIVEL')) AS atend_equips,
       status_chamado, entrega_chamado,
       (
        SELECT data_interacao FROM interacao
        WHERE id_chamado_interacao = c.id_chamado
        ORDER BY data_interacao DESC
        LIMIT 1
        ) AS data_ultima_interacao
       FROM chamado c where";

        $q .= ' status_chamado <> \'ENCERRADO\' and';


        // if ($nivel_usuario <= 2 ) { 
        //     $q .= ' (id_usuario_responsavel_chamado = ' . $id_usuario;
        //     $q .= " or id_usuario_responsavel_chamado is NULL) and";
        // }

        if ($id_fila > 0) {

            if ($id_fila == 7) { // fila de Entrega (virtual)
                $q .= " id_fila_chamado = 3";

                $q .= " and entrega_chamado = 1";
            } else {

                $q .= " id_fila_chamado = " . $id_fila;
            }
            
        } else {

            $q .= " id_fila_chamado > 0 ";
        }

        //$q .= ' order by data_chamado';

        return $this->db->query($q)->result();
    }
	
	public function listaTriagem() { // lista de chamados da fila Suporte Atendimento do OTRS (queue_id = 37)
        

        $db_otrs = $this->load->database('otrs', TRUE);

        $db_otrs->query("SET SESSION sql_mode=''");
        
        $res = $db_otrs->query("SELECT t.id, t.tn, t.create_time, t.title, REPLACE(adm.a_from,'\"','') as a_from
        FROM article_data_mime adm
        INNER JOIN article a ON (adm.article_id = a.id)
        INNER JOIN ticket t ON (a.ticket_id = t.id)
        WHERE t.queue_id = 37 AND t.ticket_state_id IN(1,4)
        GROUP BY t.tn
        ORDER BY adm.create_time ASC");

        return $res->result();
    }
	
	public function buscaTicket($id_ticket,$queue_id) { 
        $dados = array(
            "t_info" => NULL,
            "t_articles" => NULL
        );

        $db_otrs = $this->load->database('otrs', TRUE);

        $res = $db_otrs->query("SELECT t.id, t.tn, t.create_time, t.title, REPLACE(adm.a_from,'\"','') as a_from
        FROM article_data_mime adm
        INNER JOIN article a ON (adm.article_id = a.id)
        INNER JOIN ticket t ON (a.ticket_id = t.id)
        WHERE t.queue_id = ". $queue_id . " AND t.ticket_state_id IN(1,4) AND t.id = " . $id_ticket .
        " ORDER BY adm.create_time asc
        LIMIT 1");

        $dados['t_info'] = $res->row();

        $res = $db_otrs->query("SELECT adm.article_id, REPLACE(adm.a_from,'\"','') as a_from, adm.a_subject, adm.a_body, adm.create_time
        FROM article_data_mime adm
        INNER JOIN article a ON (adm.article_id = a.id)
        INNER JOIN ticket t ON (a.ticket_id = t.id)
        WHERE t.queue_id = ". $queue_id . " AND t.ticket_state_id IN(1,4) AND t.id = " . $id_ticket . 
        " ORDER BY adm.create_time asc");

        $dados['t_articles'] = $res->result();

        return $dados;
    }
    
    
	
	
    public function listaEncerrados() {

        $q = "SELECT id_chamado,ticket_chamado, nome_solicitante_chamado, 
        (SELECT nome_local FROM local WHERE id_local = id_local_chamado) AS nome_local, 
        data_chamado, data_encerramento_chamado
        FROM chamado
        WHERE status_chamado = 'ENCERRADO' order by data_encerramento_chamado desc limit 500";

        return $this->db->query($q)->result();
    }

    public function listaFilas($status = "'ATIVO'",$equipe = NULL) {
        
        $this->db->select();
        $this->db->from('fila');
        if ($status != NULL) {
            $this->db->where("status_fila = " . $status);
        }
        if ($equipe != NULL) {
            $this->db->where("equipe_fila = " . $equipe);
        }
   
        return $this->db->get()->result_array();
        
    }

    public function listaFila($id_fila) {
        
        $this->db->select();
        $this->db->from('fila');
        $this->db->where("id_fila = " . $id_fila);
        return $this->db->get()->row();
        
    }
    
    

    public function listaLocais() {
        
        $this->db->select();
        $this->db->from('local');
        $this->db->order_by('nome_local');
        return $this->db->get()->result_array();
        
    }

    public function buscaGrupo($auto) {

        $this->db->select();
        $this->db->from('grupo');
        $this->db->where('autorizacao_grupo = ' . $auto);
        return $this->db->get()->row();


    }
    

    public function buscaRapida($termo) {

        $result = NULL;

        if (strlen($termo) >= 3) {

            $result = array();
            
            $equip = NULL;

            $this->db->select();
            $this->db->from("v_equipamento");
            $this->db->where("num_equip like '%" . $termo ."%'");
            $this->db->or_where("desc_equip like '%" . $termo ."%'");
            $this->db->or_where("tag_equip like '%" . $termo ."%'");
            $this->db->limit(10);
            $equip = $this->db->get()->result_array();

            $result["equip"] = count($equip) > 0 ? $equip : array();

            $this->db->select();
            $this->db->from("v_chamado");
            $this->db->where("ticket like '%" . $termo ."%'");
            $this->db->or_where("nome_solicitante like '%" . $termo ."%'");
            $this->db->or_where("nome_local like '%" . $termo ."%'");
            $this->db->or_where("id like '%" . $termo ."%'");
            $this->db->order_by('id', 'DESC');
            $this->db->limit(10); 
            $chamado = $this->db->get()->result_array();

            $result["chamado"] = count($chamado) > 0 ?  $chamado : array();
               


        }

        return $result;
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

    public function conf() {
        $this->db->select();
        $this->db->from('configuracao');
        
        return $this->db->get()->row();
    }
    
    
}

?>