var listaVerificada = false;
var timeout;
const url = 'https://sistemas.sorocaba.sp.gov.br/acesso_patrimonio/api/patrimonio/'; //API web do SIM (patrimônio)
var toggle = 0;
var g_requer_patri = null;
var fila_atual = null;
var p_equips = [,];




// --- PLUGIN DATETIME.JS

// UMD
(function( factory ) {
    "use strict";
 
    if ( typeof define === 'function' && define.amd ) {
        // AMD
        define( ['jquery'], function ( $ ) {
            return factory( $, window, document );
        } );
    }
    else if ( typeof exports === 'object' ) {
        // CommonJS
        module.exports = function (root, $) {
            if ( ! root ) {
                root = window;
            }
 
            if ( ! $ ) {
                $ = typeof window !== 'undefined' ?
                    require('jquery') :
                    require('jquery')( root );
            }
 
            return factory( $, root, root.document );
        };
    }
    else {
        // Browser
        factory( jQuery, window, document );
    }
}
(function( $, window, document ) {
 
 
$.fn.dataTable.render.moment = function ( from, to, locale ) {
    // Argument shifting
    if ( arguments.length === 1 ) {
        locale = 'en';
        to = from;
        from = 'YYYY-MM-DD';
    }
    else if ( arguments.length === 2 ) {
        locale = 'en';
    }
 
    return function ( d, type, row ) {
        if (! d) {
            return type === 'sort' || type === 'type' ? 0 : d;
        }
 
        var m = window.moment( d, from, locale, true );
 
        // Order and type get a number value from Moment, everything else
        // sees the rendered value
        return m.format( type === 'sort' || type === 'type' ? 'x' : to );
    };
};
 
 
}));

$(function() {

	// PAINEL

	painel(g_fila_usuario); //incializa o painel na fila preferencial do usuario
	$('#slctFila').val(g_fila_usuario); //seleciona a fila preferencial do usuario
	
	// TRIAGEM
	triagem(); //incializa o painel de triagem
	
	
	/* ---- Pagina de abertura de chamado ---*/
	
	$('#btnAlteraPatrimonios').hide();

	$('#divTabelaPatrimonios').hide();

	$('#divTabelaChamadosAbertos').hide();
	
	$('#divTabelaInserviveis').hide();

	if (listaVerificada == true) {

		$('#btnAbrirChamado').removeAttr("disabled");
	}

	$('input[name=id_usuario]').val(g_id_usuario);

	// --------------- HABILITANDO FILTRAGEM NOS EVENTOS ---------------

	$('#tblEventos thead tr').clone(true).appendTo( '#tblEventos thead' );
	$('#tblEventos thead tr:eq(1) th').each( function (i) {
	var title = $(this).text();
	$(this).html( '<input type="text" placeholder="Procurar '+title+'" />' );

	$( 'input', this ).on( 'keyup change', function () {
		if ( $('#tblEventos').DataTable().column(i).search() !== this.value ) {
			$('#tblEventos').DataTable()
				.column(i)
				.search( this.value )
				.draw();
		}
		} );
	} );

});


// --------------- PAINEL ---------------------------

var enc = false

function painel(id_fila) {

	

	$('#tblPainel').DataTable( { //  inicializacao do painel

		"autoWidth": false,

		"createdRow": function( row, data, dataIndex){
			if( data[5] === 'ABERTO'){
				$(row).addClass('table-warning');
			}
			if( data[5] === 'FECHADO'){
				$(row).addClass('table-success');
			}
			if( data[4] === null){
				$(row).removeClass('table-warning');
				$(row).addClass('table-danger');
			}
			
		},
		"columnDefs": [
			{ 
				"orderable": false, "targets": [6,7]
			  
			  
			},
			{ "width": "10%", "targets": 3, "render": $.fn.dataTable.render.moment( 'YYYY-MM-DD HH:mm:ss','DD/MM/YYYY H:mm:ss' ) },
		],

		"language": {
			"decimal":        "",
			"emptyTable":     "Sem chamados :)",
			"info":           "Mostrando _START_ a _END_ de _TOTAL_ chamados",
			"infoEmpty":      "Mostrando 0 a 0 de 0 chamados",
			"infoFiltered":   "(filtrado de _MAX_ chamados)",
			"infoPostFix":    "",
			"thousands":      ".",
			"lengthMenu":     "Mostrando _MENU_ chamados",
			"loadingRecords": "Carregando...",
			"processing":     "Processando...",
			"search":         "Busca:",
			"zeroRecords":    "Sem resultados!",
			"paginate": {
				"first":      "Primeiro",
				"last":       "Último",
				"next":       "Próximo",
				"previous":   "Anterior"
			},
		},

		"ajax": base_url + 'chamado/listar_chamados_painel/' + id_fila,

		"order": [],

		"drawCallback": function(settings) {

			

			var p_id_chamado = null;

			$('.PopoverPainel').each(function() {


				p_id_chamado = $(this).attr('data-chamado');

				$.ajax({

					type: 'post',
					url: base_url + 'backend/texto_ultima_interacao',
					data: {
						id_chamado: p_id_chamado
					},
					dataType: 'json',
					success: interacao => {

						$(this).popover({content: interacao.nome_usuario + interacao.texto_interacao,  trigger: 'focus', placement: "left", html: true});
	
					}
				})
			})
		}
	}); 	
}



function mudaFila() { //troca de fila no painel => destroi o painel e reconstroi no onChange do $('#slctFila')

	if (enc === true) {

		$('#tblPainel').DataTable().ajax.url( base_url + 'chamado/listar_encerrados_painel/' + $('#slctFila').val() ).load();

	} else {

		$('#tblPainel').DataTable().ajax.url( base_url + 'chamado/listar_chamados_painel/' + $('#slctFila').val() ).load();

	}
	
}

setInterval(function() { //atualiza o painel de chamados


	$('#tblPainel').DataTable().ajax.reload(null, false);


}, 15000);


function painelEncerrados(id_fila) {

	if (enc === false) {
		$('#slctFila').val(0);

		$('#btnChamados').html('<i class="fas fa-binoculars"></i> Chamados Abertos');
		
		$('#tituloPainel').append(' (encerrados)');
		$('#tblPainel').DataTable().ajax.url( base_url + 'chamado/listar_encerrados_painel/' + id_fila ).load();
		enc = true;

	} else {
		$('#slctFila').val(0);

		$('#btnChamados').html('<i class="fas fa-binoculars"></i> Chamados Encerrados');
		$('#tituloPainel').html('Painel de chamados');
		$('#tblPainel').DataTable().ajax.url( base_url + 'chamado/listar_chamados_painel/' + id_fila ).load();
		enc = false;
	}

	


}

// ------------  PAINEL TRIAGEM

function triagem() {

	$('#tblTriagem').DataTable( { //  inicializacao do painel

		"autoWidth": false,

		"columnDefs": [
			
			{ "width": "10%", "targets": 3, "render": $.fn.dataTable.render.moment( 'YYYY-MM-DD HH:mm:ss','DD/MM/YYYY H:mm:ss' ) },
		],

		"language": {
			"decimal":        "",
			"emptyTable":     "Sem chamados :)",
			"info":           "Mostrando _START_ a _END_ de _TOTAL_ chamados",
			"infoEmpty":      "Mostrando 0 a 0 de 0 chamados",
			"infoFiltered":   "(filtrado de _MAX_ chamados)",
			"infoPostFix":    "",
			"thousands":      ".",
			"lengthMenu":     "Mostrando _MENU_ chamados",
			"loadingRecords": "Carregando...",
			"processing":     "Processando...",
			"search":         "Busca:",
			"zeroRecords":    "Sem resultados!",
			"paginate": {
				"first":      "Primeiro",
				"last":       "Último",
				"next":       "Próximo",
				"previous":   "Anterior"
			},
		},

		"ajax": base_url + 'triagem/listar_triagem/',

		"order": [],


	}); 
	
}


// =============== MODAIS ====================

function buscaPatrimonios(p_id_chamado, p_id_fila_ant, p_atendimento, ins = false, p_espera = false, p_alt_fila = false) {

	var lista_patrimonios = [];
	$('#divPatrimonios').empty();
	$('#btnRegistrarInteracao').removeAttr('disabled');
	
	$.ajax({
		
		url: base_url + 'backend/patrimonios', 
		data: { 
			id_chamado: p_id_chamado, 
			espera: p_espera 
		},
		type: 'POST',
		async: true,
		dataType: 'json',
		success: function(data) {
			
			//console.log(data);

			data.filas.forEach(function(fila) { //exibindo as filas
			
				if (fila.id_fila == 6 && p_alt_fila == true) {
					return; // se for a fila de Sol. de Equipamentos (id 6) no modal de Interacao, pular e não exibir
				} 
				else if(p_id_fila_ant == 6 && fila.id_fila != 3)  { 
					return;
				}
				else if(p_id_fila_ant != 6 && data.id_fila == 6 ) {
					return;
				}
				else {

					if(p_id_fila_ant == 6) {
						$('select[name=id_fila]').append("<option value=\"6\" >Solicitação de Equipamento</option>");
					}
					
					$('select[name=id_fila]').append("<option value=\"" + fila.id_fila + "\" >" + fila.nome_fila + "</option>");
				
					if (p_atendimento == true) {

						$('option[value=' + data.id_fila + ']').prop("selected", "true"); //se for atendimento, 
																						//selecionar a fila atual do chamado
						
					} else {

						$('#slctFila option[value=' + p_id_fila_ant + ']').remove(); //se não, remover a fila atual da lista
					}
							
				}
				
			});

			
	
			if (data.patrimonios != null && p_id_fila_ant != 6) { //se houver patrimonios no chamado para atender, exibi-los
			
				data.patrimonios.forEach(function(patrimonio) {
				
					lista_patrimonios.push(patrimonio['num_patrimonio']);
			
				});
		
			} 

			// ================= EQUIPAMENTOS SEM PATRIMONIO ==================

			var tem_equipamentos = 0;

			$.ajax({
				type: "POST",
				async: true, //
				url: base_url + 'backend/equipamentos',
				data: {
					id_chamado: g_id_chamado,
				},
				dataType: 'JSON',
				success: function(data) {
					if (data != null) {
						tem_equipamentos = 1;

						p_equips = [,];
						
						//console.log(data.equipamentos);
						data.equipamentos.forEach(function(equip) {
							p_equips.push([equip.num_equipamento, equip.desc_equipamento]);
						});
					}
				},
				complete: function() {

					if(p_equips.length > 1 && $('#slctTipo').val() == 'ATENDIMENTO') {
						$('#divPatrimonios').append("<p>Serão considerados os seguintes equipamentos para esse atendimento:</p>");
						$('#divPatrimonios').append("<ul></ul>");

						p_equips.forEach(function(equip) {
							$('#divPatrimonios ul').append("<li>" + equip[0] + " - " + equip[1] + "</li>");
						});

					}
					
					
				}
			});

			if (lista_patrimonios.length > 0) {

				$('#divPatrimonios').empty();
		
				if (p_atendimento == true) {
		
					$('#divPatrimonios').prepend("<p>Marque os equipamentos que foram finalizados:</p>");
			
					if (!ins) { // nao sendo a opçao Classificar como inservivel, o check de equipamento é opcional
						$('#divPatrimonios p').append(" <small><strong>(opcional)</strong></small>");
					}
	
				} else {
		
					if (p_espera == false) {
		
						$('#divPatrimonios').prepend("<p>Marque os equipamentos que serão deixados em espera:</p>");
		
					} else {
		
						$('#divPatrimonios').prepend("<p>Marque os equipamentos que sairão da espera:</p>");
					}	
				}
				lista_patrimonios.forEach(function(patrimonio) { //criando os checkbox com os patrimonios
					$('#divPatrimonios').append(
					"<input class=\"chkPatri\" type=\"checkbox\" id=\"" + patrimonio + "\" value=\"" + patrimonio + "\">" +
					"<label class=\"mr-2\" for=\"" + patrimonio + "\">&nbsp;" + patrimonio + "</label>");
				});
		
			}
			else {
		
				if (p_espera == true) {
					$('#divPatrimonios').prepend("<p>Não existem equipamentos em espera!</p>");
					if(!p_alt_fila) {
						$('#btnRegistrarInteracao').prop('disabled','true');
					}
					
				} 
				else if(p_id_fila_ant == 6) { // no caso da fila estar como 'Solicitacao de Equipamento'
					$('#divPatrimonios').prepend("<p>Para este tipo de interação, altere a fila para <b>Manutenção de Hardware</b></p>");
					if(!p_alt_fila) {
						$('#btnRegistrarInteracao').prop('disabled','true');
					}
				}
				
				else {

					if (tem_equipamentos == 0) {

						$('#divPatrimonios').prepend("<p>Não existem equipamentos disponíveis para isso!</p>");
						if(!p_alt_fila) {
							$('#btnRegistrarInteracao').prop('disabled','true');
		
						}
					}
				} 
			}
		}
	});
}


