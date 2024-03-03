<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Reparo extends CI_Controller {

  function __construct() {
    parent::__construct();
    $this->load->model("reparo_model");
    $this->load->model("bancada_model");
    $this->load->model("equipamento_model");
    $this->load->model("garantia_model");
  }

  public function listar_reparos() {

    if (isset($_SESSION['id_usuario'])) {
      $id_chamado = $this->input->post("id_chamado");
      $result = $this->reparo_model->listarReparosChamado($id_chamado);

      header("Content-Type: application/json");

      echo json_encode($result);
    }
    else {
        header('HTTP/1.0 403 Forbidden');
    }
  
  }

  public function lista_historico() {
    if (isset($_SESSION['id_usuario'])) {
      $id_reparo = $this->input->post("id_reparo");
      $result = array(
        "reparo" => $this->reparo_model->listarReparo($id_reparo),
        "servicos" => $this->reparo_model->buscarReparoServicosHistorico($id_reparo)
      );

      header("Content-Type: application/json");
      echo json_encode($result);
    } else {
      header('HTTP/1.0 403 Forbidden');
    }
  }

  public function buscar_garantia() {
    if (isset($_SESSION['id_usuario'])) {
      $id_reparo = $this->input->post("id_reparo");
      $garantia = $this->garantia_model->buscaGarantia($id_reparo);

      if ($garantia) {
        $result = $garantia;
      }

      header("Content-Type: application/json");
      echo json_encode($result);
    }
    else {
      header('HTTP/1.0 403 Forbidden');
    }
  }

  public function buscar_reparo() {

    if (isset($_SESSION['id_usuario'])) {
      $id_reparo = $this->input->post("id_reparo");

      $result = array();
      $result['reparo'] = $this->reparo_model->buscarReparo($id_reparo);
      $result['desc_equip'] = $this->equipamento_model->buscaDescEquipamento($result['reparo']->num_equipamento_reparo);

      header("Content-Type: application/json");
      echo json_encode($result);
    }
    else {
      header('HTTP/1.0 403 Forbidden');
    }
  
  }

  public function buscar_equipamento_reparo() {
    if (isset($_SESSION['id_usuario'])) {
      $id_reparo = $this->input->post('id_reparo');
      $result = $this->reparo_model->buscarEquipamentoReparo($id_reparo);

      header("Content-Type: application/json");
      echo json_encode($result);
    }
  }

  public function buscar_servicos() {
    if (isset($_SESSION['id_usuario'])) {
      $result = array();
      $id_reparo = $this->input->post('id_reparo');
      $result = $this->reparo_model->buscarServicos($id_reparo);

      header("Content-Type: application/json");
      echo json_encode($result);
    }
    else {
      header('HTTP/1.0 403 Forbidden');
    }
  }

  public function buscar_reparo_servico() {
    if (isset($_SESSION['id_usuario'])) {
      $id_reparo = $this->input->post('id_reparo');
      $result = $this->reparo_model->buscarReparoServicos($id_reparo);

      header("Content-Type: application/json");
      echo json_encode($result);
    }
  }

  public function adicionar_reparo_chamado() {
    if (isset($_SESSION['id_usuario'])) {
      $id_usuario = $_SESSION['id_usuario'];
      $id_reparo = $this->input->post('id_reparo');
      $id_servico = $this->input->post('id_servico');
      $data_reparo_servico = date("Y-m-d H:i:s");

      $result = $this->reparo_model->criarReparoServico($id_usuario, $id_reparo, $id_servico, $data_reparo_servico);
      if($result['return']) {
        header("Content-Type: application/json");
        echo json_encode($result);
      };
    }
  }

  public function realizar_servico() {
    if (isset($_SESSION['id_usuario'])) {
      $id_usuario = $_SESSION['id_usuario'];
      $id_reparo_servico = $this->input->post('id_reparo_servico');
      $data_encerramento = date("Y-m-d H:i:s");
      //$id_servico = $this->input->post('id_servico');

      if($this->reparo_model->realizarServico($id_usuario, $id_reparo_servico, $data_encerramento)) {
        header("Content-Type: application/json");
        echo json_encode(array(
          "mensagem" => "Serviço adicionado com sucesso"
        ));
      };
    }
  }

  public function acionar_garantia() {
    if (isset($_SESSION['id_usuario'])) {
      $id_reparo = $this->input->post('id_reparo');
      $ticket_garantia = $this->input->post('ticket_garantia');
      $justificativa_reparo = $this->input->post('justificativa_reparo');
      $reparo = $this->reparo_model->buscarReparo($id_reparo);

      if (
        $this->reparo_model->alterarStatusReparo($id_reparo, "GARANTIA", $justificativa_reparo) &&
        $this->garantia_model->criarGarantia($id_reparo, $_SESSION['id_usuario'], $ticket_garantia)
      ) {
        $atualiza_equip = $this->equipamento_model->alterarStatusEquipamentoChamado(
          $reparo->num_equipamento_reparo,
          $reparo->id_chamado_reparo, 
          'GARANTIA',
          'REPARO'
        );
        $libera_bancada = $this->bancada_model->atualizarBancada($reparo->id_bancada_reparo, 0);

        header("Content-Type: application/json");
        echo json_encode(array(
          "mensagem" => "Reparo em garantia"
        ));
      } else {
        echo "Erro com sucesso!";
      }
    }
  }

  public function registrar_laudo() {
    $this->load->library('form_validation');
    $this->form_validation->set_rules('id_reparo', 'laudoGarantia', 'required');
    if ($this->form_validation->run() == false) {
      http_response_code(400);
      header('Content-Type: application/json');
      echo json_encode(array(
          "erro" => true,
          "mensagem" => "Erro ao fazer upload do arquivo: O arquivo selecionado é muito grande."
      ));
      return;
    }
    $id_reparo = $this->input->post('id_reparo');
    $id_garantia = $this->garantia_model->buscaGarantia($id_reparo)->id_garantia;
    $id_usuario = $_SESSION['id_usuario'];

    $config = array();
    $config['upload_path']          = $this->config->item('caminho_termos');
    $config['overwrite']            = true;
    $config['allowed_types']        = 'pdf'; //tipos de arquivos permitidos
    $config['max_size']             = $this->config->item('limit_size_file');

    // ----- UPLOAD TERMO LAUDO GARANTIA -----
    $config['file_name'] = 'Laudo_Garantia_' . date('d-m-Y') . '_' . $id_garantia . '.pdf';
    $this->load->library('upload', $config);

    if (! $this->upload->do_upload('laudoGarantia')) {
      $erro = true;
      $dados['erros_upload'] = array('error' => $this->upload->display_errors());

      foreach ($dados['erros_upload'] as $erro) {
        echo $erro;
      }

      http_response_code(500);
      header('Content-Type: application/json');
      echo json_encode(array(
          "erro" => true,
          "mensagem" => "Erro ao fazer upload do arquivo"
      ));
      return;
    } else {
      $nome_laudo =  $config['file_name'];
    }
    unset($this->upload);

    $reparo = $this->reparo_model->buscarReparo($id_reparo);
    $this->garantia_model->salvarLaudoGarantia($id_garantia, $nome_laudo);
    $atualiza_equip = $this->equipamento_model->alterarStatusEquipamentoChamado(
      $reparo->num_equipamento_reparo,
      $reparo->id_chamado_reparo, 
      'ABERTO',
      'GARANTIA'
    );
    $this->reparo_model->atualizarReparo($id_reparo, 'FINALIZADO', $id_usuario);
    $result_reparo = $this->reparo_model->finalizarReparo($reparo->id_reparo, $reparo->id_chamado_reparo, $_SESSION['id_usuario'], date("Y-m-d H:i:s"));
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode(array(
      "erro" => false,
      "mensagem" => "Upload realizado com sucesso!"
    ));
  }

  public function cancelar_servico() {
    if (isset($_SESSION['id_usuario'])) {
      $id_usuario = $_SESSION['id_usuario'];
      $id_reparo_servico = $this->input->post('id_reparo_servico');
      $data_encerramento = date("Y-m-d H:i:s");

      if($this->reparo_model->cancelarServico($id_reparo_servico, $id_usuario, $data_encerramento)) {
        header("Content-Type: application/json");
        echo json_encode(array(
          "mensagem" => "Serviço adicionado com sucesso"
        ));
      };
    }
  }

  public function cancelar_reparo() {

    if (isset($_SESSION['id_usuario'])) {

      $result = FALSE;

      $id_reparo = $this->input->post("id_reparo");
      $justificativa = $this->input->post("texto_justificativa");
      $tipo_servico = 'REPARO';
      
      if ($this->input->post("tipo_servico")) {
        $tipo_servico = $this->input->post("tipo_servico");
      }

      $reparo = $this->reparo_model->buscarReparo($id_reparo);

      if ($this->bancada_model->buscaBancada($reparo->id_bancada_reparo)->status_bancada == 1) {
        $libera_bancada = $this->bancada_model->atualizarBancada($reparo->id_bancada_reparo,0);

        $cancelamento = $this->reparo_model->cancelarReparo($id_reparo, $_SESSION['id_usuario'],$justificativa);

        $atualiza_equip = $this->equipamento_model->alterarStatusEquipamentoChamado(
          $reparo->num_equipamento_reparo,
          $reparo->id_chamado_reparo, 
          'ABERTO',
          $tipo_servico
        );

        $result = $libera_bancada && $cancelamento && $atualiza_equip;
      }

      header("Content-Type: application/json");
      echo json_encode($result);
    }
    else {
      header('HTTP/1.0 403 Forbidden');
    }
  
  }

  public function criar_reparo() {

    if (isset($_SESSION['id_usuario'])) {
      $dados = array (
          "id_chamado_reparo" => $this->input->post("id_chamado"),
          "id_bancada_reparo" => $this->input->post("id_bancada"),
          "id_usuario_reparo" => $_SESSION['id_usuario'],
          "num_equipamento_reparo" => $this->input->post("num_equipamento"),
          "data_inicio_reparo" => date("Y-m-d H:i:s")
      );
      $reparos_padroes = $this->config->item('id_servicos_equipamento_padrao');
      $result = NULL;

      if ($this->bancada_model->buscaBancada($this->input->post("id_bancada"))->ocupado_bancada == 0) {
        $atualizaEquipamento = $this->equipamento_model->alterarStatusEquipamentoChamado(
          $dados['num_equipamento_reparo'],
          $dados['id_chamado_reparo'],
          "REPARO"
        );
        
        $reparo = $this->reparo_model->criarReparo($dados);
        $atualizaBancada = $this->bancada_model->atualizarBancada($this->input->post("id_bancada"),1);

        if($reparo['return'] && $atualizaBancada && $atualizaEquipamento) {
          $result = array (
            "mensagem" => "Reparo iniciado."
          );
        }

        foreach ($reparos_padroes as $id_reparo) {
          $this->reparo_model->criarReparoServico(1, $reparo['id'], $id_reparo, $dados['data_inicio_reparo']);
        }
      }

      header("Content-Type: application/json");
      echo json_encode($result);        
    }
    else {
        header('HTTP/1.0 403 Forbidden');
    }
  }

  public function finaliza_reparo() {
    if (isset($_SESSION['id_usuario'])) {
      $dados = array (
        "id_reparo" => $this->input->post("id_reparo"),
        // "id_chamado_reparo" => $this->input->post("id_chamado"),
        "data_fim_reparo" => date("Y-m-d H:i:s"),
      );
      $reparo = $this->reparo_model->buscarReparo($dados['id_reparo']);
      $result = NULL;

      if ($this->bancada_model->buscaBancada($reparo->id_bancada_reparo)->ocupado_bancada == true) {
        $atualizaEquipamento = $this->equipamento_model->alterarStatusEquipamentoChamado(
          $reparo->num_equipamento_reparo,
          $reparo->id_chamado_reparo,
          "ENTREGA",
          "REPARO"
        );

        $result_reparo = $this->reparo_model->finalizarReparo($reparo->id_reparo, $reparo->id_chamado_reparo, $_SESSION['id_usuario'], $dados['data_fim_reparo']);
        $atualizaBancada = $this->bancada_model->atualizarBancada($reparo->id_bancada_reparo, 0);

        if($result_reparo && $atualizaBancada && $atualizaEquipamento) {
          $result = array (
            "mensagem" => "Reparo finalizado com sucesso."
          );

          header("Content-Type: application/json");
          echo json_encode($result);
        }
      }
    }
    else {
        header('HTTP/1.0 403 Forbidden');
    }
  }
}