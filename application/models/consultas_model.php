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

        


        $q = "select id_triagem, nome_solicitante_triagem, email_triagem,
        		data_triagem, ticket_triagem from triagem where triado_triagem = 0 order by data_triagem asc";
   

        return $this->db->query($q)->result();
    }
	
	public function buscaTriagem($id_triagem) { 

        


        $q = "select * from triagem where id_triagem = ". $id_triagem;
   

        return $this->db->query($q)->row();
    }
    
    
	
	
    public function listaEncerrados() {

        $q = "SELECT id_chamado,ticket_chamado, nome_solicitante_chamado, 
        (SELECT nome_local FROM LOCAL WHERE id_local = id_local_chamado) AS nome_local, 
        DATE_FORMAT(data_chamado, \"%d/%m/%Y - %H:%i:%s\") AS data_chamado,
        (SELECT DATE_FORMAT(data_alteracao, \"%d/%m/%Y - %H:%i:%s\") FROM alteracao_chamado WHERE id_chamado_alteracao = id_chamado ORDER BY data_alteracao desc LIMIT 1) AS data_alt_chamado,
        (SELECT usuario.nome_usuario FROM usuario WHERE usuario.id_usuario = chamado.id_usuario_responsavel_chamado) AS nome_responsavel, 
        (SELECT nome_fila FROM fila WHERE id_fila = chamado.id_fila_chamado) AS nome_fila 
        FROM chamado
        WHERE status_chamado = 'ENCERRADO'";

        // if ($id_fila > 0 ) {

        //     if ($id_fila == 6) {
        //         $q .= " and id_fila_chamado = 3";

        //         $q .= " and entrega_chamado = 1";
        //     } else {

        //         $q .= " and id_fila_chamado = " . $id_fila;
        //     }
        // }

        // $q .= ' order by data_chamado';
        
        
        
        return $this->db->query($q)->result();
    }

    
	

    
    public function listaFilas() {
        
        $this->db->select();
        $this->db->from('fila');
        $this->db->where('status_fila = \'ATIVO\'');
        return $this->db->get()->result_array();
        
    }

    public function listaFila($id_fila) {
        
        $this->db->select();
        $this->db->from('fila');
        $this->db->where("id_fila = " . $id_fila);
        return $this->db->get()->row();
        
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
            $this->db->limit(10);
            $equip = $this->db->get()->result_array();

            $result["equip"] = count($equip) > 0 ? $equip : array();

            $this->db->select();
            $this->db->from("v_chamado");
            $this->db->where("ticket like '%" . $termo ."%'");
            $this->db->or_where("nome_solicitante like '%" . $termo ."%'");
            $this->db->or_where("nome_local like '%" . $termo ."%'");
            $this->db->limit(10); 
            $chamado = $this->db->get()->result_array();

            $result["chamado"] = count($chamado) > 0 ?  $chamado : array();
               

            $this->db->select();
            $this->db->from("v_triagem");
            $this->db->where("ticket like '%" . $termo ."%'");
            $this->db->or_where("nome_solicitante like '%" . $termo ."%'");
            $this->db->limit(10); 
            $triagem = $this->db->get()->result_array();
            
            $result["triagem"] = count($triagem) > 0 ? $triagem : array();

        }

        return $result;
    }
    
    
}

?>