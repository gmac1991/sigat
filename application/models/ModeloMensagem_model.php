<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class ModeloMensagem_model extends CI_Model {
    public function listaModeloMensagem($tipo, $id_fila) { 
        if (isset($tipo, $id_fila)) {
            $busca = $this->db->query("
                SELECT id_modelo_mensagem, status_modelo_mensagem, fila_modelo_mensagem, mensagem_modelo_mensagem, tipo_modelo_mensagem
                FROM modelo_mensagem
                WHERE tipo_modelo_mensagem = '{$tipo}' AND fila_modelo_mensagem = '{$id_fila}' AND status_modelo_mensagem = true")->result_array();
        } else {
            $busca = $this->db->query("
                SELECT *
                    FROM modelo_mensagem
                    order by alterado_modelo_mensagem DESC;
            ")->result_array();
        }

        if (count($busca) > 0) {
            return $busca;
        }

        else
            return NULL;
    }

    public function buscaModeloMensagem($id_modelo_mensagem)  {
        return $this->db->query("
            SELECT *
            FROM modelo_mensagem
            WHERE id_modelo_mensagem = '{$id_modelo_mensagem}'"
        )->row();
    }

    public function insereModeloMensagem($dados) {

        $valores = array(
            'mensagem_modelo_mensagem' =>           $dados['mensagem_modelo_mensagem'],
            'tipo_modelo_mensagem' =>               $dados['tipo_modelo_mensagem'],
            'fila_modelo_mensagem' =>               $dados['fila_modelo_mensagem'],
            'status_modelo_mensagem' =>             $dados['status_modelo_mensagem'],
            'data_modelo_mensagem' =>               date('Y-m-d H:i:s'),
            'alterado_modelo_mensagem' =>           date('Y-m-d H:i:s'),
        );

        $this->db->insert('modelo_mensagem', $valores);
        $valores['id_modelo_mensagem'] = $this->db->insert_id();

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'CRIAR_MODELO_MENSAGEM',
            'desc_evento' => 'ID MODELO_MENSAGEM: ' . $valores['id_modelo_mensagem'] ,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );
        
        $this->db->insert('evento', $log);
 
        // -------------- /LOG ----------------
    }

    public function atualizaModeloMensagem($dados) {

        $valores = array(
            'id_modelo_mensagem' =>             $dados['id_modelo_mensagem'],
            'fila_modelo_mensagem' =>           $dados['fila_modelo_mensagem'],
            'mensagem_modelo_mensagem' =>       $dados['mensagem_modelo_mensagem'],
            'tipo_modelo_mensagem' =>           $dados['tipo_modelo_mensagem'],
            'status_modelo_mensagem' =>         $dados['status_modelo_mensagem'],
            'status_modelo_mensagem' =>        $dados['status_modelo_mensagem'],
            'alterado_modelo_mensagem' =>       date('Y-m-d H:i:s'),
        );

        $this->db->where('id_modelo_mensagem', $dados['id_modelo_mensagem']);
        $this->db->update('modelo_mensagem', $valores);

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'ALTERAR_MODELO_MENSAGEM',
            'desc_evento' => 'ID MODELO MENSAGEM: ' . $dados['id_modelo_mensagem'] ,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );

        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------

        return $valores;
    }

    public function buscaUltimoModeloMensagem() {
        return $this->db->query("
            SELECT *
            FROM modelo_mensagem
            WHERE id_modelo_mensagem > 1
            ORDER BY id_modelo_mensagem DESC
            LIMIT 1; 
        ")
        ->row();
    }
}