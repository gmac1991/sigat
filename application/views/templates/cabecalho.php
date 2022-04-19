<!doctype html>
<html>
    <head>
    	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1">

        <title>SIGAT</title>
        
        <link rel="stylesheet" href="<?= base_url("css/bootstrap.min.css") ?>">
        <link rel="stylesheet" href="<?= base_url("datatables/datatables.min.css") ?>">
        <link rel="stylesheet" href="<?= base_url("css/jquery.auto-complete.css") ?>">
        <link rel="stylesheet" href="<?= base_url("summernote/summernote-bs4.css") ?>">
        <link rel="stylesheet" href="<?= base_url("css/custom.css") ?>">
        <link rel="stylesheet" href="<?= base_url("css/jsgrid.min.css") ?>">
        <link rel="stylesheet" href="<?= base_url("css/jsgrid-theme.min.css") ?>">
        
        <link href="<?= base_url("fa/css/all.min.css") ?>" rel="stylesheet">

        <!-- FAVICON -->

        <link rel="apple-touch-icon" sizes="57x57" href="<?= base_url("icon/apple-icon-57x57.png") ?>">
        <link rel="apple-touch-icon" sizes="60x60" href="<?= base_url("icon/apple-icon-60x60.png") ?>">
        <link rel="apple-touch-icon" sizes="72x72" href="<?= base_url("icon/apple-icon-72x72.png") ?>">
        <link rel="apple-touch-icon" sizes="76x76" href="<?= base_url("icon/apple-icon-76x76.png") ?>">
        <link rel="apple-touch-icon" sizes="114x114" href="<?= base_url("icon/apple-icon-114x114.png") ?>">
        <link rel="apple-touch-icon" sizes="120x120" href="<?= base_url("icon/apple-icon-120x120.png") ?>">
        <link rel="apple-touch-icon" sizes="144x144" href="<?= base_url("icon/apple-icon-144x144.png") ?>">
        <link rel="apple-touch-icon" sizes="152x152" href="<?= base_url("icon/apple-icon-152x152.png") ?>">
        <link rel="apple-touch-icon" sizes="180x180" href="<?= base_url("icon/apple-icon-180x180.png") ?>">
        <link rel="icon" type="image/png" sizes="192x192"  href="<?= base_url("icon/android-icon-192x192.png") ?>">
        <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url("icon/favicon-32x32.png") ?>">
        <link rel="icon" type="image/png" sizes="96x96" href="<?= base_url("icon/favicon-96x96.png") ?>">
        <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url("icon/favicon-16x16.png") ?>">
        <link rel="manifest" href="<?= base_url("icon/manifest.json") ?>">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="<?= base_url("icon/ms-icon-144x144.png") ?>">
        <meta name="theme-color" content="#ffffff">

        <!-- /FAVICON -->
        
    </head>
    <body>
        <div class="modal" id="modalBuscaRapida" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    
                    
                </div> <!-- FIM MODAL BODY -->
                </div>
            </div>
        </div>
        <nav class="navbar navbar-dark bg-dark">
        <a class="navbar-brand" href="<?= base_url() ?>">
        <img id="img-logo" src="<?= base_url("img/logo_pms.png") ?>" width="40" height="40" class="d-inline-block align-top" alt="">
       <h3 class="d-inline">SIGAT</h3>
        </a>
        <form class="form-inline" id="frmBuscaRapida">
        <div class="input-group">
            <input type="text" class="form-control" placeholder="Busca rápida..." id="txtBuscaRapida" value="<?php if($this->input->get("t") !== NULL) echo $this->input->get("t"); ?>">
            <div class="input-group-append">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
            </div>
            </div>
        </form>
        <?php if(isset($nome_usuario)): ?>
        <div class="float-right d-inline text-light">
           
            Olá, <strong><?= $nome_usuario ?></strong>!
			<a href="<?= base_url("painel") ?>" class="btn btn-primary btn-sm" role="button" aria-pressed="true"><i class="fas fa-bars"></i> Painel</a>
            <?php if ($autorizacao_usuario >= 4): ?>
            <a class="btn btn-sm btn-secondary" href="<?= base_url('admin'); ?>" role="button"><i class="fas fa-wrench"></i> Administração</a> 
            <?php endif; ?>
			
			<a class="btn btn-sm btn-danger" role="button" href="<?= base_url('acesso/sair') ?>"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </div>
        <?php endif; ?>
        </nav>
