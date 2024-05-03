const patrimonio_regex = /\b[1-9]{1}[0-9]{5}\b/g
var fila_atual = null;

var chamados_expo = []
var cont_imp = 0;
const config = Promise.resolve(carregaConfig()).then(value => {
    return value;
})

async function carregaConfig() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: base_url + 'json/config_js',
            type: 'GET',
            async: true,
            success: (data) => {
                resolve(data);
            },
            error: (error) => {
                reject(error);
            }
        });
    });
}

function checkExpo() {

    
    
    $('.chkExpo').on('click', function(e) {
                
        $(this).is(":checked") ? cont_imp++ : cont_imp--
        
        cont_imp > 0 ? $("#contImp").text(cont_imp) : $("#contImp").text("")

        chamados_expo.push($(this).attr('value'))
    
    
    
    })

}



// --- PLUGIN DATETIME.JS

// UMD
(function(factory) {
        "use strict";

        if (typeof define === 'function' && define.amd) {
            // AMD
            define(['jquery'], function($) {
                return factory($, window, document);
            });
        } else if (typeof exports === 'object') {
            // CommonJS
            module.exports = function(root, $) {
                if (!root) {
                    root = window;
                }

                if (!$) {
                    $ = typeof window !== 'undefined' ?
                        require('jquery') :
                        require('jquery')(root);
                }

                return factory($, root, root.document);
            };
        } else {
            // Browser
            factory(jQuery, window, document);
        }
    }
    (function($, window, document) {


        $.fn.dataTable.render.moment = function(from, to, locale) {
            // Argument shifting
            if (arguments.length === 1) {
                locale = 'en';
                to = from;
                from = 'YYYY-MM-DD';
            } else if (arguments.length === 2) {
                locale = 'en';
            }

            return function(d, type, row) {
                if (!d) {
                    return type === 'sort' || type === 'type' ? 0 : d;
                }

                var m = window.moment(d, from, locale, true);

                // Order and type get a number value from Moment, everything else
                // sees the rendered value
                return m.format(type === 'sort' || type === 'type' ? 'x' : to);
            };
        };


    }));

function getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for(let i = 0; i <ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') {
        c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
        return c.substring(name.length, c.length);
        }
    }
    return "";
}


window.addEventListener('load', function(){
  
    $("#btnFilas").children('label').each(function() {
        
        if($(this).attr("data-fila") == getCookie("fila_painel")) {
            $(this).addClass("active");
            return false;
        }

        if($(this).attr("data-fila") == g_fila_painel) {
            $(this).addClass("active");
        }    
    })
  });


var fila_painel = getCookie("fila_painel") !=  "" ? getCookie("fila_painel") : g_fila_painel

$(function() {

    // PAINEL


   

    

    painel(fila_painel); //incializa o painel na fila preferencial do usuario
    

   

   

    // TRIAGEM
    triagem(); //incializa o painel de triagem

    painelEncerrados();

    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    const view = urlParams.get('v');

    if (view == 'triagem') {

        $('#triagem-tab').tab('show');
    }

    if (view == 'encerrados') {

        $('#encerrados-tab').tab('show');
    }


    // --------------- HABILITANDO FILTRAGEM NOS EVENTOS ---------------

    $('#tblEventos thead tr').clone(true).appendTo('#tblEventos thead');
    $('#tblEventos thead tr:eq(1) th').each(function(i) {
        var title = $(this).text();
        $(this).html('<input type="text" placeholder="Procurar ' + title + '" />');

        $('input', this).on('keyup change', function() {
            if ($('#tblEventos').DataTable().column(i).search() !== this.value) {
                $('#tblEventos').DataTable()
                    .column(i)
                    .search(this.value)
                    .draw();
            }
        });
    });

});


// --------------- PAINEL CHAMADOS ---------------------------

var table_painel = null;




function painel(id_fila) {



    table_painel = $('#tblPainel').DataTable({ //  inicializacao do painel

        "autoWidth": true,
        "pageLength" : 25,

        stateSave: true,

        lengthMenu: [
            [15, 25, 50, 100, 200],
            [15, 25, 50, 100, 200],
        ],

        "columnDefs": [
            {
                "targets": 1,
                //"data": "prioridade_chamado",
                "render": function ( data, type, row, meta ) {

                    var display;

                    switch(data) {

                        case "1":
                            display = "<span style=\"font-size: 0.5px; color: white\">1 - PRIOR</span> <span class=\"text-warning\"><i class=\"fas fa-star\"></i></span>";
                            break;
                        case "ABERTO":
                            display = "<span style=\"font-size: 0.5px; color: white\">2 - ABERTO</span> <span class=\"text-warning\"><i class=\"fas fa-circle\"></i></span>"
                            break;
                        case "FECHADO":
                            display = "<span style=\"font-size: 0.5px; color: white\">3 - FECHADO</span> <span class=\"text-success\"><i class=\"fas fa-circle\"></i></span>";
                            break;
                    }
                    

                    return display;
                }

            },

            {
                "targets": [1,7,9],
                "className": "text-center"

            },
            
            {
                "orderable": false,
                "targets": [7,10],
            }, 

            {
                "render": $.fn.dataTable.render.moment('YYYY-MM-DD HH:mm:ss', 'DD/MM/YYYY'),
                "targets": 8,
                
            }, 

            {
                "visible": false,
                "targets": [9],     
                
            },
        

        ],

        "language": {
            "decimal": "",
            "emptyTable": "Sem chamados :)",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ chamados",
            "infoEmpty": "Mostrando 0 a 0 de 0 chamados",
            "infoFiltered": "(filtrado de _MAX_ chamados)",
            "infoPostFix": "",
            "thousands": ".",
            "lengthMenu": "Mostrando _MENU_ chamados",
            "loadingRecords": "Carregando...",
            "processing": "Processando...",
            "search": "Busca:",
            "zeroRecords": "Sem resultados!",
            "paginate": {
                "first": "Primeiro",
                "last": "Último",
                "next": "Próximo",
                "previous": "Anterior"
            },
        },

        "ajax": base_url + 'chamado/listar_chamados_painel/' + id_fila,

        "order":  [[ 1, "asc" ], [ 9, "desc" ]],

        "processing": true,


        "initComplete": checkExpo(),

        "drawCallback": function(settings) {

            checkExpo()

            var api = this.api()

            var fila = getCookie("fila_painel") === "" ? g_fila_painel : getCookie("fila_painel")    
           
            $("label[data-fila] span").remove()
            $("label[data-fila=" + fila + "]")
            .append(
                "<span class=\"badge badge-light\">" + api.rows().count() +
                "</span>"
            )
          
            // $('.chkExpo').on('click', function(e) {
                
            //     $(this).is(":checked") ? cont_imp++ : cont_imp--
                
            //     cont_imp > 0 ? $("#contImp").text(cont_imp) : $("#contImp").text("")

            // })

        }
    });
}


$('#tblPainel').on('mousedown','tbody tr td', function (e) {

    e.preventDefault();
    
    if ($(this)[0].cellIndex == 7)

       return false;

    var row = table_painel.row($(this)).data();
    var url = base_url + 'chamado/' + row[0];


    if (e.button === 1) {
    
        window.open(url); 
    }

    else if (e.button === 2) {

        e.preventDefault();

    }

    else {
        document.location.href = url
    }

    

    
    
  });




function mudaFila(p_id_fila) { //troca de fila no painel => destroi o painel e reconstroi no onChange do $('#slctFila')
  
  
    document.cookie = "fila_painel=" + p_id_fila

 
    
    $('#tblPainel').DataTable().ajax.url(base_url + 'chamado/listar_chamados_painel/' + p_id_fila).load();

    
}

function resetPainelChamados() { 

    $('#tblPainel').DataTable().state.clear();
    $('#tblPainel').empty();
    $('#tblPainel').DataTable().destroy();
    document.location.reload(true);
   // $('#tblPainel').DataTable().ajax.reload(null, false);

    
}


$('#btnImprimirChamado').on('click', function(e) {

    e.preventDefault();


    var out = window.open(base_url + 'chamado/imprimir_chamados?chamados=' + $(this).attr("data-chamado"))
    out.print();
  
})

$('#btnImprimirRelatorioChamado').on('click', function(e) {

    e.preventDefault();


    var out = window.open(base_url + 'chamado/imprimir_relatorio_chamado?chamados=' + $(this).attr("data-chamado"))
    out.print();
  
})


$('#btnImprimir').on('click', function(e) {

    e.preventDefault();

    

    if (cont_imp == 0) {
        alert ("Sem chamados selecionados!")
        return false
    }

    

    var out = window.open(base_url + 'chamado/imprimir_chamados?chamados=' + chamados_expo)

    $("#contImp").text("");
    chamados_expo = []
    cont_imp = 0

    $('#tblPainel').DataTable().ajax.reload(null, false);
    out.print();
  
})


// --------------- PAINEL ENCERRADOS ---------------------------

var table_encerrados = null;


function painelEncerrados() {

    table_encerrados = $('#tblEncerrados').DataTable({ //  inicializacao do painel

        "pageLength" : 25,

        "autoWidth": true,

        "language": {
            "decimal": "",
            "emptyTable": "Sem resultados.",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ chamados",
            "infoEmpty": "Mostrando 0 a 0 de 0 chamados",
            "infoFiltered": "(filtrado de _MAX_ chamados)",
            "infoPostFix": "",
            "thousands": ".",
            "lengthMenu": "Mostrando _MENU_ chamados",
            "loadingRecords": "Carregando...",
            "processing": "Processando...",
            "search": "Busca:",
            "zeroRecords": "Sem resultados!",
            "paginate": {
                "first": "Primeiro",
                "last": "Último",
                "next": "Próximo",
                "previous": "Anterior"
            },
        },

        "ajax": base_url + 'chamado/listar_encerrados_painel/',

        "order": [],

        "processing": true,

        "columnDefs": [ 
        
            {
                "render": $.fn.dataTable.render.moment('YYYY-MM-DD HH:mm:ss','DD/MM/YYYY - HH:mm:ss'),
                "targets": [4,5]
                
            }, 

        ]

    });
}


$('#tblEncerrados').on('click', 'tbody tr', function () {
    var row = table_encerrados.row($(this)).data();
    window.open(base_url + 'chamado/' + row[0]);
  });






table_triagem = null;



// ------------  PAINEL TRIAGEM

function triagem() {

    table_triagem = $('#tblTriagem').DataTable({ //  inicializacao do painel

        "autoWidth": false,

        "pageLength" : 50,

        "columnDefs": [

            {
                "width": "10%",
                "targets": 2,
                "render": $.fn.dataTable.render.moment('YYYY-MM-DD HH:mm:ss', 'DD/MM/YYYY H:mm:ss')
            },
            {
                "visible":false,
                "targets": 0
            }
        ],

        "language": {
            "decimal": "",
            "emptyTable": "Sem chamados :)",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ chamados",
            "infoEmpty": "Mostrando 0 a 0 de 0 chamados",
            "infoFiltered": "(filtrado de _MAX_ chamados)",
            "infoPostFix": "",
            "thousands": ".",
            "lengthMenu": "Mostrando _MENU_ chamados",
            "loadingRecords": "Carregando...",
            "processing": "Processando...",
            "search": "Busca:",
            "zeroRecords": "Sem resultados!",
            "paginate": {
                "first": "Primeiro",
                "last": "Último",
                "next": "Próximo",
                "previous": "Anterior"
            },
        },

        "ajax": base_url + 'triagem/listar_triagem/',

        "order": [],


    });

}

$('#tblTriagem').on('click', 'tbody td', function () {
    //clicar triagem
    
    var row = table_triagem.row($(this)).data();
    
    document.location.href = base_url + 'triagem/' + row[0];
        

  });



// =============== MODAIS ====================

async function buscaEquipamentos(p_id_chamado, p_id_fila_ant, p_atendimento, ins = false, p_espera = false, p_alt_fila = false) {


    var num_equipamentos = [];
    $('#btnRegistrarInteracao').removeAttr('disabled');

    await $.ajax({

        url: base_url + 'json/equipamentos_pendentes',
        data: {
            id_chamado: p_id_chamado,
            espera: p_espera
        },
        type: 'POST',
        async: true,
        dataType: 'json',
        success: function(data) {

            data.filas.forEach(function(fila) { //exibindo as filas


                $('select[name=id_fila]').append("<option value=\"" + fila.id_fila + "\" >" + fila.nome_fila + "</option>");


                if (p_atendimento == true) {

                    $('option[value=' + data.id_fila + ']').prop("selected", "true"); //se for atendimento, 
                    //selecionar a fila atual do chamado

                } else {

                    $('#slctFila option[value=' + p_id_fila_ant + ']').remove(); //se não, remover a fila atual da lista
                }


            });

            if (data.equipamentos != null) {

                data.equipamentos.forEach(function(equip) {

                    num_equipamentos.push(equip['num_equipamento_chamado']);

                });

            }


            
        }
    });

   

   
    $('#divEquipamentos').empty();

    /*if (num_equipamentos.length > 0) {

        num_equipamentos.sort();

        if (p_atendimento == true) {

            $('#divEquipamentos').prepend("<p>Marque os equipamentos que foram finalizados:</p>");

            if (!ins) { // nao sendo a opçao Classificar como inservivel, o check de equipamento é opcional
                $('#divEquipamentos p').append(" <small><strong>(opcional)</strong></small>");
            }

        } else {

            if (p_espera == false) {

                $('#divEquipamentos').prepend("<p>Marque os equipamentos que serão deixados em espera:</p>");

            } else {

                $('#divEquipamentos').prepend("<p>Marque os equipamentos que sairão da espera:</p>");
            }
        }
        $('#divEquipamentos').append("<input id=\"chkTudo\" type=\"checkbox\" value=\"#\" onclick=\"$('#divEquipamentos input:checkbox').not(this).prop('checked', this.checked)\">" +
            "<label class=\"mr-2\" for=\"chkTudo\">&nbsp;Todos</label>");

        num_equipamentos.forEach(function(num) { //criando os checkbox com os patrimonios
         
            $('#divEquipamentos').append(
                "<input class=\"chkPatri\" type=\"checkbox\" id=\"" + num + "\" value=\"" + num + "\">" +
                "<label class=\"mr-2\" for=\"" + num + "\">&nbsp;" + num + "</label>");
        });

    } else {

        if (p_espera == true) {
            $('#divEquipamentos').prepend("<p>Não existem equipamentos em espera!</p>");
            if (!p_alt_fila) {
                $('#btnRegistrarInteracao').prop('disabled', 'true');
            }

        } 
    }*/
    $('#divServicos').empty();

    var lista_servicos = [];
    await $.ajax({

        url: base_url + "listar_servicos_chamado/" + g_id_chamado,
        type: 'GET',
        async: true,
        dataType: 'json',
        success: function(data) {
            lista_servicos = data;
        }
    });

    var contador_servicos = 0;

    if(lista_servicos != null){
        if(lista_servicos.length > 0){
            lista_servicos.forEach(function(servico){
                if(servico.status_servico == 'ABERTO'){
                    contador_servicos++;
                }
            });
            if(contador_servicos > 0){
                $('#divServicos').prepend("<p>Marque os serviços que foram finalizados:</p>");
    
                $('#divServicos').append("<input id=\"chkTudoS\" type=\"checkbox\" value=\"#\" onclick=\"$('#divServicos input:checkbox').not(this).prop('checked', this.checked)\">" +
                "<label class=\"mr-2\" for=\"chkTudoS\">&nbsp;Todos</label>");
            }
            
            
            lista_servicos.forEach(function(servico) { //criando os checkbox com os patrimonios
                
                    if(contador_servicos > 0 && servico.status_servico == 'ABERTO'){
                        $('#divServicos').append(
                            "<input class=\"chkServ\" type=\"checkbox\" id=\"" + servico.id_servico + "\" value=\"" + servico.nome_servico + "\" id_servico=\"" + servico.id + "\">" +
                            "<label class=\"mr-2\" for=\"" + servico.id_servico + "\">&nbsp;" + " " + servico.nome_servico + " ( "+ servico.quantidade+ " " + servico.unidade_medida + " ) " + "</label>"); 
                    }
                
                });
        }
    }
    
}


function verificaTipo(fila_ant, id_chamado) { //verificar tipo da fila no modal de Registro de Atendimento

    $('select[name=id_fila]').empty();


    switch ($('#slctTipo').val()) {

        case 'ATENDIMENTO':
            buscaEquipamentos(id_chamado, fila_ant, true, false, false);
            $('#divEquipamentos').show();
            $('#divServicos').show();
            $('#divFila').show();
            $('#slctFila').attr('disabled', true);
            $('#divMensagem').show();
            listaModelosMensagems($('#slctTipo').val(), fila_ant);
            break;

        case 'ALT_FILA':
            buscaEquipamentos(id_chamado, fila_ant, false, false, false, true);
            $('#divFila').show();
            $('#divEquipamentos').hide();
            $('#divServicos').hide();
            $('#slctFila').attr('disabled', false);
            $('#divMensagem').show();
            listaModelosMensagems($('#slctTipo').val(), fila_ant);
            break;

        case 'OBSERVACAO':
            $('#divEquipamentos').hide();
            $('#divServicos').hide();
            $('#divFila').hide();
            $('#slctFila').attr('disabled', true);
            $('#btnRegistrarInteracao').removeAttr('disabled');
            $('#divMensagem').show();
            listaModelosMensagems($('#slctTipo').val(), fila_ant);
            break;
            
        // case 'INSERVIVEL':

        //     if (fila_ant == 3) {
        //         buscaEquipamentos(id_chamado, fila_ant, true, true);
        //         $('#divEquipamentos').show();
        //         $('#divFila').show();
        //         $('#slctFila').attr('disabled', true);
        //         $('#divMensagem').show();
        //         listaModelosMensagems($('#slctTipo').val(), fila_ant);
        //     } else {
        //         $('#divEquipamentos').show();
        //         $('#divEquipamentos').html('Opção disponível somente na fila <strong>Manutenção de Hardware</strong><br>');
        //         $('#btnRegistrarInteracao').prop('disabled', 'true');
        //         $('#divFila').hide();
        //         $('#divMensagem').hide();
        //     }


        //     break;

        case 'ESPERA':
            buscaEquipamentos(id_chamado, fila_ant, false);
            $('#divEquipamentos').show();
            $('#divServicos').hide();
            $('#divFila').hide();
            $('#slctFila').attr('disabled', true);
            $('#divMensagem').show();
            listaModelosMensagems($('#slctTipo').val(), fila_ant);
            break;

        case 'REM_ESPERA':
            buscaEquipamentos(id_chamado, fila_ant, false, false,true,false);
            $('#divServicos').hide();
            $('#divEquipamentos').show();
            $('#divFila').hide();
            $('#slctFila').attr('disabled', true);
            $('#divMensagem').show();
            listaModelosMensagems($('#slctTipo').val(), fila_ant);
            break;
        case 'FECHAMENTO':

            break;
    }

    if (g_fila_chamado < 5 || g_fila_chamado > 7) {
        $("#slctTipo option[value='ATENDIMENTO']").remove();
    }
}

function exibeModeloMensagem() {
    // Capturar o evento de alteração de valor
    var textoSelecionado = '<p>' + $("#slctModeloMensagem option:selected").text() + '</p>';
    // Retorna o valor para o summernote
    $('textarea[name=txtInteracao]').summernote('code', textoSelecionado);
}
async function listaModelosMensagems(tipo, id_fila) {
    // Seleciona o modelo de mensagem de acordo com o tipo
    let modeloMensagens = await carregaModeloMensagem(tipo, id_fila);
    $('#slctModeloMensagem').empty();
    $('textarea[name=txtInteracao]').summernote('code', texto_interacao)
    if (modeloMensagens === null) {
        // desativa campo para não alterar
        $('#slctModeloMensagem').prop('disabled', true);
        $('#slctModeloMensagem').append("<option disabled selected>Não possui nenhuma mensagem :(</option>");
    } else {
        $('#slctModeloMensagem').removeAttr('disabled');
        $('#slctModeloMensagem').append("<option disabled selected>Selecione a mensagem</option>");

        modeloMensagens.forEach(modeloMensagem => {
            $('#slctModeloMensagem').append('<option value=\"MENSAGEM\">'+modeloMensagem.mensagem_modelo_mensagem+'</option>');
        });
    }
}
async function carregaModeloMensagem(tipo, id_fila) {
    let out = null;
    await $.ajax({
        url: base_url + `ModeloMensagem/listar_modelo_mensagem?tipo=${tipo}&id_fila=${id_fila}`,
        type: 'GET',
        success: function(data) {
            out = data;
        }
    })

    return out;
}

async function carregaFilas() {
    var out = null;
    await $.ajax({
        url: base_url + "fila/listar_filas",
        type: 'GET',
        success: function(data) {
            out = data;
        }
    })

    return out;
}


async function carregaSecretarias() {
    var out = null;
    await $.ajax({
        url: base_url + "secretaria/listar_secretarias/",
        type: 'GET',
        success: function(data) {
            out = data;
        }
    })
    
    return out;
}

function criaFormRegistro(p_id_chamado, p_id_fila_ant) { //carregar o form no modal de Registro de Atendimento
    var html_form =
        "<div class=\"row\">" +
        "<div class=\"col\">" +
        "<label for=\"tipo\">Tipo</label>" +
        "<select class=\"form-control\" name=\"tipo\" id=\"slctTipo\" onchange=\"verificaTipo(" + p_id_fila_ant + "," + p_id_chamado + ")\">" +
        "</select>" +
        "</div>" +
        "</div>" +
        "<div class=\"row\" id=\"divFila\">" +
        "<div class=\"col\">" +
        "<label for=\"id_fila\">Fila</label>" +
        "<select class=\"form-control\" name=\"id_fila\" id=\"slctFila\"></select>" +
        "</div>" +
        "</div>" +
        "<div class=\"row\" id=\"divMensagem\">" +
        "<div class=\"col\">" +
        "<label for=\"id_mensagem\">Mensagem</label>" +
        "<select class=\"form-control\" name=\"id_mensagem\" id=\"slctModeloMensagem\" onchange=\"exibeModeloMensagem()\">" +
        "</select>" +
        "</div>" +
        "</div>" +
        "<div class=\"row mt-3\">" +
        "<div class=\"col\">" +
        "<div id=\"divEquipamentos\"></div>" +
        "</div>" +
        "</div>" +
        "<div class=\"row mb-3\">" +
        "<div class=\"col\">" +
        "<div id=\"divServicos\">teste</div>" +
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
var txt_interacao = null;
var texto_interacao = "";
$('#modalRegistro').on('show.bs.modal', function(event) { //modal de registro de interacao
    $(this).on('mouseup keyup', () => {
        texto_interacao = $('textarea[name=txtInteracao]').summernote("code");
    });

    var link = $(event.relatedTarget);
    var p_id_chamado = link.data('chamado');

    var modal = $(this);

    modal.find('.modal-body #conteudo_form').empty();
    modal.find('.modal-body #conteudo_form').prepend(criaFormRegistro(p_id_chamado, fila_atual));
  
});

$('#modalRegistro').on('shown.bs.modal', function(event) {

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
        height: 150,
        lang: 'pt-BR',
        dialogsInBody: true,
        disableDragAndDrop: false,
    });
    $('textarea[name=txtInteracao]').summernote('code', texto_interacao);

    $('#slctTipo').append("<option value=\"ATENDIMENTO\" selected>Atendimento</option>" +
        "<option value=\"OBSERVACAO\">Observação</option>" +
        "<option value=\"ALT_FILA\">Alteração de fila</option>");

    // if (g_requer_patri == true) {
    $('#slctTipo').append('<option value=\"ESPERA\">Deixar em espera</option>');
    $('#slctTipo').append('<option value=\"REM_ESPERA\">Remover da espera</option>');
   // $('#slctTipo').append('<option value=\"INSERVIVEL\">Classificar como inservível</option>');


    // } else {
    // 	$('#slctTipo').append('<option value=\"FECHAMENTO\">Finalizar</option>');
    // }

    verificaTipo(fila_atual, g_id_chamado);



});

$('#modalRegistro').on('hide.bs.modal', function(event) {

    $('div[role=alert]').remove();


});

// --- /FIM MODAL INTERACAO

// -- MODAL REGISTRO ENTREGA

$('#modalRegistroEntrega').on('hide.bs.modal', function(event) {

    $(this).find("#btnRegistrarEntrega").hide();

});

