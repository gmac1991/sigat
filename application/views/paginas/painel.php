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
						<th>Número</th>
						<th>Solicitante</th>
						<th>E-mail</th>
						<th>Data de abertura</th>
						<th>Ticket OTRS</th>		
						<th>Status</th>
						<th>&nbsp;</th>
					</tr>
					</thead>
				</table>
			</div>	
		  </div>
		  <div class="tab-pane fade show active" id="painel" role="tabpanel" aria-labelledby="painel-tab">
			
			<!-- <button class="btn btn-info" id="btnChamados" href="#" role="button" onclick="painelEncerrados(0)">
			<i class="fas fa-binoculars"></i> Chamados Encerrados</button> -->
			
			
			<div class="form-group d-inline-flex float-right">	
				<label for="id_fila" class="align-middle"><strong>Fila&nbsp;</strong></label>
				<select class="form-control" name="id_fila" onchange="mudaFila()" id="slctFila">
				<?php foreach ($filas as $fila) : ?>
					<option value="<?= $fila['id_fila'] ?>" <?php // if ($fila_atual == $fila['id_fila']) : echo 'selected'; endif; ?>><?= $fila['nome_fila'] ?></option>
				<?php endforeach; ?>
					<option value="7" <?php // if ($fila_atual == 0) : echo 'selected'; endif; ?>>Entrega</option>
					<option value="0" <?php // if ($fila_atual == 0) : echo 'selected'; endif; ?>>Todos</option>
					
				</select>
			</div>
			
			<div class="mb-5">
				<table id="tblPainel" style="width:100%" class="display">
					<thead >
					<tr>
						<th>Ticket</th>
						<th>Solicitante</th>
						<th>Local</th>
						<th>Data de abertura</th>
						<th>Responsável</th>		
						<th>Status</th>
						<th>&nbsp;</th>
						<th>&nbsp;</th>
					</tr>
					</thead>
				</table>
			</div>	
		  </div>
		  
		</div>
		
    </div>
