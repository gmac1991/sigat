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
					<table id="tblEncerrados" style="width:100%" class="display">
						
						<thead >
						<tr>
							<th>#</th>
							<th>Ticket</th>
							<th>Solicitante</th>
							<th>Local</th>
							<th>Abertura</th>
							<th>Encerramento</th>
							<th>Responsável</th>
							<th>Fila</th>	
						</tr>
						</thead>
					</table>
					</div>	
				</div>

				<!-- TRIAGEM -->
				
				<div class="tab-pane fade show mb-5" id="triagem" role="tabpanel" aria-labelledby="triagem-tab">
					<div class="mb-5">
						<table id="tblTriagem" style="width:100%" class="display">
							<thead >
							<tr>
								<th>#</th>
								<th>Ticket OTRS</th>
								<th>Data de abertura</th>
								<th>Solicitante</th>
								<th>E-mail</th>
							</tr>
							</thead>
						</table>
					</div>	
				</div>

				<!-- CHAMADOS -->

				<div class="tab-pane fade show mb-5 active" id="painel" role="tabpanel" aria-labelledby="painel-tab">
				
				
				
				<div class="mb-5">
					<div class="d-flex p-2 justify-content-center">	
						<div class="btn-group btn-group-toggle" role="group" data-toggle="buttons">
						<?php foreach ($filas as $fila) : ?>
							<label class="btn btn-primary btn-sm mr-1 <?php if($fila_usuario== $fila['id_fila']): echo " active"; endif; ?>">
								<input type="radio" onclick="mudaFila(<?= $fila['id_fila'] ?>)"<?php if($fila_usuario == $fila['id_fila']): echo " checked"; endif; ?>><i class="<?= $fila['icone_fila'] ?>"></i> <?= $fila['nome_fila'] ?>
							</label>
						<?php endforeach; ?>
							<label class="btn btn-success btn-sm mr-1">
								<input type="radio" onclick="mudaFila(7)"><i class="fas fa-truck"></i> Entrega
							</label>
							<label class="btn btn-info btn-sm mr-1<?php if($fila_usuario == 0): echo " active"; endif; ?>">
								<input type="radio" onclick="mudaFila(0)"<?php if($fila_usuario == 0): echo " checked"; endif; ?>><i class="fas fa-search"></i> Todos
							</label>
						</div>	
					</div>
				</div>
					
					<table id="tblPainel" style="width:100%" class="display">
					
						<thead >
						<tr>
							<th>#</th>
							<th>Ticket</th>
							<th>Solicitante</th>
							<th>Local</th>
							<th>Data de abertura</th>
							<th>Responsável</th>		
							<th>Status</th>
							<!-- <th>&nbsp;</th> -->
						</tr>
						</thead>
					</table>
				</div>

		
		
		</div>	
	</div>
