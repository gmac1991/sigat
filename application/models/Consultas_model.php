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
            $this->db->from("equipamento");
            $this->db->where("num_equipamento = '" . $termo ."'");
            
            $equip = $this->db->get()->result_array();
        

            if (count($equip) == 1)
            {

                $result["chamados_equip"] = array();
                $result["equip"] = $equip;

                $this->db->select(
                    "*, DATE_FORMAT(ultima_alteracao_equipamento_chamado,
                    '%d/%m/%Y') as data_ultima_alteracao"
                );
                $this->db->from("equipamento_chamado ec");
                $this->db->join("chamado c", 'ec.id_chamado_equipamento = c.id_chamado');
                $this->db->join("local l", 'c.id_local_chamado = l.id_local');
                $this->db->where("num_equipamento_chamado = '" . $equip[0]['num_equipamento'] ."'");
                $this->db->order_by('ultima_alteracao_equipamento_chamado', 'DESC');
                $this->db->limit(10);

                $chamados_equip = $this->db->get()->result_array();
                $result["chamados_equip"] = $chamados_equip;

            }

            else 
            {
                

                $this->db->select();
                $this->db->from("equipamento");
                $this->db->where("num_equipamento like '%" . $termo ."%'");
                $this->db->or_where("descricao_equipamento like '%" . $termo ."%'");
                $this->db->or_where("tag_equipamento like '%" . $termo ."%'");
                $equip = $this->db->get()->result_array();

                if (count($equip) > 0)
                {
                    
                    $result["equip"] = $equip;
                }  
            }
           
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

   
}

?>