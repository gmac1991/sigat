<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Local_model extends CI_Model {
    //public function buscaFila($id_fila)  {
    //
    //    return $this->db->query('select * from fila where id_fila = ' . $id_fila)->row();
    //  
    //}

    public function buscaLocal($id){
        $sql = "SELECT id_local, status_local, nome_local, secretaria_local, endereco_local, regiao_local, infovia 
        FROM `local` WHERE id_local = " . $id ."";

        $busca = $this->db->query($sql);

        if($busca->num_rows() >= 1) {

            return $busca->result_array();

        }

        else{
            return NULL;
        }
    }

    public function buscaLocais()  {
        return $this->db->query("
            SELECT local.*, secretaria.nome_secretaria, secretaria.sigla_secretaria, secretaria.id_secretaria
            FROM local
            INNER JOIN secretaria ON local.secretaria_local = secretaria.id_secretaria 
            order by local.alteracao_local desc;
        ")->result_array();
    }

    public function buscaSecretarias() {
        return $this->db->query("
            SELECT id_secretaria, nome_secretaria, sigla_secretaria
            FROM secretaria
            WHERE status_secretaria = true
            ORDER BY sigla_secretaria ASC
        ")->result_array();
    }

    public function insereLocal($dados) {
        $valores = array(
            'id_local' =>             NULL,
            'nome_local' =>           $dados['nome_local'],
            'endereco_local' =>       $dados['endereco_local'],
            'secretaria_local' =>     $dados['secretaria_local'],
            'regiao_local' =>         $dados['regiao_local'],
            'status_local' =>         $dados['status_local'],
            'infovia' =>              $dados['infovia'],
            'alteracao_local' =>      date("Y-m-d H:i:s"),
        );

        $this->db->trans_start();
        $this->db->insert('local', $valores);
        $query = $this->db->query('SELECT id_local from local order by id_local desc LIMIT 1');
        $linha = $query->row_array();
        $valores['id_local'] = $linha['id_local'];

        $this->db->trans_complete();

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'INSERIR_LOCAL',
            'desc_evento' => 'ID LOCAL: ' . $valores['id_local'] ,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );

        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------

        return $valores;
    }

    public function atualizaLocal($dados) {

        $valores = array(
            'id_local' =>             $dados['id_local'],
            'nome_local' =>           $dados['nome_local'],
            'endereco_local' =>       $dados['endereco_local'],
            'secretaria_local' =>     $dados['secretaria_local'],
            'regiao_local' =>         $dados['regiao_local'],
            'status_local' =>         $dados['status_local'],
            'infovia' =>              $dados['infovia'],
            'alteracao_local' =>      date("Y-m-d H:i:s"),
        );

        $this->db->where('id_local', $dados['id_local']);
        $this->db->update('local', $valores);

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'ALTERAR_LOCAL',
            'desc_evento' => 'ID LOCAL: ' . $dados['id_local'] ,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );

        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------

        return $valores;
    }

    public function ativar_local($id_local){
        
        $sql = "UPDATE local SET status_local = (CASE status_local  WHEN 0 THEN 1 ELSE 0 END)
        WHERE id_local = '{$id_local}'";

        $this->db->query($sql);

            // ------------ LOG -------------------

            $log = array(
                'acao_evento' => 'ATIVAR_LOCAL',
                'desc_evento' => 'ID LOCAL: ' . $id_local ,
                'id_usuario_evento' => $_SESSION['id_usuario']
            );
    
            $this->db->insert('evento', $log);
    
            // -------------- /LOG ----------------
    }

    public function adicionarTelefone($dados){
        $sql = "INSERT INTO telefones_local
        (setor, telefone, id_local)
        VALUES('" . $dados['setor'] ."', '". $dados['telefone'] ."', " . $dados['id_local'] .");";

        $this->db->query($sql);

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'ADICIONAR_TELEFONE_LOCAL',
            'desc_evento' => 'ID LOCAL: ' . $dados['id_local'] ,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );

        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------
    }

    public function editarTelefone($dados){
        $sql = "UPDATE telefones_local SET
        setor = '" . $dados['setor'] ."', telefone = '". $dados['telefone'] ."', id_local = '" . $dados['id_local'] ."' WHERE id = '" . $dados['id'] ."'";

        $this->db->query($sql);

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'EDITAR_TELEFONE_LOCAL',
            'desc_evento' => 'ID LOCAL: ' . $dados['id_local'] ,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );

        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------
    }

    public function excluirTelefone($id){
        $sql = "DELETE FROM telefones_local
        WHERE id=${id}";

        $this->db->query($sql);

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'EXCLUIR_TELEFONE_LOCAL',
            'desc_evento' => 'ID TELEFONE: ' . $id,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );

        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------
    }

    public function listarTelefones($valores = array()){

        $sql = "SELECT id, setor, telefone, id_local FROM telefones_local WHERE 1 = 1 ";

        if(isset($valores['telefone']) && $valores['telefone'] != ''){
            $sql . "AND telefone = '". $valores['telefone'] ."'";
        }
        if(isset($valores['id_local']) && $valores['id_local'] != null){
            $sql .= "AND id_local = '". $valores['id_local']."'";
        }
        $busca = $this->db->query($sql);

        if($busca->num_rows() >= 1) {

            return $busca->result_array();

        }

        else{
            return NULL;
        }

    }
}