$('#modalRegistroEntrega').on('show.bs.modal', function(event) {

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
        "<div id=\"divErroEntrega\" style=\"display: none\">" +
        "<div class=\"form-group\">" +
        "<label for=\"txtFalhaEntrega\"><b>Selecione o motivo:</b></label>" +
        "<select class=\"form-control\" id=\"slcErroEntrega\" onchange=\"verificaEntrega()\">" +
        "<option value=\"1\">Verificado defeito na instalação</option>" +
        "<option value=\"2\">Responsável no local ausente</option>" +
        "</select>" +
        "</div>" +
        "</div>" +
        "<div id=\"divFalhaEntrega\" style=\"display: none\">" +
        "<div class=\"form-group\">" +
        "<label for=\"txtFalhaEntrega\"><b>Detalhes:</b></label>" +
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


// ========== FIM MODAIS ================



//------------------------ AUTOCOMPLETE ----------------------------

let xhr;

$('input[name=nome_solicitante]').autoComplete({ //na abertura do chamado
    source: function(term, response) {
        try {
            xhr.abort();
        } catch (e) {}
        xhr = $.getJSON(base_url + 'json/solicitantes', {
            q: term
        }, function(data) {
            response(data);
        });
    },
    minChars: 2,
    autoFocus: true,

});



$('input[name=nome_local]').autoComplete({
    source: function(term, response) {
        try {
            xhr.abort();
        } catch (e) {}
        xhr = $.getJSON(base_url + 'json/locais', {
            q: term
        }, function(data) {
            response(data);
        });
    },
    minChars: 2,

});



//------------- CARREGA CHAMADO ---------------------

var tblEquipsChamado = null;
var tblServicosChamado = null;

async function carregaHistorico(p_id_chamado) {

    var out = null;

    await $.ajax({
        url: base_url + "chamado/historico/" + p_id_chamado,
        type: 'GET',
        success: function(data) {
            out = data;
        }
    });

    return out;
    
}


$("#tblAnexosChamado").jsGrid({
    width: '100%',
    autoload: true,
    editing: false,
    inserting: false,
    noDataContent: "Sem anexos.",
    deleteConfirm: "Tem certeza?",
    fields: [
        { 
            name: "id_anexo_otrs",
            title: "ID",
            type: "text",
            visible: false, 
                
        },
        { 
            name: "nome_arquivo_otrs",
            title: "Nome do arquivo",
            type: "text", 
            
        },
    ],
    controller: {
        loadData: function() {
            return $.ajax({
                url: base_url + "json/anexos_chamado",
                dataType: "json",
                method: "post",
                data : {
                    id_chamado: g_id_chamado
                }
            });
        },
    },
    rowClick: function(args) {
        window.open(base_url + 'anexo_otrs/' + args.item.id_anexo_otrs);
    }
});

var item_antigo = null;

var entrega = null;

var botoes = "";

var p_id_responsavel = null;

var status_chamado = null;

var botoes_chamado_equipamento = null;
async function carregaChamado(p_id_chamado,sem_equipamentos) {

    //atualiza os dados do chamado

    $("#tblEquipamentosChamado").jsGrid({

        height: "auto",
        width: "100%",
        inserting: false,
        editing: false,
        autoload: true,
        sorting: true,
        invalidMessage: "Dados inválidos inseridos!",
        loadMessage: "Aguarde...",
        deleteConfirm: "Tem certeza?",
        noDataContent: "Vazio",
    
        onInit: function(args) {
            tblEquipsChamado = args.grid;
        },

        onItemUpdating: function(args) {

            item_antigo = null;
            item_antigo = args.previousItem;

        },

        rowClick: function() {

            

        },
    
        fields: [
            {
                name: "num_equipamento",
                title: "Núm. de identificação",
                type: "text",
                readOnly: true,
                
               validate: [
                    "required",
                    { 
                        validator: "pattern", 
                        param: /^[a-zA-Z0-9]+$/, 
                        message: "Atenção!\nCaracteres permitidos:\nA-Z, a-z e 0-9" 
                    },
                ],
            }, 
            {
                name: "descricao_equipamento",
                type: "text",
                readOnly: true,
                title: "Descrição",
                validate: [
                    "required",
                    { validator: "pattern", param: /^[a-zA-Z0-9\s]+$/, message: "Atenção!\nCaracteres permitidos:\nA-Z, a-z e 0-1" },
                ],
                
            }, 
           
            {
                name: "tag_equipamento",
                type: "text",
                align: "center",
                readOnly: true,
                inserting: false,
                editing: false,
                title: "Lacre",
                /* validate: [
                    { validator: "pattern", param: /^[a-zA-Z0-9]+$/, message: "Atenção!\nCaracteres permitidos:\nA-Z, a-z e 0-1" },
                ], */
            },
            {
                name: "status_equipamento_chamado",
                title: "Status",
                width: 50,
                align: "center",
            }, 
            {
                title: "",
                width: 110,
                align: "center",
                readOnly: true,
                editing: false,
                inserting: false,
                itemTemplate: function(value, item) {
                    botoes_chamado_equipamento = `<button type="button" class="btn btn-primary btn-sm mr-2" id="bnt-ficha-${item.num_equipamento}" title="Imprimir ficha do equipamento ${item.num_equipamento}" disabled><i class="fas fa-print"></i></button>`
                    botoes_chamado_equipamento += `<a class="btn btn-primary btn-sm" href="${base_url}equipamento/${item.num_equipamento}" target="_BLANK" role="button" title="Ver informações do equipamento ${item.num_equipamento}"><i class="fas fa-search"></i></a>`;

                    return botoes_chamado_equipamento
                },
                editTemplate: function(value) {
                    return botoes_chamado_equipamento
                },
            },
            {
                type:"control",
                editButton: false,
                deleteButton: false,
            }
        ],

        

        rowClass: function(item) { return item.status_equipamento_chamado == 'ABERTO' || item.status_equipamento_chamado == 'REPARO' || item.status_equipamento_chamado == 'GARANTIA' ? 'bg-warning' : ''; },
    
        controller: {
            loadData: function() {
                return $.ajax({
                    url: base_url + "listar_equips_chamado/" + g_id_chamado,
                    dataType: "json",
                    method: "get",
                });
            },
            updateItem: async function(item) {
                var d = $.Deferred();
                var res = null;
                var num_equip = item.num_equipamento.replace(/\s+/g, "");
                console.error(num_equip)
                res = await verificaStatusEquip(num_equip);
                
                if(res !== null) {
                    if ((res.status_equipamento_chamado === "ABERTO" || 
                        res.status_equipamento_chamado === "ENTREGA" ||
                        res.status_equipamento_chamado === "ESPERA" ||
                        res.status_equipamento_chamado === "INSERVIVEL" ||
                        res.status_equipamento_chamado === "REMESSA" ||
                        res.status_equipamento_chamado === "GARANTIA" ||
                        res.status_equipamento_chamado === "REPARO") && parseInt(res.id_chamado) !== g_id_chamado)  {
                    alert("O item " + num_equip + " já está em atendimento ou foi classificado como inservível!\nChamado: " + res.id_chamado + "\n" + res.ticket_chamado);
                    d.reject();
                    return d.promise();
                    }
                    else {
                        return $.ajax({
                                url: base_url + "edit_equip_chamado",
                                dataType: "json",
                                method: "post",
                                data: {item,g_id_chamado,item_antigo},
                                success: async function() {
                                    var historico = await carregaHistorico(p_id_chamado);
                                    $("#historico").html(historico);
                                    carregaChamado(p_id_chamado);
                                }
                            });
                    }
                }
                
            },
            insertItem: async function(item) {
                var d = $.Deferred();
                var res = null;
                var num_equip = item.num_equipamento.replace(/\s+/g, "");
                res = await verificaStatusEquip(num_equip);

                if(res !== null){
                    let Toast = Swal.mixin({
                        icon: "error",
                        title: 'Falha ao inserir equipamento ao chamado<hr style="margin-bottom: -5px;">',
                        footer: `${res.ticket_chamado}<br>Chamado: <a href="${base_url}chamado/${res.id_chamado}" target="_BLANK">${res.id_chamado}</a>`,
                        toast: true,
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                          toast.onmouseenter = Swal.stopTimer;
                          toast.onmouseleave = Swal.resumeTimer;
                        }
                    });
                    switch (res.status_equipamento_chamado) {
                        case 'ABERTO':
                            Toast.fire({
                                text: `O equipamento ${num_equip} já está em atendimento!`
                            });
                            d.reject();
                            break;

                        case 'ESPERA':
                            Toast.fire({
                                text: `O equipamento ${num_equip}  está em espera!`
                            });
                            d.reject();
                            break;

                        case 'FALHA':
                            Toast.fire({
                                text: `O equipamento  ${num_equip} está em estado de falha!`
                            });
                            d.reject();
                            break;
                        
                        case 'INSERVIVEL':
                            Toast.fire({
                                text: `O equipamento ${num_equip} está inservivel!`
                            });
                            d.reject();
                            break;
                            
                        case 'REPARO':
                            Toast.fire({
                                text: `O equipamento ${num_equip} está em reparo!`
                            });
                            d.reject();
                            break;

                        case 'ENTREGA':
                            Toast.fire({
                                text: `O equipamento ${num_equip} está para entrega!`
                            });
                            d.reject();
                            break;

                        case 'REMESSA':
                            Toast.fire({
                                text: `O equipamento ${num_equip} está em remessa!`
                            });
                            d.reject();
                            break;

                        default:
                            $.ajax({
                                url: base_url + "add_equip_chamado",
                                dataType: "json",
                                method: "post",
                                data: {item,g_id_chamado},
                                success: async function() {
                                    var historico = await carregaHistorico(p_id_chamado);
                                    $("#historico").html(historico);
                                    carregaChamado(p_id_chamado);
                                }
                            });
                            break;
                    }
                } else {
                    return $.ajax({
                        url: base_url + "add_equip_chamado",
                        dataType: "json",
                        method: "post",
                        data: {item,g_id_chamado},
                        success: async function() {
                            var historico = await carregaHistorico(p_id_chamado);
                            $("#historico").html(historico);
                            carregaChamado(p_id_chamado);
                        }
                    });
                }

                return d.promise();
            },
            deleteItem: async function(item) {
                var d = $.Deferred();
                var res = null;
                var num_equip = item.num_equipamento.replace(/\s+/g, "");
                res = await verificaStatusEquip(num_equip);
                if (res.status_equipamento_chamado === 'ABERTO') {
                    
                    return $.ajax({
                        url: base_url + "del_equip_chamado",
                        dataType: "json",
                        method: "post",
                        data: {item,g_id_chamado},
                        success: async function() {
                            var historico = await carregaHistorico(p_id_chamado);
                            $("#historico").html(historico);
                            carregaChamado(p_id_chamado);
                        }
                    });
                }
                else {
                    alert("Operação não permitida!\nO item " + num_equip +" já foi alterado neste chamado!");
                    d.reject();
                    return d.promise();  
                }
            }       
        },
    });

    var servicos = null;

    async function carregarServicos(){
        let valor = '';
        await $.ajax({
            url: base_url + "listar_servicos/" + g_fila_chamado,
            type: "GET",
            success: function(data){
                if(data !== null){
                    valor = data;
                }
            },
            error: function(){
                console.error('houve um erro');
            }
        });
        servicos = valor;
    }
    await carregarServicos();
    var servicos_existentes = [];
    
    $("#tblServicosInfraChamado").jsGrid({

        height: "auto",
        width: "100%",
        inserting: false,
        editing: false,
        autoload: true,
        sorting: true,
        invalidMessage: "Dados inválidos inseridos!",
        loadMessage: "Aguarde...",
        deleteConfirm: "Tem certeza?",
        noDataContent: "Vazio",

        onInit: function(args) {
            tblServicosInfraChamado = args.grid;
        },

        onItemUpdating: function(args) {
            item_antigo = null;
            item_antigo = args.previousItem;
            if(args.item.quantidade < 1){
                alert('O campo quantidade deve ser preenchido com um valor acima de zero!');
                args.cancel = true;
            }
        },

        onItemInserting: function(args){
            if(args.item.quantidade < 1){
                alert('O campo quantidade deve ser preenchido com um valor acima de zero!');
                args.cancel = true;
            }
        },
       
        fields: [
            {
                name: "id_servico",
                title: "Nome do Serviço",
                type: "select",
                align: "center",
                autosearch: true,
                items: servicos,
                valueField: "id_servico",
                textField: "nome_servico",
                selectedIndex: -1,
                valueType: "number",
                readOnly: false,
                editing: false,
             },
             {
                name: "quantidade",
                title: "Quantidade",
                width: 20,
                type: "text",
                insertTemplate: function() {
                    let input = this.__proto__.insertTemplate.call(this); //original input
                
                    input.val(0);
                    
                    return input;
                },
                validate: [
                    "required",
                    { validator: "pattern", param: /^[0-9]+$/, message: "Atenção!\nEste campo aceita apenas números." },
                ],
                align: "center",
                readOnly: false,
            },
            {
                name: "unidade_medida",
                title: "Unidade de medida",
                width: 20,
                align: "center",
                readOnly: true,
            },  
            {
                name: "status_servico",
                title: "Status",
                width: 50,
                align: "center",
                readOnly: true,
            },
            {
                type:"control",
                editButton: false,
                deleteButton: false,
            },
        ],

        

        rowClass: function(item) { 
            servicos_existentes.push(item);
            return item.status_servico == 'ABERTO' ? 'bg-warning' : ''; },

        controller: {
            loadData: function() {
                return $.ajax({
                    url: base_url + "listar_servicos_chamado/" + g_id_chamado,
                    dataType: "json",
                    method: "post",
                });
            },
            insertItem: function(item) {
                let d = $.Deferred();
                let existente = null;
                item.status_servico = "ABERTO";
                item.id_servico = item.id_servico.toString();
                for(i=0; i<servicos.length; i++){
                    if(item.id_servico == servicos[i]['id_servico']){
                        item.nome_servico = servicos[i]['nome_servico'];
                    }
                }
                for(i=0; i<servicos_existentes.length; i++){
                    if(servicos_existentes[i]['id_servico'] == item.id_servico && servicos_existentes[i]['status_servico'] == 'ABERTO'){
                        existente = servicos_existentes[i]['nome_servico'];
                    }
                }
                if(existente != null){
                    alert('O serviço ' + existente + ' já foi adicionado e está como o status ABERTO.');
                    d.reject();
                    return d.promise();
                }else{
                    $.ajax({
                        url: base_url + "listar_servicos_chamado/" + g_id_chamado,
                        dataType: "json",
                        method: "post",
                        data:{
                            item
                        },
                        success: function(){
                            carregaChamado(p_id_chamado);
                        },
                        error: function(){
                            alert('Erro ao adicionar o serviço');
                        }
                    });
                }
                
                
            },
            deleteItem: function (item){
                let d = $.Deferred();
                if(item.status_servico == 'FECHADO'){
                    alert('Este serviço está fechado e não pode ser excluido.');
                    d.reject();
                    return d.promise();
                }else{
                    $.ajax({
                        url: base_url + "excluir_servico/" + g_id_chamado,
                        dataType: "json",
                        method: "post",
                        data:{
                            item, g_id_chamado
                        },
                        success: function(){
                            carregaChamado(p_id_chamado);
                        },
                        error: function(){
                            alert('Erro ao excluir o serviço');
                        }
                    });
                }
            },
            updateItem: function (item){
                let valor = item.quantidade;
                if (valor > 0){
                    return $.ajax({
                        url: base_url + "atualizar_servicos_chamado/" + g_id_chamado,
                        dataType: "json",
                        method: "post",
                        data: {
                            item
                        },
                        success: function(){
                            carregaChamado(p_id_chamado);
                        },
                        error: function(){
                            alert('Erro ao excluir o serviço');
                        }
                    });
                }
            }
        },
    });
   

    document.title = "Chamado #" + p_id_chamado + " - SIGAT";

    p_id_responsavel = null;
    
    $("#spnStatusChamado").fadeIn();
    
    var historico = await carregaHistorico(p_id_chamado);
    $("#historico").html(historico);

   

    $('#botoesAtendimento').html("");
    $('#botoesAtendimentoReparo').html("");
    $('#btnBloquearChamado').removeAttr("disabled");
    $('#modalReparo').find('modal-footer').hide();

    botoes = "";

    $('#botoesChamado hr').hide();

   var status_equips = [];
   var status_servicos = [];
   var id_responsavel = null;
   let remessa = null;
   
    await $.ajax({
        url: base_url + 'json/chamado',
        dataType: 'json',
        async: true,
        data: {
            id_chamado: p_id_chamado
        },
        error: function(request, status, error) {
            console.error(status)

        },
        success: function(data) {
            g_fila_chamado = parseInt(data.id_fila);
            //preencher os campos conforme o json

            $('input[name=fila]').val(data.nome_fila_chamado);
            $('input[name=resumo]').val(data.resumo_chamado);
            $('input[name=complemento]').val(data.complemento_chamado);
            $('input[name=data_chamado]').val(data.data_chamado);
            $('input[name=status]').val(data.status_chamado);
            $('input[name=nome_solicitante]').val(data.nome_solicitante_chamado);
            $('input[name=celular]').val(data.celular_chamado);
            $('input[name=telefone]').val(data.telefone_chamado);
            $('input[name=nome_local]').val(data.nome_local);
            $('input[name=id_fila_ant]').val(data.id_fila);
            //$('div[name=descricao]').html(data.descricao_chamado);
            
            var telefone = null;
            data.telefone_chamado.length > 4 ? telefone = "0" + data.telefone_chamado : telefone = data.telefone_chamado;

            $("#sipLink").attr("href","sip:"+telefone);
            $("#btnVerEndereco").attr("data-chamado",p_id_chamado);

            if (data.id_responsavel == null) {
                $('select[name=id_responsavel]').empty();
            } else {
                p_id_responsavel = data.id_responsavel;
                $('select[name=id_responsavel]').html('<option value="' + data.id_responsavel + '">' + data.nome_responsavel + '</option>');
            }

            fila_atual = data.id_fila; //variavel global fila_atual
            entrega = data.entrega_chamado;
            status_chamado = data.status_chamado;
            
            for (var i = 0; i < data.status_equipamentos.length; ++i) {

                status_equips.push(data.status_equipamentos[i].status_equipamento_chamado);
            }

            for(i = 0; i <data.status_equipamentos.length ; i++){
                if(data.status_equipamentos[i].status_equipamento_chamado == 'REMESSA'){
                    remessa = true;
                }
            }

            
            for (var i = 0; i < data.status_servicos.length; ++i) {

                status_servicos.push(data.status_servicos[i].status_servico_chamado);
   
               
            }

            // -------------------- PERMISSOES ----------------------------
        
            if (data.id_responsavel == g_id_usuario) {

               // if (!fila_atual > 1)

                tblEquipsChamado.option("editing",true);
                tblEquipsChamado.option("inserting",true);
                if(g_fila_chamado == 5 || g_fila_chamado == 6 || g_fila_chamado == 7){//somente serviços de infraestrutura
                    tblServicosInfraChamado.option("inserting", true);
                    tblServicosInfraChamado.option("editing", true);
                    tblServicosInfraChamado.fieldOption(4, "deleteButton", true);
                    tblServicosInfraChamado.fieldOption(4, "editButton", true);
                } 
                tblEquipsChamado.fieldOption(5,"editButton",true);
                tblEquipsChamado.fieldOption(5,"deleteButton",true);
                tblEquipsChamado.fieldOption(1,"readOnly",false);
                tblEquipsChamado.fieldOption(0,"readOnly",false);
                tblEquipsChamado.fieldOption(0,"editing",false);
                tblEquipsChamado.fieldOption(0,"inserting",true);
                
            
                
                if (data.id_fila == 3) {
                    // exibir
                    tblEquipsChamado.fieldOption(2,"editing",true);
                }

                if (g_auto_usuario > 3) {
                    tblEquipsChamado.fieldOption(1,"readOnly",false);
                    tblEquipsChamado.fieldOption(4,"deleteButton",true);

                }
            }
           
            if (data.status_chamado != 'ABERTO') { //se o chamado não estiver ABERTO, remover o botao Registrar Atendimento e Editar Chamado
                $('#btnModalEmail').hide();
                $('#btnEditarChamado').hide();
                $('#btnDesbloquearChamado').hide();
                $('#botoesChamado hr').hide();

                tblEquipsChamado.option("editing",false);
                tblEquipsChamado.option("inserting",false);
                tblEquipsChamado.fieldOption(4,"editButton",false);
                tblEquipsChamado.fieldOption(4,"deleteButton",false);
                tblEquipsChamado.fieldOption(0,"readOnly",true);
                tblEquipsChamado.fieldOption(1,"readOnly",true);
                tblEquipsChamado.fieldOption(2,"editing",false);

            } else {
                if ((g_auto_usuario >= 3 && data.id_responsavel != null) || data.id_responsavel == g_id_usuario) {
                    $('#btnModalEmail').show();
                    $('#btnDesbloquearChamado').show();
                    $('#btnEditarChamado').show();
                    $('#botoesChamado hr').show();

                }

                if (data.id_responsavel == null && g_auto_usuario >= 3) { //se não houver responsavel e o usuario for ADM+
                    $('#btnModalEmail').hide();
                    $('#btnEditarChamado').show();
                    $('#btnDesbloquearChamado').hide();
                    $('#botoesChamado hr').show();
                }

                if (data.id_responsavel == null && g_auto_usuario <= 2) { //Tecnico
                    $('#btnBloquearChamado').show();
                    $('#btnEditarChamado').hide();
                    $('#btnDesbloquearChamado').hide();
                    $('#botoesChamado hr').show();
                    $('#btnModalEmail').hide();
                }

                if (data.id_responsavel == null && g_auto_usuario >= 3) { //ADM +

                    $('#btnBloquearChamado').show();
                    $('#btnEditarChamado').show();
                    $('#btnDesbloquearChamado').hide();
                    $('#botoesChamado hr').show();
                    $('#btnModalEmail').show();
                }

                if (data.id_responsavel != null && g_auto_usuario >= 3) { //ADM +

                    $('#btnBloquearChamado').hide();
                    $('#btnEditarChamado').show();
                    $('#btnDesbloquearChamado').show();
                    $('#botoesChamado hr').show();
                    $('#btnModalEmail').show();
                }

                if (g_id_usuario == data.id_responsavel && g_auto_usuario <= 2) { //Tecnico 

                    $('#btnBloquearChamado').hide();
                    $('#btnEditarChamado').show();
                }
            }
        }
    });

    var reparos = null

    await $.ajax({
        method: "POST",
        url: base_url + 'reparo/listar_reparos',
        data: {
            id_chamado: g_id_chamado,
        }
    })
    .done((data) => {
       

        reparos = data

    })


    if (reparos.length > 0) {

        $("#tblReparosChamado tbody").html("")
        reparos.forEach((r) => {
            var data_fim = r.data_fim_reparo == null ? '' : r.data_fim_reparo

            var bg = null

            switch (r.status_reparo) {
                case "ABERTO":
                    bg = "table-warning"
                    break
                case "FINALIZADO":
                    bg = "table-success"
                    break
                case "CANCELADO" || "CANCELAMENTO":
                    bg = "table-secondary"
                    break

                case "CANCELAMENTO":
                    bg = "table-secondary"
                    break
                case "GARANTIA":
                    bg = "table-primary"
                    break
                case "ESPERA":
                    bg = "table-danger"
                    break
            }

            
            if(r.status_reparo == 'FINALIZADO'){
                for(i=0; i < r.servicos.length; i++){
                    
                    if(r.servicos[i].realizado_reparo_servico == 1 && r.servicos[i].subquery == 1){
                        r.status_reparo += '<br/> <small>' + r.servicos[i].nome_servico + '</small>';
                    }
                }
            }
            $("#tblReparosChamado tbody").append(
                `<tr class=${bg}>
                    <td>${r.num_equipamento_reparo}</td>
                    <td>${r.status_reparo == "GARANTIA" || r.status_reparo == "ESPERA" ? '-' : r.nome_bancada}</td>
                    <td>${r.status_reparo}</td>
                    <td>${r.data_inicio_reparo}</td>
                    <td>${data_fim}</td>
                    <td>
                        <button type="button" class="btn btn-primary btn-modal-reparo" 
                            data-toggle="modal" data-reparo="${r.id_reparo}" 
                            data-target="#modalReparo">
                            <i class="fas fa-search"></i>
                        </button>
                    </td>
                </tr>`
            );
            
        })
    }

    if (g_auto_usuario >= 3 && g_auto_usuario_enc == 1  && status_chamado == 'FECHADO') {//somente ADM+ encerra o chamado

        botoes = '<button id="btnEncerrarChamado" onclick="encerrarChamado()" class="btn btn-success"><i class=\"far fa-check-circle\"></i> Encerrar chamado</button>';
    }

    if (g_auto_usuario >= 3 && g_auto_usuario_enc == 1  && status_chamado == 'ENCERRADO') {//somente ADM+ encerra o chamado

        botoes = '<button id="btnReabrirChamado" class="btn btn-primary" onclick="reabrirChamado()"><i class="fas fa-lock-open"></i> Reabrir chamado</button>';
    }

    for (var i = 0; i < status_equips.length; ++i) {
        if ((p_id_responsavel == g_id_usuario && 
            
            (status_equips[i] == 'ABERTO' || 
            status_equips[i] == 'ESPERA' || 
            status_equips[i] == 'FALHA' || 
            status_equips[i] == 'ENTREGA' ||
            status_equips[i] == 'REPARO' ||
            status_equips[i] == 'GARANTIA'))) {
           
            botoes = "<button type=\"button\" id=\"btnModalRegistro\" class=\"btn btn-primary\"" +
            " data-toggle=\"modal\" data-target=\"#modalRegistro\" data-chamado=\"" + g_id_chamado +
            "\"><i class=\"fas fa-asterisk\"></i> Nova Ação</button> ";
    
            break;
        }
    }

    for (var i = 0; i < status_servicos.length; ++i) {
        if ((p_id_responsavel == g_id_usuario && 
            
            (status_servicos[i] == 'ABERTO' || 
            status_servicos[i] == 'ESPERA' || 
            status_servicos[i] == 'FALHA' || 
            status_servicos[i] == 'ENTREGA' ||
            status_servicos[i] == 'REPARO' ||
            status_equips[i] == 'GARANTIA'))) {
           
            botoes = "<button type=\"button\" id=\"btnModalRegistro\" class=\"btn btn-primary\"" +
            " data-toggle=\"modal\" data-target=\"#modalRegistro\" data-chamado=\"" + g_id_chamado +
            "\"><i class=\"fas fa-asterisk\"></i> Nova Ação</button> ";
    
            break;
        }
    }

    var pendentes = 0;
    
    for (var i = 0; i < status_equips.length; ++i) {

        if (status_equips[i] == 'ABERTO' || status_equips[i] == 'ESPERA' || 
        status_equips[i] == 'FALHA' || status_equips[i] == 'ENTREGA' ||
        status_equips[i] == 'REPARO' || status_equips[i] == 'GARANTIA'
        ) {

            pendentes++;

        }
    }

    for (var i = 0; i < status_servicos.length; ++i) {

        if (status_servicos[i] == 'ABERTO' || status_servicos[i] == 'ESPERA' || 
        status_servicos[i] == 'FALHA' || status_servicos[i] == 'ENTREGA' ||
        status_servicos[i] == 'REPARO' || status_equips[i] == 'GARANTIA'
        ) {

            pendentes++;

        }
    }

    if (p_id_responsavel == g_id_usuario && pendentes == 0 && status_chamado == 'ABERTO') {

        botoes = "<button type=\"button\" id=\"btnFechamentoManual\" class=\"btn btn-primary\"" +
        " onclick=\"finalizaManual(" + p_id_chamado + ")\"><i class=\"fas fa-pen-alt\"></i> Fechamento manual</button> ";

    }

    if (entrega == 1 && p_id_responsavel == g_id_usuario && status_chamado == 'ABERTO' ) {

        botoes +=  "<button type=\"button\" id=\"btnModalRegistroEntrega\" class=\"btn btn-success\" data-toggle=\"modal\" data-chamado=\"" +
                            p_id_chamado + "\" data-target=\"#modalRegistroEntrega\"><i class=\"fas fa-file-signature\"></i> Registrar Entrega</button> " +
                            "<a href=\"" + base_url + "chamado/gerar_termo/" +
                            p_id_chamado + "\" id=\"baixarTermoEntrega\" role=\"button\" class=\"btn btn-info\">" +
                            "<i class=\"fas fa-file-download\"></i> Termo de Entrega</a> " +
                            "<a href=\"" + base_url + "chamado/gerar_termo_resp/" +
                            + p_id_chamado + "\" id=\"baixarTermoResp\" role=\"button\" class=\"btn btn-info\">" +
                            "<i class=\"fas fa-file-download\"></i> Termo de Responsabilidade</a>"
    
    }

    var botoesReparo = null

    if ((fila_atual == 3 || p_id_responsavel == g_id_usuario) && status_chamado == 'ABERTO') {

        botoesReparo =  "<button type=\"button\" id=\"btnModalIniciarReparo\" class=\"btn btn-primary\" data-toggle=\"modal\" data-chamado=\"" +
                            p_id_chamado + "\" data-target=\"#modalIniciarReparo\"><i class=\"fas fa-wrench\"></i> Iniciar reparo</button>"
    
    }
    
    $('#botoesAtendimento').html(botoes);
    $('#botoesAtendimentoReparo').html(botoesReparo);
    $("#spnStatusChamado").fadeOut();
    
}

/* function IsEmail(email) {
    var exclude=/[^@.\-w]|^[_@.\-]|[._-]{2}|[@.]{2}|(@)[^@]*1/;

    var check=/@[w-]+./;
    var checkend=/.[a-zA-Z]{2,3}$/;
    if(((email.search(exclude) != -1)||(email.search(check)) == -1)||(email.search(checkend) == -1)){return false;}
    else {return true;}
} */


function removerEmail(id_email, email) {
    // var escapedEmail = email.replace(/[@.]/g, "-$&");
    Object.values(remetentes).forEach(array => {
        const index = array.indexOf(email);
        if (index !== -1) {
            array.splice(index, 1);
        }
    });
    
    $(`#${id_email}`).remove();
}

var remetentes = null;
//btnModalEmail
$('#modalEmail').off('show.bs.modal').on('show.bs.modal', async(e) => {
    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 6000,
        timerProgressBar: true,
        didOpen: (toast) => {
          toast.onmouseenter = Swal.stopTimer;
          toast.onmouseleave = Swal.resumeTimer;
        }
    });


    $.ajax({
        url: base_url + `chamado/email_solicitante/${g_id_ticket_chamado}`,
        type: 'GET',
        //data: `patrimonio=${num_equipamento}`,
        dataType: 'json',
        success: (dados) => {
            remetentes = dados;
            $('#remetente').html('');
            remetentes.address.forEach(remetente => {
                $('#remetente').append(`
                    <span class="badge badge-info mt-1">Para:</span> ${remetente}<br>
                `);
            });

            remetentes.cc.forEach(remetente => {
                $('#remetente').append(`
                    <span class="badge badge-info mt-1">CC:</span> ${remetente}<br>
                `);
            });

            remetentes.cco.forEach(remetente => {
                $('#remetente').append(`
                    <span class="badge badge-info mt-1">CCo:</span> ${remetente}<br>
                `);
            });

            $('.copia').off('blur').on('blur', () => {
                let inputs = [
                    $('#copia'),
                    $('#copiaOculta')
                ];

                inputs.forEach(input => {
                    input.removeClass('is-invalid');
                });

                inputs.forEach(input => {
                    $(`${input[0].id}`).removeClass('is-invalid');
                });

                inputs.forEach(input => {
                    let email = input.val();
                    if (!email.includes('@') && email != "") {
                        $.ajax({
                            url: `${base_url}chamado/busca_email/${email}`,
                            type: 'GET',
                            dataType: 'json',
                            xhrFields: {
                                withCredentials: false // Indica que não são necessárias credenciais para a requisição
                            },
                            success: (email, textStatus, xhr) => {
                                if (xhr.status == 204) {
                                    Toast.fire({
                                        icon: "warning",
                                        title: "Usuário não encontrado. Por favor, insira um nome de usuário válido ou um endereço de e-mail ."
                                    });

                                    return;
                                }

                                if (Object.values(remetentes).some(array => array.includes(email.email))) {
                                    input.addClass('is-invalid');

                                    Toast.fire({
                                        icon: "error",
                                        title: "O email deste usuario já esta como destinatario ou em copia"
                                    });
                                    return;
                                }

                                input.val('');
                                let email_replace = email.email.replace(/[@.]/g, "-");
                                if (input[0].id == 'copiaOculta') {
                                    $('#remetente').append(`
                                        <div id="${email_replace}">
                                            <span class="badge badge-info mt-1">CCo:</span> ${email.nome}  &lt;${email.email}&gt; <span class="badge badge-danger" id="remover-email" onclick="removerEmail('${email_replace}', '${email.email}')"><i class="fas fa-times"></i></span><br>
                                        </div>
                                    `);
                                    remetentes.cco.push(email.email);
                                } else {
                                    $('#remetente').append(`
                                        <div id="${email_replace}">
                                            <span class="badge badge-info mt-1">CC:</span> ${email.nome}  &lt;${email.email}&gt; <span class="badge badge-danger" id="remover-email" onclick="removerEmail('${email_replace}', '${email.email}')"><i class="fas fa-times"></i></span><br>
                                        </div>
                                    `);
                                    remetentes.cc.push(email.email);
                                }
                            },
                            error: (erro) => { 
                                Toast.fire({
                                    icon: "warning",
                                    title: "Erro ao consultar usuário no Active Directory!"
                                });
                            } 
                        });
                    }
                });
            });
        },
        error: erro => {
            Toast.fire({
                icon: "warning",
                title: "Erro ao consultar os remetentes!"
            });
        } 
    });
});


