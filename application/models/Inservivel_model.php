<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Inservivel_model extends CI_Model {
    public function salvarTermo($id_remessa, $nome_termo) {
        
        // ------------ LOG -------------------
        
        $log = array(
            'acao_evento' => 'GRAVAR_TERMO_REMESSA',
            'desc_evento' => 'ID REMESSA:' . $id_remessa . " - NOME TERMO: " . $nome_termo,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );
        
        $this->db->insert('evento', $log);
        
        // -------------- /LOG ----------------
        
        $this->db->set('id_chamado_termo', NULL);
        $this->db->set('nome_termo', $nome_termo);
        $this->db->set('tipo_termo', 'RE');
        $this->db->set('data_termo', date("Y-m-d H:i:s"));
        $this->db->insert('termo');
        return $this->db->insert_id();
    }

    public function abre_nova_remessa($divisao_remessa) {
        $this->db->set('id_usuario', 1);
        $this->db->set('data_abertura', date("Y-m-d H:i:s"));
        $this->db->set('divisao_remessa', $divisao_remessa);
        $this->db->insert('remessa_inservivel');
        $id_remessa = $this->db->insert_id();

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'CRIAR_NOVA_REMESSA',
            'desc_evento' => 'ID REMESSA:' . $id_remessa,
            'id_usuario_evento' => 1
        );

        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------

        
    }

    public function devolve_pool($id) {
        $this->db->select('*'); // Seleciona as colunas 'nome' e 'email'
        $this->db->from('remessa_inservivel'); // Especifica a tabela a ser consultada
        $this->db->order_by('id_remessa_inservivel', 'DESC'); // Ordena os registros em ordem decrescente
        $this->db->where('id_remessa_inservivel', $id); // Adiciona uma condição
        /* $this->db->where('pool_equipamentos!=', null); // Adiciona uma condição (opcional) */
        $this->db->limit(1); // Limita o resultado a 1 registro
        $query = $this->db->get(); // Executa a consulta
        $result = $query->result(); // Obtém os resultados

        if ($query->num_rows() > 0) {
            // Há registros no banco de dados
            return $result = $query->row();
        }
        
        // Não há registros no banco de dados
        return null;
    }

    public function alterar_pool($id_remessa, $pool_equipamentos) {
        $this->db->set('pool_equipamentos', $pool_equipamentos);
        $this->db->where('id_remessa_inservivel', $id_remessa);
        $this->db->update('remessa_inservivel');
    }

    public function listar_pool($id_remessa) {
        $this->db->select('remessa_inservivel.pool_equipamentos'); // Seleciona as colunas 'nome' e 'email'
        $this->db->from('remessa_inservivel'); // Especifica a tabela a ser consultada
        $this->db->where('id_remessa_inservivel', $id_remessa);

        return $this->db->get()->result_array(); // Executa a consulta
    }

    public function listar_equipamentos_remessa($id_remessa, $num_equipamento) {
        $this->db->select('rem.data_abertura, rem.data_fechamento, rem.falha_envio, rem.id_termo, usuario.nome_usuario, interacao.texto_interacao, interacao.id_chamado_interacao AS id_chamado, interacao.pool_equipamentos AS numero_equipamento, interacao.id_interacao, equipamento.descricao_equipamento, l.nome_local');
        $this->db->from('remessa_inservivel as rem');
        $this->db->join('usuario', 'rem.id_usuario = usuario.id_usuario', 'INNER');
        $this->db->join('interacao', "interacao.tipo_interacao = 'INSERVIVEL_REPARO' AND interacao.pool_equipamentos = '{$num_equipamento}'", 'INNER');
        $this->db->join('equipamento', 'equipamento.num_equipamento = interacao.pool_equipamentos', 'INNER');
        $this->db->join('chamado as c', 'interacao.id_chamado_interacao = c.id_chamado', 'INNER');
        $this->db->join('local as l', 'c.id_local_chamado = l.id_local', 'INNER');
        $this->db->where('id_remessa_inservivel', $id_remessa);
        $this->db->order_by('rem.data_abertura', 'DESC');

        return $this->db->get()->row_array();
    }

    public function lista_laudo_equipamento($num_equipamento) {
        $this->db->select('
            er.data_inicio_reparo,
            er.data_fim_reparo,
            er.id_usuario_reparo,
            e.descricao_equipamento,
            i.texto_interacao,
            c.data_chamado,
            c.id_ticket_chamado,
            c.ticket_chamado,
            c.nome_solicitante_chamado,
            c.telefone_chamado,
            l.nome_local,
            c.id_chamado
        ');
        $this->db->from('equipamento_chamado as ec');
        $this->db->join('interacao AS i', "(i.tipo_interacao = 'INSERVIVEL_REPARO' OR i.tipo_interacao = 'INSERVIVEL') AND i.pool_equipamentos = '{$num_equipamento}'", 'INNER');
        $this->db->join('equipamento_reparo as er', "er.num_equipamento_reparo = '{$num_equipamento}'", 'INNER');
        $this->db->join('equipamento AS e', "e.num_equipamento = '{$num_equipamento}'", 'INNER');
        $this->db->join('chamado as c', 'i.id_chamado_interacao = c.id_chamado', 'INNER');
        $this->db->join('local as l', 'c.id_local_chamado = l.id_local', 'INNER');
        $this->db->where('num_equipamento_chamado', $num_equipamento);

        return $this->db->get()->row();
    }

    public function listar_remessa($id_remessa) {
        $this->db->select('remessa_inservivel.*, usuario.nome_usuario, t.id_termo, t.nome_termo');
        $this->db->from('remessa_inservivel');
        $this->db->where('id_remessa_inservivel', $id_remessa);
        $this->db->join('usuario', 'remessa_inservivel.id_usuario = usuario.id_usuario', 'LEFT');
        $this->db->join('termo as t', 'remessa_inservivel.id_termo = t.id_termo', 'LEFT');

        return $this->db->get()->row();
    }

    public function listar_remessas() {
        $this->db->select('
            remessa_inservivel.*,
            DATE_FORMAT(data_abertura, "%d/%m/%Y %H:%i") AS data_abertura,
            DATE_FORMAT(data_fechamento, "%d/%m/%Y %H:%i") AS data_fechamento,
            DATE_FORMAT(data_entrega, "%d/%m/%Y") AS data_entrega,
            usuario.nome_usuario'
        );
        $this->db->from('remessa_inservivel');
        $this->db->order_by('falha_envio DESC, ISNULL(data_fechamento) DESC, ISNULL(data_entrega) DESC, data_fechamento DESC, data_entrega DESC');
        $this->db->join('usuario', 'remessa_inservivel.id_usuario = usuario.id_usuario');

        return $this->db->get()->result_array();
    }

    public function lista_remessa_aberta($divisao_remessa = DGTI) {
        $this->db->select('remessa_inservivel.*, usuario.nome_usuario');
        $this->db->from('remessa_inservivel');
        $this->db->order_by('data_abertura', 'DESC');
        $this->db->where('data_fechamento', NULL);
        $this->db->where('falha_envio', false);
        $this->db->where('divisao_remessa', $divisao_remessa);
        $this->db->join('usuario', 'remessa_inservivel.id_usuario = usuario.id_usuario');

        return $this->db->get()->row();
    }
    
    public function lista_remessas_erro() {
        $this->db->select('remessa_inservivel.*, usuario.nome_usuario');
        $this->db->from('remessa_inservivel');
        $this->db->order_by('data_abertura', 'DESC');
        $this->db->where('data_fechamento!=', NULL);
        $this->db->where('falha_envio', true);
        $this->db->join('usuario', 'remessa_inservivel.id_usuario = usuario.id_usuario');
        $query = $this->db->get();

        return $query->result_array();
    }

    public function count_remessa_inservivel() {
        return $this->db->count_all_results('remessa_inservivel');
    }

    public function fechar_remessa($id_remessa) {
        $this->db->set('data_fechamento', date("Y-m-d H:i:s"));
        $this->db->set('id_usuario', $_SESSION['id_usuario']);
        $this->db->where('id_remessa_inservivel', $id_remessa);
        $this->db->update('remessa_inservivel');

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'FECHAR_REMESSA',
            'desc_evento' => 'ID REMESSA: ' . $id_remessa ,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );
        
        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------
    }

    public function erroRemessa($id_remessa) {
        $this->db->set('falha_envio', true);
        $this->db->where('id_remessa_inservivel', $id_remessa);
        $this->db->update('remessa_inservivel');

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'REGISTRAR_ERRO_REMESSA',
            'desc_evento' => 'ID REMESSA: ' . $id_remessa ,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );
        
        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------
    }

    public function entregaRemessa($id_remessa, $nome_recebedor, $data_entrega, $id_termo) {
        $this->db->set('data_entrega', $data_entrega);
        $this->db->set('nome_recebedor', $nome_recebedor);
        $this->db->set('falha_envio', false);
        $this->db->set('id_termo', $id_termo);
        $this->db->where('id_remessa_inservivel', $id_remessa);
        $this->db->update('remessa_inservivel');

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'REGISTRAR_ENTREGA_REMESSA',
            'desc_evento' => 'ID REMESSA: ' . $id_remessa ,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );
        
        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------
    }

    public function reverterRemessa($equip, $remessa, $reparo, $chamado, $usuario){
        $mensagem = [];
        $sql = "SELECT id_remessa_inservivel, data_abertura, data_fechamento, data_entrega, 
        nome_recebedor, pool_equipamentos, falha_envio, id_usuario, id_termo
        FROM remessa_inservivel
        WHERE pool_equipamentos LIKE '%". $equip ."%' AND id_remessa_inservivel = " . $remessa . " ;";

        $result = $this->db->query($sql)->row();
        //$this->dd->dd($result);
        $pool_equips = array();
        $pool_atualizado = '';
        if($result->data_entrega === null){
            $pool = $result->pool_equipamentos;
            $pool_equips = explode('::', $pool);
            
            for($i = 0; $i< sizeof($pool_equips); $i++){
                if($pool_equips[$i] == $equip){
                    unset($pool_equips[$i]);
                }
            }
            if(sizeof($pool_equips) > 0){
                $pool_atualizado = implode("::", $pool_equips);
            }
            $this->db->trans_begin();
            $sql = "UPDATE remessa_inservivel
            SET pool_equipamentos='". $pool_atualizado ."'
            WHERE id_remessa_inservivel=" . $remessa . ";";
            $this->db->query($sql);
            $sql = "UPDATE equipamento_chamado
            SET status_equipamento_chamado='ABERTO', ultima_alteracao_equipamento_chamado=CURRENT_TIMESTAMP
            WHERE num_equipamento_chamado = '" . $equip . "';";
            $this->db->query($sql);
            $sql = "UPDATE equipamento_reparo SET status_reparo = 'CANCELADO', justificativa_reparo = 'Equipamento removido da remessa: {$result->id_remessa_inservivel}' WHERE id_reparo = {$reparo};";
            $this->db->query($sql);
            $sql = "UPDATE chamado
            SET status_chamado='ABERTO'
            WHERE id_chamado={$chamado};";
            $this->db->query($sql);
            
            $this->db->set('status_reparo_servico', false);
            $this->db->where('id_reparo', $reparo);
            $this->db->update('reparo_servico');
            
            $this->db->set('id_reparo_historico', $reparo);
            $this->db->set('id_usuario_historico', $usuario);
            $this->db->set('txt_historico','Remessa revertida');
            $this->db->set('data_historico','now()', false);
            $this->db->insert('historico_equipamento_reparo');
            if ($this->db->trans_status() === FALSE)
                {
                   $this->db->trans_rollback();
                }
            else
                {
                $this->db->trans_commit();
                }
            $mensagem['mensagem'] = 'Remessa desfeita para o equipamento: ' . $equip;
            $mensagem['status'] = 'success';
        }else{
            $mensagem['mensagem'] = 'Este equipamento já foi entregue e sua remessa não pode ser desfeita.';
            $mensagem['status'] = 'error';
        }

        if($mensagem == ''){
            $mensagem['mensagem'] = 'Houve um erro no processamento de remessas.';
            $mensagem['status'] = 'error';
        }

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'CANCELAR_REMESSA',
            'desc_evento' => 'ID REMESSA: ' . $result->id_remessa_inservivel . 'ID EQUIPAMENTO: ' . $equip,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );
        
        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------

        return $mensagem;
    }
}