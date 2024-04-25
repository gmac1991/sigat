<nav aria-label="breadcrumb">
   <ol class="breadcrumb">
      <li class="breadcrumb-item active"><a href="<?= base_url('painel?v=triagem'); ?>">Painel</a></li>
      <li class="breadcrumb-item" aria-current="page">Remessas de inservíveis</li>
   </ol>
</nav>
<div class="container-fluid mt-3">
    <!-- <div class="d-flex justify-content-center">
        <h2 class="align-center"><i class="fa fa-tasks mr-2"></i>Remessas de inservíveis</h2>
   </div> -->
    
    <table class="table" id="tabela-inservivel">
        <thead class="thead-dark">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Divisão</th>
                <th scope="col">Quantidade</th>
                <th scope="col">Abertura</th>
                <th scope="col">Fechamento</th>
                <th scope="col">Entrega</th>
                <th scope="col">Responsável</th>
                <th scope="col">Status</th>
            </tr>
        </thead>
        <tbody>
            <!-- Os dados da tabela serão adicionados aqui dinamicamente -->
        </tbody>
    </table>

    <nav aria-label="...">
        <!---####  paginação inicio #### -->
        <div class="col-12 mb-5 d-flex justify-content-center">
            <div id="paginacao">
                <button class="btn btn-dark" onclick="paginaAnterior()">Anterior</button>
                <span><button class="btn btn-primary mr-1" id="paginaAtual">1</span>
                <button class="btn btn-dark" onclick="proximaPagina()">Próxima</button>
            </div>
        </div>
    </nav>
</div>