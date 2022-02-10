<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Consultas_model extends CI_Model {

   
    public function listaChamados($id_fila = NULL,$id_usuario) {

        //removida verificacao do solicitante

        $nivel_usuario = $this->db->query('select autorizacao_usuario from usuario where id_usuario = ' . $id_usuario)->row()->autorizacao_usuario;


        $q = "select id_chamado, ticket_chamado, id_fila_chamado, nome_solicitante_chamado, 
        (select nome_local from local where id_local = id_local_chamado) as nome_local, 
		data_chamado, 
        (select usuario.nome_usuario 
        from usuario where usuario.id_usuario = chamado.id_usuario_responsavel_chamado) as nome_responsavel, 
        status_chamado, entrega_chamado from chamado where";

        $q .= ' status_chamado <> \'ENCERRADO\' and';


        if ($nivel_usuario <= 2 ) { 
            $q .= ' (id_usuario_responsavel_chamado = ' . $id_usuario;
            $q .= " or id_usuario_responsavel_chamado is NULL) and";
        }

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

        $q .= ' order by nome_responsavel is not NULL, status_chamado <> \'ABERTO\',status_chamado <> \'FECHADO\', data_chamado';

        return $this->db->query($q)->result();
    }
	
	public function listaTriagem() { // lista de chamados insertados pelo OTRS

        


        $q = "select id_chamado, nome_solicitante_chamado, email_chamado, status_chamado, 
		data_chamado, ticket_chamado from chamado where id_fila_chamado is NULL order by data_chamado asc";
   

        return $this->db->query($q)->result();
    }
	
	public function buscaTriagem($id_chamado) { // lista de chamados insertados pelo OTRS

        


        $q = "select * from chamado where id_chamado = ". $id_chamado;
   

        return $this->db->query($q)->row();
    }
    
    
	
	
    public function listaEncerrados($id_fila) {

        $q = "select id_chamado, nome_solicitante_chamado, 
        (select nome_local from local where id_local = id_local_chamado) as nome_local, 
        DATE_FORMAT(data_chamado, \"%d/%m/%Y - %H:%i:%s\") as data_chamado, 
        (select usuario.nome_usuario 
        from usuario where usuario.id_usuario = chamado.id_usuario_responsavel_chamado) as nome_responsavel, 
        status_chamado from chamado where status_chamado = 'ENCERRADO'";

        if ($id_fila > 0 ) {

            if ($id_fila == 6) {
                $q .= " and id_fila_chamado = 3";

                $q .= " and entrega_chamado = 1";
            } else {

                $q .= " and id_fila_chamado = " . $id_fila;
            }
        }

        $q .= ' order by data_chamado';
        
        
        
        return $this->db->query($q)->result();
    }
	

    
    public function listaFilas() {
        
        $this->db->select();
        $this->db->from('fila');
        $this->db->where('status_fila = \'ATIVO\'');
        return $this->db->get()->result_array();
        
    }

    public function listaFila($id_fila = NULL) {
        
        $this->db->select();
        $this->db->from('fila');
        if ($id_fila != NULL) {
            $this->db->where('id_fila = '. $id_fila);
        }
        
        return $this->db->get()->result_array();
        
    }
    
    public function listaSolicitantes() {
        
      /*  $this->db->select();
        $this->db->from('solicitante');
        $this->db->where("nome_solicitante <> '-0'"); //excluindo da consulta os usuarios com nome ' -0 '
        $this->db->order_by('nome_solicitante');
        return $this->db->get()->result_array();*/
        
    }

    public function listaLocais() {
        
        $this->db->select();
        $this->db->from('local');
        $this->db->order_by('nome_local');
        return $this->db->get()->result_array();
        
    }
    
    
    
}

?>