<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Garantia_model extends CI_Model {
    public function criarGarantia($id_reparo, $id_usuario, $ticket_garantia) {
        $this->db->set('id_reparo_garantia', $id_reparo);
        $this->db->set('id_usuario_garantia', $id_usuario);
        $this->db->set('ticket_garantia', $ticket_garantia);
        $this->db->set('data_garantia', date("Y-m-d H:i:s"));

        $result = $this->db->insert('garantia');

        $id = $this->db->insert_id();


        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'REGISTRAR_ABERTURA_GARANTIA',
            'desc_evento' => 'ID GARANTIA: ' . $id . ' - ID REPARO: ' . $id_reparo. " - TICKET GARANTIA: " .  $ticket_garantia,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );
        
        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------

        return $result;
    }

    public function buscaGarantia($id_reparo) {
        $this->db->select('*');
        $this->db->from('garantia g');
        $this->db->where('g.id_reparo_garantia', $id_reparo);

        return $this->db->get()->row();
    }

    public function salvarLaudoGarantia($id_garantia, $nome_laudo) {
        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'GRAVAR_LAUDO_GARANTIA',
            'desc_evento' => 'ID GARANTIA: ' . $id_garantia. " - TICKET GARANTIA: " .  $id_garantia,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );
        
        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------

        $this->db->set('nome_laudo_garantia', $nome_laudo);
        $this->db->where('id_garantia', $id_garantia);

        return $this->db->update('garantia');
    }
}