const regex_email = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
$('#copiasEmail').on('submit', async(e) => {
    e.preventDefault();
    
    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 6000,
        timerProgressBar: true,
        didOpen: (toast) => {
          toast.onmouseenter = Swal.stopTimer;
          toast.onmouseleave = Swal.resumeTimer;
        }
    });

    let inputs = [
        $('#copia'),
        $('#copiaOculta')
    ];

    inputs.forEach(input => {
        input.removeClass('is-invalid');
        let email = input.val();
        let email_replace = email.replace(/[@.]/g, "-");
        
        if (email.trim() == "") {
            return;
        }
        if (!regex_email.test(email.trim())) {
            input.addClass('is-invalid');
            Toast.fire({
                icon: "error",
                title: "Digite um email válido!"
            });

            return;
        }
        if (Object.values(remetentes).some(array => array.includes(email))) {
            input.addClass('is-invalid');
            Toast.fire({
                icon: "error",
                title: "O email deste usuario já esta como destinatario ou em copia"
            });

            return;
        }

        if (input[0].id == 'copiaOculta') {
            $('#remetente').append(`
                <div id="${email_replace}">
                    <span class="badge badge-info mt-1">CCo:</span> ${email} <span class="badge badge-danger" id="remover-email" onclick="removerEmail('${email_replace}', '${email}')"><i class="fas fa-times"></i></span><br>
                </div>
            `);
            remetentes.cco.push(email);
        } else {
            $('#remetente').append(`
                <div id="${email_replace}">
                    <span class="badge badge-info mt-1">CC:</span> ${email} <span class="badge badge-danger" id="remover-email" onclick="removerEmail('${email_replace}', '${email}')"><i class="fas fa-times"></i></span><br>
                </div>
            `);
            remetentes.cc.push(email);
        }

        input.val('');
    });
});

$('#btnPingEquipamento').on('click', function() {
    let spinner = 
    `<span class=" ml-1 spinner-border text-light spinner-border-sm"           role="status">
                <span class="sr-only">Carregando...</span>
            </span>`;

    let ok = `<i class="far fa-check-circle"></i>`;

    let erro = `<i class="fas fa-exclamation-triangle"></i>`;
    
       
        let num_equipamento = $('#patrimonio').val();
        $(this).prop('disabled', 'true');
        $(this).append(spinner);
        

        $.ajax({
            url: base_url + `Ping/exec_ping`,
            type: 'GET',
            data: `patrimonio=${num_equipamento}`,
            dataType: 'json',
            success: dados => {
                $(this).html('');
                $(this).removeAttr('disabled');
                if(dados.status === true){
                    $(this).html('Resposta OK! ');
                    $(this).append(ok);
                }else{
                    $(this).removeClass('btn-success');
                    $(this).addClass('btn-danger');
                    $(this).html('Sem resposta! ');
                    $(this).append(erro);
                }
            },
            error: erro => { alert('Erro ao processar Ping!'); } 
        });
        
});
async function ping(patrimonio) {   
    let spinner = `
        <div class="d-flex justify-content-center">
            <div class="spinner-border text-secondary spinner-border-sm" role="status">
                <span class="sr-only">Carregando...</span>
            </div>
        </div>`
    $(`#bnt-ping-${patrimonio}`).prop('disabled', 'true');
    $(`#icon-ping-${patrimonio}`).html(spinner);

    $.ajax({
        url: base_url + `Ping/exec_ping?patrimonio=${patrimonio}`,
        type: 'GET',
    }).then((data) => {
        let status = data.status;
        if (status === true) {
            $(`#icon-ping-${patrimonio}`).html("");
            $(`#icon-ping-${patrimonio}`).html('<i class="fa fa-check" style="color: #11ff00;"></i>');
        } else {
            $(`#icon-ping-${patrimonio}`).html("");
            $(`#icon-ping-${patrimonio}`).html('<i class="fa fa-times" style="color: #ff0000;"></i>');
        }
        $(`#bnt-ping-${patrimonio}`).removeAttr('disabled', 'true');
    }).catch((err) => {
        alert(err);
        $(`#icon-ping-${patrimonio}`).html('<i class="fa fa-exclamation-triangle" style="color: #ff0000;"></i>');
    })
    
}

function reverterRemessa($equip, $remessa, $reparo){
    $.ajax({

        url: base_url + "inservivel/reverter_remessa/" ,
        type: 'POST',
        async: true,
        dataType: 'json',
        data: {
            equip: $equip,
            remessa: $remessa,
            reparo: $reparo,
            chamado: g_id_chamado,
        },
        success: function(data) {
            Swal.fire({
                title: "Reverter Remessa",
                text: data.mensagem,
                icon: data.status
            });
            $('#modalReparo').modal('hide');
            carregaChamado(g_id_chamado);

        },
        error: function(data){
            Swal.fire({
                title: "Reverter Remessa",
                text: "Erro desfazer a remessa.",
                icon: "error"
              });
        }

    });
}

function cancelarEntrega($equip, $id_reparo){
    $.ajax({

        url: base_url + "reparo/cancelar_entrega/" ,
        type: 'POST',
        async: true,
        dataType: 'json',
        data: {
            equip: $equip,
            id_reparo: $id_reparo,
            id_chamado: g_id_chamado,
        },
        success: function(data) {
            Swal.fire({
                title: "Cancelar Entrega",
                text: data.mensagem,
                icon: data.status
            });
            $('#modalReparo').modal('hide');
            carregaChamado(g_id_chamado);

        },
        error: function(data){
            Swal.fire({
                title: "Cancelar Entrega",
                text: "Erro desfazer a remessa.",
                icon: "error"
              });
        }

    });
}

function finalizaManual(p_id_chamado) {

    $.ajax({
        url: base_url + "chamado/finalizar_manual_chamado",
        type: 'POST',
        data: {id_chamado: p_id_chamado},
        beforeSend: function() {
            $('#btnFechamentoManual').prop('disabled','true');
        },
        success: function() {
            atualizaInteracoes(p_id_chamado);
            carregaChamado(p_id_chamado);
        }
    })
}

$('#comunicacao-tab').on('click', function(){
    if(g_id_usuario == p_id_responsavel){
        $.ajax({
            url: base_url + "chamado/zerar_nao_lidos/" + g_id_chamado,
            type: 'GET',
            async: true,
            dataType: 'json',
        });
    }
});

// ---------------- INTERACOES --------------------

