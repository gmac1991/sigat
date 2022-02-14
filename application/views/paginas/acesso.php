<html>
    <head>
    	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sigat</title>
        <link rel="stylesheet" href="<?= base_url("css/bootstrap.min.css") ?>">
        <link rel="stylesheet" href="<?= base_url("datatables/datatables.min.css") ?>">
        <link rel="stylesheet" href="<?= base_url("css/jquery.auto-complete.css") ?>">
        <link rel="stylesheet" href="<?= base_url("summernote/summernote-bs4.css") ?>">
        <link rel="stylesheet" href="<?= base_url("css/custom.css") ?>">

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
        <div class="container mw-100 my-4" style="width:340px">
            <div class="card" style="margin-top:75%">
                <article class="card-body">
                    <div class="text-center">
                        <img id="img-logo" src="<?= base_url("img/logo_pms.png") ?>" width="80" height="80">
                        <h1>SIGAT</h1>
                    </div>
                    
                    <?php if(isset($msg)) : echo $msg; endif; ?>
                    <form id="frmAcesso" action="<?= base_url('acesso/entrar') ?>" method="post">
                        <div class="form-group">
                            <input name="login_usuario" class="form-control" placeholder="Nome de usuário" type="text">
                        </div> <!-- form-group// -->
                        <div class="form-group">
                            <input name="senha_usuario" class="form-control" placeholder="Senha" type="password">
                        </div> <!-- form-group// --> 
                        <div class="form-group"> 
                        </div> <!-- form-group// -->  
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block"> Entrar  </button>
                        </div> <!-- form-group// -->                                                           
                    </form>
                </article>
                <p style="font-size: 12px" class="p-0 m-1 text-right">DGTI - v<?= $this->config->item('versao');?></p>
            </div> <!-- card.// -->
        </div> 
        <script src="<?= base_url("js/jquery-3.4.1.min.js") ?>"></script>
        <script type="text/javascript">
            $('#frmAcesso').on('submit',function(e) {

            e.preventDefault();

            if ($('#frmAcesso input[name=login_usuario]').val() != '' && $('#frmAcesso input[name=senha_usuario]').val() != '') {
                
                $('#frmAcesso button').prop('disabled','true');
                
                return;
                
            } 

            

            });
        </script>
    </body>
</html>