function verificaTipo(fila_ant, id_chamado) { //verificar tipo da fila no modal de Registro de Atendimento

	$('select[name=id_fila]').empty();
	

	switch ($('#slctTipo').val()) {

		case 'ATENDIMENTO':
			if (g_requer_patri == true) {
				buscaPatrimonios(id_chamado, fila_ant,true,false,false);
				$('#divPatrimonios').show();
				$('#divFila').show();
				$('#slctFila').attr('disabled',true);

			} else {

				$('#divPatrimonios').hide();
				$('#divFila').hide();
				$('#slctFila').attr('disabled',true);
			}
			
			break;
		case 'ALT_FILA':
			buscaPatrimonios(id_chamado, fila_ant,false,false,false,true);
			$('#divFila').show();
			$('#divPatrimonios').hide();
			$('#slctFila').attr('disabled',false);
			
			break;
		case 'OBSERVACAO':
			$('#divPatrimonios').hide();
			$('#divFila').hide();
			$('#slctFila').attr('disabled',true);
			$('#btnRegistrarInteracao').removeAttr('disabled');
			break;
		case 'INSERVIVEL':
			
			if (fila_ant == 3) {
				buscaPatrimonios(id_chamado, fila_ant, true, true);
				$('#divPatrimonios').show();
				$('#divFila').show();
				$('#slctFila').attr('disabled',true);
			} else {
				$('#divPatrimonios').show();
				$('#divPatrimonios').html('Opção disponível somente na fila <strong>Manutenção de Hardware</strong><br>');
				$('#btnRegistrarInteracao').prop('disabled','true');
				$('#divFila').hide();
			}
			
			
			break;

		case 'ESPERA':
			buscaPatrimonios(id_chamado, fila_ant, false);
			$('#divPatrimonios').show();
			$('#divFila').hide();
			$('#slctFila').attr('disabled',true);
			break;

		case 'REM_ESPERA':
			buscaPatrimonios(id_chamado, fila_ant, false,false,true);
			$('#divPatrimonios').show();
			$('#divFila').hide();
			$('#slctFila').attr('disabled',true);
			break;
		case 'FECHAMENTO':
		
			break;
	
	}

}

function criaFormRegistro(p_id_chamado,p_id_fila_ant) {  //carregar o form no modal de Registro de Atendimento
	var html_form = 
	"<div class=\"row\">"+
	"<div class=\"col\">"+
	"<label for=\"tipo\">Tipo</label>" +
	"<select class=\"form-control\" name=\"tipo\" id=\"slctTipo\" onchange=\"verificaTipo(" + p_id_fila_ant + "," + p_id_chamado + ")\">" +
	"</select>" +
	"</div>"+
	"</div>"+
	"<div class=\"row\" id=\"divFila\">" +
	"<div class=\"col\">" +
	"<label for=\"id_fila\">Fila</label>" +
	"<select class=\"form-control\" name=\"id_fila\" id=\"slctFila\"></select>" +
	"</div>" +
	"</div>" +
	"<div class=\"row mt-3\">" +
	"<div class=\"col\">" +
	"<div id=\"divPatrimonios\"></div>" +
	"</div>" +
	"</div>" +
	"<div class=\"row mb-3\">" +
	"<div class=\"col\">" +
	"<textarea name=\"txtInteracao\"></textarea>" +
	"</div>" +
	"</div>" +
	"<input type=\"hidden\" name=\"id_chamado\" value=\"" + p_id_chamado + "\"/>" + 
	"<input type=\"hidden\" name=\"id_fila_ant\" value=\"" + p_id_fila_ant + "\"/>";

	return html_form;
}


// --- MODAL INTERACAO

$('#modalRegistro').on('show.bs.modal', function (event) { //modal de registro de interacao
	
	var link = $(event.relatedTarget);
	var p_id_chamado = link.data('chamado');

	var modal = $(this);

	$.ajax({
		url: base_url + 'backend/requer_patrimonio', 
		data: { id_fila: fila_atual },
		type: 'GET',
		async: false,
		success: function(data){ 

			if (data == '1' ) { //verificar a fila do chamado requer patrimonio
			
				g_requer_patri = true;
				modal.find('.modal-body #conteudo_form').empty();
				modal.find('.modal-body #conteudo_form').prepend(criaFormRegistro(p_id_chamado,fila_atual));
				
			} else {

				modal.find('.modal-body #conteudo_form').empty();
				modal.find('.modal-body #conteudo_form').prepend(criaFormRegistro(p_id_chamado,fila_atual));
			}
		}
	});		
});

$('#modalRegistro').on('shown.bs.modal', function (event) {

	$('#btnRegistrarInteracao').removeAttr('disabled');

	$('textarea[name=txtInteracao]').summernote({ //inicialização do SummerNote 

		toolbar: [
			['style', ['bold', 'italic', 'underline', 'clear']],
			['font', ['strikethrough', 'superscript', 'subscript']],
			['fontsize', ['fontsize']],
			['color', ['color']],
			['para', ['ul', 'ol', 'paragraph']],
			['height', ['height']],
			['insert', ['link', 'picture']],
		  ],
		height: 200,
		lang: 'pt-BR',
		dialogsInBody: true,
		disableDragAndDrop: false,
	});

	$('#slctTipo').append("<option value=\"ATENDIMENTO\" selected>Atendimento</option>" +
							"<option value=\"OBSERVACAO\">Observação</option>" +
							"<option value=\"ALT_FILA\">Alteração de fila</option>");

	if (g_requer_patri == true) {
		$('#slctTipo').append('<option value=\"ESPERA\">Deixar em espera</option>');
		$('#slctTipo').append('<option value=\"REM_ESPERA\">Remover da espera</option>');
		$('#slctTipo').append('<option value=\"INSERVIVEL\">Classificar como inservível</option>');


	} else {
		$('#slctTipo').append('<option value=\"FECHAMENTO\">Finalizar</option>');
	}

	verificaTipo(fila_atual, g_id_chamado);

	

});

$('#modalRegistro').on('hide.bs.modal', function (event) {

	$('div[role=alert]').remove();
	

});

// --- /FIM MODAL INTERACAO

// -- MODAL REGISTRO ENTREGA

$('#modalRegistroEntrega').on('hide.bs.modal', function (event) {

	$(this).find("#btnRegistrarEntrega").hide();

});

$('#modalRegistroEntrega').on('show.bs.modal', function (event) {

	var link = $(event.relatedTarget);
	var p_id_chamado = link.data('chamado');

	$(this).find('.modal-body form #conteudo_form_entrega').html(
		
		"<label>A entrega foi realizada? </label><br>" +
		"<div class=\"custom-control custom-radio custom-control-inline\">" +
		"<input onchange=\"verificaEntrega()\" type=\"radio\" value=\"1\" id=\"rdSim\" name=\"confirmaEntrega\" class=\"custom-control-input\">" +
		"<label class=\"custom-control-label\" for=\"rdSim\">Sim</label>" +
		"</div>" +
		"<div class=\"custom-control custom-radio custom-control-inline\">" +
		"<input onchange=\"verificaEntrega()\" type=\"radio\" value=\"0\" id=\"rdNao\" name=\"confirmaEntrega\" class=\"custom-control-input\">" +
		"<label class=\"custom-control-label\" for=\"rdNao\">Não</label>" +
		"</div>" +	
		"<div id=\"divFalhaEntrega\" style=\"display: none\">" +		
		"<div class=\"form-group\">" +		
		"<label for=\"txtFalhaEntrega\"><b>Descreva o motivo:</b></label>" +	
		"<textarea class=\"form-control\" id=\"txtFalhaEntrega\" rows=\"3\"></textarea>" +	
		"</div>" +
		"</div>" +	

		"<div id=\"divEntrega\" style=\"display: none\">" +	
		"<div class=\"form-group\">" +		
		"<label for=\"termo\"><strong>Termo de entrega assinado</strong></label>" +	
		"<input type=\"file\" class=\"form-control-file\" name=\"termo_entrega\" accept=\".pdf\">" +	
		"</div>" +	
		"<div class=\"form-group\">" +		
		"<label for=\"termo\"><strong>Termo de responsabilidade assinado <small>(se houver)</small></strong></label>" +	
		"<input type=\"file\" class=\"form-control-file\" name=\"termo_responsabilidade\" accept=\".pdf\">" +	
		"</div>" +		
		"<div class=\"form-group\">" +		
		"<label for=\"nome_recebedor\"><strong>Nome do recebedor</strong></label>" +	
		"<input type=\"text\" class=\"form-control\" name=\"nome_recebedor\">" +
		"</div>" +	
		"</div>" +
		"<input type=\"hidden\" name=\"id_chamado\" value=\"" + p_id_chamado + "\" />");

});

// -- /FIM MODAL REGISTRO ENTREGA

$('#modalEquipamentos').on('show.bs.modal', function() {

	$('#btnAdicionarEquipamentos').removeAttr('disabled');

});

$('#modalEquipamentos').on('hidden.bs.modal', function (event) {
	//$('#modalEquipamentos').modal('dispose');
	$('tblEquipamentos tbody').empty();
	listaVerificada = false;
});

$('#modalEquipamentos').on('show.bs.modal', function (event) {
	listaVerificada = false;
});


// ========== FIM MODAIS ================



//------------------------ AUTOCOMPLETE ----------------------------

let xhr;

$('input[name=nome_solicitante]').autoComplete({ //na abertura do chamado
	source: function(term, response){
		try { xhr.abort(); } catch(e){}
		xhr = $.getJSON(base_url + 'backend/solicitantes', { q: term }, function(data){ response(data); });
	},
	minChars: 2,
	autoFocus: true,

});




$('input[name=nome_local]').autoComplete({
	source: function(term, response){
		try { xhr.abort(); } catch(e){}
		xhr = $.getJSON(base_url + 'backend/locais', { q: term }, function(data){ response(data); });
	},
	minChars: 2,

});

// ------------------------------------- ABERTURA DO CHAMADO --------------------------------


function precisaPatrimonio(id_fila,triagem) { // verifica se a fila escolhida na abertura do chamado precisa de patrimonio
	
	$( "#listaPatrimonios" ).hide();
	$("#msg div[id=alerta]").remove();
	
	$( "#btnVerificaPatrimoniosTriagem" ).hide(); //TRIAGEM
	
	$.get(base_url + 'backend/requer_patrimonio', {id_fila: id_fila}, function(response) {
		if (response == '1') { //caso a fila requeira patrimonio
			
			if (!triagem) {

				$( "#listaPatrimonios" ).show();
				$( "#txtPatrimonios" ).removeAttr('readonly');
				$( "#btnVerificaPatrimonios" ).show();
				$( "#flagPrecisaPatrimonio" ).val(1);
				clearTimeout(timeout);
				$('#btnAbrirChamado').removeAttr("disabled");

				listaVerificada = false;

				if (id_fila == 6) {  	// em caso de solicitacao de equipamentos, 
										// fazer o bypass da lista de patrimonios 
										// para que eles sejam adicionados posteriormente

					$( "#divTabelaPatrimonios" ).hide();
					$( "#listaPatrimonios" ).hide();

					listaVerificada = true;
				}

			} 
			
			else {
				$( "#btnVerificaPatrimoniosTriagem" ).show();
			}
		
		} else { //senao

			$( "#divTabelaPatrimonios" ).hide();
			$( "#listaPatrimonios" ).hide();
			$( "#flagPrecisaPatrimonio" ).val(0);
			$( "#btnVerificaPatrimoniosTriagem" ).hide();
		}
				
			
	});	
}

