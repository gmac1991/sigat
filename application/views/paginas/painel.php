    <div class="content m-3">
	
		<ul class="nav nav-tabs" role="tablist">
			<li class="nav-item">
				<a class="nav-link" id="triagem-tab"  data-toggle="tab" href="#triagem" aria-controls="triagem" aria-selected="true">Triagem</a>
		 	</li>
			<li class="nav-item">
				<a class="nav-link active" id="painel-tab" data-toggle="tab" href="#painel" aria-controls="painel" aria-selected="true">Chamados</a>
			</li>
		</ul>
		<div class="tab-content mt-3">
		<div class="tab-pane fade show " id="triagem" role="tabpanel" aria-labelledby="triagem-tab">
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
		  <div class="tab-pane fade show active" id="painel" role="tabpanel" aria-labelledby="painel-tab">
			
			<!-- <button class="btn btn-info" id="btnChamados" href="#" role="button" onclick="painelEncerrados(0)">
			<i class="fas fa-binoculars"></i> Chamados Encerrados</button> -->
			
			
		
			
			<div class="mb-5">
				
				<table id="tblPainel" style="width:100%" class="display">
				<div class="d-inline-flex position-absolute" style="margin-left: 20%; z-index: 1">	
			
					<div class="btn-group btn-group-toggle" role="group" data-toggle="buttons">
					<?php foreach ($filas as $fila) : ?>
						<label class="btn btn-info btn-sm">
							<input type="radio" onclick="mudaFila(<?= $fila['id_fila'] ?>)"> <?= $fila['nome_fila'] ?>
						</label>
					<?php endforeach; ?>
						<label class="btn btn-info btn-sm">
							<input type="radio" onclick="mudaFila(7)"> Entrega
						</label>
						<label class="btn btn-success btn-sm">
							<input type="radio" onclick="mudaFila(0)"> Todos
						</label>
					</div>	
				</div>
					<thead >
					<tr>
						<th>#</th>
						<th>Ticket</th>
						<th>Solicitante</th>
						<th>Local</th>
						<th>Data de abertura</th>
						<th>Responsável</th>		
						<th>Status</th>
						<th>&nbsp;</th>
					</tr>
					</thead>
				</table>
			</div>	
		  </div>
		  
		</div>
		
    </div>
