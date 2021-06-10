	<div class="mt-3 mb-5 mx-3">
		<h3 id="titulo">Administração</h3>	
		<hr />
		<ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
		<li class="nav-item">
				<a class="nav-link active" id="pills-log-tab" data-toggle="pill" href="#pills-log" role="tab" aria-controls="pills-log" aria-selected="false">
				<i class="fas fa-list"></i> Log de Eventos</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" id="pills-usuarios-tab" data-toggle="pill" href="#pills-usuarios" role="tab" aria-controls="pills-usuarios" aria-selected="true">
				<i class="fas fa-users"></i> Usuários</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" id="pills-filas-tab" data-toggle="pill" href="#pills-filas" role="tab" aria-controls="pills-filas" aria-selected="false">
				<i class="fas fa-project-diagram"></i> Filas</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" id="pills-relatorios-tab" data-toggle="pill" href="#pills-relatorios" role="tab" aria-controls="pills-relatorios" aria-selected="false">
				<i class="fas fa-chart-pie"></i> Relatórios</a>
			</li>
			
		</ul>
		
		<div class="tab-content" id="pills-tabContent">
			<div class="tab-pane fade show active" id="pills-log" role="tabpanel" aria-labelledby="pills-log-tab">
				<table id="tblEventos" class="display compact" style="width:100%">
					<thead>
						<tr>
							<th>ID</th>
							<th>Ação</th>
							<th>Descrição</th>
							<th>Data/Hora</th>
							<th>Usuário</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
			<div class="tab-pane fade" id="pills-usuarios" role="tabpanel" aria-labelledby="pills-usuarios-tab">
				<div id="usuarios-grid"></div>
			</div>
			<div class="tab-pane fade" id="pills-filas" role="tabpanel" aria-labelledby="pills-filas-tab">
				<h4 class="mt-3">Filas avulsas</h4>
				<div id="filas-avulsas-grid"></div>
				<h4 class="mt-3">Filas fixas</h4>
				<div id="filas-grid"></div>
				
			</div>
			<div class="tab-pane fade" id="pills-relatorios" role="tabpanel" aria-labelledby="pills-relatorios-tab">relatorios</div>
			
		</div>
	</div>



	