//--------- Verificaçao da lista de patrimonios ---------------

var vetorListaOK = [];

async function criaTabelaPatrimonios(vetor,url) {

	vetorListaOK = [];
	
	var vetorPatrInv = [];

	var vetorPatrIns = [];
	var vetorPatrInsDesc = [];
	var vetorPatrInsChamado = [];

	var vetorPatrOK = [];
	var vetorPatrOKDesc = [];

	var vetorPatrAberto = [];
	var vetorPatrAbertoDesc = [];
	var vetorPatrAbertoChamado = [];
	
	$('[for="listaPatrimonios"]').append("<div class=\"spinner-border spinner-border-sm\" role=\"status\">&nbsp;&nbsp;<span class=\"sr-only\">Carregando...</span\></div>");
	
	
	for (var item of vetor) { 

		await fetch(base_url + 'backend/patrimonio?url='+ url + item + '&q=' + item)
			
		.then(response => response.json())
		
		.then(data => {
				
				if (data.descricao != null) {

					if (data.inservivel) { // verificando se não é inservivel
						vetorPatrIns.push(item);              
						vetorPatrInsDesc.push(data.descricao);
						vetorPatrInsChamado.push(data.inservivel['id_chamado_patrimonio']);
					} else {
						vetorPatrOK.push(item);              // se houver descricao, o patrimonio é valido
						vetorPatrOKDesc.push(data.descricao);
					}
					
					if (data.chamado != null) {
						
						vetorPatrAberto.push(item); // se houver chamado, enfileirar nos vetores PatrAberto (num_patrimonio, descricao, id_chamado)
						vetorPatrAbertoDesc.push(data.descricao);
						vetorPatrAbertoChamado.push(data.chamado);

						vetorPatrOK.pop(); //remover dos vetores de patrimonios validos
						vetorPatrOKDesc.pop();

					}

				} else {
					vetorPatrInv.push(item); //se não houver descricao, o patrimonio é invalido e será enfileirado no vetorPatriInv

				}
			
			}
		);

	}


	if (vetorPatrInv.length > 0) { //se houverem patrimonios invalidos...

		$('#listaVerificada div').remove();
		$('[for="listaPatrimonios"] .spinner-border').remove();
		
		vetorPatrInv.forEach(function (item){
			$("#msgPatr").append("<div class=\"alert alert-warning alert-dismissible fade show\" role=\"alert\">Patrimônio inválido: <strong>" + item + "</strong><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button></div>");
		});
		
		$("#txtPatrimonios").removeAttr('readonly');
		$("#txtPatrimonios").show();
		$("#txtPatrimonios").focus();
		$("#btnAlteraPatrimonios").hide();
		$('#btnVerificaPatrimonios').show();
		$('#divTabelaPatrimonios').hide();
		

	} else { //se não houverem patrimonios inválidos ...
		
		$('[for="listaPatrimonios"] .spinner-border').remove();

		if (vetorPatrIns.length > 0) { // se houverem patrimonios inservie...

			$('#divTabelaInserviveis').show();
			
			for (var i=0; i<vetorPatrIns.length; i++) {

				$('#tblInserviveis tbody').append('<tr class=\"table-danger\"><td>' + vetorPatrIns[i] + 
				'</td><td>' + vetorPatrInsDesc[i] + '</td><td>#' +  vetorPatrInsChamado[i] + 
				' <a target=\"_blank\" href=\"' + base_url + 'chamado/' + vetorPatrInsChamado[i] + '\">Mais...</a></td></tr>');

			}

			listaVerificada = false;

			$('#btnRemovePatrimonios').show();

		}
		
		
		if (vetorPatrAberto.length > 0) { // se houverem patrimonios com chamado aberto...

			$('#divTabelaChamadosAbertos').show();
			
			for (var i=0; i<vetorPatrAberto.length; i++) {

				$('#tblPatrimoniosAbertos tbody').append('<tr class=\"table-warning\"><td>' + vetorPatrAberto[i] + 
				'</td><td>' + vetorPatrAbertoDesc[i] + '</td><td>#' +  vetorPatrAbertoChamado[i] + 
				' <a target=\"_blank\" href=\"' + base_url + 'chamado/' + vetorPatrAbertoChamado[i] + 
				'"><small>Mais...</small></a></td></tr>');
			
			}	

			listaVerificada = false;
		
			$('#btnRemovePatrimonios').show();
		} 
		if (vetorPatrOK.length > 0) { //se houverem patrimonios válidos...

			$('#divTabelaPatrimonios').show();
			$("#btnAlteraPatrimonios").show();
			
			for (var i=0; i<vetorPatrOK.length; i++) {

				$('#tblPatrimonios tbody').append('<tr><td>' + vetorPatrOK[i] + 
				'</td><td>' + vetorPatrOKDesc[i] + '</td></tr>');

				vetorListaOK.push(vetorPatrOK[i]);

			}

			if (vetorPatrAberto.length == 0) {
				listaVerificada = true;
			}			

		}
	
	}

}



$("#btnVerificaPatrimonios").click(function() {

	var lista = $('#txtPatrimonios').val();
	
	$('[name=descricao]').val(lista);

	var vetor = lista.match(/[1-9]\d{5}/g);

	if (vetor != null) {
		
		$('#msgPatr div[role=alert]').remove();

		$('#txtPatrimonios').prop('readonly','true');

		$('#tblPatrimonios tbody tr').remove();
		$('#tblPatrimoniosAbertos tbody tr').remove();

		$('#btnVerificaPatrimonios').hide();	

		
		// verificando duplicatas...
		
		duplicado = false;

		for (i = 0; i < vetor.length - 1; i++) {

			x = vetor[i];

			for (j = i + 1; j < vetor.length; j++) {

				if (x == vetor[j]) {

					duplicado = true;
					break;
				}
			}

		}

		if (duplicado == true) {

			$('#listaVerificada div').remove();
			$('[for="listaPatrimonios"] .spinner-border').remove();
			
			
			$("#msgPatr").append("<div class=\"alert alert-warning alert-dismissible fade show\" role=\"alert\">Existem patrimônios duplicados na lista!<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button></div>");
			
			$("#txtPatrimonios").removeAttr('readonly');
			$("#txtPatrimonios").focus();
			$("#btnAlteraPatrimonios").hide();
			$('#btnVerificaPatrimonios').show();
			$('#divTabelaPatrimonios').hide();
		}
		else {
			
			criaTabelaPatrimonios(vetor, url);

		}
		
	}

});


$("#btnRemovePatrimonios").click(function() {

	$('#tblPatrimoniosAbertos tbody tr').remove();
	$('#divTabelaChamadosAbertos').hide();
	$('#tblInserviveis tbody tr').remove();
	$('#divTabelaInserviveis').hide();
	$("#btnRemovePatrimonios").hide();

	listaVerificada = true;

	$('#txtPatrimonios').val(vetorListaOK);

	if ($('#txtPatrimonios').val() == '') {
		$("#txtPatrimonios").removeAttr('readonly');
		$("#txtPatrimonios").focus();
		$('#btnVerificaPatrimonios').show();

	}

	vetorListaOK = [];
	
});
	

$("#btnAlteraPatrimonios").click(function() {

	$('#tblPatrimonios tbody tr').remove();
	$('#tblPatrimoniosAbertos tbody tr').remove();
	$('#btnVerificaPatrimonios').show();
	$("#btnAlteraPatrimonios").hide();
	$('#txtPatrimonios').removeAttr('readonly');
	$("#txtPatrimonios").focus();
	$('#divTabelaPatrimonios').hide();
	$('#divTabelaChamadosAbertos').hide();

	listaVerificada = false;

});

//--------- FIM Verificaçao da lista de patrimonios ---



// -- verificando se vai ter anexo

var tem_anexo = 0;

$('#chkAnexo').on('click', function() {

	if (tem_anexo == 0) {
		$('#divAnexo').append("<input type=\"file\" class=\"form-control-file\" accept=\".gif,.jpg,.png,.pdf,.doc,.docx,.xls,.xlsx,.odt,.ods,.jpeg,.txt\" name=\"anexo\">");
		$('#chkAnexo').val(1);
		tem_anexo = 1
	} else {
		$("#divAnexo input[name=anexo]").remove();
		$('#chkAnexo').val(0);
		tem_anexo = 0
	}
	


});

//------------------ SUBMIT DA ABERTURA DE CHAMADO --------------


