<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Usuario_model extends CI_Model {
	


    
    public function validaUsuario($dados) {

        $busca = $this->db->query('select id_usuario, nome_usuario from usuario where login_usuario = \'' 
        . $dados['login_usuario'] . '\' and status_usuario = \'ATIVO\'');
        
        $cont = $busca->num_rows();

        if ($cont == 1) {

            $adServer = "ldap://pms-indcr01.prefeitura.local";

            $ldap = ldap_connect($adServer);
            $username = $dados['login_usuario'];
            $password = $dados['senha_usuario'];
        
            $ldaprdn = 'prefeitura' . "\\" . $username;
        
            ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
        
            $bind = ldap_bind($ldap, $ldaprdn, $password);
        
            if ($bind) {

                $usuario = array();

                $usuario['id_usuario'] = $busca->row()->id_usuario;
                $usuario['nome_usuario'] = $busca->row()->nome_usuario;

                return $usuario;
            }

            else {

                return NULL;
            }

        }
            
        else
            return NULL;
    }

    public function buscaUsuario($id_usuario)  {

        return $this->db->query('select *, DATE_FORMAT(data_usuario, "%d/%m/%Y") as data_usuario from usuario where id_usuario = ' . $id_usuario)->row();
      
    }

    public function buscaUsuarios()  {

        return $this->db->query('select *,DATE_FORMAT(data_usuario, "%d/%m/%Y") as data_usuario, 
        DATE_FORMAT(alteracao_usuario, "%d/%m/%Y - %H:%i:%s") as alteracao_usuario from usuario where id_usuario > 1')->result_array();
      
    }

    public function atualizaUsuario($dados) {

        $valores = array(
            'nome_usuario' => $dados['nome_usuario'],
            'login_usuario' => $dados['login_usuario'],
            'status_usuario' => $dados['status_usuario'],
            'autorizacao_usuario' => $dados['autorizacao_usuario'],
            'fila_usuario' => $dados['fila_usuario'],
            'alteracao_usuario' => date('Y-m-d G:i:s'),
        );
        
        $this->db->where('id_usuario', $dados['id_usuario']);
        $this->db->update('usuario', $valores);

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'ALTERAR_USUARIO',
            'desc_evento' => 'ID USUARIO: ' . $dados['id_usuario'] ,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );
        
        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------
    }

    public function insereUsuario($dados) {

        $valores = array(
            'nome_usuario' => $dados['nome_usuario'],
            'login_usuario' => $dados['login_usuario'],
            'status_usuario' => $dados['status_usuario'],
            'autorizacao_usuario' => $dados['autorizacao_usuario'],
            'fila_usuario' => $dados['fila_usuario'],
            'data_usuario' => date('Y-m-d G:i:s'),
        );
        
        $this->db->insert('usuario', $valores);

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'CRIAR_USUARIO',
            'desc_evento' => 'ID USUARIO: ' . $dados['id_usuario'] ,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );
        
        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------
    }

    public function buscaUltimoUsuario() {

        return $this->db->query("SELECT *,DATE_FORMAT(data_usuario, \"%d/%m/%Y\") as data_usuario FROM usuario ORDER BY id_usuario DESC limit 1 where id_usuario > 1")->row();
    }


         
}


?>