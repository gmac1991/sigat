<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Servico_model extends CI_Model {
    public function listaServicos($valor = NULL,$grupos = NULL, $status = NULL) {
        $this->db->from("servico");
        $this->db->select("*");
        if ($valor != NULL) {
            $this->db->where("nome_servico like '%" . $valor . "%'");
        }
        if ($status != NULL) {
            $this->db->where("status_servico = '" . $status . "'");
        }
        if ($grupos != NULL) {     
            $arr_grupos = explode(",",$grupos);
            $str_grupos = "'" . $arr_grupos[0] . "'";
            array_splice($arr_grupos,0,1);
            foreach ($arr_grupos as $g) {
                $str_grupos .= ",'" . $g . "'";
            }  
            $this->db->where('grupo_servico in (' . $str_grupos . ')');
        }
        $result = $this->db->get()->result();
        return $result;
    }
}