$('#frmAbrirChamado').on('submit', 

function(e) {
	
	e.preventDefault();
	
	}).validate ({
		rules: {
			nome_solicitante: "required",
			nome_local: "required",
			telefone: {
				required: true, 
				digits: true,
				minlength: 3,
			},
			listaPatrimonios: {
				required: function() {
					if ($('#flagPrecisaPatrimonio').val() == 1 && $('#id_fila').val() != 6) { //bypass da fila Solicitacao de Equipamentos
						return true;
					} else {
						return false;
					}
				},
				minlength: 6,
				maxlength: 2000
			},
			descricao: {
				required: true,
				maxlength: 2000,
				minlength: 10,
				normalizer: function(value) {
					return $.trim(value);
				}
			}
		},
		messages: {
			nome_solicitante: "Campo obrigatório!",
			nome_local: "Campo obrigatório!",
			telefone: {
				required: "Campo obrigatório!",
				digits: "Somente dígitos (0-9)!",
				minlength: "Mínimo 3 dígitos!"
			} ,
			descricao: {
				required: "Campo obrigatório!",
				minlength: "Descrição insuficiente!",
				maxlength: "Tamanha máximo excedido!"
			},

			listaPatrimonios: {
				required: "Campo obrigatório!",
				minlength: "Informe pelo menos 1 patrimônio!",
				maxlength: "Tamanha máximo excedido!"
			},
		},
		submitHandler: function(form) {
			var script_url = base_url + "chamado/registrar_chamado";
	
			var dados = new FormData(form);
			
			$.ajax({
					
					url: script_url,
					type: 'POST',
					data: dados,
					contentType: false,
					cache: false,
					processData: false,
					beforeSend: function () {

						if (listaVerificada == false && $( "#flagPrecisaPatrimonio" ).val() == 1) {

							$("#msg div[id=alerta]").remove();
							
							$("#msg").append("<div id=\"alerta\" class=\"alert alert-warning alert-dismissible\">");
							$("#alerta").append("<a href=\"#\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">&times;</a>É necessário verificar a lista de patrimônios!");
							$("#btnVerificaPatrimonios").focus();

							targetOffset = $('#msg').offset().top;
			
							$('html, body').animate({ 
								scrollTop: targetOffset - 100
							}, 200);
							
							return false;
						}

						

						$('#btnAbrirChamado').prop("disabled","true");
						
					},
				success: function(msg) {

					$("#msg div[id=alerta]").remove();
					
					$("#msg").append(msg);

					listaVerificada = false;

					if (msg.includes('anexo') == false && msg.includes('Local') == false) {

						$(form).trigger('reset'); //só resetar o form se não houver erros no upload ou no anexo
					}
					else {

						if (msg.includes('Local')) {

							$('input[name=nome_local').focus();
						}

						if (msg.includes('anexo')) {

							$('input[name=anexo').focus();
						}

						listaVerificada = true;
						$('#btnAbrirChamado').removeAttr("disabled");
					}
					
					
					if ($( "#flagPrecisaPatrimonio" ).val() == 0) {

						timeout = setTimeout(function() {
							$('#btnAbrirChamado').removeAttr("disabled");
			
						},10000);
					}

					else {

						$('#btnAbrirChamado').removeAttr("disabled");

						if (listaVerificada == false) {

							$('#tblPatrimonios tr').remove();
							$('#btnVerificaPatrimonios').show();
							$("#btnAlteraPatrimonios").hide();
							$('#txtPatrimonios').removeAttr('readonly');
							$("#txtPatrimonios").focus();
							$( "#divTabelaPatrimonios" ).hide();
						}
							
					}

					targetOffset = $('#msg').offset().top;
			
					$('html, body').animate({ 
						scrollTop: targetOffset - 100
					}, 200);

					msg = null;
	
				
				},
				error: function(xhr, ajaxOptions, thrownError) {
					
					$("#msg").prepend("<div id=\"alerta\" class=\"alert alert-danger alert-dismissible\">");
					$("#alerta").append("<a href=\"#\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">&times;</a>" + thrownError);
					
					
					targetOffset = $('#msg').offset().top;
			
					$('html, body').animate({ 
						scrollTop: targetOffset - 100
					}, 200);

					$('#btnAbrirChamado').removeAttr("disabled");
				}
	
			});

	return false;
			
	}
});

//------------- CARREGA CHAMADO ---------------------


function carregaChamado(p_id_chamado, sem_patrimonios) {

	//atualiza os dados do chamado
	
	document.title = "Chamado #" + p_id_chamado + " - Sigat";

	var p_id_responsavel = null;
	

	$('#botoesAtendimento #btnModalRegistroEntrega').remove();
	$('#botoesAtendimento #baixarTermoEntrega').remove();
	$('#botoesAtendimento #baixarTermoResp').remove();
	$('#botoesAtendimento #baixarLaudoIns').remove();
	$('#botoesAtendimento #btnModalRegistro').remove();
	$('#botoesAtendimento #btnEncerrarChamado').remove();
	$('#botoesAtendimento #btnModalEquipamentos').remove();
	
	$('#botoesChamado hr').hide();

	$.ajax({
		url: base_url + 'backend/chamado',
		dataType: 'json',
		async: true,
		data: { id_chamado: p_id_chamado },
		success: function(data) {

			//preencher os campos conforme o json
			
			$('input[name=fila]').val(data.nome_fila_chamado);
			$('input[name=data_chamado]').val(data.data_chamado);
			$('input[name=status]').val(data.status_chamado);
			$('input[name=nome_solicitante]').val(data.nome_solicitante_chamado);
			$('input[name=telefone]').val(data.telefone_chamado);
			$('input[name=nome_local]').val(data.nome_local);
			$('input[name=id_fila_ant]').val(data.id_fila);
			$('textarea[name=descricao]').val(data.descricao_chamado);

			if (data.id_responsavel == null)  {
				$('select[name=id_responsavel]').empty();
			}
			else {
				p_id_responsavel = data.id_responsavel;
				$('select[name=id_responsavel]').html('<option value="' + data.id_responsavel +'">' + data.nome_responsavel + '</option>');
			}
			
			fila_atual = data.id_fila; //variavel global fila_atual
			var entrega = data.entrega_chamado;
			var status_chamado = data.status_chamado;

			if (data.id_fila == 6) {

				if (!$('#botoesAtendimento #btnModalEquipamentos').length && data.id_responsavel == g_id_usuario) {
					$('#botoesAtendimento').prepend(
						"<button type=\"button\" id=\"btnModalEquipamentos\" class=\"btn btn-primary\""+
						" data-toggle=\"modal\" data-target=\"#modalEquipamentos\" data-chamado=\"" + p_id_chamado +
						"\"><i class=\"fas fa-plus-square\"></i> Adicionar Equipamentos</button> ");
				}

				if (!$('#botoesAtendimento #btnModalRegistro').length && data.id_responsavel == g_id_usuario) {
								
					$('#botoesAtendimento').append(
						"<button type=\"button\" id=\"btnModalRegistro\" class=\"btn btn-primary\""+
						" data-toggle=\"modal\" data-target=\"#modalRegistro\" data-chamado=\"" + p_id_chamado +
						"\"><i class=\"far fa-file-alt\"></i> Nova Interação</button> ");
				}
			}

			if (data.equipamentos.length > 0 && (data.id_fila == 6 || data.id_fila == 3)) {

				$('#tblEquipamentosChamado tbody').html('');

				data.equipamentos.forEach(function(equip) {
				
					$('#tblEquipamentosChamado tbody').append(
						"<tr>" +
						"<td>" + equip.num_equipamento + "</td>" +
						"<td>" + equip.desc_equipamento + "</td>" +
						"<td>" + equip.status_equipamento + "</td>" +
						"</tr>");
				});

				if (!$('#botoesAtendimento #btnModalRegistro').length && data.id_responsavel == g_id_usuario) {
								
					$('#botoesAtendimento').append(
						"<button type=\"button\" id=\"btnModalRegistro\" class=\"btn btn-primary\""+
						" data-toggle=\"modal\" data-target=\"#modalRegistro\" data-chamado=\"" + p_id_chamado +
						"\"><i class=\"far fa-file-alt\"></i> Nova Interação</button> ");
				}

			}

			else {

				$('#listaEquipamentosChamado').hide();
			}
			
			
			if (sem_patrimonios != true /*&& data.requer_patrimonio_fila == 1 */) { //puxar a descricao de cada patrimonio via api json do SIM
				
				$('#tblPatrimonios tbody').html('');

				data.patrimonios.forEach(function(patrimonio) {
				
					$.ajax({
						url: base_url + 'backend/patrimonio?url='+ url + patrimonio.num_patrimonio + '&q=' + patrimonio.num_patrimonio,
						dataType: 'json',
						async: true,
						success: function(data) {

							var status_patrimonio = null;
							if (patrimonio.status_patrimonio_chamado == 'FALHA') { // se o patrimonio estiver como falha de entrega, mostrar como aberto
								status_patrimonio = 'ABERTO';
							}
							else {
								status_patrimonio = patrimonio.status_patrimonio_chamado;
							}

							$('#tblPatrimonios tbody').append(
							"<tr>" +
							"<td>" + patrimonio.num_patrimonio + "</td>" +
							"<td>" + data.descricao + "</td>" +
							"<td>" + status_patrimonio + "</td>" +
							"</tr>");


							if ((patrimonio.status_patrimonio_chamado == 'ENTREGA' || entrega == 1) && status_chamado == 'ABERTO') { 
								
								//se existerem entregas no chamado, criar o botao Termo de Entrega / Termo de Responsabilidade
								
								$('#botoesAtendimento #btnModalRegistroEntrega').remove();
								$('#botoesAtendimento #baixarTermoEntrega').remove();
								$('#botoesAtendimento #baixarTermoResp').remove();
								$('#botoesAtendimento').append(
								"<button type=\"button\" id=\"btnModalRegistroEntrega\" class=\"btn btn-success\" data-toggle=\"modal\" data-chamado=\"" 
								+ p_id_chamado + "\" data-target=\"#modalRegistroEntrega\"><i class=\"fas fa-file-signature\"></i> Registrar entrega</button> " +
								"<a href=\"" + base_url + "chamado/gerar_termo/"
								+ p_id_chamado + "\" id=\"baixarTermoEntrega\" role=\"button\" class=\"btn btn-info\">" + 
								"<i class=\"fas fa-file-download\"></i> Baixar Termo de Entrega</a> " + 
								"<a href=\"" + base_url + "chamado/gerar_termo_resp/" +
								+ p_id_chamado + "\" id=\"baixarTermoResp\" role=\"button\" class=\"btn btn-info\">" + 
								"<i class=\"fas fa-file-download\"></i> Baixar Termo de Responsabilidade</a>");

							} 

							if ((patrimonio.status_patrimonio_chamado == 'ABERTO' || patrimonio.status_patrimonio_chamado == 'ESPERA' || 
								patrimonio.status_patrimonio_chamado == 'FALHA')
								&& (p_id_responsavel == g_id_usuario)) {
								

								if (!$('#botoesAtendimento #btnModalRegistro').length) {
									
									$('#botoesAtendimento').prepend(
										"<button type=\"button\" id=\"btnModalRegistro\" class=\"btn btn-primary\""+
										" data-toggle=\"modal\" data-target=\"#modalRegistro\" data-chamado=\"" + p_id_chamado +
										"\"><i class=\"far fa-file-alt\"></i> Nova Interação</button> ");
								}	
							} 		
						}
					})
				});

				

			} else { // criar o botao de Registrar Antendimento sem patrimonios
				if (!$('#botoesAtendimento #btnModalRegistro').length && data.id_responsavel == g_id_usuario) {
									
					$('#botoesAtendimento').append(
						"<button type=\"button\" id=\"btnModalRegistro\" class=\"btn btn-primary\""+
						" data-toggle=\"modal\" data-target=\"#modalRegistro\" data-chamado=\"" + p_id_chamado +
						"\"><i class=\"far fa-file-alt\"></i> Nova Interação</button> ");
					}

						
			}		
				
			if(data.status_chamado != 'ABERTO') {
				$('#botoesAtendimento #btnModalRegistro').remove();
				$('#botoesAtendimento #btnModalEquipamentos').remove();
			}

			


			// -------------------- PERMISSOES ----------------------------


			if (g_auto_usuario == 4 && data.status_chamado == 'FECHADO') { //somente MASTER encerra ou reabre o chamado

				if(!$('#btnEncerrarChamado').length) {

					$('#botoesAtendimento').prepend(
					'<button id="btnEncerrarChamado" onclick="encerrarChamado()" class="btn btn-success"><i class=\"far fa-check-circle\"></i> Encerrar chamado</button>');
				};
				//$('#btnReabrirChamado').show();

			}
			
			if (data.status_chamado != 'ABERTO') {  //se o chamado não estiver ABERTO, remover o botao Registrar Atendimento e Editar Chamado
				
				$('#btnEditarChamado').hide();
				$('#btnDesbloquearChamado').hide();
				$('#botoesChamado hr').hide();
			
			}

			else {
				if ((g_auto_usuario >=3 && data.id_responsavel != null) || data.id_responsavel == g_id_usuario  ) {

					$('#btnDesbloquearChamado').show();
					$('#btnEditarChamado').show();
					$('#botoesChamado hr').show();
	
	
				}

				if (data.id_responsavel == null && g_auto_usuario >= 3) { //se não houver responsavel e o usuario for ADM+

					$('#btnEditarChamado').show();
					$('#btnDesbloquearChamado').hide();
					$('#botoesChamado hr').show();
				}
	
				if (data.id_responsavel == null && g_auto_usuario <= 2) { //Tecnico
	
					$('#btnBloquearChamado').show();
					$('#btnEditarChamado').hide();
					$('#btnDesbloquearChamado').hide();
					$('#botoesChamado hr').show();
				}
	
				if (data.id_responsavel == null && g_auto_usuario >= 3) { //ADM +
	
					$('#btnBloquearChamado').show();
					$('#btnEditarChamado').show();
					$('#btnDesbloquearChamado').hide();
					$('#botoesChamado hr').show();
				}
	
				if (data.id_responsavel != null && g_auto_usuario >= 3) { //ADM +
	
					$('#btnBloquearChamado').hide();
					$('#btnEditarChamado').show();
					$('#btnDesbloquearChamado').show();
					$('#botoesChamado hr').show();
				}
	
				if (g_id_usuario == data.id_responsavel && g_auto_usuario <= 2) { //Tecnico 
	
					$('#btnBloquearChamado').hide();
					$('#btnEditarChamado').show();
				}
			}
		}
	});
}




// ---------------- INTERACOES --------------------

function removeInteracao(p_id_interacao, p_id_chamado) {

	var bloqueado = false;


	$.ajax({
		
		url: base_url + 'interacao/remover_interacao',
		type: 'POST',
		data: {id_interacao: p_id_interacao},

		beforeSend: function() {

			$('#btnDesfazer').prop('disabled','true');

			

			$.ajax({
				url: base_url + 'backend/interacao',
				type: 'GET',
				dataType: 'text',
				async: false, //necessario para evitar remoção nao autorizada
				data: 
				{
					id_chamado: g_id_chamado, 
					id_usuario: g_id_usuario,
					id_interacao: p_id_interacao
				},
				success: function(data) {
		
					if (data === '1' ) {
						bloqueado = true;

					}
				}
			});

			if (bloqueado) {

				$('#btnDesfazer').removeAttr('disabled');
				alert('Operação não permitida!');
				return false;
			}
				


		},
		
		success: function() {

			atualizaInteracoes(p_id_chamado);
			//$('#btnDesfazer').removeAttr('disabled');
			carregaChamado(p_id_chamado);
			


		}

	});
}

$('#frmInteracao').on('submit', function(e) { //submit da interacao
	
	e.preventDefault();

	var script_url = base_url + "chamado/registrar_interacao";

	var p_txtInteracao = $('textarea[name=txtInteracao]').summernote('code');
	var p_id_chamado = $('input[name=id_chamado]').val();
	var p_id_fila_ant = $('input[name=id_fila_ant]').val();
	var p_tipo = $('select[name=tipo]').val();
	var p_id_fila = $('select[name=id_fila]').val();

	var p_vetor_patrimonios_atendidos = [];

	$('input[class=chkPatri]').each(function() {

		if ($(this).is(':checked')) {
			p_vetor_patrimonios_atendidos.push($(this).val());
		}
		

	});

	$.ajax({
			
			url: script_url,
			type: 'POST',
			data: {
				txtInteracao: p_txtInteracao,
				id_chamado: p_id_chamado,
				tipo: p_tipo,
				id_fila: p_id_fila,
				id_fila_ant: p_id_fila_ant,
				patrimonios_atendidos: p_vetor_patrimonios_atendidos,
				equipamentos_atendidos: p_equips,
				id_usuario: g_id_usuario
			},
			beforeSend: function () {

				if ((p_vetor_patrimonios_atendidos.length == 0 && p_id_fila_ant == 3 && p_tipo == 'INSERVIVEL') || (p_vetor_patrimonios_atendidos.length == 0 && (p_tipo == 'REM_ESPERA' || p_tipo == 'ESPERA' ))) {

					$('#modalRegistro .modal-body').prepend(
					"<div class=\"alert alert-warning fade show\" role=\"alert\">" +
						"Selecione os equipamentos!" +
						"<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">" +
							"<span aria-hidden=\"true\">&times;</span>" +
						"</button>" +
					"</div>");
					
					
					return false;
				}
				
				if ($('textarea[name=txtInteracao]').summernote('isEmpty')) {
					
					$('#modalRegistro .modal-body').prepend(
						"<div class=\"alert alert-warning fade show\" role=\"alert\">" +
							"O texto não pode ficar em branco!" +
							"<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">" +
								"<span aria-hidden=\"true\">&times;</span>" +
							"</button>" +
						"</div>");

					return false;
				}

				

				$('#btnRegistrarInteracao').prop("disabled","true");
				
			},
		success: function(msg) {
			
			$('#btnRegistrarInteracao').removeAttr("disabled");
			atualizaInteracoes(p_id_chamado);
			$('textarea[name=txtInteracao]').summernote('reset');
			$('#modalRegistro').modal('hide');
			carregaChamado(p_id_chamado);
			//console.log(p_equips)
			p_equips = [,]
		
		},
		error: function(xhr, ajaxOptions, thrownError) {

			alert(xhr + thrownError);

			$('#btnRegistrarInteracao').removeAttr("disabled");
		}

	});

	return false;
			
});





function atualizaInteracoes(id_chamado) { //carrega as interacoes

	$.post(base_url + "backend/interacoes",{id : id_chamado})
		.done(function(dados) {

			$("#interacoes").html(dados);
		});

}


// -------------------- ENTREGA ---------------------------------

function verificaEntrega() { //confirmando se a entrega foi realizada

	if ($('input[name=confirmaEntrega]:checked').val() == 1) {

		$('#divEntrega').show();
		$('#divFalhaEntrega').hide();
		$('#btnRegistrarEntrega').show();
		$('#btnRegistrarEntrega span').html(' Registrar entrega');
	}

	else {

		$('#divEntrega').hide();
		$('#divFalhaEntrega').show();
		$('#btnRegistrarEntrega').show();
		$('#btnRegistrarEntrega span').html(' Registrar falha de entrega');
		$('#txtFalhaEntrega').summernote({ //inicialização do SummerNote 

			toolbar: [
				// [groupName, [list of button]]
				['style', ['bold', 'italic', 'underline', 'clear']],
				['font', ['strikethrough', 'superscript', 'subscript']],
				['fontsize', ['fontsize']],
				['color', ['color']],
				['para', ['ul', 'ol', 'paragraph']],
				['height', ['height']]
			  ],
			height: 200,
			lang: 'pt-BR',
			dialogsInBody: true,
			disableDragAndDrop: true,
		});
	}
}



$('#frmRegistroEntrega').on('submit', function(e) {  //submit do registro de entrega

	e.preventDefault();

	const script_url = base_url + "interacao/registrar_entrega";

	const script_url_falha = base_url + "interacao/registrar_falha_entrega";

	var form = $(this)[0];

	var p_id_chamado = $('input[name=id_chamado]').val();
	 
	var opcao = $('input[name=confirmaEntrega]:checked').val();

	//;

	if (opcao == 1) {

		$.ajax({
		
			url: script_url,
			type: 'POST',
			data: new FormData(form),
			contentType: false,
			processData: false,
	
			beforeSend: function () {

				if ($('input[name=termo]').val() == '') {

					$('#divEntrega').prepend(
						"<div class=\"alert alert-warning fade show\" role=\"alert\">" +
							"Está faltando o termo!" +
							"<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">" +
								"<span aria-hidden=\"true\">&times;</span>" +
							"</button>" +
						"</div>");
					
					$('input[name=termo]').focus();
	
					return false;
				}
				
				if ($('input[name=nome_recebedor]').val() == '') {

					$('#divEntrega').prepend(
						"<div class=\"alert alert-warning fade show\" role=\"alert\">" +
							"Informe o nome do recebedor!" +
							"<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">" +
								"<span aria-hidden=\"true\">&times;</span>" +
							"</button>" +
						"</div>");
					
					$('input[name=nome_recebedor]').focus();
	
					return false;
				}
	
				$('#btnRegistrarEntrega').prop("disabled","true");
				
			},
	
			success: function(msg) {

				if (msg.includes('ok')) {

					
					atualizaInteracoes(p_id_chamado);
					$('#modalRegistroEntrega').modal('hide');
					carregaChamado(p_id_chamado);
					$('#btnRegistrarEntrega').removeAttr("disabled");

					
				
				} else {

					
					$('#divEntrega').prepend(
						"<div class=\"alert alert-warning fade show\" role=\"alert\">" +
							"<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">" +
								"<span aria-hidden=\"true\">&times;</span>" +
							"</button>" +
							msg +
						"</div>");

						$('#btnRegistrarEntrega').removeAttr("disabled");

					

				} 
			}
	
		});


	} else { // falha na entrega

		

		var desc = $('#txtFalhaEntrega').summernote('code');
		

		$.ajax({
		
			url: script_url_falha,
			type: 'POST',
			data: {
				descr_falha: desc,
				id_chamado: p_id_chamado
			 },
	
			beforeSend: function () {

				if ($('#txtFalhaEntrega').summernote('isEmpty')) {

					$('#divFalhaEntrega').prepend(
						"<div class=\"alert alert-warning fade show\" role=\"alert\">" +
							"Preencha a descrição!" +
							"<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">" +
								"<span aria-hidden=\"true\">&times;</span>" +
							"</button>" +
						"</div>");
	
					return false;
				}
	
				$('#btnRegistrarEntrega').prop("disabled","true");
				
			},
	
			success: function() {

				atualizaInteracoes(p_id_chamado);
				$('#modalRegistroEntrega').modal('hide');
				carregaChamado(p_id_chamado);
				$('#btnRegistrarEntrega').removeAttr("disabled");
				
			},
			error: function() {

				atualizaInteracoes(p_id_chamado);
				$('#modalRegistroEntrega').modal('hide');
				carregaChamado(p_id_chamado);
				$('#btnRegistrarEntrega').removeAttr("disabled");
				
			}
	
		});

	}
	

	

});

// -------------- FIM ENTREGA ------------


// --------------- ALTERACOES CHAMADO -----------

function encerrarChamado() {

	var btn = $('#btnEncerrarChamado');

	if (g_auto_usuario == 4) {
		if (confirm('Deseja realmente encerrar? Isso não poderá ser desfeito!')) {
			
			$.ajax({
				type: 'post',
				url: base_url + 'chamado/encerrar_chamado',
				data: {
					id_chamado: g_id_chamado,
					id_usuario: g_id_usuario

				},
				beforeSend: function() { btn.prop('disabled','true') },
				success: function(msg) {
					if (msg != 0) { // se for autorizado

						btn.removeAttr('disabled');
						atualizaInteracoes(g_id_chamado);
						carregaChamado(g_id_chamado, true);
					}
				}
			});
		}

	}
	
}

// -------------- VERIFICA BLOQUEIO ---------------

var bloqueado = false;

$('#btnBloquearChamado').on('click', function(e) {

	e.preventDefault();
	$.ajax({
		type: "POST",
		url: base_url + 'backend/atualiza_responsavel',
		data: {
			id_chamado: g_id_chamado,
			id_usuario: g_id_usuario,
			tipo: 'b'
		},
		beforeSend: function() {
			
			$('#btnBloquearChamado').prop('disabled','true');

			$.ajax({
				type: "GET",
				async: true, //
				url: base_url + 'backend/chamado',
				data: {
					id_chamado: g_id_chamado,
				},
				dataType: 'JSON',
				complete: function(data) {
		
					if (data.id_responsavel != null) {
						$('#btnBloquearChamado').removeAttr('disabled');
						$('#btnBloquearChamado').hide();
		
						alert('Chamado já está bloqueado por ' + data.nome_responsavel);
						carregaChamado(g_id_chamado, true);
						bloqueado = true;
					}
				}
			});
			if(bloqueado) {
				return;
			}
		},

		success: function() {
			$('#btnBloquearChamado').removeAttr('disabled');
			carregaChamado(g_id_chamado,true);
		},
	  });
	


});


$('#btnDesbloquearChamado').on('click', function(e) {

	e.preventDefault();
	$.ajax({
		type: "POST",
		url: base_url + 'backend/atualiza_responsavel',
		data: {
			id_chamado: g_id_chamado,
			id_usuario: g_id_usuario,
			tipo: 'd'
		},
		beforeSend: function() {
			$('#btnDesbloquearChamado').prop('disabled','true');
		},
		success: function(msg) {

			if (msg == 0) {
				$('#btnDesbloquearChamado').removeAttr('disabled');
				$('#btnDesbloquearChamado').hide();
				carregaChamado(g_id_chamado,true);
			} else {

				alert('Operação não permitida: o chamado está fechado!')
			}
			
		},
	  });
	


});

// ---------------- FIM BLOQUEIO --------------------------------


$('#btnEditarChamado').on('click', function(e) {

	e.preventDefault();
	$('#btnBloquearChamado').hide();
	$('#btnDesbloquearChamado').hide();
	$('#btnEditarChamado').hide();
	
	$.ajax({
		url: base_url + 'backend/chamado',
		dataType: 'json',
		
		data: { id_chamado: g_id_chamado },
		success: function(data) {

			if(g_auto_usuario >= 3) {

				$('#frmEditarChamado select[name=id_responsavel]').removeAttr('disabled');
				$('#frmEditarChamado select[name=id_responsavel]').html('');
				
				$.ajax({
					url: base_url + 'backend/usuarios',
					dataType: 'json',
					type: 'post',
					success: function(data) {

						$('#frmEditarChamado select[name=id_responsavel]').append(
							'<option></option>');

						data.forEach(function(usuario) {

							$('#frmEditarChamado select[name=id_responsavel]').append(
								'<option value="' + usuario.id_usuario + '">' + usuario.nome_usuario + '</option>');


						});

					},
				});

			}

			if ((data.id_responsavel == g_id_usuario || g_auto_usuario >= 3 )) {

				$('#frmEditarChamado input[name=nome_solicitante]').removeAttr('disabled');
				$('#frmEditarChamado input[name=telefone]').removeAttr('disabled');
				$('#frmEditarChamado input[name=nome_local]').removeAttr('disabled');
				$('#frmEditarChamado button[type=submit]').removeAttr('hidden');
				$('#frmEditarChamado button[type=submit]').removeAttr("disabled");
				$('#frmEditarChamado #btnCancelarEdicao').removeAttr('hidden');	
				
			} else {

				alert('Chamado bloqueado!');
				$('#btnEditarChamado').show();
			}

			

			
		}

	});
	
	
	

	
});

$('#btnCancelarEdicao').on('click', function(e) {

	carregaChamado(g_id_chamado,true);


	e.preventDefault();

	$('#frmEditarChamado input[name=nome_solicitante]').prop('disabled','true');
	$('#frmEditarChamado input[name=telefone]').prop('disabled','true');
	$('#frmEditarChamado input[name=nome_local]').prop('disabled','true');
	$('#frmEditarChamado button[type=submit]').prop('hidden','true');
	$('#frmEditarChamado select[name=id_responsavel]').attr('disabled','true');
	$('#frmEditarChamado #btnEditarChamado').show();
	
	$(this).prop('hidden','true');

	
});

$('#frmEditarChamado').on('submit', function(e) {

	e.preventDefault();


	}).validate ({
		rules: {
			nome_solicitante: "required",
			nome_local: "required",
			telefone: {
				required: true, 
				digits: true,
				minlength: 3,
			},
			
		},
		messages: {
			nome_solicitante: "Campo obrigatório!",
			nome_local: "Campo obrigatório!",
			telefone: {
				required: "Campo obrigatório!",
				digits: "Somente dígitos (0-9)!",
				minlength: "Mínimo 3 dígitos!"
			} ,
		},
		submitHandler: function(form) {
			var script_url = base_url + "chamado/alterar_chamado";

			var dados = new FormData(form);

			
			
			$.ajax({
					
				url: script_url,
				type: 'POST',
				data: dados,
				contentType: false,
				cache: false,
				processData: false,
				beforeSend: function () {

					var fechado = false;

					$('#frmEditarChamado input[type=submit]').prop("disabled","true");
					$.ajax({
						url: base_url + 'backend/chamado',
						dataType: 'json',
						
						data: { id_chamado: g_id_chamado },
						success: function(data) {
							if(data.status_chamado = 'FECHADO') {

								fechado = true;
							}

						}
					});

					if(fechado) {
						alert("Operação não permitida: chamado fechado!");
						return false;

					}
						
						
				},
				success: function(msg) {
					carregaChamado(g_id_chamado,true);
					atualizaInteracoes(g_id_chamado);
					
					$('#frmEditarChamado #alerta').prepend(msg);

					setTimeout(function() {
						$('#msg_sucesso').alert('close')
					},2500);

					$('#frmEditarChamado input[name=nome_solicitante]').prop('disabled','true');
					$('#frmEditarChamado select[name=id_responsavel]').prop('disabled','true');
					$('#frmEditarChamado input[name=telefone]').prop('disabled','true');
					$('#frmEditarChamado input[name=nome_local]').prop('disabled','true');
					$('#frmEditarChamado button[type=submit]').prop('hidden','true');
					$('#frmEditarChamado #btnEditarChamado').show();
					$('#frmEditarChamado #btnCancelarEdicao').prop('hidden','true');
			
				},
				error: function(xhr, ajaxOptions, thrownError) {
					
					alert(thrownError);

					//$('#frmEditarChamado input[type=submit]').removeAttr("disabled");
				}

			});
		}

	});


	// ------------- SOLICITACAO DE EQUIPAMENTOS ------------- 

	async function criaTabelaPatrimoniosEquip(vetor,url) {

		vetorListaOK = [];
		
		var vetorPatrInv = [];
	
		var vetorPatrIns = [];
		var vetorPatrInsDesc = [];
		var vetorPatrInsChamado = [];
	
		var vetorPatrOK = [];
		var vetorPatrOKDesc = [];
	
		var vetorPatrAberto = [];
		var vetorPatrAbertoDesc = [];
		var vetorPatrAbertoChamado = [];
		
		$('[for="listaPatrimoniosEquip"]').append("<div class=\"spinner-border spinner-border-sm\" role=\"status\">&nbsp;&nbsp;<span class=\"sr-only\">Carregando...</span\></div>");
		
		
		for (var item of vetor) { 
	
			await fetch(base_url + 'backend/patrimonio?url='+ url + item + '&q=' + item)
				
			.then(response => response.json())
			
			.then(data => {
					
					if (data.descricao != null) {
	
						if (data.inservivel) { // verificando se não é inservivel
							vetorPatrIns.push(item);              
							vetorPatrInsDesc.push(data.descricao);
							vetorPatrInsChamado.push(data.inservivel['id_chamado_patrimonio']);
						} else {
							vetorPatrOK.push(item);              // se houver descricao, o patrimonio é valido
							vetorPatrOKDesc.push(data.descricao);
						}
						
						if (data.chamado != null) {
							
							vetorPatrAberto.push(item); // se houver chamado, enfileirar nos vetores PatrAberto (num_patrimonio, descricao, id_chamado)
							vetorPatrAbertoDesc.push(data.descricao);
							vetorPatrAbertoChamado.push(data.chamado);
	
							vetorPatrOK.pop(); //remover dos vetores de patrimonios validos
							vetorPatrOKDesc.pop();
	
						}
	
					} else {
						vetorPatrInv.push(item); //se não houver descricao, o patrimonio é invalido e será enfileirado no vetorPatriInv
	
					}
				
				}
			);
	
		}
	
	
		if (vetorPatrInv.length > 0) { //se houverem patrimonios invalidos...
	
			$('#listaVerificadaEquip div').remove();
			$('[for="listaPatrimoniosEquip"] .spinner-border').remove();
			
			vetorPatrInv.forEach(function (item){
				$("#msgPatrEquip").append("<div class=\"alert alert-warning alert-dismissible fade show\" role=\"alert\">Patrimônio inválido: <strong>" + item + "</strong><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button></div>");
			});
			
			$("#txtPatrimoniosEquip").removeAttr('readonly');
			$("#txtPatrimoniosEquip").show();
			$("#txtPatrimoniosEquip").focus();
			$("#btnAlteraPatrimoniosEquip").hide();
			$('#btnVerificaPatrimoniosEquip').show();
			$('#divTabelaPatrimoniosEquip').hide();
			
	
		} else { //se não houverem patrimonios inválidos ...
			
			$('[for="listaPatrimoniosEquip"] .spinner-border').remove();
	
			if (vetorPatrIns.length > 0) { // se houverem patrimonios inservie...
	
				$('#divTabelaInserviveisEquip').show();
				
				for (var i=0; i<vetorPatrIns.length; i++) {
	
					$('#tblInserviveisEquip tbody').append('<tr class=\"table-danger\"><td>' + vetorPatrIns[i] + 
					'</td><td>' + vetorPatrInsDesc[i] + '</td><td>#' +  vetorPatrInsChamado[i] + 
					' <a target=\"_blank\" href=\"' + base_url + 'chamado/' + vetorPatrInsChamado[i] + '\">Mais...</a></td></tr>');
	
				}
	
				listaVerificada = false;
	
				$('#btnRemovePatrimoniosEquip').show();
	
			}
			
			
			if (vetorPatrAberto.length > 0) { // se houverem patrimonios com chamado aberto...
	
				$('#divTabelaChamadosAbertosEquip').show();
				
				for (var i=0; i<vetorPatrAberto.length; i++) {
	
					$('#tblEquipAbertos tbody').append('<tr class=\"table-warning\"><td>' + vetorPatrAberto[i] + 
					'</td><td>' + vetorPatrAbertoDesc[i] + '</td><td>#' +  vetorPatrAbertoChamado[i] + 
					' <a target=\"_blank\" href=\"' + base_url + 'chamado/' + vetorPatrAbertoChamado[i] + 
					'"><small>Mais...</small></a></td></tr>');
				
				}	
	
				listaVerificada = false;
			
				$('#btnRemovePatrimoniosEquip').show();
			} 
			if (vetorPatrOK.length > 0) { //se houverem patrimonios válidos...
	
				$('#divTabelaPatrimoniosEquip').show();
				$("#btnAlteraPatrimoniosEquip").show();
				
				for (var i=0; i<vetorPatrOK.length; i++) {
	
					$('#tblPatrimoniosEquip tbody').append('<tr><td>' + vetorPatrOK[i] + 
					'</td><td>' + vetorPatrOKDesc[i] + '</td></tr>');
	
					vetorListaOK.push(vetorPatrOK[i]);
	
				}
	
				if (vetorPatrAberto.length == 0) {
					listaVerificada = true;
					
				}	

			}
		
		}
	
	}

	$("#btnVerificaPatrimoniosEquip").click(function() {

		var lista = $('#txtPatrimoniosEquip').val();
	
		var vetor = lista.match(/[1-9]\d{5}/g);
	
		if (vetor != null) {
			
			$('#msgPatrEquip div[role=alert]').remove();
	
			$('#txtPatrimoniosEquip').prop('readonly','true');
	
			$('#tblPatrimoniosEquip tbody tr').remove();
			$('#tblEquipAbertos tbody tr').remove();
			$('#tblInserviveisEquip tbody tr').remove();
	
			$('#btnVerificaPatrimoniosEquip').hide();	
	
			
			// verificando duplicatas...
			
			duplicado = false;
	
			for (i = 0; i < vetor.length - 1; i++) {
	
				x = vetor[i];
	
				for (j = i + 1; j < vetor.length; j++) {
	
					if (x == vetor[j]) {
	
						duplicado = true;
						break;
					}
				}
	
			}
	
			if (duplicado == true) {
	
				$('[for="listaPatrimoniosEquip"] .spinner-border').remove();
				
				
				$("#msgPatrEquip").append("<div class=\"alert alert-warning alert-dismissible fade show\" role=\"alert\">Existem patrimônios duplicados na lista!<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button></div>");
				
				$("#txtPatrimoniosEquip").removeAttr('readonly');
				$("#txtPatrimoniosEquip").focus();
				$("#btnAlteraPatrimoniosEquip").hide();
				$('#btnVerificaPatrimoniosEquip').show();
				$('#divTabelaPatrimoniosEquip').hide();
			}
			else {
				
				criaTabelaPatrimoniosEquip(vetor, url);
	
			}
			
		}
	
	});

	$("#btnRemovePatrimoniosEquip").click(function() {

		$('#tblPatrimoniosAbertosEquip tbody tr').remove();
		$('#divTabelaChamadosAbertosEquip').hide();
		$('#tblInserviveisEquip tbody tr').remove();
		$('#divTabelaInserviveisEquip').hide();
		$("#btnRemovePatrimoniosEquip").hide();

	
		$('#txtPatrimoniosEquip').val(vetorListaOK);
	
		if ($('#txtPatrimoniosEquip').val() == '') {
			$("#txtPatrimoniosEquip").removeAttr('readonly');
			$("#txtPatrimoniosEquip").focus();
			$('#btnVerificaPatrimoniosEquip').show();
	
		}
	
		vetorListaOK = [];
		
	});

	$("#btnAlteraPatrimoniosEquip").click(function() {

		$('#tblEquipamentos tbody tr').remove();
		$('#tblPatrimoniosAbertosEquip tbody tr').remove();
		$('#btnVerificaPatrimoniosEquip').show();
		$("#btnAlteraPatrimoniosEquip").hide();
		$('#txtPatrimoniosEquip').removeAttr('readonly');
		$("#txtPatrimoniosEquip").focus();
		$('#divTabelaPatrimoniosEquip').hide();
		$('#divTabelaChamadosAbertosEquip').hide();
	
		listaVerificada = false;
	
	});

	//-------- EQUIP SEM PATRIMONIO ---------

	var vEquip = [
		[,]
	];

	function zeraEquipamentos() {
		vEquip = [
			[,]
		];	
	};

	

	$('#btnAdcEquip').on('click', function() { //ADD EQUIP
		$("#msgEquip div[id=alerta]").remove();

		if($('#txtNumSerieEquip').val() != '' && $('#txtDescEquip').val() != '') {
			vEquip.push([$('#txtNumSerieEquip').val(),$('#txtDescEquip').val().toUpperCase()]);
			$('#tblEquipamentos tbody').append(
			'<tr><td>' + $('#txtNumSerieEquip').val() + '</td><td>' 
			+ $('#txtDescEquip').val().toUpperCase() + '</td></tr>');

			$('#txtNumSerieEquip').val(''); 
			$('#txtDescEquip').val('');
		}

	});

	$('#btnLimpaEquip').on('click', function() { //LIMPA EQUIP
		$("#msgEquip div[id=alerta]").remove();
		
		$('#tblEquipamentos tbody').empty();

		zeraEquipamentos();

	});

	

	//-------- SUBMIT DOS EQUIPAMENTOS ---- 

	$('#frmEquipamentos').on('submit', function(e){
		e.preventDefault();

		var dados = new FormData($('#frmEquipamentos')[0]);

		if (vEquip.length > 0) { //se houverem equipamentos sem patrimonio . . . 
			var json_equip = JSON.stringify(vEquip);
			dados.append('json_equip',json_equip);
			
		}

		$.ajax({
					
			url: base_url + 'interacao/adicionar_equipamentos',
			type: 'POST',
			data: dados,
			contentType: false,
			cache: false,
			processData: false,
			beforeSend: function() {
				$('#btnAdicionarEquipamentos').attr('disabled','true');

				if (listaVerificada == false && $('#chkEquipamentos').is(':unchecked')) {
					$('#btnAdicionarEquipamentos').removeAttr('disabled');
					
					$("#msgPatrEquip div[id=alerta]").remove();
							
					$("#msgPatrEquip").append("<div id=\"alerta\" class=\"alert alert-warning alert-dismissible\">");
					$("#alerta").append("<a href=\"#\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">&times;</a>É necessário verificar a lista de patrimônios!");
					
					$("#btnVerificaPatrimoniosEquip").focus();

					$('#btnAdicionarEquipamentos').removeAttr('disabled');
					
					return false;
				}

				if ($('#chkEquipamentos').is(':checked') && vEquip.length == 0) {

					$("#msgEquip div[id=alerta]").remove();
					
					$("#msgEquip").append("<div id=\"alerta\" class=\"alert alert-warning alert-dismissible\">");
					$("#alerta").append("<a href=\"#\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">&times;</a>Adicione pelo menos 1 equipamento!");

					$('#btnAdicionarEquipamentos').removeAttr('disabled');
					
					return false;
				}
			},
			success: function() {
				$('#modalEquipamentos').modal('hide');
				zeraEquipamentos();
				atualizaInteracoes(g_id_chamado);
				carregaChamado(g_id_chamado);
				listaVerificada = false;
				$('#btnAdicionarEquipamentos').removeAttr('disabled');
				$('#frmEquipamentos').trigger('reset');
				$('#tblEquipamentos tbody').empty();
				$("#txtPatrimoniosEquip").removeAttr('readonly');
				$("#txtPatrimoniosEquip").focus();
				$("#btnAlteraPatrimoniosEquip").hide();
				$('#btnVerificaPatrimoniosEquip').show();
				$('#divTabelaPatrimoniosEquip').hide();
				$('#listaEquipamentos').hide();
			},
			complete: function() {
				
			}

		});
	}); 

// ----------- ADMIN -------------------

// ----------- USUARIOS ---------------

var autorizacoes = [
	{ Name: "Técnico", Id: "2" },
	{ Name: "Administrador", Id: "3" },
	{ Name: "Master", Id: "4" }
]; 

var estados = [
	{ Name: "ATIVO", Id: "ATIVO" },
	{ Name: "INATIVO", Id: "INATIVO"}
]; 

var opcoes_fila = [
	{ Name: "SIM", Id: "1" },
	{ Name: "NÃO", Id: "0" },
]; 

// --- ATUALIZA FILAS ----
var filas = [{id_fila: "0", nome_fila: "Todos"}]; 

$.ajax({
	url: base_url + 'backend/filas',
	dataType: 'json',
	complete: resp => {
		Array.prototype.push.apply(filas,resp.responseJSON);
		
		//console.log(filas);

		$("#usuarios-grid").jsGrid({

			fields: [
				//{ name: "id_usuario", type: "text", readOnly:true },
				{ name: "nome_usuario", type: "text", validate: "required", title:"Nome"},
				{ name: "login_usuario", type: "text", validate: "required", title:"Login"},
				{ name: "data_usuario", type: "text", readOnly:true, title:"Data de criação" },
				{ name: "status_usuario", type: "select", items: estados, textField: "Name", valueField:"Id", title:"Situação"  },
				{ name: "autorizacao_usuario", type: "select", items: autorizacoes, textField: "Name", valueField:"Id", title:"Autorização" },
				{ name: "fila_usuario", type: "select", items: filas, textField: "nome_fila", valueField:"id_fila", title:"Fila preferencial" },
				{ name: "alteracao_usuario", type: "text", readOnly:true, title:"Última alteração" },
				{ type: "control", deleteButton: false}
			]
			
		});
	}
});



//--------------------

$("#usuarios-grid").jsGrid({
	width: "100%",
	height: "auto",

	autoload: true,
	inserting: true,
	editing: true,
	sorting: true,
	paging: true,
	filtering: false,

	loadMessage: "Carregando...",

	noDataContent: "(vazio)",

	controller: {
		loadData: function () {
			return $.ajax({
				url: base_url + "usuario/listar_usuarios",
				dataType: "json"
			  });
		},
		updateItem: function(item) {
			return $.ajax({
				type: "POST",
				url: base_url + "usuario/atualizar_usuario",
				data: item,
				dataType: "json"
			});
		},
		insertItem: function(item) {
			return $.ajax({
				type: "POST",
				url: base_url + "usuario/inserir_usuario",
				data: item,
				dataType: "json"
			});
		},
	},

	fields: [
		//{ name: "id_usuario", type: "text", readOnly:true },
		{ name: "nome_usuario", type: "text", validate: "required", title:"Nome"},
		{ name: "login_usuario", type: "text", validate: "required", title:"Login"},
		{ name: "data_usuario", type: "text", readOnly:true, title:"Data de criação" },
		{ name: "status_usuario", type: "select", items: estados, textField: "Name", valueField:"Id", title:"Situação"  },
		{ name: "autorizacao_usuario", type: "select", items: autorizacoes, textField: "Name", valueField:"Id", title:"Autorização" },
	//	{ name: "fila_usuario", type: "select", items: filas, textField: "nome_fila", valueField:"id_fila", title:"Fila preferencial" },
		{ name: "alteracao_usuario", type: "text", readOnly:true, title:"Última alteração" },
		{ type: "control", deleteButton: false}
	]
});

// ------------ FIM USUARIOS ---------------

// ------------- FILAS --------------------

$("#filas-grid").jsGrid({ // FILAS FIXAS
	width: "100%",
	height: "auto",

	autoload: true,
	inserting: false,
	editing: false,
	sorting: true,
	paging: true,
	filtering: false,

	loadMessage: "Carregando...",

	noDataContent: "(vazio)",

	controller: {
		loadData: function () {
			return $.ajax({
				url: base_url + "admin/listar_filas/1",
				dataType: "json"
			  });
		},
		updateItem: function(item) {
			return $.ajax({
				type: "POST",
				url: base_url + "admin/atualizar_fila",
				data: item,
				dataType: "json"
			});
		},
		insertItem: function(item) {
			return $.ajax({
				type: "POST",
				url: base_url + "admin/inserir_fila",
				data: item,
				dataType: "json"
			});
		},
	},

	fields: [
		//{ name: "id_fila", type: "text", readOnly:true },
		{ name: "nome_fila", type: "text", validate: "required", title:"Nome"},
		{ name: "status_fila", type: "select", items: estados, textField: "Name", valueField:"Id", title:"Situação"  },
		// { name: "requer_patrimonio_fila", type: "select", items: opcoes_fila, textField: "Name", valueField:"Id", title:"Requer patrimônio?" },
	]
});

$("#filas-avulsas-grid").jsGrid({ // FILAS AVULSAS
	width: "100%",
	height: "auto",

	autoload: true,
	inserting: true,
	editing: true,
	sorting: true,
	paging: true,
	filtering: false,

	loadMessage: "Carregando...",

	noDataContent: "(vazio)",

	controller: {
		loadData: function () {
			return $.ajax({
				url: base_url + "admin/listar_filas/0", // trazer filas avulsas
				dataType: "json"
			  });
		},
		updateItem: function(item) {
			return $.ajax({
				type: "POST",
				url: base_url + "admin/atualizar_fila",
				data: item,
				dataType: "json"
			});
		},
		insertItem: function(item) {
			return $.ajax({
				type: "POST",
				url: base_url + "admin/inserir_fila",
				data: item,
				dataType: "json"
			});
		},
	},

	fields: [
		//{ name: "id_fila", type: "text", readOnly:true },
		{ name: "nome_fila", type: "text", validate: "required", title:"Nome"},
		{ name: "status_fila", type: "select", items: estados, textField: "Name", valueField:"Id", title:"Situação"  },
		{ name: "requer_patrimonio_fila", type: "select", items: opcoes_fila, textField: "Name", valueField:"Id", title:"Requer patrimônio?" },
		{ type: "control", deleteButton: false}
	]
});

//------------- FIM FILAS ------------------
	

// ---------------- LOG DE EVENTOS --------------


$('#tblEventos').DataTable( { //  tabela de eventos
	
	"autoWidth": true,

	"columnDefs": [
		{ "width": "10%", "targets": 3, "render": $.fn.dataTable.render.moment( 'YYYY-MM-DD HH:mm:ss','DD/MM/YYYY H:mm:ss' ) },
		{ "width": "10%", "targets": 0 },
		{ "width": "40%", "targets": 2 }
	],

	"orderCellsTop": false,
    "fixedHeader": true,

	"language": {
		"decimal":        "",
		"emptyTable":     "(vazio)",
		"info":           "Mostrando _START_ a _END_ de _TOTAL_ eventos",
		"infoEmpty":      "Mostrando 0 a 0 de 0 eventos",
		"infoFiltered":   "(filtrado de _MAX_ eventos)",
		"infoPostFix":    "",
		"thousands":      ".",
		"lengthMenu":     "Mostrando _MENU_ eventos",
		"loadingRecords": "Carregando...",
		"processing":     "Processando...",
		"search":         "Busca:",
		"zeroRecords":    "Sem resultados!",
		"paginate": {
			"first":      "Primeiro",
			"last":       "Último",
			"next":       "Próximo",
			"previous":   "Anterior"
		},
	},

	"ajax": base_url + 'admin/listar_eventos/',

	"order": [[ 0, "desc" ]]


}); 


setInterval(function() { //atualiza o log de eventos

	$('#tblEventos').DataTable().ajax.reload(null, false);


}, 15000);

// ----------- /LOG DE EVENTOS -------------

//---------------- TRIAGEM -------------------------

UTF8 = {
	encode: function(s){
		for(var c, i = -1, l = (s = s.split("")).length, o = String.fromCharCode; ++i < l;
			s[i] = (c = s[i].charCodeAt(0)) >= 127 ? o(0xc0 | (c >>> 6)) + o(0x80 | (c & 0x3f)) : s[i]
		);
		return s.join("");
	},
	decode: function(s){
		for(var a, b, i = -1, l = (s = s.split("")).length, o = String.fromCharCode, c = "charCodeAt"; ++i < l;
			((a = s[i][c](0)) & 0x80) &&
			(s[i] = (a & 0xfc) == 0xc0 && ((b = s[i + 1][c](0)) & 0xc0) == 0x80 ?
			o(((a & 0x03) << 6) + (b & 0x3f)) : o(128), s[++i] = "")
		);
		return s.join("");
	}
};



function carregaTriagem(p_id_chamado) {

	
	$('div[name=descricao_triagem]').html('<p>Carregando...</p>');
	
	//traz os dados do chamado MIGRADO (OTRS)
	
	document.title = "Triagem #" + p_id_chamado + " - Sigat";

	var p_id_responsavel = null;

	$.ajax({
		url: base_url + 'backend/triagem',
		dataType: 'json',
		async: true,
		data: { id_chamado: p_id_chamado },
		success: function(data) {

			//preencher os campos conforme o json
			
			$('input[name=nome_solicitante]').val(data.chamado.nome_solicitante_chamado);
			$('div[name=descricao_triagem]').html(UTF8.decode(data.chamado.descricao_chamado));
			
			if (data.anexos_otrs.length > 0) {
				
				
				$("#listaAnexosOTRS").html("<thead><th>Nome do arquivo</th><th>Download</th></thead><tbody></tbody>");
				
				data.anexos_otrs.forEach(function(item) {
					
					
					$("#listaAnexosOTRS tbody").append("<tr><td>" + item.nome_arquivo_otrs + "</td><td><a class=\"btn btn-primary btn-sm active\" role=\"button\" href=\"" + base_url + "anexo_otrs/" 
														+ item.id_anexo_otrs + "\" download><i class=\"fas fa-download\"></i></a></tr>")
				});
			} else {
				
				$("#listaAnexosOTRS").html("<tr><td>Sem anexos</td></tr>");
			}
		}	
	});
	
}



async function criaTabelaPatrimoniosTriagem(vetor,url) {

	vetorListaOK = [];
	
	var vetorPatrInv = [];

	var vetorPatrIns = [];
	var vetorPatrInsDesc = [];
	var vetorPatrInsChamado = [];

	var vetorPatrOK = [];
	var vetorPatrOKDesc = [];

	var vetorPatrAberto = [];
	var vetorPatrAbertoDesc = [];
	var vetorPatrAbertoChamado = [];
	
	$('[for="descricao"]').append("<div class=\"spinner-border spinner-border-sm\" role=\"status\">&nbsp;&nbsp;<span class=\"sr-only\">Carregando...</span\></div>");
	
	
	for (var item of vetor) { 

		await fetch(base_url + 'backend/patrimonio?url='+ url + item + '&q=' + item)
			
		.then(response => response.json())
		
		.then(data => {
				
				if (data.descricao != null) {

					if (data.inservivel) { // verificando se não é inservivel
						vetorPatrIns.push(item);              
						vetorPatrInsDesc.push(data.descricao);
						vetorPatrInsChamado.push(data.inservivel['id_chamado_patrimonio']);
					} else {
						vetorPatrOK.push(item);              // se houver descricao, o patrimonio é valido
						vetorPatrOKDesc.push(data.descricao);
					}
					
					if (data.chamado != null) {
						
						vetorPatrAberto.push(item); // se houver chamado, enfileirar nos vetores PatrAberto (num_patrimonio, descricao, id_chamado)
						vetorPatrAbertoDesc.push(data.descricao);
						vetorPatrAbertoChamado.push(data.chamado);

						vetorPatrOK.pop(); //remover dos vetores de patrimonios validos
						vetorPatrOKDesc.pop();

					}

				} else {
					vetorPatrInv.push(item); //se não houver descricao, o patrimonio é invalido e será enfileirado no vetorPatriInv

				}
			
			}
		);

	}


	if (vetorPatrInv.length > 0) { //se houverem patrimonios invalidos...

		$('#listaVerificada div').remove();
		$('[for="descricao"] .spinner-border').remove();
		
		vetorPatrInv.forEach(function (item){
			$("#msgPatr").append("<div class=\"alert alert-warning alert-dismissible fade show\" role=\"alert\">Patrimônio inválido: <strong>" + item + "</strong><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button></div>");
		});
		
		$("#btnAlteraPatrimoniosTriagem").hide();
		$('#btnVerificaPatrimoniosTriagem').show();
		$('#divTabelaPatrimonios').hide();
		

	} else { //se não houverem patrimonios inválidos ...
		
		$('[for="descricao"] .spinner-border').remove();

		if (vetorPatrIns.length > 0) { // se houverem patrimonios inservie...

			$('#divTabelaInserviveis').show();
			
			for (var i=0; i<vetorPatrIns.length; i++) {

				$('#tblInserviveis tbody').append('<tr class=\"table-danger\"><td>' + vetorPatrIns[i] + 
				'</td><td>' + vetorPatrInsDesc[i] + '</td><td>#' +  vetorPatrInsChamado[i] + 
				' <a target=\"_blank\" href=\"' + base_url + 'chamado/' + vetorPatrInsChamado[i] + '\">Mais...</a></td></tr>');

			}

			listaVerificada = false;

			$('#btnRemovePatrimoniosTriagem').show();

		}
		
		
		if (vetorPatrAberto.length > 0) { // se houverem patrimonios com chamado aberto...

			$('#divTabelaChamadosAbertos').show();
			
			for (var i=0; i<vetorPatrAberto.length; i++) {

				$('#tblPatrimoniosAbertos tbody').append('<tr class=\"table-warning\"><td>' + vetorPatrAberto[i] + 
				'</td><td>' + vetorPatrAbertoDesc[i] + '</td><td>#' +  vetorPatrAbertoChamado[i] + 
				' <a target=\"_blank\" href=\"' + base_url + 'chamado/' + vetorPatrAbertoChamado[i] + 
				'"><small>Mais...</small></a></td></tr>');
			
			}	

			listaVerificada = false;
		
			$('#btnRemovePatrimoniosTriagem').show();
		} 
		if (vetorPatrOK.length > 0) { //se houverem patrimonios válidos...

			$('#divTabelaPatrimonios').show();
			$("#btnAlteraPatrimoniosTriagem").show();
			
			for (var i=0; i<vetorPatrOK.length; i++) {

				$('#tblPatrimonios tbody').append('<tr><td>' + vetorPatrOK[i] + 
				'</td><td>' + vetorPatrOKDesc[i] + '</td></tr>');

				vetorListaOK.push(vetorPatrOK[i]);

			}

			if (vetorPatrAberto.length == 0) {
				listaVerificada = true;
			}			

		}
	
	}

}




$("#btnVerificaPatrimoniosTriagem").click(function() {
	

	var lista = $('[name=descricao]').val();

	var vetor = lista.match(/[1-9]\d{5}/g);

	if (vetor != null) {
		
		$('#msgPatr div[role=alert]').remove();

		

		$('#tblPatrimonios tbody tr').remove();
		$('#tblInserviveis tbody tr').remove();

		$('#btnVerificaPatrimoniosTriagem').prop('disabled','true');	

		
		// verificando duplicatas...
		
		duplicado = false;

		for (i = 0; i < vetor.length - 1; i++) {

			x = vetor[i];

			for (j = i + 1; j < vetor.length; j++) {

				if (x == vetor[j]) {

					duplicado = true;
					break;
				}
			}

		}

		if (duplicado == true) {

			$('[for="descricao"] .spinner-border').remove();
			
			
			$("#msgPatr").append("<div class=\"alert alert-warning alert-dismissible fade show\" role=\"alert\">Existem patrimônios duplicados na lista!<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button></div>");
			
			$("#btnAlteraPatrimoniosTriagem").hide();
			$('#btnVerificaPatrimoniosTriagem').show();
			$('#divTabelaPatrimonios').hide();
		}
		else {
			
			criaTabelaPatrimoniosTriagem(vetor, url);

		}
		
	}

});

$("#btnRemovePatrimoniosTriagem").click(function() {

		$('#tblPatrimoniosAbertos tbody tr').remove();
		$('#divTabelaChamadosAbertos').hide();
		$('#tblInserviveis tbody tr').remove();
		$('#divTabelaInserviveis').hide();
		$("#btnRemovePatrimoniosTriagem").hide();

	
		
		$('#btnVerificaPatrimoniosTriagem').removeAttr('disabled');
	
	
		vetorListaOK = [];
		
	});

$("#btnAlteraPatrimoniosTriagem").click(function() {

		
		$('#tblPatrimoniosAbertos tbody tr').remove();
		$('#btnVerificaPatrimoniosTriagem').removeAttr('disabled');
		$("#btnAlteraPatrimoniosEquip").hide();
	
		$('#divTabelaPatrimonios').hide();
		$('#divTabelaChamadosAbertos').hide();
	
		listaVerificada = false;
	
	});
	
	//------------------ SUBMIT DA TRIGEM --------------


$('#frmRegistrarChamado').on('submit', 

function(e) {
	
	e.preventDefault();
	
	}).validate ({
		rules: {
			nome_solicitante: "required",
			nome_local: "required",
			telefone: {
				required: true, 
				digits: true,
				minlength: 3,
			},
			listaPatrimonios: {
				required: function() {
					if ($('#flagPrecisaPatrimonio').val() == 1 && $('#id_fila').val() != 6) { //bypass da fila Solicitacao de Equipamentos
						return true;
					} else {
						return false;
					}
				},
				minlength: 6,
				maxlength: 2000
			},
			descricao: {
				required: true,
				maxlength: 2000,
				minlength: 10,
				normalizer: function(value) {
					return $.trim(value);
				}
			}
		},
		messages: {
			nome_solicitante: "Campo obrigatório!",
			nome_local: "Campo obrigatório!",
			telefone: {
				required: "Campo obrigatório!",
				digits: "Somente dígitos (0-9)!",
				minlength: "Mínimo 3 dígitos!"
			} ,
			descricao: {
				required: "Campo obrigatório!",
				minlength: "Descrição insuficiente!",
				maxlength: "Tamanha máximo excedido!"
			},

			listaPatrimonios: {
				required: "Campo obrigatório!",
				minlength: "Informe pelo menos 1 patrimônio!",
				maxlength: "Tamanha máximo excedido!"
			},
		},
		submitHandler: function(form) {
			var script_url = base_url + "chamado/registrar_chamado";
	
			var dados = new FormData(form);
			
			$.ajax({
					
					url: script_url,
					type: 'POST',
					data: dados,
					contentType: false,
					cache: false,
					processData: false,
					beforeSend: function () {

						if (listaVerificada == false && $( "#flagPrecisaPatrimonio" ).val() == 1) {

							$("#msg div[id=alerta]").remove();
							
							$("#msg").append("<div id=\"alerta\" class=\"alert alert-warning alert-dismissible\">");
							$("#alerta").append("<a href=\"#\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">&times;</a>É necessário verificar a lista de patrimônios!");
							$("#btnVerificaPatrimonios").focus();

							targetOffset = $('#msg').offset().top;
			
							$('html, body').animate({ 
								scrollTop: targetOffset - 100
							}, 200);
							
							return false;
						}

						

						$('#btnAbrirChamado').prop("disabled","true");
						
					},
				success: function(msg) {

					$("#msg div[id=alerta]").remove();
					
					$("#msg").append(msg);

					listaVerificada = false;

					if (msg.includes('anexo') == false && msg.includes('Local') == false) {

						$(form).trigger('reset'); //só resetar o form se não houver erros no upload ou no anexo
					}
					else {

						if (msg.includes('Local')) {

							$('input[name=nome_local').focus();
						}

						if (msg.includes('anexo')) {

							$('input[name=anexo').focus();
						}

						listaVerificada = true;
						$('#btnAbrirChamado').removeAttr("disabled");
					}
					
					
					if ($( "#flagPrecisaPatrimonio" ).val() == 0) {

						timeout = setTimeout(function() {
							$('#btnAbrirChamado').removeAttr("disabled");
			
						},10000);
					}

					else {

						$('#btnAbrirChamado').removeAttr("disabled");

						if (listaVerificada == false) {

							$('#tblPatrimonios tr').remove();
							$('#btnVerificaPatrimonios').show();
							$("#btnAlteraPatrimonios").hide();
							$('#txtPatrimonios').removeAttr('readonly');
							$("#txtPatrimonios").focus();
							$( "#divTabelaPatrimonios" ).hide();
						}
							
					}

					targetOffset = $('#msg').offset().top;
			
					$('html, body').animate({ 
						scrollTop: targetOffset - 100
					}, 200);

					msg = null;
	
				
				},
				error: function(xhr, ajaxOptions, thrownError) {
					
					$("#msg").prepend("<div id=\"alerta\" class=\"alert alert-danger alert-dismissible\">");
					$("#alerta").append("<a href=\"#\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">&times;</a>" + thrownError);
					
					
					targetOffset = $('#msg').offset().top;
			
					$('html, body').animate({ 
						scrollTop: targetOffset - 100
					}, 200);

					$('#btnAbrirChamado').removeAttr("disabled");
				}
	
			});

	return false;
			
	}
});


	//--------  /TRIAGEM ---------

