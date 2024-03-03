<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Usuario_model extends CI_Model {
	   
    public function validaUsuario($dados) {


        $busca = $this->db->query("SELECT id_usuario, nome_usuario, fila_usuario FROM usuario WHERE login_usuario = '" . $dados['login_usuario'] . "' and status_usuario = true");

        if ($busca->num_rows() == 1) {

            $usuario = array();
            $usuario['id_usuario'] = $busca->row()->id_usuario;
            $usuario['nome_usuario'] = $busca->row()->nome_usuario;

            return $usuario;
        }
            
        else {
            return NULL;
        }
    }

    public function buscaUsuario($id_usuario)  {

        return $this->db->query('select *, DATE_FORMAT(data_usuario, "%d/%m/%Y") as data_usuario from usuario where id_usuario = ' . $id_usuario)->row();
      
    }

    public function test()  {

        return $this->db->query('select *, from usuario')->result_array();
      
    }

    public function buscaUsuarios()  {

        return $this->db->query('select * from usuario where id_usuario > 1 
        order by alteracao_usuario desc')->result_array();
      
    }

    public function atualizaUsuario($dados) {

        $valores = array(
            'nome_usuario' =>               $dados['nome_usuario'],
            'login_usuario' =>              $dados['login_usuario'],
            'status_usuario' =>             $dados['status_usuario'],
            'autorizacao_usuario' =>        $dados['autorizacao_usuario'],
            'fila_usuario' =>               $dados['fila_usuario'],
            'triagem_usuario' =>            $dados['triagem_usuario'],
            'encerramento_usuario' =>       $dados['encerramento_usuario'],
            'alteracao_usuario' =>          date('Y-m-d H:i:s'),
        );
        
        $this->db->where('id_usuario', $dados['id_usuario']);
        $this->db->update('usuario', $valores);
        $valores['id_usuario'] = $dados['id_usuario'];
        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'ALTERAR_USUARIO',
            'desc_evento' => 'ID USUARIO: ' . $dados['id_usuario'] ,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );
        
        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------

        return $valores;
    }

    public function insereUsuario($dados) {

        $valores = array (
            'id_usuario' =>             NULL,
            'nome_usuario' =>           $dados['nome_usuario'],
            'login_usuario' =>          $dados['login_usuario'],
            'status_usuario' =>         $dados['status_usuario'],
            'autorizacao_usuario' =>    $dados['autorizacao_usuario'],
            'fila_usuario' =>           $dados['fila_usuario'],
            'triagem_usuario' =>        $dados['triagem_usuario'],
            'encerramento_usuario' =>   $dados['triagem_usuario'],
            'data_usuario' =>           date('Y-m-d H:i:s'),
            'alteracao_usuario' =>      date('Y-m-d H:i:s'),
        );

        $this->db->trans_start();
        $this->db->insert('usuario', $valores);
        $query = $this->db->query('SELECT id_usuario from usuario order by data_usuario desc LIMIT 1');
        $linha = $query->row_array();
        $valores['id_usuario'] = $linha['id_usuario'];
        $this->db->trans_complete();

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'CRIAR_USUARIO',
            'desc_evento' => 'ID USUARIO: ' . $valores['id_usuario'] ,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );
        
        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------

        return $valores;
    }

    // public function buscaUltimoUsuario() {

    //     return $this->db->query("
    //         SELECT *,
    //         DATE_FORMAT(data_usuario, '%d/%m/%Y') AS data_usuario,
    //         DATE_FORMAT(alteracao_usuario, '%d/%m/%Y') AS alteracao_usuario
    //         FROM usuario
    //         WHERE id_usuario > 1
    //         ORDER BY id_usuario DESC
    //         LIMIT 1;
    //     ")->row();
    // }

    public function buscaTotalInteracoesUsuario($id)  {

        return $this->db->query("
        select COUNT(*) as interacoes from interacao 
        where id_usuario_interacao = " . $id ." and data_interacao 
        BETWEEN DATE_SUB(now(),INTERVAL 30 DAY) AND now()")->row();
      
    }

    public function buscaTotalInteracoesUsuarioPorDia($id)  {

        return $this->db->query("
        select DATE_FORMAT(data_interacao,'%d/%m') DATA, count(*) QTDE from interacao 
        where id_usuario_interacao = " . $id ." and ( data_interacao 
        BETWEEN DATE_SUB(now(),INTERVAL 30 DAY) AND now() ) group by DATE_FORMAT(data_interacao,'%d/%m')")->result_array();
      
    }

    public function buscaAberturaChamadosPorDia($id){
        return $this->db->query("select DATE_FORMAT(data_chamado,'%d/%m') DATA, count(*) QTDE from chamado 
        where id_usuario_abertura_chamado = " . $id ." and ( data_chamado 
        BETWEEN DATE_SUB(now(),INTERVAL 30 DAY) AND now() ) group by DATE_FORMAT(data_chamado,'%d/%m')")->result_array();
    }

    public function buscaEnceramentoChamadosPorDia($id){
        return $this->db->query("select DATE_FORMAT(c.data_encerramento_chamado,'%d/%m') DATA, count(*) QTDE from chamado c
		LEFT JOIN interacao i on i.id_chamado_interacao = c.id_chamado 
        where i.tipo_interacao = 'ENC' and ( c.data_encerramento_chamado
        BETWEEN DATE_SUB(now(),INTERVAL 30 DAY) AND now() ) and i.id_usuario_interacao = " . $id ." group by DATE_FORMAT(c.data_encerramento_chamado ,'%d/%m')")->result_array();
    }

    public function buscaUltimoLogin($id){
        return $this->db->query("SELECT data_evento FROM evento WHERE id_usuario_evento = ". $id ." ORDER BY data_evento DESC LIMIT 1")->row();
    }

    public function ativar_permissoes($id_usuario, $permissao){
        $sql = '';

        if($permissao == 'triagem'){
            $sql = "UPDATE usuario SET triagem_usuario = (CASE triagem_usuario  WHEN 0 THEN 1 ELSE 0 END)
            WHERE id_usuario = {$id_usuario }";
        }

        if($permissao == 'encerramento'){
            $sql = "UPDATE usuario SET encerramento_usuario = (CASE encerramento_usuario  WHEN 0 THEN 1 ELSE 0 END)
            WHERE id_usuario = {$id_usuario}";
        }

        if($permissao == 'inserviveis'){
            $sql = "UPDATE usuario SET inservivel_usuario = (CASE inservivel_usuario  WHEN 0 THEN 1 ELSE 0 END)
            WHERE id_usuario = {$id_usuario}";
        }
        
        if($sql != ''){
            $this->db->query($sql);

            // ------------ LOG -------------------

            $log = array(
                'acao_evento' => 'ATIVAR_PERMISSAO_USUARIO',
                'desc_evento' => 'ID USUARIO: ' . $id_usuario ,
                'id_usuario_evento' => $_SESSION['id_usuario']
            );
    
            $this->db->insert('evento', $log);
    
            // -------------- /LOG ----------------
        }
        
    }
         
}


?>