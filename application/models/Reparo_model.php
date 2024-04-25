<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Reparo_model extends CI_Model {
    public function listarReparosChamado($id_chamado) {
        $this->db->select("DATE_FORMAT(data_inicio_reparo,'%d/%m/%Y %H:%i:%s') as data_inicio_reparo");
        $this->db->select("DATE_FORMAT(data_fim_reparo,'%d/%m/%Y %H:%i:%s') as data_fim_reparo");
        $this->db->select("nome_bancada, num_equipamento_reparo, id_reparo, status_reparo");
        $this->db->select("id_reparo");
        $this->db->from('equipamento_reparo');
        $this->db->join('bancada', 'equipamento_reparo.id_bancada_reparo = bancada.id_bancada');
        $this->db->where('id_chamado_reparo', $id_chamado);
        
        $this->db->order_by("CASE WHEN status_reparo LIKE 'A%' THEN 1 ELSE 2 END");
        $this->db->order_by("CASE WHEN status_reparo LIKE 'E%' THEN 1 ELSE 2 END");
        $this->db->order_by("CASE WHEN status_reparo LIKE 'G%' THEN 1 ELSE 2 END");
        $this->db->order_by("CASE WHEN status_reparo LIKE 'F%' THEN 1 ELSE 2 END");
        $this->db->order_by('data_fim_reparo', 'DESC');
        $this->db->order_by('data_inicio_reparo', 'DESC');

        return $this->db->get()->result();
    }

    public function listarReparo($id_reparo) {
        $this->db->select("
            er.id_reparo,
            er.id_chamado_reparo,
            er.status_reparo,
            er.num_equipamento_reparo,
            DATE_FORMAT(er.data_inicio_reparo, '%d%/%m/%Y %H:%i:%s') AS data_inicio_reparo,
            DATE_FORMAT(er.data_fim_reparo, '%d%/%m/%Y %H:%i:%s') AS data_fim_reparo,
            u.id_usuario as id_abertura_usuario,
            u.nome_usuario as nome_abertura_usuario,
            user.id_usuario as id_encerramento_usuario,
            user.nome_usuario as nome_encerramento_usuario
        ");
        $this->db->from('equipamento_reparo er');
        $this->db->join('usuario u', 'u.id_usuario = er.id_usuario_reparo');
        $this->db->join('usuario user', 'user.id_usuario = er.id_fechamento_usuario', 'LEFT');
        $this->db->where('er.id_reparo', $id_reparo);

        return $this->db->get()->result();
    }

    public function criarReparo($dados) {
        $result = array(
            "return" => $this->db->insert("equipamento_reparo",$dados),
            "id" => $this->db->insert_id()
        );

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'CRIAR_REPARO',
            'desc_evento' => 'ID REPARO: ' . $result["id"] ,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );

        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------

        return $result;
    }

    public function esperaReparo($id_reparo, $justificativa, $id_usuario, $id_chamado) {
        $this->db->set('status_reparo', "ESPERA");
        $this->db->set('justificativa_reparo', $justificativa);
        $this->db->where('id_reparo', $id_reparo);
        if ($this->db->update('equipamento_reparo')) {
            // Verifica se houve alguma linha afetada
            if ($this->db->affected_rows() > 0) {
                $this->db->set('txt_historico', 'Colocou o reparo em espera');
                $this->db->set('id_reparo_historico', $id_reparo);
                $this->db->set('id_usuario_historico', $id_usuario);
                $this->db->set('data_historico', 'NOW()', false);
                $this->db->insert('historico_equipamento_reparo');
    
                $this->db->select('*');
                $this->db->from('equipamento_reparo');
                $this->db->where('id_reparo', $id_reparo);
                $result = $this->db->get()->row();

                $log = array(
                    'acao_evento' => 'ESPERA_REPARO',
                    'desc_evento' => 'ID_CHAMADO: ' . $id_chamado . ' - ID_REPARO: ' . $id_reparo . ' JUSTIFICATIVA: '. $justificativa,
                    'id_usuario_evento' => $_SESSION['id_usuario']
                );
                $this->db->insert('evento', $log);

                return $result;
            }
        }

        return false;
    }

    public function removerEsperaReparo($id_reparo, $id_usuario, $id_bancada, $id_chamado) {
        $this->db->set('status_reparo', "ABERTO");
        $this->db->set('id_bancada_reparo', $id_bancada);
        $this->db->set('justificativa_reparo', NULL);
        $this->db->where('id_reparo', $id_reparo);
        if ($this->db->update('equipamento_reparo')) {
            // Verifica se houve alguma linha afetada
            if ($this->db->affected_rows() > 0) {
                $this->db->set('txt_historico', 'Removeu reparo de espera');
                $this->db->set('id_reparo_historico', $id_reparo);
                $this->db->set('id_usuario_historico', $id_usuario);
                $this->db->set('data_historico', 'NOW()', false);

                if ($this->db->insert('historico_equipamento_reparo')){
                    $log = array(
                        'acao_evento' => 'REMOVER_ESPERA_REPARO',
                        'desc_evento' => 'ID_CHAMADO: ' . $id_chamado . ' - ID_REPARO: ' . $id_reparo,
                        'id_usuario_evento' => $_SESSION['id_usuario']
                    );

                    return $this->db->insert('evento', $log);
                }
            }
        }

        return false;
    }

    public function finalizarReparo($id_reparo, $id_chamado_reparo, $id_usuario, $data_fim_reparo, $garantia = false, $ocupado_bancada) {
        $this->db->set('id_fechamento_usuario', $id_usuario);
        $this->db->set('data_fim_reparo', $data_fim_reparo);
        $this->db->set('status_reparo', "FINALIZADO");
        $this->db->where('id_reparo', $id_reparo);
        if($this->db->update('equipamento_reparo')) {
            if(!$garantia && $ocupado_bancada == true) {
                $this->db->set('entrega_chamado', true);
                $this->db->where('id_chamado', $id_chamado_reparo);
                $this->db->update('chamado');
            }

            $log = array(
                'acao_evento' => 'FINALIZAR_REPARO',
                'desc_evento' => 'ID_REPARO: ' . $id_reparo,
                'id_usuario_evento' => $_SESSION['id_usuario']
            );

            return $this->db->insert('evento', $log);
        }
        return false;
    }

    public function criarReparoServico($id_usuario, $id_reparo, $id_servico, $data_abertura) {
        $this->db->set('id_reparo', $id_reparo);
        $this->db->set('id_servico', $id_servico);
        $this->db->set('id_usuario', $id_usuario);
        $this->db->set('data_reparo_servico', $data_abertura);

        $result = array(
            "return" => $this->db->insert("reparo_servico"),
            "id_reparo_servico" => $this->db->insert_id()
        );

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'CRIAR_SERVICO_REPARO',
            'desc_evento' => 'ID REPARO_SERVICO: ' . $result["id_reparo_servico"] . " - ID SERVICO: " . $id_servico . " - ID_REPARO: " . $id_reparo,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );

        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------
        return $result;
    }

    public function buscarReparo($id_reparo) {
        $this->db->from("equipamento_reparo");
        $this->db->where('id_reparo',$id_reparo);

        return $this->db->get()->row();
    }

    public function buscarEquipamentoReparo($id_reparo) {
        $this->db->select("er.num_equipamento_reparo, ec.status_equipamento_chamado, er.id_remessa, ri.id_termo");
        $this->db->from("equipamento_reparo er");
        $this->db->join("equipamento_chamado ec","er.num_equipamento_reparo = ec.num_equipamento_chamado");
        $this->db->join("remessa_inservivel ri","ri.id_remessa_inservivel = er.id_remessa", "LEFT");
        $this->db->where('er.id_reparo',$id_reparo);
        $this->db->order_by("ultima_alteracao_equipamento_chamado","DESC");
        $this->db->limit(1);

        return $this->db->get()->row();
    }

    public function buscarReparoServicos($id_reparo) {
        $this->db->select('
            reparo_servico.id_reparo_servico,
            reparo_servico.realizado_reparo_servico,
            reparo_servico.status_reparo_servico,
            reparo_servico.data_reparo_servico,
            reparo_servico.data_encerramento_reparo_servico,
            reparo_servico.id_fechamento_usuario,
            servico.*
        ');
        $this->db->from("reparo_servico");
        $this->db->join('servico', 'reparo_servico.id_servico = servico.id_servico');
        $this->db->order_by('realizado_reparo_servico', 'DESC');
        $this->db->where('id_reparo', $id_reparo);
        $this->db->where('status_reparo_servico', true);

        return $this->db->get()->result();
    }

    public function buscarReparoServicosHistorico($id_reparo) {
        $result = $this->db->query("
            SELECT *
            FROM (
                SELECT
                1 as subquery,
                rs.id_reparo_servico,
                rs.realizado_reparo_servico,
                rs.status_reparo_servico,
                DATE_FORMAT(rs.data_reparo_servico, '%d%/%m/%Y %H:%i:%s') AS data_reparo_servico,
                DATE_FORMAT(rs.data_encerramento_reparo_servico, '%d%/%m/%Y %H:%i:%s') AS data_encerramento_reparo_servico,
                data_encerramento_reparo_servico AS data,
                u.id_usuario AS id_abertura_usuario,
                u.nome_usuario AS nome_abertura_usuario,
                user.id_usuario AS id_fechamento_usuario,
                user.nome_usuario AS nome_fechamento_usuario,
                s.id_servico,
                s.nome_servico
                FROM reparo_servico rs 
                INNER JOIN usuario u ON u.id_usuario = rs.id_usuario
                LEFT JOIN usuario user ON user.id_usuario = rs.id_fechamento_usuario
                INNER JOIN servico s ON rs.id_servico = s.id_servico 
                WHERE rs.id_reparo = {$id_reparo} AND rs.data_encerramento_reparo_servico IS NOT NULL

                UNION ALL

                SELECT 
                    2 as subquery,
                    rs.id_reparo_servico,
                    rs.realizado_reparo_servico,
                    rs.status_reparo_servico,
                    DATE_FORMAT(rs.data_reparo_servico, '%d%/%m/%Y %H:%i:%s') AS data_reparo_servico,
                    DATE_FORMAT(rs.data_encerramento_reparo_servico, '%d%/%m/%Y %H:%i:%s') AS data_encerramento_reparo_servico,
                    data_reparo_servico AS data,
                    u.id_usuario AS id_abertura_usuario,
                    u.nome_usuario AS nome_abertura_usuario,
                    user.id_usuario AS id_fechamento_usuario,
                    user.nome_usuario AS nome_fechamento_usuario,
                    s.id_servico,
                    s.nome_servico
                FROM reparo_servico rs 
                INNER JOIN usuario u ON u.id_usuario = rs.id_usuario
                LEFT JOIN usuario user ON user.id_usuario = rs.id_fechamento_usuario
                INNER JOIN servico s ON rs.id_servico = s.id_servico 
                WHERE rs.id_reparo = {$id_reparo} AND rs.id_usuario != 1

                UNION ALL

                SELECT 
                    3 as subquery,
                    h.id_historico,
                    FALSE as realizado_reparo_servico,
                    FALSE as status_reparo_servico,
                    DATE_FORMAT(h.data_historico, '%d%/%m/%Y %H:%i:%s') AS data_reparo_servico,
                    DATE_FORMAT(h.data_historico, '%d%/%m/%Y %H:%i:%s') AS data_encerramento_reparo_servico,
                    h.data_historico AS data,
                    u.id_usuario as id_abertura_usuario,
                    u.nome_usuario AS nome_abertura_usuario,
                    u.id_usuario AS id_fechamento_usuario,
                    u.nome_usuario AS nome_fechamento_usuario,
                    NULL AS id_servico,
                    h.txt_historico AS nome_servico
                FROM historico_equipamento_reparo AS h
                INNER JOIN usuario u ON u.id_usuario = h.id_usuario_historico
                WHERE h.id_reparo_historico = {$id_reparo}


            )t
            ORDER BY t.data
        ");
        /* $this->db->select('sub.*');
        $this->db->from("
            (
                SELECT 
                    1 as subquery,
                    rs.id_reparo_servico,
                    rs.realizado_reparo_servico,
                    rs.status_reparo_servico,
                    DATE_FORMAT(rs.data_reparo_servico, '%d/%m/%Y %H:%i:%s') AS data_reparo_servico,
                    DATE_FORMAT(rs.data_encerramento_reparo_servico, '%d/%m/%Y %H:%i:%s') AS data_encerramento_reparo_servico,
                    u.id_usuario AS id_abertura_usuario,
                    u.nome_usuario AS nome_abertura_usuario,
                    user.id_usuario AS id_fechamento_usuario,
                    user.nome_usuario AS nome_fechamento_usuario,
                    s.id_servico,
                    s.nome_servico
                FROM reparo_servico rs
                INNER JOIN usuario u ON u.id_usuario = rs.id_usuario
                LEFT JOIN usuario user ON user.id_usuario = rs.id_fechamento_usuario
                INNER JOIN servico s ON rs.id_servico = s.id_servico
                WHERE rs.id_reparo = {$id_reparo} AND rs.data_encerramento_reparo_servico IS NOT NULL

                UNION ALL

                SELECT 
                    2 as subquery,
                    rs.id_reparo_servico,
                    rs.realizado_reparo_servico,
                    rs.status_reparo_servico,
                    DATE_FORMAT(rs.data_reparo_servico, '%d/%m/%Y %H:%i:%s') AS data_reparo_servico,
                    DATE_FORMAT(rs.data_encerramento_reparo_servico, '%d/%m/%Y %H:%i:%s') AS data_encerramento_reparo_servico,
                    u.id_usuario AS id_abertura_usuario,
                    u.nome_usuario AS nome_abertura_usuario,
                    user.id_usuario AS id_fechamento_usuario,
                    user.nome_usuario AS nome_fechamento_usuario,
                    s.id_servico,
                    s.nome_servico
                FROM reparo_servico rs
                INNER JOIN usuario u ON u.id_usuario = rs.id_usuario
                LEFT JOIN usuario user ON user.id_usuario = rs.id_fechamento_usuario
                INNER JOIN servico s ON rs.id_servico = s.id_servico
                WHERE rs.id_reparo = {$id_reparo}) as sub
            )"
        );
        $this->db->order_by('sub.subquery ASC, sub.data_encerramento_reparo_servico DESC, sub.data_reparo_servico DESC'); */

        /* $query = $this->db->get();
        $resultados = $query->result(); */

        
        
        /* $this->db->select("
            rs.id_reparo_servico,
            rs.realizado_reparo_servico,
            rs.status_0reparo_servico,
            DATE_FORMAT(rs.data_reparo_servico, '%d%/%m/%Y %H:%i:%s') AS data_reparo_servico,
            DATE_FORMAT(rs.data_encerramento_reparo_servico, '%d%/%m/%Y %H:%i:%s') AS data_encerramento_reparo_servico,
            u.id_usuario AS id_abertura_usuario,
            u.nome_usuario AS nome_abertura_usuario,
            user.id_usuario AS id_fechamento_usuario,
            user.nome_usuario AS nome_fechamento_usuario,
            s.id_servico,
            s.nome_servico
        ");
        $this->db->from("reparo_servico rs");
        $this->db->join('usuario u', 'u.id_usuario = rs.id_usuario');
        $this->db->join('usuario user', 'user.id_usuario = rs.id_fechamento_usuario', 'LEFT');
        $this->db->join('servico s', 'rs.id_servico = s.id_servico');
        $this->db->where('data_encerramento_reparo_servico IS NULL AND id_reparo =', $id_reparo); */



        //$this->db->order_by('data_encerramento_reparo_servico ASC, ISNULL(data_encerramento_reparo_servico), data_reparo_servico');
        // $this->db->order_by('status_reparo_servico');
        //$this->db->order_by('data_encerramento_reparo_servico ASC, data_reparo_servico ASC');
        /* $this->db->where('id_reparo', $id_reparo); */

        return $result->result();
    }

    public function buscarServicos($id_reparo, $id_fila) {
        $result = $this->db->query("
            SELECT s.*
            FROM servico s 
            WHERE NOT EXISTS
            (
                SELECT 1
                FROM reparo_servico rs
                INNER JOIN equipamento_reparo er ON rs.id_reparo = er.id_reparo
                WHERE rs.id_reparo  = '{$id_reparo}' and rs.realizado_reparo_servico = 0 AND rs.status_reparo_servico = 1
                AND rs.id_servico = s.id_servico
            )
            AND s.id_fila_servico = {$id_fila}
            ORDER BY s.nome_servico
        ");

        return $result->result();
    }

    public function realizarServico($id_usuario, $id_reparo_servico, $data_encerramento) {
       

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'FINALIZAR_SERVICO_REPARO',
            'desc_evento' => 'ID REPARO_SERVICO: ' . $id_reparo_servico,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );

        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------

        $this->db->set('realizado_reparo_servico', true);
        $this->db->set('id_fechamento_usuario', $id_usuario);
        $this->db->set('data_encerramento_reparo_servico', $data_encerramento);
        $this->db->where('id_reparo_servico', $id_reparo_servico);

        return $this->db->update('reparo_servico');
    }

    public function alterarStatusReparo($id_reparo, $status_reparo, $justificativa_reparo = null) {
        

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'ALTERAR_STATUS_REPARO',
            'desc_evento' => 'ID REPARO: ' . $id_reparo . " - NOVO STATUS: " . $status_reparo,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );

        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------

        $this->db->set('status_reparo', $status_reparo);
        $this->db->set('justificativa_reparo', $justificativa_reparo);
        $this->db->where('id_reparo', $id_reparo);

        return $this->db->update('equipamento_reparo');
    }

    public function cancelarServico($id_reparo_servico, $id_usuario, $data_encerramento) {
        

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'CANCELAR_SERVICO_REPARO',
            'desc_evento' => 'ID REPARO_SERVICO: ' . $id_reparo_servico,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );

        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------

        $this->db->set('status_reparo_servico', false);
        $this->db->set('id_fechamento_usuario', $id_usuario);
        $this->db->set('data_encerramento_reparo_servico', $data_encerramento);
        $this->db->where('id_reparo_servico', $id_reparo_servico);

        return $this->db->update('reparo_servico');
    }

    public function cancelarReparo($id_reparo, $id_usuario, $justificativa) {
        $this->db->from('equipamento_reparo');
        $this->db->where('id_reparo', $id_reparo);
        $id_chamado = $this->db->get()->row()->id_chamado_reparo;

        $this->db->set('status_reparo', "CANCELADO");
        $this->db->set('id_fechamento_usuario', $id_usuario);
        $this->db->set('justificativa_reparo', $justificativa);
        $this->db->set('data_fim_reparo', date("Y-m-d H:i:s"));
        $this->db->where('id_reparo', $id_reparo);
        if ($this->db->update('equipamento_reparo')) {
            $log = array(
                'acao_evento' => 'CANCELAR_REPARO',
                'desc_evento' => 'ID_CHAMADO: ' . $id_chamado . ' - ID_REPARO: ' . $id_reparo,
                'id_usuario_evento' => $_SESSION['id_usuario']
            );
            return $this->db->insert('evento', $log);
        }

        return false;
    }

    public function desfazerReparoServico($id_reparo_servico, $id_reparo) {
        $this->db->set('id_fechamento_usuario', NULL);
        $this->db->set('realizado_reparo_servico', false);
        $this->db->set('status_reparo_servico', true);
        $this->db->set('data_encerramento_reparo_servico', date("Y-m-d H:i:s"));
        $this->db->where('id_reparo_servico', $id_reparo_servico);
        if ($this->db->update('reparo_servico')) {
            $log = array(
                'acao_evento' => 'DESFAZER_REPARO_SERVICO',
                'desc_evento' => 'ID_REPARO_SERVICO: ' . $id_reparo_servico,
                'id_usuario_evento' => $_SESSION['id_usuario']
            );

            $this->db->set('txt_historico', 'DESFEZ o reparo do serviço');
            $this->db->set('id_reparo_historico', $id_reparo);
            $this->db->set('id_usuario_historico', $_SESSION['id_usuario']);
            $this->db->set('data_historico', 'NOW()', false);

            $this->db->insert('historico_equipamento_reparo');
            return $this->db->insert('evento', $log);
        }

        return false;
    }

    public function atualizarReparo($id_reparo, $status, $id_usuario, $id_remessa = NULL) {
        

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'ATUALIZAR_REPARO',
            'desc_evento' => 'ID REPARO:' . $id_reparo . " - NOVO STATUS: " . $status . " - ID REMESSA: " . $id_remessa,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );

        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------

        $this->db->set('status_reparo', $status);
        $this->db->set('data_fim_reparo', date("Y-m-d H:i:s"));
        $this->db->set('id_fechamento_usuario', $id_usuario);
        $this->db->set('id_remessa', $id_remessa);
        $this->db->where('id_reparo', $id_reparo);

        return $this->db->update('equipamento_reparo');
    }

    public function cancelarEntrega($id_reparo, $id_usuario, $justificativa, $equip, $id_chamado){

        $log = null;

        $log = $this->cancelarReparo($id_reparo, $id_usuario, $justificativa);
        $mensagem = [];
        if ($log != null){
            $this->db->trans_begin();
            $this->db->set('status_equipamento_chamado', 'ABERTO');
            $this->db->set('ultima_alteracao_equipamento_chamado', 'now()');
            $this->db->where('num_equipamento_chamado', $equip);
            $this->db->where('id_chamado_equipamento', $id_chamado);
            $this->db->update('equipamento_chamado');

            $this->db->set('entrega_chamado', false);
            $this->db->where('id_chamado', $id_chamado);
            $this->db->update('chamado');

            $this->db->set('status_reparo_servico', false);
            $this->db->where('id_reparo', $id_reparo);
            $this->db->update('reparo_servico');
            
            $this->db->set('id_reparo_historico', $id_reparo);
            $this->db->set('id_usuario_historico', $id_usuario);
            $this->db->set('txt_historico','Entrega CANCELADA');
            $this->db->set('data_historico','now()', false);
            $this->db->insert('historico_equipamento_reparo');
            if ($this->db->trans_status() === FALSE)
                {
                   $this->db->trans_rollback();
                   $mensagem['mensagem'] = 'Erro ao cancelar a entrega.';
                   $mensagem['status'] = 'error';
                }
            else
                {
                    $this->db->trans_commit();
                    $mensagem['mensagem'] = 'Entrega cancelada com sucesso!';
                    $mensagem['status'] = 'success';
                }
        }

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'ATUALIZAR_REPARO',
            'desc_evento' => 'ID REPARO:' . $id_reparo . " - NOVO STATUS: ENTREGA CANCELADA",
            'id_usuario_evento' => $_SESSION['id_usuario']
        );

        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------

        return $mensagem;
    }

    public function excluirReparo($reparo){
        $result = null;
        $this->db->trans_begin();
        $this->db->where('id_usuario', 1);
        $this->db->where('id_fechamento_usuario', null);
        $this->db->where('status_reparo_servico', true);
        $this->db->where('realizado_reparo_servico', false);
        $this->db->where('id_reparo', $reparo);
        $result = $this->db->delete('reparo_servico');

        if ($this->db->trans_status() === FALSE)
            {
                $this->db->trans_rollback();
            }
        else
            {
                $this->db->trans_commit();
            }

        return $result;
    }
    
}