<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Consultas_model extends CI_Model {

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

    public function conf() {
        $this->db->select();
        $this->db->from('configuracao');
        
        return $this->db->get()->row();
    } 
}

?>