    <div class="content m-3">
	
		<ul class="nav nav-tabs" role="tablist">
		<?php if ($triagem_usuario == 1) : ?>
			<li class="nav-item">
				<a class="nav-link" id="triagem-tab"  data-toggle="tab" href="#triagem" aria-controls="triagem" aria-selected="true"><i class="fas fa-filter"></i> Triagem</a>
		 	</li>
		<?php endif; ?>
			<li class="nav-item">
				<a class="nav-link active" id="painel-tab" data-toggle="tab" href="#painel" aria-controls="painel" aria-selected="true"><i class="fas fa-headset"></i> Chamados</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" id="encerrados-tab" data-toggle="tab" href="#encerrados" aria-controls="encerrados" aria-selected="true"><i class="fas fa-archive"></i> Arquivo</a>
			</li>
		</ul>

		<div class="tab-content mt-3">

				<!-- ENCERRADOS -->
			
				<div class="tab-pane fade show mb-5" id="encerrados" role="tabpanel" aria-labelledby="encerrados-tab">
					<div class="mb-5">
					<table id="tblEncerrados" style="width:100%" class="display nowrap">
						<thead>
						<tr>
							<th>#</th>
							<th>Ticket</th>
							<th>Solicitante</th>
							<th>Local</th>
							<th>Abertura</th>
							<th>Encerramento</th>
							<!-- <th>Responsável</th>
							<th>Fila</th>	 -->
						</tr>
						</thead>
					</table>
					</div>	
				</div>

				<!-- TRIAGEM -->
				
				<div class="tab-pane fade show mb-5" id="triagem" role="tabpanel" aria-labelledby="triagem-tab">
					<div class="mb-5">
						<table id="tblTriagem" style="width:100%" class="display nowrap">
							<thead>
							<tr>
								<th>#</th> <!-- oculto -->
								<th>Ticket</th>
								<th>Data de abertura</th>
								<th>Assunto</th>
								<th>Solicitante</th>
							</tr>
							</thead>
						</table>
					</div>	
				</div>

				<!-- CHAMADOS -->

				<div class="tab-pane fade show mb-5 active" id="painel" role="tabpanel" aria-labelledby="painel-tab">
				<div class="mb-5">
					<div class="d-flex p-2 justify-content-center">	
						<div class="btn-group btn-group-toggle" role="group" data-toggle="buttons" id="btnFilas">
						<?php foreach ($filas as $fila) : ?>
							<label data-fila="<?= $fila['id_fila'] ?>" class="btn btn-primary btn-sm mr-1">
								<input type="radio" onclick="mudaFila(<?= $fila['id_fila'] ?>)"><i class="<?= $fila['icone_fila'] ?>"></i> <?= $fila['nome_fila'] ?>
							</label>
						<?php endforeach; ?>
							<label data-fila="7" class="btn btn-success btn-sm mr-1">
								<input type="radio" onclick="mudaFila(7)"><i class="fas fa-truck"></i> Entrega
							</label>
							<label data-fila="0" class="btn btn-info btn-sm mr-1">
								<input type="radio" onclick="mudaFila(0)"><i class="fas fa-search"></i> Todos
							</label>
						</div>
						<div class="btn-toolbar" role="toolbar">
							<div class="btn-group-toggle" data-toggle="buttons">
								<label class="btn btn-secondary btn-sm" >
									<input type="checkbox" id="btnImprimir"><i class="fas fa-print"></i> Imprimir
									<span class="badge badge-light" id="contImp"></span>
								</label>
								<label class="btn btn-secondary btn-sm">
									<input type="checkbox" onclick="resetPainelChamados()"><i class="fas fa-broom"></i>
								</label>
							</div>
						</div>
					</div>
				</div>
					
				<table id="tblPainel" style="width:100%" class="display nowrap">
				
					<thead >
					<tr>
						<th>#</th>
						<th>Status</th>
						<th>Ticket</th>
						<th>Solicitante</th>
						<th>Solicitação</th>
						<th>Local</th>
						<th>Região</th>
						<th><i class="fas fa-tasks"></i></th>
						<th>Data</th>
						<th>Horas de espera (oculto)</th>
						<th class="text-center" title="Última interação"><i class="far fa-clock"></i></th>
						<th>Responsável</th>
					</tr>
					</thead>
				</table>
			</div>
		</div>	
	</div>
