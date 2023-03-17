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
				<i class="fas fa-chart-pie"></i> Dashboard</a>
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
			<div class="tab-pane fade" id="pills-relatorios" role="tabpanel" aria-labelledby="pills-relatorios-tab">
				<!-- MONITOR SSTEC -->
				<iframe 
				src="http://pms-labarq02:5000/public/dashboard/67dec996-cf31-45a3-843b-3b31970dd58d" 
				frameborder="0" 
				width="1800" 
				height="1000" 
				allowtransparency
				></iframe>
				<!-- INTERAÇÕES -->
				<iframe
				src="http://pms-labarq02:5000/public/dashboard/e53a91fc-284d-4e36-bf85-0760a5ce753f"
				frameborder="0"
				width="1800"
				height="1100"
				allowtransparency
				></iframe>
				<!-- SETOR DE MANUTENÇÃO -->
				<iframe
					src="http://pms-labarq02:5000/public/dashboard/2addca91-fe2f-47f8-b324-2823d0719943"
					frameborder="0"
					width="1800"
					height="1000"
					allowtransparency
				></iframe>
			</div>
			
		</div>
	</div>



	