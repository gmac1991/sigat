<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Equipamento_model extends CI_Model {


    public function buscaDescEquipamento($num_equip) {

        $busca = $this->db->query("select * from equipamento where num_equipamento = '" . $num_equip . "'");

        if($busca->num_rows() == 1) {

            return $busca->row()->descricao_equipamento;
        }

        else
            return NULL;

    }

    public function buscaStatusEquipamento($num_equip) {

        $sql = "SELECT id_chamado, ticket_chamado, status_equipamento_chamado
        FROM chamado,equipamento_chamado
        WHERE equipamento_chamado.num_equipamento_chamado = '". $num_equip.
        "' AND equipamento_chamado.id_chamado_equipamento = chamado.id_chamado
        ORDER BY chamado.id_chamado DESC LIMIT 1";

        $busca = $this->db->query($sql);
        
        if($busca->num_rows() >= 1) {

            return $busca->row();

            
        }

        else
            return NULL;

    }

    public function insereEquipamento($num_equip,$desc_equip) {

        $insercao = $this->db->query("insert into equipamento values(".$num_equip.",'".$desc_equip."')");

        if($insercao) {
            // ------------ LOG -------------------

            $log = array(
                'acao_evento' => 'ADICIONAR_EQUIP',
                'desc_evento' => 'ID: ' . $num_equip . ' - DESC: ' . $desc_equip,
                'id_usuario_evento' => $_SESSION['id_usuario']
            );
            
            $this->db->insert('evento', $log);

            // -------------- /LOG ----------------

            return TRUE;
        }

        else
            return NULL;

    }
}