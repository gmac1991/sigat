<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Triagem_model extends CI_Model {
    

    public function buscaAnexosTicket($id_ticket) {

        $db_otrs = $this->load->database('otrs', TRUE);

        
        $res = $db_otrs->query("SELECT aa.id, aa.filename FROM article_data_mime_attachment aa
        INNER JOIN article a ON (aa.article_id = a.id)
        WHERE(aa.disposition = 'attachment' OR aa.content_type LIKE 'image%') AND a.ticket_id = " .$id_ticket);

        return $res->result_array();
    }

    public function buscaTicket($id_ticket) { 
        $dados = array(
            "t_info" => NULL,
            "t_articles" => NULL
        );

        $db_otrs = $this->load->database('otrs', TRUE);

        $res = $db_otrs->query("SELECT t.queue_id, t.id, t.tn, t.create_time, t.title, REPLACE(adm.a_from,'\"','') as a_from
        FROM article_data_mime adm
        INNER JOIN article a ON (adm.article_id = a.id)
        INNER JOIN ticket t ON (a.ticket_id = t.id)
        WHERE t.ticket_state_id IN(1,4) AND t.id = " . $id_ticket .
        " ORDER BY adm.create_time desc
        LIMIT 1");

        $dados['t_info'] = $res->row();

        $res = $db_otrs->query("SELECT adm.article_id, REPLACE(adm.a_from,'\"','') as a_from, adm.a_subject, adm.a_body, adm.create_time
        FROM article_data_mime adm
        INNER JOIN article a ON (adm.article_id = a.id)
        INNER JOIN ticket t ON (a.ticket_id = t.id)
        WHERE t.ticket_state_id IN(1,4) AND t.id = " . $id_ticket . 
        " ORDER BY adm.create_time desc");

        $dados['t_articles'] = $res->result();

        return $dados;
    }

    public function listaTriagem() { // lista de chamados da fila Suporte Atendimento do OTRS (queue_id = 37)
        

        $db_otrs = $this->load->database('otrs', TRUE);

        $db_otrs->query("SET SESSION sql_mode=''");

        $filas_ticketsys = implode(",",array_keys($this->config->item("conversao_id_filas")));
        
        $res = $db_otrs->query("SELECT t.id, t.tn, t.create_time, t.title, REPLACE(adm.a_from,'\"','') as a_from
        FROM article_data_mime adm
        INNER JOIN article a ON (adm.article_id = a.id)
        INNER JOIN ticket t ON (a.ticket_id = t.id)
        WHERE t.queue_id IN (" . $filas_ticketsys  . ") AND t.ticket_state_id IN(1,4)
        GROUP BY t.tn
        ORDER BY adm.create_time ASC");

        return $res->result();
    }
}