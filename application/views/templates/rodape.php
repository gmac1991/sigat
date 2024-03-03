

<?php 

$versao = $this->config->item('versao');
$amb = ENVIRONMENT;

$grupo = $this->consultas_model->buscaGrupo($autorizacao_usuario);


echo '<div class="navbar bg-dark text-light fixed-bottom">' . 
      '<div class="text-right w-100 h-auto"><small>';

if (isset($_SESSION['id_usuario'])) { 

  echo '<p class="d-inline">Autorização: ';

  echo '<span class="badge badge-' . $grupo->cor_grupo .'">' . $grupo->nome_grupo . '</span>';
  
  echo " | Ambiente: " . $amb;
  echo " | Versão: " . $versao . " <a target=\"_blank\" class=\"badge badge-secondary\" href=\"" . $this->config->item('changelog_url') . "\">notas</a></p> | SEAD/CGTI</small></div></div>";
  

  echo "<script type=\"text/javascript\">const g_id_usuario = " . $id_usuario . "</script>";
  echo "<script type=\"text/javascript\">const g_auto_usuario = " . $autorizacao_usuario . "</script>";
  echo "<script type=\"text/javascript\">const g_auto_usuario_enc = " . $encerramento_usuario . "</script>";
  echo "<script type=\"text/javascript\">const g_fila_painel = " . $fila_usuario . "</script>";
}
 
if (isset($chamado)) { 
 
  echo "<script type=\"text/javascript\">const g_id_chamado = " . $chamado->id_chamado . "</script>";
  echo "<script type=\"text/javascript\">const g_id_ticket_chamado = " . $chamado->id_ticket_chamado . "</script>";
  echo "<script type=\"text/javascript\">const g_fila_chamado = " . $chamado->id_fila . "</script>"; 
}

?>
  <script type="text/javascript">const base_url = '<?= base_url() ?>';</script>
 
  <script src="<?= base_url("js/jquery-3.4.1.min.js") ?>"></script>
  <script src="<?= base_url("js/bootstrap.bundle.min.js") ?>"></script>
  <script src="<?= base_url("datatables/datatables.min.js")?>"></script>
  <script src="<?= base_url("js/jquery.auto-complete.min.js") ?>"></script>
  <script src="<?= base_url("js/jquery.validate.min.js") ?>"></script>
  <script src="<?= base_url("summernote/summernote-bs4.min.js") ?>"></script>
  <script src="<?= base_url("summernote/lang/summernote-pt-BR.js") ?>"></script>
  <script src="<?= base_url("js/jsgrid.min.js"); ?>"></script>
  <script src="<?= base_url("fa/js/all.min.js")?>"></script>
  <script src="<?= base_url("js/moment.min.js")?>"></script>
  <script src="<?= base_url("js/sweetalert2.js")?>"></script>
  

  <script src="<?= $amb == 'development' ? base_url("js/principal.js") : base_url("js/principal.min.js") ."?v=". $versao ?>"></script>

  <?php
  if (isset($chamado)) { 
 
   
    echo "<script type=\"text/javascript\">carregaChamado(g_id_chamado)</script>";
    echo "<script type=\"text/javascript\">atualizaInteracoes(g_id_chamado)</script>"; 
  }
  
  if (isset($triagem)) { 

    $id_fila_sigat = $this->config->item('conversao_id_filas')[$triagem["t_info"]->queue_id];
    $nome_fila_sigat = $this->config->item('id_filas')[$id_fila_sigat];
   
    echo "<script type=\"text/javascript\">const g_id_ticket = " . $triagem["t_info"]->id . "</script>";
    echo "<script type=\"text/javascript\">const g_num_ticket = '" . $triagem["t_info"]->tn . "'</script>";
    echo "<script type=\"text/javascript\">const nome_fila_sigat = '" . $nome_fila_sigat . "'</script>";
    echo "<script type=\"text/javascript\">const id_fila_sigat = '" . $id_fila_sigat . "'</script>";
    
    //echo "<script type=\"text/javascript\">const g_email_triagem = '" . $triagem->email_triagem . "'</script>";
    echo "<script type=\"text/javascript\">carregaTriagem(" . $triagem["t_info"]->id . ")</script>";
  }

  ?>
  
  


  </body>
</html>
