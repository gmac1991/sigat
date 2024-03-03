<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Equipamento_model extends CI_Model {


    public function buscaDescEquipamento($num_equip) {

        $busca = $this->db->query("select * from equipamento where num_equipamento = '" . $num_equip . "'");

        if($busca->num_rows() == 1) {

            return $busca->row()->descricao_equipamento;
        }

        else
            return NULL;

    }

    public function buscaStatusEquipamento($num_equip, $is_array) {
        $this->db->select('
            id_chamado,
            ticket_chamado,
            status_equipamento_chamado
        ');
        $this->db->from('chamado, equipamento_chamado');
        $this->db->where("equipamento_chamado.num_equipamento_chamado = '{$num_equip}' AND equipamento_chamado.id_chamado_equipamento = chamado.id_chamado
        ORDER BY chamado.id_chamado DESC");
        /* $sql = "SELECT id_chamado, ticket_chamado, status_equipamento_chamado
        FROM chamado,equipamento_chamado
        WHERE equipamento_chamado.num_equipamento_chamado = '". $num_equip.
        "' AND equipamento_chamado.id_chamado_equipamento = chamado.id_chamado
        ORDER BY chamado.id_chamado DESC"; //LIMIT 1"; */

        if ($is_array == 'true') {
            $result = $this->db->get()->result_array();
        } else {
            $this->db->limit(1);
            $result = $this->db->get()->row_array();
        }

        if(!empty($result)) {
            return $result;
        } else return NULL;

    }

    public function buscaChamadosEquipamento($num_equip){
        $sql = "SELECT equipamento_chamado.status_equipamento_chamado, chamado.id_chamado, chamado.resumo_chamado, chamado.data_chamado, chamado.ticket_chamado, chamado.id_ticket_chamado  from equipamento_chamado INNER JOIN chamado ON equipamento_chamado.id_chamado_equipamento = chamado.id_chamado 
        WHERE equipamento_chamado.num_equipamento_chamado = '". $num_equip ."' ORDER BY chamado.data_chamado desc LIMIT 5; ";

        $busca = $this->db->query($sql);

        if($busca->num_rows() >= 1) {

            return $busca->result_array();

        }

        else
            return NULL;
    }

    public function insereEquipamento($num_equip,$desc_equip) {

        $insercao = $this->db->query("insert into equipamento values(".$num_equip.",'".$desc_equip."')");

        if($insercao) {
            // ------------ LOG -------------------

            $log = array(
                'acao_evento' => 'ADICIONAR_EQUIP',
                'desc_evento' => 'ID: ' . $num_equip . ' - DESC: ' . $desc_equip,
                'id_usuario_evento' => $_SESSION['id_usuario']
            );
            
            $this->db->insert('evento', $log);

            // -------------- /LOG ----------------

            return TRUE;
        }

        else
            return NULL;

    }

    public function buscarInfoEquipamento($num_equip){
        $sql = "SELECT TOP 1 CPU, Volumes, Free_Space, Memory, Host_Name, OS_Version, User_Name, IP_2, Time_Stamp FROM BGInfoTable WHERE Host_Name  = 'PMS-" . $num_equip ."' ORDER BY Time_Stamp DESC;";

        $db_BGinfo = $this->load->database('BG_info', TRUE);

        $busca = $db_BGinfo->query($sql);

        if($busca->num_rows() >= 1) {

            return $busca->result_array();

        }

        else
            return NULL;
    }

    public function buscarUsuariosEquipamento($num_equip){
        $sql = "SELECT BGI.[User_Name], MAX(BGI.Time_Stamp) AS Time_Stamp FROM BGInfoTable BGI
        WHERE [Host_Name] = 'PMS-" . $num_equip ."' GROUP BY [User_Name]
        ORDER BY Time_Stamp DESC";

        $db_BGinfo = $this->load->database('BG_info', TRUE);

        $busca = $db_BGinfo->query($sql);

        if($busca->num_rows() >= 1) {

            return $busca->result_array();

        }

        else
            return NULL;
    }

    public function alterarStatusEquipamentoChamado($num_equipamento,$id_chamado, $novo_status, $status_atual = null) {

        $out = FALSE;

        $this->db->select('status_equipamento_chamado');
        $this->db->from('equipamento_chamado');
        $this->db->where('id_chamado_equipamento', $id_chamado);
        $this->db->where('num_equipamento_chamado', $num_equipamento);
        if ($status_atual != null) {
            $this->db->where('status_equipamento_chamado', $status_atual);
        }
        $status_ant = $this->db->get()->row()->status_equipamento_chamado;
        // $this->dd->dd($status_ant);

        $this->db->set('status_equipamento_chamado', $novo_status);
        $this->db->set('status_equipamento_chamado_ant', $status_ant);
        $this->db->where('id_chamado_equipamento', $id_chamado);
        $this->db->where('num_equipamento_chamado', $num_equipamento);
        if ($status_atual) {
            $this->db->where('status_equipamento_chamado', $status_atual);
        }

        if ($this->db->update('equipamento_chamado')) {
            $out = TRUE;
            // ------------ LOG -------------------

            $log = array(
                'acao_evento' => 'ALTERAR_STATUS_EQUIP',
                'desc_evento' => 'NUM: ' . $num_equipamento . ' - NOVO STATUS: ' . $novo_status,
                'id_usuario_evento' => $_SESSION['id_usuario']
            );
            
            $this->db->insert('evento', $log);

            // -------------- /LOG ----------------

        }
        
        
        return $out;

    }

    public function alterarTag($num_equipamento, $tag_equipamento, $id_reparo_servico) {
        $out = false;
        $this->db->set('tag_equipamento', $tag_equipamento);
        $this->db->set('data_alteracao_equipamento', date("Y-m-d H:i:s"));
        $this->db->where('num_equipamento', $num_equipamento);

        if ($this->db->update('equipamento')) {
            $out = true;

            // ------------ LOG -------------------
            $log = array(
                'acao_evento' => 'ALTERAR_TAG_EQUIP',
                'desc_evento' => 'NUM: ' . $num_equipamento . ' - NOVA TAG: ' . $tag_equipamento . ' - ID_REPARO_SERVICO: '. $id_reparo_servico,
                'id_usuario_evento' => $_SESSION['id_usuario']
            );

            $this->db->insert('evento', $log);
            // -------------- /LOG ----------------
        }

        return $out;
    }

    public function buscarServicosEquipamento($id_reparo) {

        $this->db->select('s.nome_servico, se.status_servico_equipamento');
        $this->db->from('servico_equipamento se');
        $this->db->join('servico s','se.id_servico_equipamento = s.id_servico');
        $this->db->where('s.id_fila_servico', 3);
        $this->db->where('s.status_servico', 1);
        $this->db->where('se.id_reparo_equipamento',$id_reparo);
        return $this->db->get()->result();

       

    }
}