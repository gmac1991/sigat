<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_model extends CI_Model {

   

    public function listaEventos() {

        return $this->db->query("SELECT id_evento,acao_evento,desc_evento,nome_usuario, data_evento FROM evento
        INNER JOIN usuario
        ON evento.id_usuario_evento = usuario.id_usuario
        ORDER BY id_evento DESC LIMIT 500")->result();
    }


         
}


?>