function removeInteracao(p_id_interacao, p_id_chamado) {

    var bloqueado = false;

    $('#botoesAtendimento').html("");

    $.ajax({

        url: base_url + "interacao/remover_interacao",
        type: 'POST',
        data: {
            id_interacao: p_id_interacao,
            id_chamado: g_id_chamado,
            id_usuario: g_id_usuario,
            auto_usuario: g_auto_usuario,
        },
        beforeSend: function() {
            $('#btnDesfazer').prop('disabled','true');
        },
        success: function() {
            atualizaInteracoes(p_id_chamado);
            carregaChamado(p_id_chamado);
            tblEquipsChamado.loadData();
        },
        error: function() {
            $('#btnDesfazer').removeAttr('disabled');
            atualizaInteracoes(p_id_chamado);
            carregaChamado(p_id_chamado);
            tblEquipsChamado.loadData();
            alert('Operação não permitida!');
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

    var p_equips_atendidos = [];
    var p_servicos_atendidos = [];
    var p_id_servicos_atendidos = [];
    
    $('input[class=chkPatri]').each(function() {

        if ($(this).is(':checked')) {
            p_equips_atendidos.push($(this).attr('id'));
        }

    });
   
    $('input[class=chkServ]').each(function() {
        
        if ($(this).is(':checked')) {
            p_servicos_atendidos.push($(this).attr('id'));
            p_id_servicos_atendidos.push($(this).attr('id_servico'));
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
            equipamentos_atendidos: p_equips_atendidos,
            servicos_atendidos: p_servicos_atendidos,
            id_servicos_atendidos: p_id_servicos_atendidos,
            id_usuario: g_id_usuario
        },
        beforeSend: function() {

            if ((p_equips_atendidos.length == 0 && p_id_fila_ant == 3 && p_tipo == 'INSERVIVEL') || (p_equips_atendidos.length == 0 && (p_tipo == 'REM_ESPERA' || p_tipo == 'ESPERA'))) {

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



            $('#btnRegistrarInteracao').prop("disabled", "true");

        },
        success: function(msg) {

            $('#btnRegistrarInteracao').removeAttr("disabled");
            atualizaInteracoes(p_id_chamado);
            $('textarea[name=txtInteracao]').summernote('reset');
            $('#modalRegistro').modal('hide');
            carregaChamado(p_id_chamado);
            tblEquipsChamado.loadData();
            p_equips_atendidos = [, ]

        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(xhr + thrownError);
            $('#btnRegistrarInteracao').removeAttr("disabled");
        }

    });

    return false;

});


function atualizaInteracoes(id_chamado) { //carrega as interacoes

    $.post(base_url + "json/interacoes", {
            id: id_chamado
        })
        .done(function(dados) {

            $("#interacoes").html(dados);
        });

}


// -------------------- ENTREGA ---------------------------------

function verificaEntrega() { //confirmando se a entrega foi realizada

    if ($('input[name=confirmaEntrega]:checked').val() == 1) {

        $('#btnRegistrarEntrega').attr("class",'btn btn-success');

        $('#divEntrega').show();
        $('#divFalhaEntrega').hide();
        $('#divErroEntrega').hide();
        $('#btnRegistrarEntrega').show();
        $('#btnRegistrarEntrega span').html(' Registrar Entrega');
    } else {

        $('#divEntrega').hide();
        $('#divFalhaEntrega').show();
        $('#divErroEntrega').show();
        $('#btnRegistrarEntrega').show();
        $('#txtFalhaEntrega').summernote({ //inicialização do SummerNote 

            toolbar: [
                // [groupName, [list of button]]
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture', 'video']],
            ],
            height: 150,
            lang: 'pt-BR',
            dialogsInBody: true,
            disableDragAndDrop: true,
        });

        if ($("#slcErroEntrega").val() == 1) {

            $('#btnRegistrarEntrega span').html(' Registrar Falha de Entrega');
            $('#btnRegistrarEntrega').attr("class",'btn btn-danger');
            

        }

        else {

            $('#btnRegistrarEntrega span').html(' Registrar Tentativa de Entrega');
            $('#btnRegistrarEntrega').attr("class",'btn btn-success');
            $('#txtFalhaEntrega').hide();
        }
    }
}



$('#frmRegistroEntrega').on('submit', function(e) { //submit do registro de entrega

    e.preventDefault();


    var dados = new FormData($(this)[0]);
    var opcao = $('input[name=confirmaEntrega]:checked').val();
    var erro = $('#slcErroEntrega').val();
    var p_id_chamado = $('input[name=id_chamado]').val();

    dados.append("opcao_entrega",opcao);
    dados.append("id_chamado",p_id_chamado);
    dados.append("erro_entrega",erro);

    if ($("#rdNao").is(":checked")) {
        dados.append("txtFalhaEntrega",$("#txtFalhaEntrega").summernote('code'));
    }

    $('input[name="termo_responsabilidade"]').val() !== "" ? dados.append("termo_resp",1) : dados.append("termo_resp",0);

    $.ajax({

        url: base_url + "interacao/registrar_entrega",
        type: 'POST',
        data: dados,
        processData: false,
        contentType: false,

        beforeSend: function() {

            if (opcao == 1) { //entrega OK

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
            }

            else { //problema na entrega

                if ($('#txtFalhaEntrega').summernote('isEmpty')) {

                    $('#divFalhaEntrega').prepend(
                        "<div class=\"alert alert-warning fade show\" role=\"alert\">" +
                        "Descreva os detalhes." +
                        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">" +
                        "<span aria-hidden=\"true\">&times;</span>" +
                        "</button>" +
                        "</div>");

                        return false;

                    
                }

                


            }

            $('#btnRegistrarEntrega').prop("disabled", "true");

        },

        success: function(msg) {

            if (msg === "") {


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
        },

        error: function(msg) {

            alert(msg);
            $('#btnRegistrarEntrega').removeAttr("disabled");
        },

    });

});

// -------------- FIM ENTREGA ------------


// --------------- ALTERACOES CHAMADO -----------

function encerrarChamado() {

    var btn = $('#btnEncerrarChamado');

    if (g_auto_usuario >= 3) { // Permissão de ADM+
        if (confirm('Deseja realmente encerrar? Isso não poderá ser desfeito!')) {

            $.ajax({
                type: 'post',
                url: base_url + 'chamado/encerrar_chamado',
                data: {
                    id_chamado: g_id_chamado,
                    id_usuario: g_id_usuario

                },
                beforeSend: function() {
                    btn.prop('disabled', 'true')
                },
                success: function() {
                    atualizaInteracoes(g_id_chamado);
                    carregaChamado(g_id_chamado, true);
                },
                error: function() {
                    btn.removeAttr('disabled');
                    alert("Operação não permitida!")
                    
                }
            });
        }

    }

}

function reabrirChamado() {

    var btn = $('#btnReabrirChamado');

    if (g_auto_usuario >= 3) { // Permissão de ADM+
        

        $.ajax({
            type: 'post',
            url: base_url + 'chamado/reabrir_chamado',
            data: {
                id_chamado: g_id_chamado,
                id_usuario: g_id_usuario

            },
            beforeSend: function() {
                btn.prop('disabled', 'true')
            },
            success: function() {
                atualizaInteracoes(g_id_chamado);
                carregaChamado(g_id_chamado, true);
            },
            error: function() {
                btn.removeAttr('disabled');
                alert("Operação não permitida!")
                    
            }
        });
        

    }

}


// -------------- VERIFICA BLOQUEIO ---------------

var bloqueado = false;

$('#btnBloquearChamado').on('click', function(e) {

    e.preventDefault();
    $.ajax({
        type: "POST",
        url: base_url + 'json/atualiza_responsavel',
        data: {
            id_chamado: g_id_chamado,
            id_usuario: g_id_usuario,
            auto_usuario: g_auto_usuario,
            tipo: 'b'
        },

        beforeSend: function() {
            $('#btnBloquearChamado').prop('disabled', 'true');
        },
        success: function(data) {

            if (data === "") {
                $('#btnDesbloquearChamado').removeAttr('disabled');
                carregaChamado(g_id_chamado, true);
            } 
            else {
                alert(data);
                carregaChamado(g_id_chamado, true);
            }
            
        },
    });



});


$('#btnDesbloquearChamado').on('click', function(e) {

    e.preventDefault();
    $.ajax({
        type: "POST",
        url: base_url + 'json/atualiza_responsavel',
        data: {
            id_chamado: g_id_chamado,
            id_usuario: g_id_usuario,
            tipo: 'd',
            auto_usuario: g_auto_usuario,
        },
        beforeSend: function() {
            $('#btnDesbloquearChamado').prop('disabled', 'true');
        },
        success: function() {
            $('#btnBloquearChamado').removeAttr('disabled');
            carregaChamado(g_id_chamado, true);
        },
        error: function() {
            $('#btnDesbloquearChamado').removeAttr('disabled');
            alert('Operação não permitida!');
            carregaChamado(g_id_chamado, true);
        }
    });



});

// ---------------- FIM BLOQUEIO --------------------------------


$('#btnEditarChamado').on('click', function(e) {

    e.preventDefault();
    $('#btnBloquearChamado').hide();
    $('#btnDesbloquearChamado').hide();
    $('#btnEditarChamado').hide();

    $.ajax({
        url: base_url + 'json/chamado',
        dataType: 'json',

        data: {
            id_chamado: g_id_chamado
        },
        success: function(data) {

            if (g_auto_usuario >= 3) {

                $('#frmEditarChamado select[name=id_responsavel]').removeAttr('disabled');
                $('#frmEditarChamado select[name=id_responsavel]').html('');

                $.ajax({
                    url: base_url + 'json/usuarios',
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

            if ((data.id_responsavel == g_id_usuario || g_auto_usuario >= 3)) {

                $('#frmEditarChamado input[name=nome_solicitante]').removeAttr('disabled');
                $('#frmEditarChamado input[name=telefone]').removeAttr('disabled');
                $('#frmEditarChamado input[name=celular]').removeAttr('disabled');
                $('#frmEditarChamado input[name=nome_local]').removeAttr('disabled');
                $('#frmEditarChamado button[type=submit]').removeAttr('hidden');
                $('#frmEditarChamado button[type=submit]').removeAttr("disabled");
                $('#frmEditarChamado #btnCancelarEdicao').removeAttr('hidden');
                $('#btnDesbloquearChamado').removeAttr('disabled');

            } else {

                alert('Chamado bloqueado!');
                $('#btnEditarChamado').show();
            }




        }

    });




});

$('#btnCancelarEdicao').on('click', function(e) {

    carregaChamado(g_id_chamado, true);


    e.preventDefault();

    $('#frmEditarChamado input[name=nome_solicitante]').prop('disabled', 'true');
    $('#frmEditarChamado input[name=telefone]').prop('disabled', 'true');
    $('#frmEditarChamado input[name=celular]').prop('disabled', 'true');
    $('#frmEditarChamado input[name=nome_local]').prop('disabled', 'true');
    $('#frmEditarChamado button[type=submit]').prop('hidden', 'true');
    $('#frmEditarChamado select[name=id_responsavel]').attr('disabled', 'true');
    $('#frmEditarChamado #btnEditarChamado').show();

    $(this).prop('hidden', 'true');


});

$('#frmEditarChamado').on('submit', function(e) {

    e.preventDefault();


}).validate({
    rules: {
        nome_solicitante: "required",
        nome_local: "required",
        telefone: {
            required: true,
            digits: true,
            minlength: 3,
        },
        celular: {
            required: false,
            digits: true,
            minlength: 9,
        },

    },
    messages: {
        nome_solicitante: "Campo obrigatório!",
        nome_local: "Campo obrigatório!",
        telefone: {
            required: "Campo obrigatório!",
            digits: "Somente dígitos (0-9)!",
            minlength: "Mínimo 3 dígitos!"
        },
        celular: {
            required: "Campo obrigatório!",
            digits: "Somente dígitos (0-9)!",
            minlength: "Mínimo 9 dígitos!"
        },
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
            beforeSend: function() {

                var fechado = false;

                $('#frmEditarChamado input[type=submit]').prop("disabled", "true");
                $.ajax({
                    url: base_url + 'json/chamado',
                    dataType: 'json',

                    data: {
                        id_chamado: g_id_chamado
                    },
                    success: function(data) {
                        if (data.status_chamado = 'FECHADO') {

                            fechado = true;
                        }

                    }
                });

                if (fechado) {
                    alert("Operação não permitida: chamado fechado!");
                    return false;

                }


            },
            success: function(msg) {
                carregaChamado(g_id_chamado, true);
                atualizaInteracoes(g_id_chamado);

                $('#frmEditarChamado #alerta').prepend(msg);

                setTimeout(function() {
                    $('#msg_sucesso').alert('close')
                }, 2500);

                $('#frmEditarChamado input[name=nome_solicitante]').prop('disabled', 'true');
                $('#frmEditarChamado select[name=id_responsavel]').prop('disabled', 'true');
                $('#frmEditarChamado input[name=telefone]').prop('disabled', 'true');
                $('#frmEditarChamado input[name=celular]').prop('disabled', 'true');
                $('#frmEditarChamado input[name=nome_local]').prop('disabled', 'true');
                $('#frmEditarChamado button[type=submit]').prop('hidden', 'true');
                $('#frmEditarChamado #btnEditarChamado').show();
                $('#frmEditarChamado #btnCancelarEdicao').prop('hidden', 'true');

            },
            error: function(xhr, ajaxOptions, thrownError) {

                alert(thrownError);

                //$('#frmEditarChamado input[type=submit]').removeAttr("disabled");
            }

        });
    }

});


// ----------- ADMIN -------------------

// ----------- USUARIOS ----------------

const autorizacoes = [{
    Name: "Operação",
    Id: "2"
}, {
    Name: "Supervisão",
    Id: "3"
}, {
    Name: "Master",
    Id: "4"
}];

const estados = [{
    Name: "ATIVO",
    Id: "ATIVO"
}, {
    Name: "INATIVO",
    Id: "INATIVO"
}];

//--------------------


$("#usuarios-grid").jsGrid({
    
    width: "100%",
    height: "auto",

    autoload: true,
    inserting: true,
    editing: true,
    sorting: true,
    paging: true,
    filtering: true,

    loadMessage: "Carregando...",

    noDataContent: "(vazio)",

    insertRowLocation: "top",

    /* onDataLoaded: function(args)
    {
        args.grid.sort(6,"desc")

    }, */

    onItemInserted: function(args){
        alert(`${args.item.nome_usuario} cadastrado.`)
        args.grid.sort(6,"desc")
      
    },
    rowClick: function(args) {

        // Disable row click
        let id_usuario = args.item.id_usuario
        window.location.href = `${base_url}usuario/${id_usuario}`;
                
    // args.event.preventDefault();
    },

    controller: {
        loadData: async function(filter) 
        {
            var d = $.Deferred()

            const usuarios = await $.ajax({
                url: base_url + "usuario/listar_usuarios",
                dataType: "json"
            }).done(function(response) {

                d.resolve(response)

            })
            
            return $.grep(usuarios, function(u) {
                return (!filter.nome_usuario ||
                    u.nome_usuario.toUpperCase().indexOf(filter.nome_usuario.toUpperCase()) > -1) && 
                    (!filter.login_usuario ||
                        u.login_usuario.indexOf(filter.login_usuario) > -1) &&
                    (filter.autorizacao_usuario === "TODOS" ||
                    u.autorizacao_usuario === filter.autorizacao_usuario) &&
                    (filter.status_usuario === undefined || 
                        u.status_usuario === filter.status_usuario)
                })


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
        {
            name: "nome_usuario",
            type: "text",
            validate: "required",
            title: "Nome completo",
        }, {
            name: "login_usuario",
            type: "text",
            validate: "required",
            title: "Login de rede"
        }, {
            name: "autorizacao_usuario",
            type: "select",
            items: autorizacoes,
            textField: "Name",
            valueField: "Id",
            title: "Autorização",
            filterTemplate: function() {
                var $select = jsGrid.fields.select.prototype.filterTemplate.call(this);
                $select.prepend($("<option>").prop("value", "TODOS").prop("selected","true").text("Todas"));
                return $select;
            },
        }, {
            name: "triagem_usuario",
            type: "checkbox",
            title: "Triagem",
            filtering: false,
            // itemTemplate: function(value, item) {
            //     return item.triagem_usuario == 1 ? 
            //     "<input type=\"checkbox\" checked>" : "<input type=\"checkbox\">"
            // }
        }, {
            name: "encerramento_usuario",
            type: "checkbox",
            title: "Encerramento de chamados",
            filtering: false,
        },

        {
            name: "status_usuario",
            type: "checkbox",
            title: "Ativo?",
            insertTemplate: function() {
                return $("<input>").attr("type", "checkbox").prop("checked", true).prop("disabled", true); // Set to true for checked, false for unchecked
            },
            insertValue: function() {
                return true;
            }
        },

        {
            name: "alteracao_usuario",
            type: "text",
            readOnly: true,
            title: "Última alteração",
            filtering: false,
            inserting: false,
            updating: false,
        }, 

        {
            name: "data_usuario",
            type: "text",
            readOnly: true,
            title: "Data de cadastro",
            filtering: false,
            inserting: false,
            updating: false,
        },
        
        {
            name: "id_usuario",
            type: "text",
            readOnly: true,
            title: "Id_usuario",
            visible: false,
            filtering: false,
        },
        
        {
            type: "control",
            deleteButton: false
        },/*{ //botão para visualizar usuário
            name: "visualizar_usuario",
            type: "button",
            editButton: "false",
            readOnly: true,
            title: "Visualizar Usuário"
        }*/
    ]
});

$('#CheckTriagem').on('click' , function(){
    p_id_usuario = $(this).attr("id_usuario");
    permissao = 'triagem';
    registrarPermissoes(permissao, p_id_usuario);
});

$('#CheckEncerramento').on('click' , function(){
    p_id_usuario = $(this).attr("id_usuario");
    permissao = 'encerramento';
    registrarPermissoes(permissao, p_id_usuario);
});

$('#CheckInserviveis').on('click' , function(){
    p_id_usuario = $(this).attr("id_usuario");
    permissao = 'inserviveis';
    registrarPermissoes(permissao, p_id_usuario);
});

function registrarPermissoes(p_permissao, p_id_usuario){
    
    $.ajax({

        url: base_url + 'usuario/ativar_permissoes',
        
        type: 'POST',
        async: true,
        data: {
            id_usuario: p_id_usuario,
            permissao: p_permissao
        },
        success: function(data) {
            alert('Permissão de ' + p_permissao + ' alterada');
        },
        error: function(error) {
            alert('Erro ao atualizar a permissão do usuário.');
        }
    });
}

// ------------ FIM USUARIOS ---------------


// ------------- MODELO MENSAGENS --------------------
if ($('#pills-modelos-mensagens-tab').is(":visible")) {
    Promise.resolve(carregaFilas()).then(value => {
        let tipo = [
            {
                Name: "Atendimento",
                Id: "ATENDIMENTO"
            }, {
                Name: "Observação",
                Id: "OBSERVACAO"
            }, {
                Name: "Inservível",
                Id: "INSERVIVEL"
            }, {
                Name: "Alteração de fila",
                Id: "ALT_FILA"
            }, {
                Name: "Remover da espera",
                Id: "REM_ESPERA"
            }, {
                Name: "Deixar em espera",
                Id: "ESPERA"
            }
        ];

        $("#modelos-mensagens-grid").jsGrid({
            width: "100%",
            height: "auto",
    
            autoload: true,
            inserting: true,
            editing: true,
            sorting: true,
            paging: true,
            filtering: true,
    
            loadMessage: "Carregando...",
    
            noDataContent: "(vazio)",

            /* onDataLoaded: function(args)
            {
                args.grid.sort(5,"desc")

            }, */

            onItemInserted: function(args){
                alert(`Modelo cadastrado.`)
                args.grid.sort(5,"desc")
            },
    
            controller: {
                loadData: async function(filter) {
                    var d = $.Deferred();

                    const msgs = await $.ajax({
                        url: base_url + "ModeloMensagem/listar_modelo_mensagem/",
                        dataType: "json"
                    }).done(function(response) {
                        d.resolve(response);
                    });

                    return $.grep(msgs, function(mm) {
                        return (filter.fila_modelo_mensagem === "TODOS" ||
                            mm.fila_modelo_mensagem == filter.fila_modelo_mensagem) && 
                            (filter.tipo_modelo_mensagem === "TODOS" ||
                                mm.tipo_modelo_mensagem === filter.tipo_modelo_mensagem) &&
                            (!filter.mensagem_modelo_mensagem || 
                                mm.mensagem_modelo_mensagem.indexOf(filter.mensagem_modelo_mensagem) > -1) &&
                            (filter.status_modelo_mensagem === undefined || 
                                mm.status_modelo_mensagem === filter.status_modelo_mensagem);
                    });
                   
                },
                updateItem: function(item) {
                    return $.ajax({
                        type: "POST",
                        url: base_url + "modeloMensagem/atualizar_modelo_mensagem",
                        data: item,
                        dataType: "json"
                    });
                },
                insertItem: function(item) {
                    return $.ajax({
                        type: "POST",
                        url: base_url + "modeloMensagem/inserir_modelo_mensagem",
                        data: item,
                        dataType: "json"
                    });
                },
                
                
            },
    
            fields: [
                {
                    name: "mensagem_modelo_mensagem",
                    type: "text",
                    validate: "required",
                    title: "Texto",
                   
                }, 
                {
                    name: "fila_modelo_mensagem",
                    type: "select",
                    items: value,
                    textField: "nome_fila",
                    valueField: "id_fila",
                    title: "Fila",
                    filterTemplate: function() {
                        var $select = jsGrid.fields.select.prototype.filterTemplate.call(this);
                        $select.prepend($("<option>").prop("value", "TODOS").prop("selected","true").text("Todas"));
                        return $select;
                    },
                    
                },
                {
                    name: "tipo_modelo_mensagem",
                    type: "select",
                    items: tipo,
                    textField: "Name",
                    valueField: "Id",
                    title: "Tipo",
                    filterTemplate: function() {
                        var $select = jsGrid.fields.select.prototype.filterTemplate.call(this);
                        $select.prepend($("<option>").prop("value", "TODOS").prop("selected","true").text("Todos"));
                        return $select;
                    },
                    
                    
                }, {
                    name: "status_modelo_mensagem",
                    type: "checkbox",
                    title: "Ativo?",
                    insertTemplate: function() {
                        return $("<input>").attr("type", "checkbox").prop("checked", true).prop("disabled", true); // Set to true for checked, false for unchecked
                    },
                    insertValue: function() {
                        return "true"; // Set to true for checked
                    }
                }, {
                    name: "data_modelo_mensagem",
                    type: "text",
                    readOnly: true,
                    title: "Data de criação",
                    filtering: false,
                    inserting: false,
                },  {
                    name: "alterado_modelo_mensagem",
                    type: "text",
                    readOnly: true,
                    title: "Última alteração",
                    filtering: false,
                    inserting: false,
                }, {
                    type: "control",
                    deleteButton: false
                }
            ]
        });
    });
}

//------------- FIM MODELO MENSAGENS ------------------

// ------------- LOCAIS --------------------
const secretarias = Promise.resolve(carregaSecretarias());
secretarias.then(value => {
    const regiao = [
        {
            NAME: "INTERNA",
            ID: "INTERNA"
        },
        {
            NAME: "CENTRO",
            ID: "CENTRO"
        },
        {
            NAME: "NORTE",
            ID: "NORTE"
        },
        {
            NAME: "SUL",
            ID: "SUL"
        },
        {
            NAME: "OESTE",
            ID: "OESTE"
        },
        {
            NAME: "LESTE",
            ID: "LESTE",
        },
    ]

    $("#locais-grid").jsGrid({
        width: "100%",
        height: "auto",
    
        autoload: true,
        inserting: true,
        editing: true,
        sorting: true,
        paging: true,
        filtering: true,
    
        loadMessage: "Carregando...",
    
        noDataContent: "(vazio)",

        /* onDataLoaded: function(args)
        {
            args.grid.sort(5,"desc")

        }, */

        onItemInserted: function(args){
            
            alert(`${args.item.nome_local} cadastrado.`)
            args.grid.sort(5,"desc")
        
        },

        

     
    
        controller: {
            loadData: async function(filter) {
                var d = $.Deferred();

                const locais = await $.ajax({
                    url: base_url + "local/listar_locais/",
                    dataType: "json"
                }).done(function(response) {
                    d.resolve(response);
                });
                
                return $.grep(locais, function(l) {
                    
                    return (!filter.nome_local ||
                        l.nome_local.toUpperCase().indexOf(filter.nome_local.toUpperCase()) > -1) && 
                        (!filter.endereco_local ||
                            l.endereco_local.indexOf(filter.endereco_local) > -1) &&
                        (filter.secretaria_local === "TODOS" ||
                        l.secretaria_local === filter.secretaria_local) &&
                        (filter.status_local === undefined || 
                            l.status_local === filter.status_local) &&
                        (filter.regiao_local === "TODOS" ||
                            l.regiao_local === filter.regiao_local);
                });
                
            },
            updateItem: function(item) {
                return $.ajax({
                    type: "POST",
                    url: base_url + "local/atualizar_local",
                    data: item,
                    dataType: "json"
                });
            },
            insertItem: function(item) {
                return $.ajax({
                    type: "POST",
                    url: base_url + "local/inserir_local",
                    data: item,
                    dataType: "json"
                });
            },
        },
        rowClick: function(args) {
            
            // Disable row click
            let id_local = args.item.id_local;
            window.location.href = `${base_url}local/${id_local}`;
        // args.event.preventDefault();*/
        },

        onItemInserting: function(args){
            for(i=0; i < args.grid.data.length; i++){
                if(args.item.nome_local == args.grid.data[i].nome_local){
                    args.cancel = true;
                    alert('O local ' + args.item.nome_local + ' já existe');
                }
            }
        },
        
        onItemUpdating: function(args) {
            for(i=0; i < args.grid.data.length; i++){
                if(args.item.nome_local == args.grid.data[i].nome_local && args.item.id_local != args.grid.data[i].id_local)
                {
                    args.cancel = true;
                    alert('O local ' + args.item.nome_local + ' já existe');
                }
            }
        
        },

    
        fields: [
            {
                name: "nome_local",
                type: "text",
                validate: [
                    { validator: "pattern", message: "Atenção!\nDigite um nome para o local.", param: "[a-zA-Z0-9]+.+" },
                ],
                title: "Nome",
            }, {
                name: "endereco_local",
                type: "text",
                validate: [
                    {validator: "pattern", message: "Atenção!\nDigite um endereço.", param: "[a-zA-Z0-9]+.+" },
                ],
                title: "Endereço"
            }, {
                name: "secretaria_local",
                type: "select",
                items: value,
                textField: "sigla_secretaria",
                valueField: "id_secretaria",
                title: "Secretaria",
               /*
                insertTemplate: function() {
                    let secretariasAtivas = value.filter((objeto) => {
                        return objeto.status_secretaria === true;
                    });

                    let select = $("<select id='sigla-secretaria-locais'>");
                    secretariasAtivas.forEach(secretaria => {
                        select.append($("<option>", {
                            value: secretaria.id_secretaria,
                            text: secretaria.sigla_secretaria
                        }));
                    });
                    return select;
                },
                */
                filterTemplate: function() {
                    var $select = jsGrid.fields.select.prototype.filterTemplate.call(this);
                    $select.prepend($("<option>").prop("value", "TODOS").prop("selected","true").text("Todas"));
                    return $select;
                },
                
                /*insertValue: function() {
                    return $("#sigla-secretaria-locais").val();
                },*/
                
                /*editTemplate: function() {
                    let secretariasAtivas = value.filter((objeto) => {
                        return objeto.status_secretaria === true;
                    });

                    let select = $("<select id='sigla-secretaria-locais'>");
                    //let select = $('#sigla-secretaria-locais select');
                    secretariasAtivas.forEach(secretaria => {
                        select.append($("<option>", {
                            value: secretaria.id_secretaria,
                            text: secretaria.sigla_secretaria
                        }));
                    });
                    return select;
                },*/
                /*editValue: function() {
                
                    return $("#sigla-secretaria-locais").val();
                
                }*/
                 
            }, {
                name: "regiao_local",
                type: "select",
                items: regiao,
                textField: "NAME",
                valueField: "ID",
                title: "Região",
                filterTemplate: function() {
                    var $select = jsGrid.fields.select.prototype.filterTemplate.call(this);
                    $select.prepend($("<option>").prop("value", "TODOS").prop("selected","true").text("Todas"));
                    return $select;
                },
            }, {
                name: "status_local",
                type: "checkbox",
                title: "Ativo?",
                insertTemplate: function() {
                    return $("<input>").attr("type", "checkbox").prop("checked", true).prop("disabled", true); // Set to true for checked, false for unchecked
                },
                insertValue: function() {
                    return "true"; // Set to true for checked
                }
                
            },
            {
                name: "infovia",
                type: "checkbox",
                title: "Infovia",
                insertTemplate: function() {
                    return $("<input>").attr("type", "checkbox").prop("checked", true).prop("disabled", true); // Set to true for checked, false for unchecked
                },
                insertValue: function() {
                    return "true"; // Set to true for checked
                }
                
            },
            {
                name: "alteracao_local",
                type: "text",
                readOnly: true,
                title: "Última alteração",
                filtering: false,
                inserting: false,
            },
            {
                type: "control",
                deleteButton: false
            }
        ]
    });
}).catch(err => {
    console.error(err);
})


//------------- FIM LOCAIS ------------------

// ------------- SECRETARIAS -----------------
$("#secretarias-grid").jsGrid({
    width: "100%",
    height: "auto",

    autoload: true,
    inserting: true,
    editing: true,
    sorting: true,
    paging: true,
    filtering: true,

    

    loadMessage: "Carregando...",

    noDataContent: "(vazio)",

    /* onDataLoaded: function(args)
    {
        args.grid.sort(3,"desc")

    }, */

    onItemInserted: function(args){
        alert(`${args.item.nome_secretaria} cadastrada.`)
        args.grid.sort(3,"desc")
      
    },

    controller: {
        loadData: async function(filter) {

            const _sect = await Promise.resolve(secretarias)
          

            return $.grep(_sect, function(s) {
                
                return (!filter.nome_secretaria ||
                    s.nome_secretaria.toUpperCase().indexOf(filter.nome_secretaria.toUpperCase()) > -1) && 
                    (!filter.sigla_secretaria ||
                        s.sigla_secretaria.indexOf(filter.sigla_secretaria) > -1) &&
                    (filter.status_secretaria === undefined || 
                        s.status_secretaria === filter.status_secretaria)
            });
           
            
        },
        updateItem: function(item) {
            return $.ajax({
                type: "POST",
                url: base_url + "secretaria/atualizar_secretaria",
                data: item,
                dataType: "json"
            });
        },
        insertItem: function(item) {
            return $.ajax({
                type: "POST",
                url: base_url + "secretaria/inserir_secretaria",
                data: item,
                dataType: "json"
            });
        },
    },

    

    fields: [
        {
            name: "nome_secretaria",
            type: "text",
            validate: [
                { validator: "pattern", message: "Atenção!\nDigite um nome para a secretaria.", param: "[a-zA-Z0-9]+.+" },
            ],
            title: "Nome",
            
        }, {
            name: "sigla_secretaria",
            type: "text",
            validate: [
                { validator: "pattern", message: "Atenção!\nDigite uma sigla para a secretaria.", param: "[a-zA-Z0-9]+.+" },
            ],
            title: "Sigla"
        }, {
            name: "status_secretaria",
            type: "checkbox",
            title: "Ativa?",
            insertTemplate: function() {
                return $("<input>").attr("type", "checkbox").prop("checked", true).prop("disabled", true); // Set to true for checked, false for unchecked
            },
            insertValue: function() {
                return "true"; // Set to true for checked
            }
        },
        {
            name: "ultima_alteracao",
            type: "text",
            readOnly: true,
            title: "Última alteração",
            filtering: false,
            inserting: false
        },
        {
            type: "control",
            deleteButton: false
        }
    ]
});

//------------- FIM SECRETARIAS ------------------

//------------ SERVICOS --------------------------
async function carregarTodosServicos() {
    var d = $.Deferred();

    const servicos = await $.ajax({
        url: base_url + "servico/listar_servicos_triagem/",
        dataType: 'json',
    }).done(function(response) {
        d.resolve(response);
    });

    return servicos
}

Promise.resolve(carregaFilas()).then(filas => {
    $("#servicos-grid").jsGrid({
        width: "100%",
        height: "auto",
    
        autoload: true,
        inserting: true,
        editing: true,
        sorting: true,
        paging: true,
        invalidMessage: "Erro!",
        filtering: true,
    
        loadMessage: "Carregando...",
    
        noDataContent: "(vazio)",

        /* onDataLoaded: function(args) {
            args.grid.sort(6,"DESC")
        }, */

        onItemInserting: function(args){
            for(i=0; i < args.grid.data.length; i++){
                if(args.item.nome_servico.toUpperCase() == args.grid.data[i].nome_servico){
                    args.cancel = true;
                    alert('O servico ' + args.item.nome_servico.toUpperCase() + ' já existe');
                }
            }
        },

        onItemInserted: function(args){
            alert(`Serviço ${args.item.nome_servico} cadastrado.`)
            args.grid.sort(6,"DESC")
        },

        onItemUpdating: function(args){
            for(i=0; i < args.grid.data.length; i++){
                if(args.item.nome_servico.toUpperCase() == args.grid.data[i].nome_servico && args.item.id_servico != args.grid.data[i].id_servico){
                    args.cancel = true;
                    alert('O servico ' + args.item.nome_servico.toUpperCase() + ' já existe');
                }
            }
        },
    
        controller: {
            loadData: async function(filter) {

                var d = $.Deferred();

                const servicos = await $.ajax({
                    url: base_url + "servico/listar_servicos_triagem/",
                    dataType: 'json',
                }).done(function(response) {
                    d.resolve(response);
                });

               
               

                return $.grep(servicos, function(s) {
                    
                    return (!filter.nome_servico ||
                        s.nome_servico.toUpperCase().indexOf(filter.nome_servico.toUpperCase()) > -1) && 
                        (filter.id_fila_servico === 0 ||
                        s.id_fila_servico == filter.id_fila_servico) &&
                        (filter.status_servico === undefined || 
                            s.status_servico === filter.status_servico)
                });
            },
            updateItem: function(item) {
                return $.ajax({
                    type: "POST",
                    url: base_url + "servico/atualizar_servico",
                    dataType: "json",
                    data: item,
                });
            },
            insertItem: function(item) {
                return $.ajax({
                    type: "POST",
                    url: base_url + "servico/inserir_servico",
                    data: item,
                    dataType: "json"
                });
            },
        },
    
        fields: [
            {
                name: "nome_servico",
                type: "text",
                validate: [
                    { validator: "pattern", message: "Atenção!\nDigite um nome para o serviço.", param: "[a-zA-Z0-9]+.+" },
                ],
                title: "Serviço"
            }, {
                name: "id_fila_servico",
                title: "Fila",
                type: "select",
                align: "center",
                autosearch: true,
                items: filas,
                valueField: "id_fila",
                textField: "nome_fila",
                selectedIndex: -1,
                valueType: "number",
                readOnly: false,
                filterTemplate: function() {
                    var $select = jsGrid.fields.select.prototype.filterTemplate.call(this);
                    $select.prepend($("<option>").prop("value", 0).prop("selected","true").text("Todas"));
                    return $select;
                },
            }, 
            {
                name: "unidade_medida",
                type: "text",
                validate: [
                    "required",
                    { validator: "maxLength", param: 3, message: "Atenção!\nEste campo deve ser preenchido e conter no máximo 3 caracteres." },
                ],
                title: "Unidade de medida",
                filtering: false,
                insertTemplate: function() {
                    var $input = jsGrid.fields.text.prototype.insertTemplate.call(this);
                    $input.attr("type", "text").val("UN");
                    return $input
                },
                
                
            },
            
            {
                name: "pontuacao_servico",
                type: "text",
                validate: [
                    "required",
                    { validator: "pattern", param: /^[12345]+$/, message: "Atenção!\nEste campo aceita um dígito de 1 a 5." },
                ],
                title: "Complexidade (1 a 5)",
                filtering: false,
            },
            {
                name: "valor_servico",
                type: "text",
                validate: [
                    "required",
                    { validator: "pattern", param: /^\d+(\.\d{1,2})?$/, message: "Atenção!\nNão utilize a virgula \",\" Utilize apanes o ponto \".\"\nExemplo: 99.99" },
                ],
                title: "Valor unitário (R$)",
                filtering: false,
            },
            {
                name: "status_servico",
                type: "checkbox",
                title: "Ativo?",
                insertTemplate: function() {
                    return $("<input>").attr("type", "checkbox").prop("checked", true).prop("disabled", true); // Set to true for checked, false for unchecked
                },
                insertValue: function() {
                    return "true"; // Set to true for checked
                }
            },
            {
                name: "data_ultima_alteracao",
                type: "text",
                readOnly: true,
                title: "Última alteração",
                filtering: false,
                inserting: false
            },
            {
                type: "control",
                deleteButton: false
            }
        ]
    });
});


//------------ FIM SERVICOS --------------------------

//------------ BANCADAS--------------------------
async function carregarBancadas(status = 1){

    let lista_bancadas = [];
    //let status = 1;
    await $.ajax({

        url: base_url + "bancada/lista_bancadas/",
        type: 'POST',
        async: true,
        dataType: 'json',
        data:{
            status
        },
        success: function(data) {
            lista_bancadas = data;
        }
    });
    return lista_bancadas;
}


    $("#bancadas-grid").jsGrid({
        width: "100%",
        height: "auto",
    
        autoload: true,
        inserting: true,
        editing: true,
        sorting: true,
        paging: true,
        invalidMessage: "Erro!",
        filtering: false,
    
        loadMessage: "Carregando...",
    
        noDataContent: "(vazio)",
        
        onItemInserting: function(args){
            
            let tamanho = args.grid.data.length;
            
            args.item.nome_bancada = '#' + (tamanho + 1);
        },
        onItemEditing: function(args){
            args.grid.fields[1].editing = true;
            if(args.item.ocupado_bancada == true){
                args.grid.fields[1].editing = false;
            }
        },

        onItemInserted: function(args){
            alert(`Bancada ${args.item.nome_bancada} cadastrada.`)
        },

        controller: {
            loadData: function() {
                return carregarBancadas();
            },
            updateItem: function(item) {
                if(item.ocupado_bancada == false){
                    return $.ajax({
                        type: "POST",
                        url: base_url + "bancada/atualizar_bancada",
                        dataType: "json",
                        data: item,
                    });
                }
                
            },
            insertItem: function(item) {
                return $.ajax({
                    type: "POST",
                    url: base_url + "bancada/inserir_bancada",
                    data: item,
                    dataType: "json"
                });
            },
        },
    
        fields: [
            {
                name: "nome_bancada",
                type: "text",
                insertTemplate: function() {
                    let input = this.__proto__.insertTemplate.call(this); //original input
                
                    input.val('#');
                    input.prop("disabled", true);
                
                    return input;
                },
                title: "Identificação",
                editing: false,
            }, {
                name: "status_bancada",
                type: "checkbox",
                title: "Ativa?",
                insertTemplate: function() {
                    return $("<input>").attr("type", "checkbox").prop("checked", true).prop("disabled", true); // Set to true for checked, false for unchecked
                },
                insertValue: function() {
                    return "true"; // Set to true for checked
                }
            },{
                name: "ocupado_bancada",
                type: "checkbox",
                title: "Ocupada?",
                editing: false,
                insertTemplate: function() {
                    return $("<input>").attr("type", "checkbox").prop("checked", true).prop("disabled", true); // Set to true for checked, false for unchecked
                },
                insertValue: function() {
                    return "true"; // Set to true for checked
                }
            },
            {
                type: "control",
                //editButton: false,
                deleteButton: false
            }
        ]
    });


//------------ FIM BANCADA --------------------------

// ---------------- LOG DE EVENTOS --------------


$('#tblEventos').DataTable({ //  tabela de eventos

    "autoWidth": true,

    "columnDefs": [{
        "width": "10%",
        "targets": 3,
        "render": $.fn.dataTable.render.moment('YYYY-MM-DD HH:mm:ss', 'DD/MM/YYYY H:mm:ss')
    }, {
        "width": "10%",
        "targets": 0
    }, {
        "width": "40%",
        "targets": 2
    }],

    "orderCellsTop": false,
    "fixedHeader": true,

    "language": {
        "decimal": "",
        "emptyTable": "(vazio)",
        "info": "Mostrando _START_ a _END_ de _TOTAL_ eventos",
        "infoEmpty": "Mostrando 0 a 0 de 0 eventos",
        "infoFiltered": "(filtrado de _MAX_ eventos)",
        "infoPostFix": "",
        "thousands": ".",
        "lengthMenu": "Mostrando _MENU_ eventos",
        "loadingRecords": "Carregando...",
        "processing": "Processando...",
        "search": "Busca:",
        "zeroRecords": "Sem resultados!",
        "paginate": {
            "first": "Primeiro",
            "last": "Último",
            "next": "Próximo",
            "previous": "Anterior"
        },
    },

    "ajax": base_url + 'admin/listar_eventos/',

    "order": [
        [0, "desc"]
    ]


});


setInterval(function() { //atualiza o log de eventos

    $('#tblEventos').DataTable().ajax.reload(null, false);


}, 30000); 

// ----------- /LOG DE EVENTOS -------------

//---------------- TRIAGEM -------------------------

UTF8 = {
    encode: function(s) {
        for (var c, i = -1, l = (s = s.split("")).length, o = String.fromCharCode; ++i < l; s[i] = (c = s[i].charCodeAt(0)) >= 127 ? o(0xc0 | (c >>> 6)) + o(0x80 | (c & 0x3f)) : s[i]);
        return s.join("");
    },
    decode: function(s) {
        for (var a, b, i = -1, l = (s = s.split("")).length, o = String.fromCharCode, c = "charCodeAt"; ++i < l;
            ((a = s[i][c](0)) & 0x80) &&
            (s[i] = (a & 0xfc) == 0xc0 && ((b = s[i + 1][c](0)) & 0xc0) == 0x80 ?
                o(((a & 0x03) << 6) + (b & 0x3f)) : o(128), s[++i] = "")
        );
        return s.join("");
    }
};

$("#tblAnexos").jsGrid({
    width: '100%',
    autoload: false,
    editing: false,
    inserting: false,
    noDataContent: "Sem anexos.",
    deleteConfirm: "Tem certeza?",
    fields: [
        { 
            name: "id_arquivo",
            title: "ID",
            type: "text",
            visible: false, 
                
        },
        { 
            name: "nome_arquivo",
            title: "Nome do arquivo",
            type: "text", 
            
        },
        {
            type: "control",
            deleteButton: true,
            editButton: false,
        }
    ],
    rowClick: function(args) {
        window.open(base_url + 'anexo_otrs/' + args.item.id_arquivo,'_blank ');
    }
});

var agrupamento = false;
var diffDesc = null


$("#slctEquipe").on('change', function() {
    var equipe = $(this).val()
    if (equipe == 0) {
        $("#linhaInfra").toggle()
        $("#linhaSuporte").toggle()

    } else {
        $("#linhaInfra").toggle()
        $("#linhaSuporte").toggle()

    } 
    
})


var servicos = []
var servico_atualizado = []



async function carregaTriagem(p_id_ticket) {



    //traz os dados do chamado MIGRADO (OTOBO)

    document.title = "Triagem #" + p_id_ticket + " - SIGAT";

    var anexos = [];
    
    // CARREGANDO ANEXOS OTOBO ...
    
    await $.ajax({
        url: base_url + 'triagem/carregar_anexos_ticket',
        dataType: 'json',
        async: true,
        data: {
            id_ticket: p_id_ticket
        },
        success: function(data) {

            $("#linhaInfoTriagem").show();
            
            if (data.length > 0) {

                data.forEach(function(item){
                    anexos.push({id_arquivo:item.id,nome_arquivo:item.filename})

                })   
            }            
        },
    });

    await $.ajax({
        url: base_url + 'servico/listar_servicos_triagem/1',
        dataType: 'json',
        async: true,
        type: "POST",
        data: {
            filas: id_fila_sigat
        },
        success: function(data) {
            servicos = data
        },
    });
    
    $("#tblServicos").jsGrid({
        width: '100%',
        autoload: false,
        editing: true,
        inserting: true,
        noDataContent: "Lista vazia.",
        confirmDeleting: false,
        deleteConfirm: "Tem certeza?",
        sorting: true,
     
        fields: [
            { 
                name: "id_servico",
                title: "Nome do serviço",
                type: "select",
                items: servicos, valueField: "id_servico", textField: "nome_servico", 
                visible: true, 
                    
            },
            
            {
                type: "control",
                deleteButton: true,
                editButton: true,
                insertButton: true,
            }
        ],
        onItemInserting: function(args) {

            var s = args.item.id_servico;
            
            var info_servico = servicos.find(e => e.id_servico == s)

            args.item.grupo_servico = info_servico.grupo_servico
            args.item.quantidade = 1;
            
           
            
            // args.item.timestamp = Date.now().toString()
            

        },

        onItemInserted: function(args) {
            let servicos = args.grid.data[args.grid.data.length - 1];
            for(i=0; i< args.grid.data.length - 1; i++){
                if(args.grid.data[i].id_servico == servicos.id_servico){
                     alert('Este serviço já foi cadastrado');
                     args.grid.data.pop();
                }
             }

        },

        onItemUpdating: function(args) {

            var s = args.item.id_servico

            var info_servico = servicos.find(e => e.id_servico == s)

            args.previousItem.grupo_servico = info_servico.grupo_servico

        },
     
    });
    


    verificaAutoEquip();
    $("#tblAnexos").jsGrid("option","data",anexos);

}




function uniq_fast(a) {
    var seen = {};
    var out = [];
    var len = a.length;
    var j = 0;
    for(var i = 0; i < len; i++) {
         var item = a[i];
         if(seen[item] !== 1) {
               seen[item] = 1;
               out[j++] = item;
         }
    }
    return out;
}

var g_equips = [];

async function verificaStatusEquip(p_e, return_array = false) {
    out = null;
    await $.ajax({
        method: "post",
        url: base_url + "json/status_equipamento",
        data: { 
            e_status: p_e,
            isArray: return_array
        }
    }).done(function( res ) {
        out = res;
    });

    return out;
}

async function verificaDescEquip(p_e) {
    out = null;
    await $.ajax({
        method: "post",
        url: base_url + "json/desc_equipamento/" + p_e,
      })
        .done(function( res ) {
            if (res !== "") {
                out = res;
            }
        });
    return out;
}

async function verificaAutoEquip() {
    var nums_equip = [];
    var out = [];
    var ocorrencias = [];
   
    $("#btnValidaEquip").prop("disabled","true");
    $("#pbEquips").css("width","0%");
    
    var text = $("#accordionArticles .card .card-body").html();

    $("#accordionArticles .card .card-body").each(function(index) {

        text = text + $(this).html();

    })
   
    nums_equip = text.match(patrimonio_regex);

    if (nums_equip !== null) {
        if (nums_equip.length > 0) {
            nums_equip = uniq_fast(nums_equip);
            
            percentage = (100*1)/nums_equip.length;
            total_percentage = 0;
            for (i=0;i<nums_equip.length;i++) {
    
                var status = await verificaStatusEquip(nums_equip[i]);
    
                if (status !== null) {
                    if (status.status_equipamento_chamado === "ABERTO" || 
                        status.status_equipamento_chamado === "ENTREGA" ||
                        status.status_equipamento_chamado === "ESPERA" ||
                        status.status_equipamento_chamado === "REPARO" ||
                        status.status_equipamento_chamado === "REMESSA" ||
                        status.status_equipamento_chamado === "GARANTIA" ||
                        status.status_equipamento_chamado === "INSERVIVEL"
                        ) {
                        ocorrencias.push({"Número":nums_equip[i],"Status":status.status_equipamento_chamado,"ID":status.id_chamado,"Ticket":status.ticket_chamado})
                    }
                }
    
                
    
                var res = await verificaDescEquip(nums_equip[i]);
    
    
                out.push({"Número":nums_equip[i],"Descrição":res.descricao});
    
                total_percentage = total_percentage + percentage;
                $("#pbEquips").css("width",total_percentage+"%");   
            }
            $("#tblEquips").jsGrid("option","data",out);
            g_equips = out;
            $("#btnValidaEquip").removeAttr("disabled");
            $("#btnLoteEquip").removeAttr("disabled");
        } 
    }
    else {

        $("#btnValidaEquip").removeAttr("disabled");
        $("#btnLoteEquip").removeAttr("disabled");
    } 
    
}


$("#tblEquips").jsGrid({
    width: '100%',
    autoload: false,
    editing: true,
    inserting: true,
    confirmDeleting: false,
    noDataContent: "Sem equipamentos.",
    fields: [
        { 
            name: "Número", 
            type: "text", 
            width: 50,
            validate: "required",     
        },
        { 
            name: "Descrição", 
            type: "text", 
            width: 50,
            validate: "required",
        },
        {
            type: "control",
            deleteButton: true
        }
    ],
});


var ocorrencias = [];

var confirmado = false;


$("#btnValidaEquip").on('click', async function() {


    agrupamento ? confirmado = true : confirmado = false;

    $(this).prop("disabled","true");
    
    $("#pbEquips").css("width","0%");  
   
    var grid_equips = $("#tblEquips").jsGrid("option","data");
    g_equips = [];
    var erros = [];
    
    ocorrencias = [];

    percentage = (100*1)/grid_equips.length;
    total_percentage = 0;

    if (grid_equips.length > 0) {
        for (i=0;i<grid_equips.length;i++) {
            if (grid_equips[i].Número == "" && grid_equips[i].Descrição == "") {
                erros.push("Existem itens vazios na lista!\n");
            }
            else {
                if (grid_equips[i].Número == "") {
                    erros.push("O item "+grid_equips[i].Descrição+" está sem número!\n");
                }
                else {
                    var status = await 
                    verificaStatusEquip(grid_equips[i].Número, true);
                    if (status !== null) {
                        for(j=0; j < status.length; j++){
                            if (status[j].status_equipamento_chamado === "ABERTO" || 
                            status[j].status_equipamento_chamado === "ENTREGA" ||
                            status[j].status_equipamento_chamado === "ESPERA" ||
                            status[j].status_equipamento_chamado === "INSERVIVEL" ||
                            status[j].status_equipamento_chamado === "REPARO" ||
                            status[j].status_equipamento_chamado === "GARANTIA" ||
                            status[j].status_equipamento_chamado === "REMESSA") {
                                ocorrencias.push({"Número":grid_equips[i].Número,"Status":status[j].status_equipamento_chamado,"ID":status[j].id_chamado,"Ticket":status[j].ticket_chamado})
                               
                            }
                        }
                    }
                }

                var res = await verificaDescEquip(grid_equips[i].Número);
                if (res.descricao !== null)
                    grid_equips[i].Descrição = res.descricao;

                if (grid_equips[i].Descrição === null) {
                    erros.push("O item "+grid_equips[i].Número+" está sem descrição!\n");
                }
            }
            
            total_percentage = total_percentage + percentage;
            $("#pbEquips").css("width",total_percentage+"%"); 
        }

        if (erros.length == 0) {
            $("#tblEquips").jsGrid("option","data",grid_equips); 
            if (ocorrencias.length > 0) {
                $('#modalOcorrencias').modal('show');
                $(this).removeAttr("disabled");
            }
            else {
                confirmado = true;
                g_equips = grid_equips;
                $(this).html('<i class="fa fa-check"></i> Confirmado!');
                $("#tblEquips").jsGrid("fieldOption", 2, "visible", false);
                $("#tblEquips").jsGrid("option","editing", false);
                $("#tblEquips").jsGrid("option","inserting", false);
                $(this).prop("disabled","true");
                $("#btnAlteraEquip").removeAttr("disabled");
                $("#btnLoteEquip").prop("disabled","true");
            }
            
        }
        else {
            alert(erros);
            $(this).removeAttr("disabled");
        }
    }
    else {
        if (agrupamento == false) {
            alert("A lista está vazia!");
            $(this).removeAttr("disabled");   
        }
        else {
            $(this).html('<i class="fa fa-check"></i> Confirmado!');
            $("#btnAlteraEquip").removeAttr("disabled");
            $("#btnLoteEquip").prop("disabled","true");
           
        }
        
    }
});

$('#modalOcorrencias').on('hidden.bs.modal', function (e) {

    $("#tblOcorrencias").jsGrid("option","data",[]); //resetando a tabela de ocorrencias

});

$('#modalOcorrencias').on('shown.bs.modal', function (e) {
    
    $("#tblOcorrencias").jsGrid({
        width: '100%',
        autoload: false,
        editing: false,
        inserting: false,
        data: ocorrencias,
        fields: [
            { 
                name: "Número", 
                type: "text", 
                width: 50,
                validate: "required",     
            },
            { 
                name: "Status", 
                type: "text", 
                width: 20,
                validate: "required",
            },
            { 
                name: "ID", 
                type: "text", 
                width: 30,
                validate: "required",
            },
            { 
                name: "Ticket", 
                type: "text", 
                width: 50,
                validate: "required",
            },
        ],
        rowClick: function(args) {
            window.open(base_url + 'chamado/' + args.item.ID,'_blank ');
        }
    });
})

$("#btnAlteraEquip").on('click', function() {

    confirmado = false;

    g_equips = [];

    $("#btnValidaEquip").html('<i class="fa fa-check"></i> Confirmar!');

    $("#tblEquips").jsGrid("fieldOption", 2, "visible", true);

    $("#tblEquips").jsGrid("option","editing", true);

    $(this).prop("disabled","true");

    $("#btnValidaEquip").removeAttr("disabled");
    $("#btnLoteEquip").removeAttr("disabled");
    

})

$("#btnLoteEquip").on('click', function() {

    confirmado = false;

    $('#modalLote').modal('show');

})

$("#radLoteFaixa").on('click', function() {

    $("#divListaLote").hide();
    $("#divFaixaLote").show();

})

$("#radLoteLista").on('click', function() {

    $("#divFaixaLote").hide();
    $("#divListaLote").show();
    
})

$("#btnInsereLote").on('click', async function() {

    if ($("#radLoteFaixa").is(':checked')) {

        var inicio = Number($("#txtInicioFaixaLote").val());
        var fim = Number($("#txtFimFaixaLote").val());

        if((fim - inicio) < 1 || isNaN(fim - inicio))  {

            alert("Faixa inválida");
            
        }
        else {

            $(this).prop("disabled","true");

            percentage = (100*1)/(fim - inicio);
            total_percentage = 0;

            var grid_atual = $("#tblEquips").jsGrid("option","data");

            var grid_faixa = [];

            var i = inicio;

            while (i <= fim) {

                var desc = null;

                var res = await verificaDescEquip(i);
                if (res.descricao !== null)
                    desc = res.descricao;

                grid_faixa.push({Número:i,Descrição:desc});

                i++;

                total_percentage = total_percentage + percentage;
                $("#pbLote").css("width",total_percentage+"%"); 
                

            }

            novo_grid = grid_atual.concat(grid_faixa);

            $("#tblEquips").jsGrid("option","data",novo_grid);

            $("#modalLote").removeClass('fade').modal('hide');
            $("#modalLote").modal('dispose');

            $(this).removeAttr("disabled");   
        }
    }
    
    if ($("#radLoteLista").is(':checked')) {

        var grid_atual = $("#tblEquips").jsGrid("option","data");

        if ($('#txtListaLote').val() == "")
            return;

        $(this).prop("disabled","true");

        var linhas = $('#txtListaLote').val().split('\n');

        percentage = (100*1)/(linhas.length);
        total_percentage = 0;

        var grid_lista = [];

        for(var i = 0;i < linhas.length;i++){
           
            var desc = null;
         
            var res = await verificaDescEquip(linhas[i]);
            if (res.descricao !== null)
                desc = res.descricao;

            grid_lista.push({"Número":linhas[i],"Descrição":desc});

            total_percentage = total_percentage + percentage;
            $("#pbLote").css("width",total_percentage+"%"); 
        }

        novo_grid = grid_atual.concat(grid_lista);

        $("#tblEquips").jsGrid("option","data",novo_grid);

        $("#modalLote").removeClass('fade').modal('hide');
        $("#modalLote").modal('dispose');

        $(this).removeAttr("disabled");
    
    }
});


$('#modalLote').on('hidden.bs.modal', function () {
    $("#txtInicioFaixaLote").val(null);
    $("#txtFimFaixaLote").val(null);
    $("#btnInsereLote").removeAttr("disabled");
    $("#pbLote").css("width","0%");

});


$("#frmDevolveChamado").on('submit',function(e) {

   e.preventDefault();

   var txtDescDevo = $(this).find('#txtDescDevo').val();
  
    if (txtDescDevo !== "") {

        if (confirm('Deseja realmente devolver esse ticket? Isso não poderá ser desfeito!')) {

            $.ajax({
                url: base_url + 'chamado/devolver_chamado',
                async: true,
                method: 'post',
                data: {
                    id_ticket: g_id_ticket,
                    desc_devo: txtDescDevo
                },
                beforeSend: function() {
    
                    $(this).find('submit').prop('disabled','true');
    
                },
                success: function(data) {
    
                    document.location.href = base_url + 'painel?v=triagem'
                }
    
            });
        }


    }
});

//------------------ SUBMIT DA TRIGEM --------------


$('input[name="telefone"]').on("keyup keyup keypress blur change", function(){

    var out = $(this).val();

    $(this).val(out.replace(/[\.-\s]/g,""));
} );


$('input[name="celular"]').on("keyup keyup keypress blur change", function(){

    var out = $(this).val();

    $(this).val(out.replace(/[\.-\s]/g,""));
} );





$('input[name="resumo_solicitacao"]').on("keyup keyup keypress blur change", function(){

    var out = $(this).val();

    $(this).val(out.replace(/[\"\']/g,""));
} );

$('#tblEquips input').on("keyup keyup keypress blur change", function(){

    var out = $(this).val();

    $(this).val(out.replace(/\s/g,""));
} );


$('#frmImportarChamado').on('submit',

    function(e) {

       

        e.preventDefault();

       

    }).validate(agrupamento == true ? {ignore: "*"} : {
        
    rules: {
        nome_solicitante: "required",
        nome_local: "required",
        telefone: {
            required: true,
            digits: true,
            minlength: 3,
        },
        id_fila: {
            required: true,
        },
        celular: {
            required: {
                depends: function() {
                    return nome_fila_sigat === "TELEFONIA"
                }
            },
            digits: true,
            minlength: 9,

        }, 
        resumo_solicitacao: {
            required: true,
        },
    },
    messages: {
        nome_solicitante: "Campo obrigatório!",
        nome_local: "Campo obrigatório!",
        telefone: {
            required: "Campo obrigatório!",
            digits: "Somente dígitos (0-9)!",
            minlength: "Mínimo 3 dígitos!"
        },
        resumo_solicitacao: {
            required: "Campo obrigatório!",
        },
        celular: {
            required: "Campo obrigatório para serviços de Telefonia!",
            digits: "Somente dígitos (0-9)!",
            minlength: "Mínimo 9 dígitos!"
        }
    },
    
    

    submitHandler: async function(form) {
      
        var script_url = base_url + "chamado/importar_chamado";
        var dados = new FormData(form);

        dados.append('id_fila',id_fila_sigat);

        if (id_fila_sigat == 1) {
            dados.append('listaEquipamentos', JSON.stringify(g_equips));
        } else {
            dados.append('listaServicos', JSON.stringify($("#tblServicos").jsGrid("option","data")));

        }
       
        dados.append('num_ticket',g_num_ticket);
        dados.append('g_anexos', JSON.stringify($("#tblAnexos").jsGrid("option","data")));
        dados.append('id_ticket', g_id_ticket);
        let existe = false;
        await $.ajax({
            url: base_url + 'triagem/consultar_chamado_aberto',
            type: 'GET',
            data: `id_ticket_chamado=${g_id_ticket}`,
            datatype: 'json',
            success: dados => {
                if(dados != null){
                    alert(`O chamado ${dados[0].id_chamado} já está aberto`);
                    existe = true;
                }
            },
            error: erro => {
                console.error(erro);
            }
        });
        if(existe == true){
            window.location.href = base_url + "painel#triagem";
            return true;
        }
        
        $.ajax({

            url: script_url,
            type: 'POST',
            data: dados,
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {

                var grid_servicos = $("#tblServicos").jsGrid("option","data")
          
                if (confirmado == false && grid_servicos.length < 1) {
                    alert("Verifique a lista de equipamentos ou adicione um dos serviços!")
                    targetOffset = $('#tblServicos').offset().top;
                    $('html, body').animate({
                        scrollTop: targetOffset - 100
                    }, 200);
                    return false;
                }
                $('#btnImportarChamado').prop("disabled", "true");
            },
            success: function(msg) {
                confirmado = false;
                if (msg.includes('Local') == false) {
                    $('#divTriagem').html('');
                    $("#msg div[id=alerta]").remove();
                    $("#msg").html(msg);
                } else {
                    $('input[name=nome_local').focus();
                    $('#listaLocais').popover({
                        content: 'Local inválido!',
                        trigger: 'focus',
                    });
                    $('#listaLocais').popover('toggle');
                    confirmado = true;
                    $('#btnImportarChamado').removeAttr("disabled");
                }
                $('#btnImportarChamado').removeAttr("disabled");
                msg = null;
            },
            error: function(xhr, ajaxOptions, thrownError) {
                $("#msg").prepend("<div id=\"alerta\" class=\"alert alert-danger alert-dismissible\">");
                $("#alerta").append("<a href=\"#\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">&times;</a>" + thrownError);
                targetOffset = $('#msg').offset().top;
                $('html, body').animate({
                    scrollTop: targetOffset - 100
                }, 200);
                $('#btnImportarChamado').removeAttr("disabled");
            }
        });
        return false;
    }
});

//--------  /TRIAGEM ---------

// --------- BUSCA RAPIDA ----------



var result_br = [];

$("#frmBuscaRapida button").on("click", async function(e) {

    e.preventDefault();

    var termo = $("#txtBuscaRapida").val();

    if (termo.length >= 3) {

        await $.ajax({

            url: base_url + "busca",
            type: 'GET',
            data: {"t":termo},
            success: function(res) {
                result_br = res
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError);
            }
        });

        $("#modalBuscaRapida").modal("show");

    } 


    



});

$("#modalBuscaRapida").on("show.bs.modal", function() {

    $(this).find(".modal-body").html(result_br);
    $(this).find(".modal-title").html("<i class=\"fas fa-search\"></i> Buscando por <em>" + $("#txtBuscaRapida").val() + "</em>");


});

$("#modalBuscaRapida").on("shown.bs.modal", function() {

    $("#tblChamadosBr tbody tr").on("click", function() {

       window.open(base_url + "chamado/" + $(this).find("td").first().text());
    });

    $("#tblTriagemBr tbody tr").on("click", function() {
 
        window.open(base_url + "triagem/" + $(this).find("td").first().text());
     });


     $("#tblEquipsBr tbody tr ").on("click", function() {
 
        window.open(base_url + "equipamento/" + $(this).find("td").first().text());
     });

     $("#tblChamadosEquipBr tbody tr ").on("click", function() {
 
        window.open(base_url + "chamado/" + $(this).find("td").first().text());
     });


});




$("#frmBuscaRapida").on("submit", function(e) {

    e.preventDefault();

});

$("#chkPrioridade").on('click', async function() {

    p_id_chamado = $(this).attr("id_chamado");

    await $.ajax({

        url: base_url + 'chamado/priorizar_chamado',
        
        type: 'POST',
        async: true,
        data: {
            id_chamado: p_id_chamado
        },
        success: function(data) {
            $("#headerChamado #estrela_prioridade").toggle()
        },
        error: function(error) {
            alert(error)
        }
    })

    
})


/*$('#modalEndereco').on('show.bs.modal', async function (e) {
   var id_chamado = $(e.relatedTarget).attr("data-chamado");
   var endereco = null
   await $.ajax({
        url: base_url + "endereco_local/" + id_chamado,
        type: 'POST',
        success: function(res) {
            endereco = res
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError);
        }
    });
   $(this).find(".modal-body").html("<h5>" + $("input[name=nome_local]").val() + "</h5><p>" + endereco + "</p>")
})
*/

$('#modalEmail').on('shown.bs.modal', function() {
    $('#titleEmail').val(`Re: [Chamado: #${g_id_chamado}]`);

    $('textarea[name=txtEmail]').summernote({ //inicialização do SummerNote 
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['insert', ['link', 'picture']],
        ],
        height: 300,
        lang: 'pt-BR',
        dialogsInBody: true,
        disableDragAndDrop: false,
    });
});

$('#frmEmail').on('submit', function(e) { //submit da interacao
    e.preventDefault();
    
    let p_txtEmail = $('textarea[name=txtEmail]').summernote('code');
    let spinner = 
        `<span class=" ml-1 spinner-border text-light spinner-border-sm" role="status">
            <span class="sr-only">Carregando...</span>
        </span>`;
    $('#modalEmail').append(spinner);

    let dados = new FormData($(this)[0]);
    dados.append("conteudo", p_txtEmail);
    dados.append("id_ticket_chamado", g_id_ticket_chamado);
    dados.append("id_chamado", g_id_chamado);
    remetentes.cc.forEach((remetente, i) => {
        dados.append(`remetentes[cc][${i}]`, remetente);
    });
    remetentes.cco.forEach((remetente, i) => {
        dados.append(`remetentes[cco][${i}]`, remetente);
    });
    //dados.append("anexoEmail", $('textarea[name=txtEmail]'));
    $('input[id="fileAnexosEmail"]').val() !== "" ? dados.append("anexos",1) : dados.append("anexos",0);
    if(p_txtEmail.toLowerCase().indexOf('anexo') !== -1 && $('input[id="fileAnexosEmail"]').val() === "") {
    
        let confirm_anexo = confirm('Você pode ter esquecido de anexar um arquivo.\nDeseja enviar este email mesmo assim?');

        if(!confirm_anexo) {
            return false;
        }
    }

    if ($('textarea[name=txtEmail]').summernote('isEmpty')) {
        $('#modalEmail .modal-body').prepend(
            "<div class=\"alert alert-warning fade show\" role=\"alert\">" +
                "O texto não pode ficar em branco!" +
                "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">" +
                    "<span aria-hidden=\"true\">&times;</span>" +
                "</button>" +
            "</div>");

        return false;
    }
    

    return $.ajax({
        url: base_url + "interacao/enviar_email",
        dataType: "json",
        contentType: false,
        processData: false,
        method: "post",
        data : dados,
        beforeSend: () => {
            //$('#btnEnviarEmail').prop("disabled", true);
        },
        success: (() => {
            $('textarea[name=txtEmail]').summernote('reset');
            $('#fileAnexosEmail').val('');
            alert("Email enviado com sucesso");
            $('#modalEmail').modal('hide');
            window.location.reload(false);
        }),
        complete: () => {
            $('#btnEnviarEmail').removeAttr("disabled");
        },
        error: (response, status, error) => {
            $('#modalEmail .modal-body').prepend(
                "<div class=\"alert alert-danger fade show\" role=\"alert\">" +
                    "Erro ao enviar e-mail!<br>" +
                    response.responseJSON.message +
                    "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">" +
                        "<span aria-hidden=\"true\">&times;</span>" +
                    "</button>" +
                "</div>"
            );
            alert("Erro ao enviar email!");
            $('#btnEnviarEmail').removeAttr("disabled");
        }
    });
});


//------------- VIEW LOCAL ---------------//

$("#chkLocal").on('click', async function() {

    p_id_local = $(this).attr("id_local");
    

    await $.ajax({

        url: base_url + 'local/ativar_local',
        
        type: 'POST',
        async: true,
        data: {
            id_local: p_id_local
        },
        success: function(data) {
            if(document.getElementById("chkLocal").checked == false){
                alert('Local desativado: Este local não será mais listado.');
            }else{
                alert('Local Ativado');
            }
        },
        error: function(error) {
            alert('Erro ao atualizar status do local');
        }
    });

});
$('#btnSalvarLocal').hide();
$('#btnEditarLocal').on("click", function(){
    let dados = [];
    dados.push($('#nome_local').val(), $('#secretaria_local').val(), $('#endereco_local').val(), $('#regiao_local').val());
    $('#btnSalvarLocal').show();
    $('#btnCancelarEdicaoLocal').removeAttr('hidden');
    $('input').removeAttr('disabled');
    $('select').removeAttr('disabled');

    $('#btnCancelarEdicaoLocal').on('click', function(){
        $('#nome_local').val(dados[0]);
        $('#secretaria_local').val(dados[1]);
        $('#endereco_local').val(dados[2]);
        $('#regiao_local').val(dados[3]);
        $('#btnSalvarLocal').hide();
        $('#frmEditarLocal input').prop('disabled', true);
        $('#frmEditarLocal select').prop('disabled', true);
        $('#btnCancelarEdicaoLocal').prop('hidden', true);
    });
});

function editarTelefone(id){ 

    $('button').attr('disabled', 'disabled');
    let telefone = $('#tel'+id).html();
    let setor = $('#set' +id).html();
    let btnSalvar = '<button onclick="inserirTelefone('+id+')" type="button" class="btn btn-success mr-2 mb-2"><i class="fas fa-save"></i> Salvar</button>';
    let btnCancelar = '<button id="btnCancelarTel" type="button" class="btn btn-danger mb-2"><i class="fas fa-window-close"></i> Cancelar</button>';
    let btnEditar = '<button id="btnEditarTel" type="button" class="btn btn-info mr-2 mb-2" onclick="editarTelefone('+id+')"><i class="fas fa-edit"></i> Editar</button>';
    let btnExcluir = '<button type="button" onclick="excluirTelefone('+id+')" class="btn btn-danger mr-2 mb-2"><i class="fas fa-trash"></i> Excluir</button>'
    $('#tel'+id).html('');
    $('#tel'+id).append(`<input onkeyup="handlePhone(event)" class="form-control" name="telefone" id="telEd${id}" type="text" value="${telefone}" maxlength="15"></input>`);
    $('#set'+id).html('');
    $('#set'+id).append(`<input name="setor" type="text" id="setEd${id}" class="form-control" value="${setor}" maxlength="100"></input>`);
    $('#edit'+id).html('');
    $('#edit'+id).append(btnSalvar);
    $('#edit'+id).append(btnCancelar);

    $('#btnCancelarTel').on('click', function(){
        $('#tel'+id).html(telefone);
        $('#set'+id).html(setor);
        $('#edit'+id).html('');
        $('#edit'+id).append(btnEditar);
        $('#edit'+id).append(btnExcluir);
        $('button').removeAttr('disabled');
    });
};

function excluirTelefone(id){
    let text = "Deseja realmente excluir este telefone?";
    let idLocal = $('#IdLocal').val();
    let acao = 'excluir';
    let telefone = 'z'; //telefone não pdoe ser nulo
    if (confirm(text) == true) {
        $.ajax({
    
            url: base_url + 'local/' + idLocal,
                
            type: 'POST',
            async: true,
            data: {
                telefone: telefone,
                acao: acao,
                id: id
            },
            success: function(data) {
                location.replace(base_url + '/local/' + idLocal + '?tel=true');
            },
            error: function(error) {
                alert('Erro ao inserir ou editar um telefone. ');
            }
        });
    } else {
        //ao cancelar não se faz nada
    }
}

$('#addTelLocal').on('click', function(){
    $('button').attr('disabled', 'disabled');
    let novaLinha = '<tr id="addLinha"> <td id="campoTelAdd"><input id="telAdd" name="telefone" class="form-control" onkeyup="handlePhone(event)" type="text" value="" maxlength="15"></input></td> <td id="campoSetAdd"><input class="form-control" id="setAdd" name="setor" maxlength="100" type="text" value=""></input></td><td id="edit"><button onclick="inserirTelefone()" type="button" class="btn btn-success mr-2"><i class="fas fa-save"></i> Salvar</button><button id="btnCancelarAdd" type="button" class="btn btn-danger"><i class="fas fa-window-close"></i> Cancelar</button></td></tr>';

    $('#tabela_telefones tbody').append(novaLinha);
   
    $('#addTelLocal').hide();

    $('#btnCancelarAdd').on('click', function(){
        $('#addLinha').remove();
        $('#addTelLocal').show();
        $('button').removeAttr('disabled');
    });
});

function inserirTelefone(id = 0){
    $('spam').remove();
    $('br').remove();
    let campoVazio = '<spam class="text-danger">Este campo é obrigatório</spam><br/>';
    let campoCurto = '<spam class="text-danger">Este campo deve ter no mínimo 4 caracteres</spam><br/>';
    let idLocal = $('#IdLocal').val();   
    let telefone = '';
    let setor = '';
    let acao = ''
    if(id == 0){
        telefone = $('#telAdd').val();
        setor = $('#setAdd').val();
        acao = 'inserir';
    }else{
        telefone = $('#telEd'+id).val();
        setor = $('#setEd'+id).val();
        acao = 'editar';
    }
     
    let valido = false;
    
    if(telefone != '' && setor != '' && setor.length >= 4 && telefone.length >= 4){
        valido = true;
    }else{
        valido = false;
        if(telefone == ''){
            $('#campoTelAdd').append(campoVazio);
            $('#tel'+id).append(campoVazio);
        }if(setor == ''){
            $('#campoSetAdd').append(campoVazio);
            $('#set'+id).append(campoVazio);
        }if(setor.length < 4){
            $('#campoSetAdd').append(campoCurto);
            $('#set'+id).append(campoCurto);
        }if(telefone.length < 4){
            $('#campoTelAdd').append(campoCurto);
            $('#tel'+id).append(campoCurto);
        }
    }
        
    if(valido == true){
        $.ajax({
    
            url: base_url + 'local/' + idLocal,
                
            type: 'POST',
            async: true,
            data: {
                telefone: telefone,
                //celular: celular,
                setor: setor,
                acao: acao,
                id: id
            },
            success: function(data) {
                location.replace(base_url + '/local/' + idLocal + '?tel=true');
            },
            error: function(error) {
                alert('Erro ao inserir ou editar um telefone. ');
            }
        });
    }
    
}


const handlePhone = (event) => {
    let input = event.target;
    input.value = phoneMask(input.value);
  }
  
  const phoneMask = (value) => {
    if (!value) return "";
    value = value.replace(/\D/g,'');
    if (value.length >9) value = value.replace(/(\d{2})(\d)/,"($1) $2");
    value = value.replace(/(\d)(\d{4})$/,"$1-$2");
    return value;
  }

  


// ------------------------ REPAROS ---------------------------------- //


$('#modalIniciarReparo').on('show.bs.modal', async function (event) {

    var modal = $(this)

    var button = $(event.relatedTarget) // Button that triggered the modal
    var p_id_chamado = button.data('chamado')

    var listaEquip = null
    var listaBancadas = null

    $(this).find("#alerta-reparo").remove()

    await $.ajax({
        "method": "POST",
        "url": base_url + "json/equipamentos_pendentes",
        "data": {
            id_chamado: p_id_chamado,
            espera: false
        }
    })
    .done((data) => {
        listaEquip = data
    })

    await $.ajax({
        "method": "POST",
        "url": base_url + "bancada/lista_bancadas",
        "data": {
            status: 0,
        }
    })
    .done((data) => {
        listaBancadas = data
    })

    
    if (listaBancadas.length == 0) {

        $("#btnIniciarReparo").prop("disabled","true")
        $("#listaBancadas").prop("disabled","true")
        $("#listaEquipReparo").prop("disabled","true")

        $('#modalIniciarReparo').find('.modal-body')
            .prepend(
                `<div id="alerta-reparo" class="alert alert-warning" role="alert">
                Não existem bancadas disponíveis!
              </div>`
            )

        return false
    }

    if (listaEquip.equipamentos.length == 0) {

        $("#btnIniciarReparo").prop("disabled","true")
        $("#listaBancadas").prop("disabled","true")
        $("#listaEquipReparo").prop("disabled","true")

        $('#modalIniciarReparo').find('.modal-body')
            .prepend(
                `<div id="alerta-reparo" class="alert alert-warning" role="alert">
                Não existem equipamentos disponíveis!
              </div>`
            )

        return false

    }

    listaEquip.equipamentos.forEach((equip) => {
        modal.find('.modal-body #listaEquipReparo')
        .append(
        `<option value="${equip.num_equipamento_chamado}">
        ${equip.num_equipamento_chamado}
        </option>`)

    })

    if(g_fila_chamado == 3){
        listaBancadas.forEach((b) => {
            if(b.status_bancada == true){
                modal.find('.modal-body #listaBancadas')
                .append(
                `<option value="${b.id_bancada}">
                ${b.nome_bancada}
                </option>`)
                
            }
    
        })
    }else{
        listaBancadas.forEach((b) => {
            
            if(b.nome_bancada == '0'){
                modal.find('.modal-body #listaBancadas')
                .append(
                `<option value="${b.id_bancada}" selected>
                ${b.nome_bancada}
                </option>`)
                modal.find('.modal-body #listaBancadas').prop('disabled', true);
            }
    
        })

    }


    
  })

$('#modalIniciarReparo').on('hide.bs.modal', function (event) {


    $(this).find('.modal-body #listaBancadas').html("")
    $(this).find('.modal-body #listaBancadas').removeAttr("disabled")
    $(this).find('.modal-body #listaEquipReparo').html("")
    $(this).find('.modal-body #listaEquipReparo').removeAttr("disabled")
    $("#btnIniciarReparo").removeAttr("disabled")

})

var p_id_reparo = null

async function carregaReparo(p_id_reparo) {

    var reparo = null

    await $.ajax({
        url: base_url + 'reparo/buscar_reparo',
        method: "POST",
        data: {
            id_reparo: p_id_reparo
        }
    })
    .done((data) => {
        reparo = data
    })

    return reparo

}

async function carregaServicos(id_reparo, id_fila) {

    let servicos = [];

    await $.ajax({
        url: base_url + 'reparo/buscar_servicos',
        method: "POST",
        data: {
            id_reparo: id_reparo,
            id_fila: id_fila
        },
    })
    .done((data) => {
        servicos = data
    })

    return servicos

}

async function carregaReparoServicos(p_id_reparo) {

    var servicos = null

    await $.ajax({
        url: base_url + 'reparo/buscar_reparo_servico',
        method: "POST",
        data: {
            id_reparo: p_id_reparo
        }
    })
    .done((data) => {
        servicos = data
    })

    return servicos

}

$('#slctListaModeloLaudo').on('change', function(e) {

    $("#txtLaudoInservivelEquip").val($(this).val())
})

$('#modalLaudoInservivelEquip').on('show.bs.modal', async function (e) {
   
    
    
    $(this).find('.modal-footer #btnInservivelEquip').attr("data-equip",num_equip_reparo_atual)
    $(this).find('.modal-footer #btnInservivelEquip').attr("data-reparo",p_id_reparo)

    $(this).find(" .modal-title").html(
    `<i class="fas fa-ban"></i> ${num_equip_reparo_atual} - Classificar como inservível`
    )

    

    const mensagens = await carregaModeloMensagem('INSERVIVEL',3)

    mensagens.forEach(msg => {

        let string = msg.mensagem_modelo_mensagem.slice(0,50) + "..."

        $(this).find('.modal-body #slctListaModeloLaudo').append(
            `<option
            value="${msg.mensagem_modelo_mensagem}"
            >${string}</option>`
        )
    })
})

$('#modalLaudoInservivelEquip').on('hide.bs.modal', function (e) {
    $(this).find('.modal-body #slctListaModeloLaudo').html(`<option
    value="">Escolha...</option>`)

   
    

    
})

$("#btnInservivelEquip").on("click", async function(e) { //classificar como inservível

    e.preventDefault();

    if ($("#txtLaudoInservivelEquip").val().length < 143) {
        alert("Laudo técnico muito curto!")
        return false
    }

    const this_btn = $(this)


    await $.ajax({
        method: "POST",
        url: base_url + "/interacao/registrar_interacao_reparo",
        data: {
            id_usuario: g_id_usuario,
            id_chamado: g_id_chamado,
            tipo: 'INSERVIVEL_REPARO',
            id_reparo: $(this).attr("data-reparo"),
            num_equipamento: $(this).attr("data-equip"),
            txtInteracaoReparo: $("#txtLaudoInservivelEquip").val()
        },
        beforeSend: function() {
            this_btn.prop("disabled","true")
            this_btn.html(`<span class="spinner-border spinner-border-sm"></span> Enviando...`)
            $("#txtLaudoInservivelEquip").prop("disabled","true");
            $("#slctListaModeloLaudo").prop("disabled","true");
            $('#modalReparo').modal('hide')
            $(".btn-modal-reparo").prop("disabled", true);
        }
    }).done((res) => {
        if (res.error == false) {
            Swal.fire({
                icon: "success",
                title: `Sucesso!`,
                text: `Equipamento ${$(this).attr("data-equip")} enviado para remessa de inservíveis!`
            });

            $('#modalLaudoInservivelEquip').modal('hide')
            $('#modalReparo').modal('hide')
        }
    })

    carregaChamado(g_id_chamado)
})

$('#modalLaudoInservivelEquip').on('hidden.bs.modal', function() {

    $("#btnInservivelEquip").html(`<i class="far fa-arrow-alt-circle-right"></i> Enviar</button>`)

    $("#txtLaudoInservivelEquip").removeAttr("disabled");
    $("#slctListaModeloLaudo").removeAttr("disabled");

    $("#btnInservivelEquip").removeAttr("disabled");
    $("#btnEncerrarReparo").removeAttr("disabled");
    $("#btnGarantiaReparo").removeAttr("disabled");

   



})

var num_equip_reparo_atual = null

$('#modalReparo').on('show.bs.modal', async function (event) {

   
    $('#btnGarantiaReparo').hide();
    $('#btnLaudoInservivelEquip').hide();
    
    
    $('#btnEncerrarReparo').hide();
    $('#btnEsperaReparo').hide();
    $('.btnEsperaReparo').hide();
    $('#btnJustificativaCancelamento').hide();
    $('#btnLaudoGarantiaEquip').hide();
    $('#btnRmEsperaReparo').hide();
    $('#collapseEspera').collapse('hide');
    
    $(this).find('.modal-footer #btnJustificativaCancelamento').prop("disabled","true")
    $(this).find('.modal-footer #btnLaudoInservivelEquip').prop("disabled","true")

    const btn = $(event.relatedTarget)
    p_id_reparo = btn.data('reparo')
    var reparo = null
    var garantia_bool = false;

    const modal = $(this)

    $(this).find('.modal-body').html('')

    res = await carregaReparo(p_id_reparo);
    res.reparo.servicos = await carregaReparoServicos(p_id_reparo);
    num_equip_reparo_atual = res.reparo.num_equipamento_reparo
    modal.find('.modal-title').html(
        `<i class="fas fa-wrench"></i>
        ${res.reparo.num_equipamento_reparo} -
        ${res.desc_equip}
        <small>(Reparo #${p_id_reparo})</small>`
    )

    // se esta como responsavel do chamado irá exibir os controles do reparo
    if (p_id_responsavel == g_id_usuario || g_fila_chamado === 3) {
        $(this).find('.modal-footer').show();
    } else {
        $(this).find('.modal-footer').hide();
    }

    if (res.reparo.status_reparo == "ABERTO") {
        $(this).find('.modal-footer #btnGarantiaReparo').removeAttr("disabled")
        $(this).find('.modal-footer #btnEsperaReparo').removeAttr("disabled")
        $(this).find('.modal-footer #btnJustificativaCancelamento').removeAttr("disabled")
        $(this).find('.modal-footer #btnLaudoInservivelEquip').removeAttr("disabled")
        $(this).find('.modal-footer #btnEncerrarReparo').removeAttr("disabled")
        $(this).find('.modal-footer #btnLaudoInservivelEquip').attr("data-equip",res.reparo.num_equipamento_reparo)
        $(this).find('.modal-footer #btnLaudoInservivelEquip').attr("data-reparo",p_id_reparo)
        if(g_fila_chamado == 3){
        $('#btnGarantiaReparo').show();
        $('#btnGarantiaReparo').show();
       
            $('#btnGarantiaReparo').show();
       
            $('#btnLaudoInservivelEquip').show();
            $('#btnEsperaReparo').show();
            $('.btnEsperaReparo').show();
        }
        
        $('#btnEncerrarReparo').show();
        $('#btnJustificativaCancelamento').show();
        // $('#btnLaudoGarantiaEquip').hide(); // Redundante

        
        $(this).find('.modal-body').html(`
            <h5>Lista de serviços</h5>
            <div id="conteudo-reparo-servico">
                <div class="form-check">

                </div>
            </div>
            ${p_id_responsavel == g_id_usuario || g_fila_chamado === 3 ? `
                <div class="input-group mt-3" id="divSlctListaServicoEquip">
                    <select class="custom-select" id="slctListaServicoEquip">
                        
                    </select>
                    <div class="input-group-append">
                        <button class="btn btn-primary btn-sm" id="btn-add-servico" type="button">
                            <i class="fas fa-plus"></i> Adicionar
                        </button>
                    </div>
                </div>` : ''
            }
        `);

        
        res.servicos = await carregaServicos(p_id_reparo, g_fila_chamado);
        res.reparo.servicos.forEach(reparo_servico => {
            
            if(reparo_servico.realizado_reparo_servico == true) {
                $('#conteudo-reparo-servico').find(".form-check").append(`
                    <div id="check-servico-${reparo_servico.id_reparo_servico}">
                        <input class="form-check-input check-servico" onclick="desfazerReparoServico(${reparo_servico.id_reparo_servico}, '${reparo_servico.nome_servico}', ${reparo_servico.id_servico}, ${p_id_reparo});" type="checkbox" value="${reparo_servico.id_reparo_servico}" checked>
                        <label class="form-check-label">
                            ${reparo_servico.nome_servico}
                        </label>
                    </div>
                `);
            } else {
                $('#conteudo-reparo-servico').find(".form-check").append(`
                    <div id="check-servico-${reparo_servico.id_reparo_servico}">
                        <input class="form-check-input check-servico" id="checkbox-servico-${reparo_servico.id_reparo_servico}" type="checkbox" value="${reparo_servico.id_reparo_servico}">
                        <label for="check-servico-${reparo_servico.id_reparo_servico}" class="form-check-label">
                            ${reparo_servico.nome_servico}
                        </label>
                    </div>
                `);
                
                // verifica se o chamado está bloqueado pelo usuario
                //if (/*p_id_responsavel == g_id_usuario &&*/ g_fila_chamado === 3) {
                    // coloca o onclick no checkbox
                    $(`#checkbox-servico-${reparo_servico.id_reparo_servico}`).attr('onclick', `realizaServico(${reparo_servico.id_reparo_servico}, '${reparo_servico.nome_servico}', ${reparo_servico.id_servico})`);

                    if(p_id_responsavel == g_id_usuario || g_fila_chamado === 3){

                        // coloca o botão de cancelar reparo
                        $(`#check-servico-${reparo_servico.id_reparo_servico} > label`).append(`
                            <span class="badge badge-danger" onclick="cancelaServico(${reparo_servico.id_reparo_servico}, '${reparo_servico.nome_servico}', ${reparo_servico.id_servico})"><i class="fas fa-times"></i></span>
                        `);
                    }else if(p_id_responsavel != g_id_usuario){
                        // coloca disable no botão de realizar serviço
                        $(`#checkbox-servico-${reparo_servico.id_reparo_servico}`).prop('disabled', true);
                    }

                /*} else {
                    // coloca disable no botão de realizar serviço
                    $(`#checkbox-servico-${reparo_servico.id_reparo_servico}`).prop('disabled', true);
                }*/
                
                
            }
        });

        if (res.servicos.length === 0) {
            $('#slctListaServicoEquip').append(
                `<option value="0">Não existe nenhum serviço para realizar, caso deseje adicionar consulte o administrador!</option>`
            );
            $('#slctListaServicoEquip').prop('disabled', true);
            $('#btn-add-servico').prop('disabled', true);

        } else {
            res.servicos.forEach(servico => {
                $('#slctListaServicoEquip').append(
                    `<option value="${servico.id_servico}">${servico.nome_servico}</option>`
                );
            });
        }

        $('#btn-add-servico').on('click',(e) => {
            let select = {
                id_reparo_servico: 0,
                id_servico: $('#slctListaServicoEquip').val(),
                nome: $('#slctListaServicoEquip option:selected').text()
            };
            $.ajax({
                url: base_url + 'reparo/adicionar_reparo_chamado',
                type: 'POST',
                data: {
                    id_chamado: g_id_chamado,
                    id_reparo: p_id_reparo,
                    id_servico: select.id_servico
                },
                beforeSend: (/* jqXHR, settings */) => {
                    $('#btn-add-servico').prop('disabled', true);
                }
            }).done((data) => {
                // caso sucesso
                select.id_reparo_servico = data.id_reparo_servico;
                if (data !== null) {
                    $('#btn-add-servico').removeAttr('disabled');
                    // coloca o reparo na lista como aberto
                    $('#conteudo-reparo-servico').find(".form-check").append(`
                        <div id="check-servico-${select.id_reparo_servico}">
                            <input class="form-check-input check-servico" id="checkbox-servico-${select.id_reparo_servico}" type="checkbox" value="${select.id_reparo_servico}" onclick="realizaServico(${select.id_reparo_servico}, '${select.nome}', ${select.id_servico})">
                            <label for="check-servico-${select.id_reparo_servico}" class="form-check-label">
                                ${select.nome}
                                <span class="badge badge-danger" onclick="cancelaServico(${select.id_reparo_servico}, '${select.nome}', ${select.id_servico})"><i class="fas fa-times"></i></span>
                            </label>
                        </div>
                    `);

                    // ação no histórico
                    // $('#conteudo-historico-modal').prepend(`
                    //     <p class="border rounded p-2 my-3">
                    //         <span class="badge badge-info">${new Date().toLocaleString('pt-BR')}</span> <strong>SISTEMA</strong> <strong class="text-info">adicionou</strong> o serviço <b>${select.nome}</b> ao reparo
                    //     </p>
                    // `);

                    // remove o option do select
                    $(`#slctListaServicoEquip option[value='${select.id_servico}']`).remove();

                    // if para quando acabar os options para desativar o select e colocar uma mensagem de erro
                    if ($('#slctListaServicoEquip option').length == 0) {
                        $('#slctListaServicoEquip').append(
                            `<option value="0">Não existe nenhum serviço para realizar, caso deseje adicionar consulte o administrador!</option>`
                        );
                        $('#slctListaServicoEquip').prop('disabled', true);
                        $('#btn-add-servico').prop('disabled', true);
                    }
                }
                else {
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        icon: "success",
                        title: "Erro ao adicionar serviço!",
                        didOpen: (toast) => {
                          toast.onmouseenter = Swal.stopTimer;
                          toast.onmouseleave = Swal.resumeTimer;
                        }
                    });

                    return false
                }
            })
        });
    }

    else if (res.reparo.status_reparo == "FINALIZADO") {
        $.ajax({
            url: base_url + "reparo/buscar_garantia",
            type: 'POST',
            data: {
                id_reparo: p_id_reparo,
            }
        }).done((data) => {
            $('#btn-laudo-garantia').remove();
            if(data !== null) {
                $(this).find('.modal-body').append(`
                    <a class="btn btn-primary" id="btn-laudo-garantia" href="${base_url}termos/${data.nome_laudo_garantia}" target="_BLANK" role="button">Laudo técnico da garantia</a>
                `)
                garantia_bool = true;
            }
        });

        $.ajax({
            url: base_url + "reparo/buscar_equipamento_reparo",
            type: 'POST',
            data: {
                id_reparo: p_id_reparo,
            }
        }).done((data) => {
            if (data.status_equipamento_chamado === "REMESSA" || data.status_equipamento_chamado === "INSERVIVEL"){
                $(this).find('.modal-body').append(`
                    <p>Este equipamento foi classificado como ${data.status_equipamento_chamado} e foi incluído na remessa 
                    <a href="${base_url}inservivel/${data.id_remessa}">#${data.id_remessa}</a></p>
                    <a href="${base_url}inservivel/gerartermo/${data.num_equipamento_reparo}" role="button" class="btn btn-primary" target="_blank">
                    <i class="fas fa-file-download"></i> Laudo técnico
                    </a>
                `)
                if(g_auto_usuario > 3 && status_chamado != 'ENCERRADO' && res.reparo.id_remessa != null) $(this).find('.modal-body').append(`<button class="btn btn-secondary" onclick="reverterRemessa('${data.num_equipamento_reparo}',${data.id_remessa}, ${p_id_reparo})"><i class="fas fa-redo"></i> Reverter Remessa</button>`);
            } else if (data.status_equipamento_chamado === "ENTREGA" && status_chamado != 'ENCERRADO'){
                $(this).find('.modal-body').append(`<button class="btn btn-warning" onclick="cancelarEntrega('${data.num_equipamento_reparo}', ${res.reparo.id_reparo})"><i class="fas fa-redo"></i> Cancelar ENTREGA</button>`);
            }
        });

        $(this).find('.modal-body').html(
            `<p>O reparo foi finalizado.</p>`
        )
    }

    else if (res.reparo.status_reparo == "GARANTIA") {
        $('#btnLaudoGarantiaEquip').show();
        $('#btnJustificativaCancelamento').show();
        $('#btnJustificativaCancelamento').removeAttr('disabled');
        $('#btnLaudoGarantiaEquip').removeAttr('disabled');

        $(this).find('.modal-body').html(
            `<p>O reparo foi para garantia pelo(s) seguinte(s) motivo(s):</p>
            <p>${res.reparo.justificativa_reparo}</p>
            `
        )
        $.ajax({
            url: base_url + "reparo/buscar_garantia",
            type: 'POST',
            data: {
                id_reparo: p_id_reparo,
            }
        }).done((data) => {
            $('#btn-laudo-garantia').remove();
            if(data !== null) {
                $(this).find('.modal-body').append(`
                    <p>Ticket da garantia: <strong>${data.ticket_garantia}</strong></p>
                `)
                garantia_bool = true;
            }
        });


        $('#modalLaudoGarantiaEquip').find('#frmLaudoGarantiaEquip').off('submit').on('submit', function(e) {
            e.preventDefault();

            let dados = new FormData($(this)[0]);
            dados.append("id_reparo", p_id_reparo);

            $.ajax({
                url: base_url + "reparo/registrar_laudo",
                dataType: "json",
                contentType: false,
                processData: false,
                method: "post",
                data : dados,
                beforeSend: () => {
                    $('#fileLaudo').val("");
                    $('#btnGarantiaEquip').prop("disabled", true);
                }
            }).done((data) => {
                Swal.fire({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    icon: "success",
                    title: data.mensagem,
                    didOpen: (toast) => {
                      toast.onmouseenter = Swal.stopTimer;
                      toast.onmouseleave = Swal.resumeTimer;
                    }
                    
                });

                $('#btnGarantiaEquip').removeAttr("disabled");
                $('#modalLaudoGarantiaEquip').modal('hide');
                $('#modalReparo').modal('hide');
                carregaChamado(g_id_chamado);
            }).fail((data) => {
                Swal.fire({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    icon: "error",
                    title: data.responseJSON.mensagem,
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });
                $('#btnGarantiaEquip').removeAttr("disabled");
            });
        });
    }

    else if(res.reparo.status_reparo == "ESPERA") {
        $(this).find('.modal-body').html(
            `<p>O reparo foi para garantia pelo(s) seguinte(s) motivo(s):</p>
            <p>${res.reparo.justificativa_reparo}</p>
            `
        )

        $('#btnRmEsperaReparo').show();
        $('#btnRmEsperaReparo').removeAttr('disabled');

        $(this).find('#btnRmEsperaReparo').off('click').on('click', async() => {
            let bancadas = await carregarBancadas(false);

            Swal.fire({
                title: 'Selecione a bancada',
                html:
                    `
                    <select id="slc-bancadas" class="form-control"> </select>
                    `,
                showCancelButton: true,
                confirmButtonText: 'Enviar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                focusConfirm: false,
                preConfirm: () => {
                    return $('#slc-bancadas').val();
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    let val_id_bancada = result.value;

                    $.ajax({
                        url: base_url + 'reparo/remover_espera_reparo',
                        type: 'POST',
                        data: {
                            id_reparo: p_id_reparo,
                            id_bancada: val_id_bancada,
                            id_chamado: g_id_chamado,
                            num_equip: num_equip_reparo_atual,
                        }
                    }).done((data) => {
                        carregaChamado(g_id_chamado);
                        Swal.fire('Removido da espera', data.mensagem, 'success');
                        $('#modalReparo').modal('hide');
                    }).fail((e) => {
                        Swal.fire('Erro', 'Falha ao enviar remover da espera!', 'error');
                    });
                }
            });

            bancadas.forEach(bancada => {
                if(bancada.status_bancada == true){
                    $('#slc-bancadas').append(`<option value="${bancada.id_bancada}">${bancada.nome_bancada}</option>`);

                }
            });
        });
    }
    
    else {
        $(this).find('.modal-body').html(
            `<p>O reparo foi cancelado pelo(s) seguinte(s) motivo(s):</p>
            <p>${res.reparo.justificativa_reparo}</p>
            `
        )
    }

    $.ajax({
        url: base_url + 'reparo/lista_historico',
        type: 'POST',
        data: {
            id_reparo: p_id_reparo,
        },
        /* beforeSend: function() {
            $("#btnIniciarReparo").prop("disabled","true")
            $("#listaBancadas").prop("disabled","true")
            $("#listaEquipReparo").prop("disabled","true")
        } */
    }).done((data) => {
        if (data !== null) {
            $('#conteudo-historico-modal').empty();
            let reparo = data.reparo[0];

            $('#conteudo-historico-modal').prepend(`
                <p class="border rounded p-2 my-3">
                    <span class="badge badge-info">${reparo.data_inicio_reparo}</span> <strong>${reparo.nome_abertura_usuario}</strong> <b>iniciou</b> o reparo <b>#${reparo.id_reparo}</b>.
                </p>
            `);

            data.servicos.forEach(servico => {
                if (servico.subquery == 3) {
                    $('#conteudo-historico-modal').prepend(`
                        <p class="border rounded p-2 my-3">
                            <span class="badge badge-info">${servico.data_reparo_servico}</span> <strong>${servico.nome_abertura_usuario}</strong> ${servico.nome_servico.toUpperCase()}</b>.
                        </p>
                    `);
                } else if (servico.realizado_reparo_servico == true && servico.subquery == 1) {
                    $('#conteudo-historico-modal').prepend(`
                        <p class="border rounded p-2 my-3">
                            <span class="badge badge-info">${servico.data_encerramento_reparo_servico}</span> <strong>${servico.nome_fechamento_usuario}</strong> <strong class="text-success">finalizou</strong> o serviço <b>${servico.nome_servico}</b>.
                        </p>
                    `);
                } else if (servico.status_reparo_servico == false && servico.subquery == 1) {
                    $('#conteudo-historico-modal').prepend(`
                        <p class="border rounded p-2 my-3">
                            <span class="badge badge-info">${servico.data_encerramento_reparo_servico}</span> <strong>${servico.nome_fechamento_usuario}</strong> <strong class="text-danger">removeu</strong> o serviço <b>${servico.nome_servico}</b>.
                        </p>
                    `);
                } else {
                    $('#conteudo-historico-modal').prepend(`
                        <p class="border rounded p-2 my-3">
                            <span class="badge badge-info">${servico.data_reparo_servico}</span> <strong>${servico.nome_abertura_usuario}</strong> <strong class="text-primary">adicionou</strong> o serviço <b>${servico.nome_servico}</b>.
                        </p>
                    `);
                }
            });

            if(reparo.status_reparo == "CANCELADO") {
                $('#conteudo-historico-modal').prepend(`
                    <p class="border rounded p-2 my-3">
                        <span class="badge badge-info">${reparo.data_fim_reparo}</span> <strong>${reparo.nome_encerramento_usuario}</strong> <strong class="text-danger">cancelou</strong> o reparo <b>#${reparo.id_reparo}</b> - <b>${reparo.num_equipamento_reparo}</b>
                    </p>
                `);
            } else if (reparo.data_fim_reparo !== null) {
                $('#conteudo-historico-modal').prepend(`
                    <p class="border rounded p-2 my-3">
                        <span class="badge badge-info">${reparo.data_fim_reparo}</span> <strong>${reparo.nome_encerramento_usuario}</strong> <strong>encerrou</strong> o reparo <b>#${reparo.id_reparo}</b>.
                    </p>
                `);
            }
        } else {
            $('#modalIniciarReparo').find('.modal-body')
            .prepend(
                `<div id="alerta-reparo" class="alert alert-danger" role="alert">
                    Erro ao criar o reparo!
              </div>`
            )

            return false;
        }
    })
})

function desfazerReparoServico(id_reparo_servico, nome_reparo_servico, id_servico, id_reparo) {
    Swal.fire({
        title: "Tem certeza?",
        text: "Você não poderá reverter isso!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sim, desfaça o serviço!",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed && id_reparo == p_id_reparo) {
            $.ajax({
                url: base_url + 'reparo/desfazer_reparo_servico',
                type: 'POST',
                data: {
                    id_reparo: id_reparo,
                    id_reparo_servico: id_reparo_servico
                },
                beforeSend: () => {
                    $(`input[value="${id_reparo_servico}"]`).prop('disabled', true);
                }
            }).done((data) => {
                Swal.fire({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    icon: "success",
                    title: data.mensagem,
                    didOpen: (toast) => {
                      toast.onmouseenter = Swal.stopTimer;
                      toast.onmouseleave = Swal.resumeTimer;
                    }
                });


                $(`#check-servico-${id_reparo_servico}`).html(`
                    <div id="check-servico-${id_reparo_servico}">
                        <input class="form-check-input check-servico" id="checkbox-servico-${id_reparo_servico}" type="checkbox" value="${id_reparo_servico}" onclick="realizaServico(${id_reparo_servico}, '${nome_reparo_servico}', ${id_servico})">
                        <label for="check-servico-${id_reparo_servico}" class="form-check-label">
                            ${nome_reparo_servico}
                            <span class="badge badge-danger" onclick="cancelaServico(${id_reparo_servico}, '${nome_reparo_servico}', ${id_servico})"><i class="fas fa-times"></i></span>
                        </label>
                    </div>
                `);
                
            }).fail((res) => {
                $(`input[value="${id_reparo_servico}"]`).prop('checked', true);
                Swal.fire({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                    icon: "error",
                    text: `${res.responseJSON.mensagem}`,
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });
                $(`input[value="${id_reparo_servico}"]`).removeAttr('disabled');
            });
            
        }
    });
};

$("#btnEsperaReparo").on('click', async (e) => {
    Swal.fire({
        title: "Tem certeza?",
        text: "Você não poderá reverter isso!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sim, Coloque em espera!",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        let justificativa_espera = $('#justificativaEspera').val();
        const regex = /^[A-Za-záàâãéèêíïóôõöúçñÁÀÂÃÉÈÍÏÓÔÕÖÚÇÑ 0-9]+$/;
        
        if (result.isConfirmed) {
            if (!regex.test(justificativa_espera)) {
                return $('#justificativaEspera').addClass('is-invalid');
            }

            $.ajax({
                url: base_url + 'reparo/espera_reparo',
                type: 'POST',
                data: {
                    id_chamado: g_id_chamado,
                    id_reparo: p_id_reparo,
                    justificativa_reparo: justificativa_espera,
                    // id_servico: id_servico
                },
                beforeSend: () => {
                    $('#justificativaEspera').removeClass('is-invalid');
                    $('#btn-add-servico').prop('disabled', true);
                    $('#btnJustificativaCancelamento').prop('disabled', true);
                    $('#btnEsperaReparo').prop('disabled', true);
                    $('#btnEncerrarReparo').prop('disabled', true);
                    $('#btnLaudoInservivelEquip').prop('disabled', true);
                    $('#btnGarantiaReparo').prop('disabled', true);
                    $('#btnLaudoGarantiaEquip').prop('disabled', true);
                }
            }).done((data) => {
                $('#justificativaEspera').val('');
                Swal.fire({
                    title: "Espera!",
                    text: data.mensagem,
                    icon: "success"
                });

                carregaChamado(g_id_chamado);
                $('#modalReparo').modal('hide');
            }).fail(() => {
                Swal.fire({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                    icon: "error",
                    text: `Falha ao colocar reparo em espera`,
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });
            });

            $('#btn-add-servico').prop('disabled', false);
            $('#btnJustificativaCancelamento').prop('disabled', false);
            $('#btnEsperaReparo').prop('disabled', false);
            $('#btnEncerrarReparo').prop('disabled', false);
            $('#btnLaudoInservivelEquip').prop('disabled', false);
            $('#btnGarantiaReparo').prop('disabled', false);
            $('#btnLaudoGarantiaEquip').prop('disabled', false);
        }
    });
});


$('#btnEncerrarReparo').on('click', function () {
    if ($('#conteudo-reparo-servico').find('.check-servico').is(':not(:checked)')) {
        $('#modalReparo').find('.modal-body')
        .prepend(
            `<div id="alerta-reparo" class="alert alert-danger" role="alert">
                Existem serviços pendentes que impedem o encerramento do reparo!
            </div>`
        );
        return false;
    } else if ($('#conteudo-reparo-servico').find('.check-servico').length === 0) {
        $('#modalReparo').find('.modal-body')
        .prepend(
            `<div id="alerta-reparo" class="alert alert-danger" role="alert">
                Realize pelo menos um serviço para encerrar ou cancele o reparo!
            </div>`
        );
        return false;
    }

    $.ajax({
        url: base_url + 'reparo/finaliza_reparo',
        type: 'POST',
        data: {
            id_chamado: g_id_chamado,
            id_reparo: p_id_reparo,
        }
    }).done((data) => {
        if (data !== null) {
            Swal.fire({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                icon: "success",
                title: data.mensagem,
                didOpen: (toast) => {
                  toast.onmouseenter = Swal.stopTimer;
                  toast.onmouseleave = Swal.resumeTimer;
                }
            });

            $('#modalReparo').modal('hide')
            carregaChamado(g_id_chamado);
        } else {
            $('#modalReparo').find('.modal-body')
            .prepend(
                `<div id="alerta-reparo" class="alert alert-danger" role="alert">
                    Erro ao criar o reparo!
                </div>`
            );

            return false;
        }
    })

    carregaChamado(g_id_chamado);
});

$('#btnGarantiaEquip').on(('click'), () => {
    let ticket_garantia = $('#txtTicketGarantia').val();
    let justicativa = $('#txtJustificativaGarantia').val();

    if (ticket_garantia && justicativa) {
        $.ajax({
            url: base_url + 'reparo/acionar_garantia',
            type: 'POST',
            dataType: "json",
            data: {
                id_reparo: p_id_reparo,
                ticket_garantia: ticket_garantia,
                justificativa_reparo: justicativa
            },
            beforeSend: () => {
                $('#txtTicketGarantia').val("");
                $('#txtJustificativaGarantia').val("");
                $('#btnGarantiaEquip').prop("disabled", true);
            }
        }).done((data) => {
            Swal.fire({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                icon: "success",
                title: data.mensagem,
                didOpen: (toast) => {
                  toast.onmouseenter = Swal.stopTimer;
                  toast.onmouseleave = Swal.resumeTimer;
                }
            });

            $('#btnGarantiaEquip').removeAttr("disabled");
            $('#modalGarantiaReparo').modal('hide');
            $('#modalReparo').modal('hide');
            carregaChamado(g_id_chamado);
        });
    }
})

async function realizaServico(id_reparo_servico, texto_reparo_servico, id_servico) {
    if (confirm(`Tem certeza que o serviço ${texto_reparo_servico} foi realizado?`)) {
        config.then(async(value) => {
            const config = value;

            if (id_servico === config.id_servico.lacre) {
                const lacre = prompt("Digite o lacre do equipamento:");
                if (/^[a-zA-Z0-9]+$/.test(lacre) && lacre !== null) {
                    let num_equipamento = await carregaReparo(p_id_reparo);
                    num_equipamento = num_equipamento.reparo.num_equipamento_reparo;

                    $.ajax({
                        url: base_url + 'equipamento/controller/registra_lacre',
                        type: 'POST',
                        dataType: "json",
                        data: {
                            id_reparo_servico: id_reparo_servico,
                            num_equipamento: num_equipamento,
                            tag_equipamento: lacre,
                        }
                    });
                } else if(lacre !== null){
                    alert(`Dados inválidos inseridos!\nAtenção!\nCaracteres permitidos:\nA-Z, a-z, 0-9`);
                    $(`#checkbox-servico-${id_reparo_servico}`).prop('checked', false);

                    return false;
                } else {
                    $(`#checkbox-servico-${id_reparo_servico}`).prop('checked', false);
                    return false;
                }
            } else if(id_servico === config.id_servico.CMOS.id_servico) {
                // #modalVerificarBateria
                $(document).ready(function() {
                    $('#modalVerificarBateria').modal({
                        backdrop: 'static',
                        keyboard: false,
                        show: true
                    });

                    $('#modalVerificarBateria').find('#btnRegistrarVoltagem').off('click').on('click', function() {
                        $('#btnRegistrarVoltagem').prop('disabled', true);
                        var id_servico = null, nome_servico = "";
                        const volts_troca = 2.7;
                        let volts_bat = $('#input-voltagem').val();

                        if (volts_bat < volts_troca) {
                            // if caso for serviço para allInOne ou notebook
                            if ($('#is-notebook-allinone').is(':checked')) {    
                                id_servico = config.id_servico.CMOS.troca_notebook.id_servico;
                                nome_servico = config.id_servico.CMOS.troca_notebook.nome_servico;
                            } else {
                                id_servico = config.id_servico.CMOS.troca_desktop.id_servico;
                                nome_servico = config.id_servico.CMOS.troca_desktop.nome_servico;
                            }

                            $.ajax({
                                url: base_url + 'reparo/adicionar_reparo_chamado',
                                type: 'POST',
                                data: {
                                    id_chamado: g_id_chamado,
                                    id_reparo: p_id_reparo,
                                    id_servico: id_servico
                                },
                                beforeSend: (/* jqXHR, settings */) => {
                                    $('#conteudo-reparo-servico').find(".form-check").append(`
                                        <div id="check-servico-${id_reparo_servico}">
                                            <input class="form-check-input check-servico" id="checkbox-servico-${id_reparo_servico}" type="checkbox" value="${id_reparo_servico}">
                                            <label for="check-servico-${id_reparo_servico}" class="form-check-label">
                                                ${nome_servico}
                                            </label>
                                        </div>
                                    `)

                                    $('#btn-add-servico').prop('disabled', true);
                                }
                            }).done((data) => {
                                Swal.fire({
                                    toast: true,
                                    position: "top-end",
                                    showConfirmButton: false,
                                    timer: 5000,
                                    timerProgressBar: true,
                                    icon: "warning",
                                    text: `Bateria ruim, adicionado servico ${nome_servico} ao reparo`,
                                    didOpen: (toast) => {
                                        toast.onmouseenter = Swal.stopTimer;
                                        toast.onmouseleave = Swal.resumeTimer;
                                    }
                                });
                                $('#input-voltagem').val('');
                            }).fail(() => {
                                Swal.fire({
                                    toast: true,
                                    position: "top-end",
                                    showConfirmButton: false,
                                    timer: 5000,
                                    timerProgressBar: true,
                                    icon: "error",
                                    text: `Falha ao adicionar servico ${nome_servico} ao reparo, tente realizar novamente ou adicione-o manualmente`,
                                    didOpen: (toast) => {
                                        toast.onmouseenter = Swal.stopTimer;
                                        toast.onmouseleave = Swal.resumeTimer;
                                    }
                                });

                                return false;
                            });

                            $('#modalVerificarBateria').modal('hide');
                            $('#btn-add-servico').prop('disabled', false);
                            $('#btnRegistrarVoltagem').prop('disabled', false);
                        } else {
                            $('#modalVerificarBateria').modal('hide');
                            $('#btn-add-servico').prop('disabled', false);
                            $('#btnRegistrarVoltagem').prop('disabled', false);

                            Swal.fire({
                                toast: true,
                                position: "top-end",
                                showConfirmButton: false,
                                timer: 5000,
                                timerProgressBar: true,
                                icon: "success",
                                text: `Bateria está ok, não é necessário a troca`,
                                didOpen: (toast) => {
                                    toast.onmouseenter = Swal.stopTimer;
                                    toast.onmouseleave = Swal.resumeTimer;
                                }
                            });
                        }
                    });
                });
            }
        });
    
        $.ajax({
            url: base_url + 'reparo/realizar_servico',
            type: 'POST',
            data: {
                id_reparo_servico: id_reparo_servico,
            }
        }).done((data) => {
            // caso sucesso adiciona um serviço aberto a lista
            if (data !== null) {
                $(`#check-servico-${id_reparo_servico}`).html(`
                    <div id="check-servico-${id_reparo_servico}">
                        <input class="form-check-input check-servico" type="checkbox" value="${id_reparo_servico}" onclick="desfazerReparoServico(${id_reparo_servico}, '${texto_reparo_servico}', ${id_servico},${p_id_reparo});" checked>
                        <label class="form-check-label">
                            ${texto_reparo_servico}
                        </label>
                    </div>
                `);
            } else {
                Swal.fire({
                    title: "Ocorreu um erro!",
                    text: "Erro ao criar o reparo!",
                    icon: "error"
                  });

                return false
            }
        })
        
    } else {
        $(`#checkbox-servico-${id_reparo_servico}`).prop('checked', false);
        return false;
    }
}

async function cancelaServico(id_reparo_servico, texto_servico, id_servico) {
    // let id_reparo_servico = $(this).val();
    // let texto_reparo_servico = $(this).siblings('label').text();
    $.ajax({
        url: base_url + 'reparo/cancelar_servico',
        type: 'POST',
        data: {
            id_reparo_servico: id_reparo_servico,
        }
    }).done((data) => {
        // caso sucesso
        if (data !== null) {
            if ($('#slctListaServicoEquip').val() == 0) {
                $('#slctListaServicoEquip').removeAttr('disabled');
                $('#btn-add-servico').removeAttr('disabled');
                $('#slctListaServicoEquip').empty();
            }
            $('#slctListaServicoEquip').append(
                `<option value="${id_servico}">${texto_servico}</option>`
            );
            $(`#check-servico-${id_reparo_servico}`).remove();
        } else {
            Swal.fire({
                title: "Ocorreu um erro!",
                text: "Erro ao cancelar serviço",
                icon: "error"
            });

            return false;
        }
    })
}

$("#frmIniciarReparo").on('submit',(e) => {

    e.preventDefault()

    const p_num_equipamento = $("#listaEquipReparo").val()
    const p_id_bancada = $("#listaBancadas").val()
    let nome_bancada = $('#listaBancadas :selected').html();
    const p_nome_bancada = nome_bancada.trim();
    $.ajax({

        url: base_url + 'reparo/criar_reparo',
        type: 'POST',
        data: {
            id_chamado: g_id_chamado,
            id_bancada: p_id_bancada,
            num_equipamento: p_num_equipamento,
            nome_bancada: p_nome_bancada
        },
        beforeSend: function() {
            $("#btnIniciarReparo").prop("disabled","true")
            $("#listaBancadas").prop("disabled","true")
            $("#listaEquipReparo").prop("disabled","true")
        }
    })
    .done((data) => {
        if (data !== null) {
            $('#modalIniciarReparo').find('.modal-body')
            .prepend(
                `<div id="alerta-reparo" class="alert alert-success" role="alert">
                    ${data.mensagem}
                </div>`
            )

        carregaChamado(g_id_chamado)

        }
        else {
            $('#modalIniciarReparo').find('.modal-body')
            .prepend(
                `<div id="alerta-reparo" class="alert alert-danger" role="alert">
                Erro ao criar o reparo!
              </div>`
            )


            return false
        }

    })
  })


 

$("#btnCancelarReparo").on('click', async (e) => {


    e.preventDefault();

    const btn = $(this)

    const txtJustificativaCancelamento = $('#txtJustificativaCancelamento').val()
    if(txtJustificativaCancelamento.length < 5) {
        Swal.fire({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            icon: "error",
            text: 'Texto muito curto!',
            didOpen: (toast) => {
              toast.onmouseenter = Swal.stopTimer;
              toast.onmouseleave = Swal.resumeTimer;
            }
        });
        return false;
    }
    var cancelamento = null
    res = await carregaReparo(p_id_reparo);
    if (res.reparo.status_reparo == 'ABERTO') {
        res.reparo.status_reparo = 'REPARO';
    }

    await $.ajax({
        method: "POST",
        url: base_url + 'reparo/cancelar_reparo',
        data: {
            id_reparo: p_id_reparo,
            texto_justificativa: txtJustificativaCancelamento,
            tipo_servico: res.reparo.status_reparo
        },
        beforeSend: () => {
            btn.prop("disabled","true")
        }
    }).done((data) => {
        cancelamento = data
        Swal.fire({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            icon: "success",
            title: "Reparo cancelado com sucesso!",
            didOpen: (toast) => {
              toast.onmouseenter = Swal.stopTimer;
              toast.onmouseleave = Swal.resumeTimer;
            }
        });
        btn.removeAttr("disabled")
    })

    if (cancelamento == true) {

        $("#modalJustificativaCancelamento").modal('hide')
        $("#modalReparo").modal('hide')
        $("#txtJustificativaCancelamento").val('')

    }

    carregaChamado(g_id_chamado)
})

// ------- REMESSA INSERVIVEL -------
$("#painel-inservivel").jsGrid({
    width: "100%",
    height: "auto",

    autoload: true,
    inserting: false,
    editing: false,
    sorting: true,
    paging: false,
    filtering: true,
    loadMessage: "Carregando...",
    noDataContent: "(vazio)",
    controller: {
        loadData: function (filter) {
            let dadosJsGrid = $("#painel-inservivel").data("JSGrid").data;

            if(!filter.numero_equipamento && !filter.descricao_equipamento && !filter.nome_local && !filter.texto_interacao && !filter.id_chamado) {
                return $.ajax({
                    url: base_url + "listar_detalhado/" + $(location).attr('pathname').split('/')[2],
                    dataType: "json"
                }).done(function (data) {
                    // Ordenar os dados pela coluna 'numero_equipamento'
                    data.sort(function (a, b) {
                        return a.numero_equipamento - b.numero_equipamento;
                    });
    
                    // Atualizar os dados no jsGrid
                    $("#painel-inservivel").jsGrid("option", "data", data);
                    // Ativa os botões após o load
                    $("#realizar-entrega").prop("disabled", false);
                    $("#btn-impressao").prop("disabled", false);
                });
            }

            return $.grep(dadosJsGrid, function(item) {
                return (
                    !filter.numero_equipamento || item.numero_equipamento.indexOf(filter.numero_equipamento) > -1)
                    && (!filter.descricao_equipamento || item.descricao_equipamento.indexOf(filter.descricao_equipamento) > -1)
                    && (!filter.nome_local || item.nome_local.indexOf(filter.nome_local) > -1)
                    && (!filter.texto_interacao || item.texto_interacao.indexOf(filter.texto_interacao) > -1
                )
            });
        },
    },

    fields: [
        {
            type: "checkbox",
            title: "",
            filtering: false,
            width: 5,
            align: "center",
            itemTemplate: function(item, value) {
                return `<input type="checkbox" id="checkbox_${value.numero_equipamento} value="${value.numero_equipamento}" name="checkbox_inservivel">`
            }
        }, {
            name: "numero_equipamento",
            type: "text",
            filtering: true,
            title: "Nº equipamento"
        }, {
            name: "descricao_equipamento",
            type: "text",
            filtering: true,
            title: "Descrição"
        }, {
            name: "nome_local",
            type: "text",
            filtering: true,
            title: "Local"
        }, {
            name: "texto_interacao",
            type: "text",
            title: "Laudo",
            width: 180,
        }, {
            name: "id_chamado",
            type: "text",
            title: "Nº chamado",
            filtering: false,
            width: 40,
            align: "center",
            itemTemplate: function(item) {
                return `<a href=${base_url}chamado/${item} target="blank">#${item}</a>`
            }
        }, {
            type: "control",
            deleteButton: false,
            editButton: false,
        }
    ]
});

$("#fechar_lista").click(function() {
    $.ajax({
        url: `${base_url}inservivel/fecharRemessa`,
        type: 'POST',
        async: true,
        data: {
            id_remessa: $(location).attr('pathname').split('/')[2],
        },
        success: function() {
            window.location.href = `${base_url}/inservivel`;
        },
        error: function(error) {
            alert(error.responseJSON.mensagem);
        }
    });
});

$("#btn-impressao").click(function(){
    let checkboxs = $("input[name='checkbox_inservivel']:checked");
    if(checkboxs.length == 0) {
        alert("Nenhum patrimônio selecionado!");
    } else {
        let equipamentos = [];
        // Para cada checkbox em 'checkboxs' selecionado
        checkboxs.each(function () {
            let row = $(this).closest("tr");
            let rowData = $("#painel-inservivel").jsGrid("option", "data")[row.index()];
            equipamentos.push(rowData);
        });

        equipamentos.forEach(function (equipamento) {
            linha = `<tr>
                <td>${equipamento.numero_equipamento}</td>
                <td>${equipamento.descricao_equipamento}</td>
                <td class="text-left">${equipamento.nome_local}</td>
                <td class="text-left">${equipamento.texto_interacao}</td>
            </tr>`;

            tableBody = $("table #tbody-impressao");
            tableBody.append(linha);
        });

        window.print();
        $("table #tbody-impressao td").remove();
    }
});

$(document).ready(function () {
    $("#realizar-entrega").click(function(){
        // momento que clicar no sim ou não entra na função
        $('.custom-control-input').on('click', function() {
            // limpa todo conteudo e button
            $('#formEntregaInservivel').empty();
            $('#button-submit-remessa').remove();
            let botao = null;
            
            // irá executar quando o radio estiver em sim
            if ($('#rdSim').is(":checked")) {
                $('#formEntregaInservivel').append(`
                    <div class="form-group">
                        <div class="mb-3">
                            <label for="termo">Termo de entrega assinado</label>
                            <input type="file" class="form-control-file" id="termo-entrega" name="termo_remessa" accept="application/pdf" required>
                        </div>
                        <div class="mb-3" id="input-nome-recebedor">
                            <label for="nome_recebedor">Nome do recebedor</label>
                            <input type="text" class="form-control" id="nome-recebedor-remessa">
                            <div class="invalid-feedback">
                                Por favor, insira um nome válido. Certifique-se de utilizar apenas caracteres alfabéticos e verifique se a formatação está correta.
                            </div>
                        </div>
                        <div class="mb-3" id="input-data-entrega">
                            <label for="data_entrega">Data de recebimento no almoxarifado</label>
                            <input type="date" class="form-control" id="data-recebimento" required>
                            <div class="invalid-feedback">
                                Data fora do prazo permitido. Por favor, entre em contato com o administrador para obter assistência.
                            </div>
                        </div>
                    </div>
                `);
                botao = `<div class="text-right"><button type="submit" class="btn btn-success" id="button-submit-remessa"><i class="fas fa-check"></i> Registrar entrega</button></div>`;
            }else {
                $('#formEntregaInservivel').append(`
                    <div class="form-group">
                        <div class="mb-3">
                            <h6>Selecione os equipamentos que voltaram</h6>
                            <input type="checkbox" class="mr-2" id="selecionar_todos">
                            <label for="selecionar_todos">Selecionar todos equipamentos</label>
                            <div id="equipamentos-remessa"></div>
                        </div>
                    </div>
                `);

                $('#painel-inservivel tbody tr').each(function() {               
                    // Objeto para armazenar os dados da linha atual
                    let dadosLinha = {};

                    // Iterar sobre cada célula da linha
                    $(this).find('td').each(function(index, cell) {
                        // Adicionar o valor da célula ao objeto com base no índice da coluna
                        dadosLinha['coluna' + index] = $(cell).text();
                    });

                    $('#equipamentos-remessa').append(`
                        <input type=checkbox value="${dadosLinha['coluna1']}" class="checkbox-falha-entrega-remessa mr-2" id="checkbox-falha-entrega-remessa-${dadosLinha['coluna1']}">
                        <label for="checkbox-falha-entrega-remessa-${dadosLinha['coluna1']}">${dadosLinha['coluna1']} - ${dadosLinha['coluna2']}</label><br>
                    `);
                });
                botao = `<div class="text-right"><button type="submit" class="btn btn-danger" id="button-submit-remessa"><i class="fas fa-times-circle"></i> Registrar falha de entrega</button></div>`;
                $('#selecionar_todos').click(function () {
                    if ($('#selecionar_todos').is(':checked')) {
                        $('.checkbox-falha-entrega-remessa').each(function () {
                            // Adicionar o valor do checkbox marcado ao array
                            // selecione no máximo 5 equipamentos caso contrário remessa será colocada como erro
                            $(this).prop("disabled", true);
                            $(this).prop("checked", true);
                        });
                    } else {
                        // Iterar sobre todos os checkboxes com a classe 'checkbox-selecao'
                        $('.checkbox-falha-entrega-remessa').each(function () {
                            // Adicionar o valor do checkbox marcado ao array
                            // selecione no máximo 5 equipamentos caso contrário remessa será colocada como erro
                            $(this).prop("checked", false);
                            $(this).prop("disabled", false);
                        });
                    }
                });
            }
            $('#formEntregaInservivel').append(botao);
        });
    });
});

// quando der submit no form chama a function
$('#frm-remessa-entrega').on('submit', function(e) {
    e.preventDefault();
    let dados = new FormData($(this)[0]);
    let erro_remessa = false;

    if ($('#rdSim').is(":checked")) {
        let nomeRegex = /^[a-zA-ZÀ-ÖØ-öø-ÿ\s']+$/;
        let nome_recebedor = $('#nome-recebedor-remessa').val();
        let data_recebimento = $('#data-recebimento').val();
        let submitErro = false;
        let termo_entrega = $('#termo-entrega').val();

        // Converte a entrada para um objeto Date
        let dataInserida = new Date(data_recebimento);
        // Obtém a data atual
        let dataAtual = new Date();
        // Obtém a data um mês atrás
        let dataUmMesAtras = new Date();
        dataUmMesAtras.setMonth(dataAtual.getMonth() - 1);
        // caso data inserida seja inválida (data inserida maior que data atual)
        if(dataInserida >= dataAtual && dataInserida != 'Invalid Date') {
            submitErro = true;
            $('#data-recebimento').addClass('is-invalid');
        // caso usuario não seja administrador a data não pode ser maior que 1 mês
        }else if (g_auto_usuario != 4 && !(dataInserida > dataUmMesAtras)) {
            submitErro = true;
            $('#data-recebimento').addClass('is-invalid');
        }else {
            $('#data-recebimento').removeClass('is-invalid');
            $('#data-recebimento').addClass('is-valid');
        }

        if (!nomeRegex.test(nome_recebedor)) {
            submitErro = true;
            $('#nome-recebedor-remessa').addClass('is-invalid');
        } else {
            $('#nome-recebedor-remessa').removeClass('is-invalid');
            $('#nome-recebedor-remessa').addClass('is-valid');
        }

        dados.append("nome_recebedor", nome_recebedor);
        dados.append("data_recebimento", data_recebimento);
    }else {
        let equipamentos_selecionados = [];
        erro_remessa = true;

        if (!$('#selecionar_todos').is(':checked')) {
            // Iterar sobre todos os checkboxes com a classe 'checkbox-selecao'
            $('.checkbox-falha-entrega-remessa:checked').each(function() {
                // Adicionar o valor do checkbox marcado ao array
                equipamentos_selecionados.push($(this).val());
            });

            // validação para erro dos selecionados se for acima de 5 ou esvaziar a lista
            if(equipamentos_selecionados.length > 5) {
                return alert("O limite máximo de equipamentos é de 5");
            }
            if(!equipamentos_selecionados.length || $('.checkbox-falha-entrega-remessa').length == equipamentos_selecionados.length) {
                return alert("Por favor, selecione pelo menos um equipamentos ou todos.");
            }

            dados.append("equipamentos", JSON.stringify(equipamentos_selecionados));
        }else {
            dados.append("equipamentos", null);
        }
    }
    dados.append("erro_remessa", erro_remessa);
    dados.append("id_remessa", $(location).attr('pathname').split('/')[2]);

    
    $.ajax({
        url: base_url + "/inservivel/registrarEntrega",
        dataType: "json",
        contentType: false,
        processData: false,
        method: "post",
        data: dados,
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("Erro na solicitação AJAX:", textStatus, errorThrown);
        },
        success: function () {
            let mensagem = "";
            if ($('#rdNao').is(":checked")) {
                if ($('#selecionar_todos').is(':checked')) {
                    mensagem = "Falha registrada!\nA remessa permanecerá aberta com estado de erro!"
                } else {
                    mensagem = "Os equipamentos selecionados foram alocados na remessa seguinte."
                }
            } else {
                mensagem = "Entrega registrada com sucesso!";
            }
            
            alert(mensagem)
            window.location.reload();
        }
    });
    
});

let dados = [];

if (window.location.href.includes("inservivel")) {
    $.ajax({
        url: `${base_url}inservivel/listarRemessas`,
        type: 'GET'
    }).done(function (data) {
        dados = data;
        exibirDados(); // Chame a função exibirDados após obter os dados
    });

    const itensPorPagina = 10;
    let paginaAtual = 1;

    function exibirDados() {
        const tbody = $('#tabela-inservivel tbody');
        tbody.empty();

        const totalPages = Math.ceil(dados.length / itensPorPagina);

        // Garante que a página atual está dentro dos limites
        paginaAtual = Math.max(1, Math.min(paginaAtual, totalPages));

        // Calcula os índices de início e fim
        const inicio = (paginaAtual - 1) * itensPorPagina;
        const fim = Math.min(inicio + itensPorPagina, dados.length);

        // Se a página atual for menor que 1, define como 1
        if (paginaAtual < 1) {
            paginaAtual = 1;
        }

        const dadosPagina = dados.slice(inicio, fim);

        dadosPagina.forEach(dado => {
            let status = {};
            dado.data_abertura = (dado.data_abertura == null) ? "" : dado.data_abertura;
            dado.data_fechamento = (dado.data_fechamento == null) ? "" : dado.data_fechamento;
            dado.data_entrega = (dado.data_entrega == null) ? "" : dado.data_entrega;
            if (typeof dado.pool_equipamentos === 'string' || dado.pool_equipamentos == null) {
                dado.pool_equipamentos = (dado.pool_equipamentos == "" || dado.pool_equipamentos == null) ? 0 : dado.pool_equipamentos.split("::").length;
            }

            if (dado.data_entrega) {
                status = {
                    mensagem: "Entregue",
                    class: "active"
                }
            } else if (dado.falha_envio == true) {
                status = {
                    mensagem: "Erro",
                    class: "warning"
                }
            } else if (dado.data_fechamento) {
                status = {
                    mensagem: "Fechada",
                    class: "danger"
                }
            } else {
                status = {
                    mensagem: "Aberta",
                    class: "success"
                }
            }

            const linhas = $(`
                <tr class='table-${status.class}' onclick="location.href='${base_url}inservivel/${dado.id_remessa_inservivel}'" style="cursor: pointer;">
                    <td><strong>${dado.id_remessa_inservivel}</strong></td>
                    <td><strong>${dado.divisao_remessa}</strong></td>
                    <td>${dado.pool_equipamentos}</td>
                    <td>${dado.data_abertura}</td>
                    <td>${dado.data_fechamento}</td>
                    <td>${dado.data_entrega}</td>
                    <td>${dado.nome_usuario}</td>
                    <td><strong>${status.mensagem}</strong></td>
                </tr>
            `);
            tbody.append(linhas);
        });

        $('#paginaAtual').text(paginaAtual);
    }

    function proximaPagina() {
        const totalPages = Math.ceil(dados.length / itensPorPagina);
        if (paginaAtual < totalPages) {
            paginaAtual++;
            exibirDados();
        }
    }

    function paginaAnterior() {
        if (paginaAtual > 1) {
            paginaAtual--;
            exibirDados();
        }
    }

    // Inicializar exibição
    exibirDados();
}
// ------- FIM REMESSA INSERVIVEL -------