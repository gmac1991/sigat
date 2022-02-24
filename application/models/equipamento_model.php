<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Equipamento_model extends CI_Model {


    public function buscaDescEquipamento($num_equip) {

        $busca = $this->db->query("select * from equipamento where num_equipamento = " .$num_equip);

        if($busca->num_rows() == 1) {

            return $busca->row()->descricao_equipamento;
        }

        else
            return NULL;

    }

    public function insereEquipamento($num_equip,$desc_equip) {

        $insercao = $this->db->query("insert into equipamento values(".$num_equip.",'".$desc_equip."')");

        if($insercao) {

            return TRUE;
        }

        else
            return NULL;

    }
}