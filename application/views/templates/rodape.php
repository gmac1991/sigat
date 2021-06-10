

<?php 

$versao = $this->config->item('versao');
$amb = ENVIRONMENT;

echo '<div class="navbar bg-dark text-light fixed-bottom">' .
      '<div class="text-right w-100 h-auto"><small>';

if (isset($_SESSION['id_usuario'])) { 

  echo '<p class="d-inline">Perfil: ';

  if ($autorizacao_usuario == 2) {

    echo '<span class="badge badge-primary">Técnico</span>';
  }
  elseif ($autorizacao_usuario == 3) {

    echo '<span class="badge badge-success">Administrador</span>';
  }

  elseif ($autorizacao_usuario == 4) {
    echo '<span class="badge badge-danger">Master</span>';
  
  }
  echo " | Ambiente: " . $amb;
  echo " | Versão: " . $versao . "</p></small></div></div>";
  

  echo "<script type=\"text/javascript\">const g_id_usuario = " . $id_usuario . "</script>";
  echo "<script type=\"text/javascript\">const g_auto_usuario = " . $autorizacao_usuario . "</script>";
  echo "<script type=\"text/javascript\">const g_fila_usuario = " . $fila_usuario . "</script>";
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
  <script type="text/javascript" src="<?= base_url("js/jsgrid.min.js"); ?>"></script>
  <script src="<?= base_url("fa/js/all.min.js")?>"></script>
  <script src="<?= base_url("js/moment.min.js")?>"></script>
  <script src="<?= base_url("js/principal.js")?>"></script>
  
  
  
<?php 
if (isset($filas) && !isset($chamado)) { 
  echo "<script type=\"text/javascript\">precisaPatrimonio(" .  $filas[0]['id_fila'] .")</script>"; 
} 
 
if (isset($chamado)) { 
 
  echo "<script type=\"text/javascript\">var fila_atual = " . $chamado->id_fila . "</script>";
  echo "<script type=\"text/javascript\">var g_id_chamado = " . $chamado->id_chamado . "</script>";
  echo "<script type=\"text/javascript\">carregaChamado(g_id_chamado)</script>";
  echo "<script type=\"text/javascript\">precisaPatrimonio(fila_atual,false)</script>"; 
  echo "<script type=\"text/javascript\">atualizaInteracoes(g_id_chamado)</script>"; 
}

if (isset($triagem)) { 
 
  echo "<script type=\"text/javascript\">var g_id_chamado = " . $triagem->id_chamado . "</script>";
  echo "<script type=\"text/javascript\">carregaTriagem(g_id_chamado)</script>";
}


?>

  </body>
</html>
