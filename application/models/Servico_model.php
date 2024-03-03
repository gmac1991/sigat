<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Servico_model extends CI_Model {
    public function listaServicos($valor = NULL,$filas = NULL, $status = NULL) {
        $this->db->from("servico");
        $this->db->select("*");
        if ($valor != NULL) {
            $this->db->where("nome_servico like '%" . $valor . "%'");
        }
        if ($status != NULL) {
            $this->db->where("status_servico = " . $status);
        }
        if ($filas != NULL) {     
            $arr_filas = explode(",",$filas);
            $str_filas = "'" . $arr_filas[0] . "'";
            array_splice($arr_filas,0,1);
            foreach ($arr_filas as $f) {
                $str_filas .= ",'" . $f . "'";
            }  
            $this->db->where('id_fila_servico in (' . $str_filas . ')');
        }
        $result = $this->db->get()->result();
        return $result;
    }

    public function inserirServicos($dados){
        $valores = array(
            'id_servico' =>          NULL,
            'nome_servico' =>        $dados['nome_servico'],
            'id_fila_servico' =>       $dados['id_fila_servico'],
            'status_servico' =>      $dados['status_servico'],
            'valor_servico' =>       $dados['valor_servico'],
            'unidade_medida' =>       $dados['unidade_medida'],
            'pontuacao_servico' =>       $dados['pontuacao_servico'],
            'data_ultima_alteracao' =>       date('Y-m-d H:i:s')
        );

        $this->db->trans_start();
        $this->db->insert('servico', $valores);
        $query = $this->db->query('SELECT id_servico from servico order by id_servico desc LIMIT 1');
        $linha = $query->row_array();
        $valores['id_servico'] = $linha['id_servico'];

        $this->db->trans_complete();

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'INSERIR_SERVICO',
            'desc_evento' => 'NOME SERVICO: ' . $dados['nome_servico'] ,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );

        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------

        return $valores;

    }

    public function atualizarServico($dados){
        
        $valores = array(
            'id_servico' =>          $dados['id_servico'],
            'nome_servico' =>        $dados['nome_servico'],
            'id_fila_servico' =>       $dados['id_fila_servico'],
            'status_servico' =>      $dados['status_servico'],
            'valor_servico' =>       $dados['valor_servico'],
            'unidade_medida' =>       $dados['unidade_medida'],
            'pontuacao_servico' =>       $dados['pontuacao_servico'],
            'data_ultima_alteracao' =>       date('Y-m-d H:i:s'),
        );

        $this->db->where('id_servico', $dados['id_servico']);
        $this->db->update('servico', $valores);

         // ------------ LOG -------------------

         $log = array(
            'acao_evento' => 'ALTERAR_SERVICO',
            'desc_evento' => 'NOME SERVICO: ' . $dados['nome_servico'] ,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );

        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------

        return $valores;
    }
}