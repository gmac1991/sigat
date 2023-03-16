const patrimonio_regex = /\b[1-9]\d{5}\b/g
var fila_atual = null;

var chamados_expo = []
var cont_imp = 0;

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
        "pageLength" : 15,

        stateSave: true,

        lengthMenu: [
            [15, 25, 50, 100],
            [15, 25, 50, 100],
        ],

        "columnDefs": [
            {
                "targets": 1,
                //"data": "prioridade_chamado",
                "render": function ( data, type, row, meta ) {

                    var display;

                    switch(data) {

                        case "1":
                            display = "1 - PRIOR <span class=\"text-warning\"><i class=\"fas fa-star\"></i></span>";
                            break;
                        case "ABERTO":
                            display = "2 - ABERTO <span class=\"text-warning\"><i class=\"fas fa-circle\"></i></span>";
                            break;
                        case "FECHADO":
                            display = "3 - FECHADO <span class=\"text-success\"><i class=\"fas fa-circle\"></i></span>";
                            break;
                    }
                    

                    return display;
                }

            },

            {
                "targets": [7,9],
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

    if (num_equipamentos.length > 0) {

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
    }
}


function verificaTipo(fila_ant, id_chamado) { //verificar tipo da fila no modal de Registro de Atendimento

    $('select[name=id_fila]').empty();


    switch ($('#slctTipo').val()) {

        case 'ATENDIMENTO':
            buscaEquipamentos(id_chamado, fila_ant, true, false, false);
            $('#divEquipamentos').show();
            $('#divFila').show();
            $('#slctFila').attr('disabled', true);
            break;

        case 'ALT_FILA':
            buscaEquipamentos(id_chamado, fila_ant, false, false, false, true);
            $('#divFila').show();
            $('#divEquipamentos').hide();
            $('#slctFila').attr('disabled', false);
            break;

        case 'OBSERVACAO':
            $('#divEquipamentos').hide();
            $('#divFila').hide();
            $('#slctFila').attr('disabled', true);
            $('#btnRegistrarInteracao').removeAttr('disabled');
            break;
            
        case 'INSERVIVEL':

            if (fila_ant == 3) {
                buscaEquipamentos(id_chamado, fila_ant, true, true);
                $('#divEquipamentos').show();
                $('#divFila').show();
                $('#slctFila').attr('disabled', true);
            } else {
                $('#divEquipamentos').show();
                $('#divEquipamentos').html('Opção disponível somente na fila <strong>Manutenção de Hardware</strong><br>');
                $('#btnRegistrarInteracao').prop('disabled', 'true');
                $('#divFila').hide();
            }


            break;

        case 'ESPERA':
            buscaEquipamentos(id_chamado, fila_ant, false);
            $('#divEquipamentos').show();
            $('#divFila').hide();
            $('#slctFila').attr('disabled', true);
            break;

        case 'REM_ESPERA':
            buscaEquipamentos(id_chamado, fila_ant, false, false,true,false);
            $('#divEquipamentos').show();
            $('#divFila').hide();
            $('#slctFila').attr('disabled', true);
            break;
        case 'FECHAMENTO':

            break;

    }

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
        "<div class=\"row mt-3\">" +
        "<div class=\"col\">" +
        "<div id=\"divEquipamentos\"></div>" +
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

$('#modalRegistro').on('show.bs.modal', function(event) { //modal de registro de interacao

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

    $('#slctTipo').append("<option value=\"ATENDIMENTO\" selected>Atendimento</option>" +
        "<option value=\"OBSERVACAO\">Observação</option>" +
        "<option value=\"ALT_FILA\">Alteração de fila</option>");

    // if (g_requer_patri == true) {
    $('#slctTipo').append('<option value=\"ESPERA\">Deixar em espera</option>');
    $('#slctTipo').append('<option value=\"REM_ESPERA\">Remover da espera</option>');
    $('#slctTipo').append('<option value=\"INSERVIVEL\">Classificar como inservível</option>');
    


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



async function carregaChamado(p_id_chamado, sem_equipamentos) {

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
    
        fields: [
            {
                name: "num_equipamento",
                title: "Núm. de identificação",
                type: "text",
                readOnly: true,
                validate: [
                    "required",
                    { validator: "pattern", param: /^[a-zA-Z0-9]+$/, message: "Atenção!\nCaracteres permitidos:\nA-Z, a-z e 0-1" },
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
                inserting: false,
                editing: false,
                title: "Lacre",
                validate: [
                    { validator: "pattern", param: /^[a-zA-Z0-9]+$/, message: "Atenção!\nCaracteres permitidos:\nA-Z, a-z e 0-1" },
                ],
            },
            {
                name: "status_equipamento_chamado",
                title: "Status",
                width: 50,
                align: "center",
            }, 
            {
                type:"control",
                editButton: false,
                deleteButton: false,
            }
        ],

        

        rowClass: function(item) { return item.status_equipamento_chamado == 'ABERTO' ? 'bg-warning' : ''; },
    
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
                await $.ajax({
                    url: base_url + "json/status_equipamento",
                    dataType: "json",
                    method: "post",
                    data: {e_status: num_equip},
                }).done(function(data) {
                    res = data;
                });
               
                if(res !== null && parseInt(res.id_chamado) !== g_id_chamado) {
                    alert("O item " + num_equip + " já está em atendimento!\nChamado: " + res.id_chamado + "\n" + res.ticket_chamado);
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
            },
            insertItem: async function(item) {
                var d = $.Deferred();
                var res = null;
                var num_equip = item.num_equipamento.replace(/\s+/g, "");
                await $.ajax({
                    url: base_url + "json/status_equipamento",
                    dataType: "json",
                    method: "post",
                    data: {e_status:num_equip},
                }).done(function(data) {
                    res = data;
                    
                });
                if(res !== null){
                    if (res.status_equipamento_chamado == 'ABERTO') {
                        alert("O item " + num_equip + " já está em atendimento!\nChamado: " + res.id_chamado + "\n" + res.ticket_chamado);
                        d.reject();
                        return d.promise();
                    }
                }

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
                   
            },
    
            deleteItem: async function(item) {
                var d = $.Deferred();
                var res = null;
                var num_equip = item.num_equipamento.replace(/\s+/g, "");
                await $.ajax({
                    url: base_url + "json/status_equipamento",
                    dataType: "json",
                    method: "post",
                    data: {e_status: num_equip},
                }).done(function(data) {
                    res = data;
                });
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

    document.title = "Chamado #" + p_id_chamado + " - SIGAT";

    p_id_responsavel = null;
    
    $("#spnStatusChamado").fadeIn();
    
    var historico = await carregaHistorico(p_id_chamado);
    $("#historico").html(historico);

   

    $('#botoesAtendimento').html("");
    $('#btnBloquearChamado').removeAttr("disabled");

    botoes = "";

    $('#botoesChamado hr').hide();

   var status_equips = [];
   var id_responsavel = null;

    await $.ajax({
        url: base_url + 'json/chamado',
        dataType: 'json',
        async: true,
        data: {
            id_chamado: p_id_chamado
        },
        success: function(data) {

            //preencher os campos conforme o json

            $('input[name=fila]').val(data.nome_fila_chamado);
            $('input[name=resumo]').val(data.resumo_chamado);
            $('input[name=complemento]').val(data.complemento_chamado);
            $('input[name=data_chamado]').val(data.data_chamado);
            $('input[name=status]').val(data.status_chamado);
            $('input[name=nome_solicitante]').val(data.nome_solicitante_chamado);
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


            // -------------------- PERMISSOES ----------------------------
            
            
            
    

            if (data.id_responsavel == g_id_usuario) {

                tblEquipsChamado.option("editing",true);
                tblEquipsChamado.option("inserting",true);
                tblEquipsChamado.fieldOption(4,"editButton",true);
                tblEquipsChamado.fieldOption(4,"deleteButton",true);
                tblEquipsChamado.fieldOption(1,"readOnly",false);
                tblEquipsChamado.fieldOption(0,"readOnly",false);
                tblEquipsChamado.fieldOption(0,"editing",false);
                tblEquipsChamado.fieldOption(0,"inserting",true);
               // tblEquipsChamado.fieldOption(2,"editing",true);
                
               
                if (data.id_fila == 3) {

                    tblEquipsChamado.fieldOption(2,"editing",true);
                
                }

                if (g_auto_usuario > 3) {

                   
                    tblEquipsChamado.fieldOption(1,"readOnly",false);
                    tblEquipsChamado.fieldOption(4,"deleteButton",true);

                }
            }
           
            
            

            if (data.status_chamado != 'ABERTO') { //se o chamado não estiver ABERTO, remover o botao Registrar Atendimento e Editar Chamado

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

   


    if (g_auto_usuario >= 3 && g_auto_usuario_enc == 1  && status_chamado == 'FECHADO') {//somente ADM+ encerra o chamado

        botoes = '<button id="btnEncerrarChamado" onclick="encerrarChamado()" class="btn btn-success"><i class=\"far fa-check-circle\"></i> Encerrar chamado</button>';
    }

    

    for (var i = 0; i < status_equips.length; ++i) {

        if ((p_id_responsavel == g_id_usuario && 
            (status_equips[i] == 'ABERTO' || status_equips[i] == 'ESPERA' || status_equips[i] == 'FALHA' || status_equips[i] == 'ENTREGA'))) {

            botoes = "<button type=\"button\" id=\"btnModalRegistro\" class=\"btn btn-primary\"" +
            " data-toggle=\"modal\" data-target=\"#modalRegistro\" data-chamado=\"" + p_id_chamado +
            "\"><i class=\"fas fa-asterisk\"></i> Nova Ação</button> ";
    
            break;
        }

        

       
    }

    var pendentes = 0;
    
    for (var i = 0; i < status_equips.length; ++i) {

        

        if (status_equips[i] == 'ABERTO' || status_equips[i] == 'ESPERA' || status_equips[i] == 'FALHA' || status_equips[i] == 'ENTREGA') {

            pendentes++;

        }

       
    }

    if (p_id_responsavel == g_id_usuario && pendentes == 0 && status_chamado == 'ABERTO') {

        botoes = "<button type=\"button\" id=\"btnFechamentoManual\" class=\"btn btn-primary\"" +
        " onclick=\"finalizaManual(" + p_id_chamado + ")\"><i class=\"fas fa-pen-alt\"></i> Fechamento manual</button> ";

    }

    if (entrega == 1 && p_id_responsavel == g_id_usuario && status_chamado == 'ABERTO' ) {

               

        botoes = botoes +  "<button type=\"button\" id=\"btnModalRegistroEntrega\" class=\"btn btn-success\" data-toggle=\"modal\" data-chamado=\"" +
                            p_id_chamado + "\" data-target=\"#modalRegistroEntrega\"><i class=\"fas fa-file-signature\"></i> Registrar Entrega</button> " +
                            "<a href=\"" + base_url + "chamado/gerar_termo/" +
                            p_id_chamado + "\" id=\"baixarTermoEntrega\" role=\"button\" class=\"btn btn-info\">" +
                            "<i class=\"fas fa-file-download\"></i> Termo de Entrega</a> " +
                            "<a href=\"" + base_url + "chamado/gerar_termo_resp/" +
                            + p_id_chamado + "\" id=\"baixarTermoResp\" role=\"button\" class=\"btn btn-info\">" +
                            "<i class=\"fas fa-file-download\"></i> Termo de Responsabilidade</a>"
    
    }
    $('#botoesAtendimento').html(botoes);
    $("#spnStatusChamado").fadeOut();


    
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

    $('input[class=chkPatri]').each(function() {

        if ($(this).is(':checked')) {
            p_equips_atendidos.push($(this).val());
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

    // console.log(dados);


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

    },
    messages: {
        nome_solicitante: "Campo obrigatório!",
        nome_local: "Campo obrigatório!",
        telefone: {
            required: "Campo obrigatório!",
            digits: "Somente dígitos (0-9)!",
            minlength: "Mínimo 3 dígitos!"
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

// ----------- USUARIOS ---------------

var autorizacoes = [{
    Name: "Técnico",
    Id: "2"
}, {
    Name: "Administrador",
    Id: "3"
}, {
    Name: "Master",
    Id: "4"
}];

var estados = [{
    Name: "ATIVO",
    Id: "ATIVO"
}, {
    Name: "INATIVO",
    Id: "INATIVO"
}];

var opcoes_fila = [{
    Name: "SIM",
    Id: "1"
}, {
    Name: "NÃO",
    Id: "0"
}, ];

// --- ATUALIZA FILAS ----
var filas = [{
    id_fila: "0",
    nome_fila: "Todos"
}];

$.ajax({
    url: base_url + 'json/filas',
    dataType: 'json',
    complete: resp => {
        Array.prototype.push.apply(filas, resp.responseJSON);

        //console.log(filas);

        $("#usuarios-grid").jsGrid({

            fields: [
                //{ name: "id_usuario", type: "text", readOnly:true },
                {
                    name: "nome_usuario",
                    type: "text",
                    validate: "required",
                    title: "Nome"
                }, {
                    name: "login_usuario",
                    type: "text",
                    validate: "required",
                    title: "Login"
                }, {
                    name: "data_usuario",
                    type: "text",
                    readOnly: true,
                    title: "Data de criação"
                }, {
                    name: "status_usuario",
                    type: "select",
                    items: estados,
                    textField: "Name",
                    valueField: "Id",
                    title: "Situação"
                }, {
                    name: "autorizacao_usuario",
                    type: "select",
                    items: autorizacoes,
                    textField: "Name",
                    valueField: "Id",
                    title: "Autorização"
                }, {
                    name: "fila_usuario",
                    type: "select",
                    items: filas,
                    textField: "nome_fila",
                    valueField: "id_fila",
                    title: "Fila preferencial"
                }, {
                    name: "alteracao_usuario",
                    type: "text",
                    readOnly: true,
                    title: "Última alteração"
                }, {
                    type: "control",
                    deleteButton: false
                }
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
        loadData: function() {
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
        {
            name: "nome_usuario",
            type: "text",
            validate: "required",
            title: "Nome"
        }, {
            name: "login_usuario",
            type: "text",
            validate: "required",
            title: "Login"
        }, {
            name: "data_usuario",
            type: "text",
            readOnly: true,
            title: "Data de criação"
        }, {
            name: "status_usuario",
            type: "select",
            items: estados,
            textField: "Name",
            valueField: "Id",
            title: "Situação"
        }, {
            name: "autorizacao_usuario",
            type: "select",
            items: autorizacoes,
            textField: "Name",
            valueField: "Id",
            title: "Autorização"
        },
        //	{ name: "fila_usuario", type: "select", items: filas, textField: "nome_fila", valueField:"id_fila", title:"Fila preferencial" },
        {
            name: "alteracao_usuario",
            type: "text",
            readOnly: true,
            title: "Última alteração"
        }, {
            type: "control",
            deleteButton: false
        }
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
        loadData: function() {
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
        {
            name: "nome_fila",
            type: "text",
            validate: "required",
            title: "Nome"
        }, {
            name: "status_fila",
            type: "select",
            items: estados,
            textField: "Name",
            valueField: "Id",
            title: "Situação"
        },
        // { name: "requer_equipamento_fila", type: "select", items: opcoes_fila, textField: "Name", valueField:"Id", title:"Requer patrimônio?" },
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
        loadData: function() {
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
        {
            name: "nome_fila",
            type: "text",
            validate: "required",
            title: "Nome"
        }, {
            name: "status_fila",
            type: "select",
            items: estados,
            textField: "Name",
            valueField: "Id",
            title: "Situação"
        },
        //{ name: "requer_equipamento_fila", type: "select", items: opcoes_fila, textField: "Name", valueField:"Id", title:"Requer patrimônio?" },
        {
            type: "control",
            deleteButton: false
        }
    ]
});

//------------- FIM FILAS ------------------


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

async function carregaTriagem(p_id_ticket) {

   
    //$('div[name=descricao_triagem]').html('');

    //traz os dados do chamado MIGRADO (OTRS)

    document.title = "Triagem #" + p_id_ticket + " - SIGAT";

    var anexos = [];

    $("#linhaInfoTriagem").hide();

    await $.ajax({
        url: base_url + 'json/triagem',
        dataType: 'json',
        async: true,
        data: {
            id_ticket: p_id_ticket
        },
        success: function(data) {

            if (data.agrupamento == 1) {
                $("#header_triagem")
                .after("<div class=\"alert alert-info\" role=\"alert\">" +
                "<p class=\"mb-0\"><i class=\"fas fa-info-circle\"></i> Já existe um <a target=\"_blank\" href=\"" + base_url + "chamado/" + data.chamado.id_chamado + 
                "\" class=\"alert-link\">chamado aberto<sup><i class=\"fas fa-external-link-square-alt\"></i></sup></a> para este ticket! Caso seja feita a importação, as novas informações serão agrupadas nele.</p></div>");
            
                agrupamento = true;
                //diffDesc = data.diff;

                $("#linhaInfoTriagem").html("");
            }


            $("#linhaInfoTriagem").show();
    

            
            if (data.anexos_otrs.length > 0) {

                data.anexos_otrs.forEach(function(item){
                    anexos.push({id_arquivo:item.id,nome_arquivo:item.filename})

                })   
            }            
        },
    });

    // if (!agrupamento) {
    //     desc_triagem = UTF8.decode(desc_triagem)
    // }

    // $('#descricao_triagem').html(desc_triagem);

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

async function verificaStatusEquip(p_e) {
    out = null;
    await $.ajax({
        method: "post",
        url: base_url + "json/status_equipamento",
        data: { e_status: p_e}
      })
        .done(function( res ) {
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

  

    // let response = await fetch(base_url + "triagem/descricao/" + g_id_triagem);

    // if (response.ok) { 
    //     text = await response.text();
    // } else {
    //     console.log("HTTP-Error: " + response.status);
    // }
    
    if (agrupamento) {

        // var diff_n1 = text.match(/<div class="diff">(.*?)<\/div>/g);

        // var novo_texto = null

        // diff_n1.forEach(function (item) {

        //     novo_texto = novo_texto + item.match(/<div class="diff">(.*?)<\/div>/g);

        
        // });

        // nums_equip = novo_texto.match(patrimonio_regex);

        // confirmado = true;

        $("#btnValidaEquip").removeAttr("disabled");
        $("#btnLoteEquip").removeAttr("disabled");
        
    }
    // else {
        nums_equip = text.match(patrimonio_regex);
    // }

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
                        status.status_equipamento_chamado === "INSERVIVEL") {
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
                    var status = await verificaStatusEquip(grid_equips[i].Número);
                    if (status !== null) {
                        if (status.status_equipamento_chamado === "ABERTO" || 
                            status.status_equipamento_chamado === "ENTREGA" ||
                            status.status_equipamento_chamado === "INSERVIVEL") {
                            ocorrencias.push({"Número":grid_equips[i].Número,"Status":status.status_equipamento_chamado,"ID":status.id_chamado,"Ticket":status.ticket_chamado})
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

        //console.log(erros)

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
        // descricao: {
        //     required: true,
        //     minlength: 10,
        //     normalizer: function(value) {
        //         return $.trim(value);
        //     }
        // },
        id_fila: {
            required: true,
        },
        resumo_solicitacao: {
            required: true,
        }
    },
    messages: {
        nome_solicitante: "Campo obrigatório!",
        nome_local: "Campo obrigatório!",
        telefone: {
            required: "Campo obrigatório!",
            digits: "Somente dígitos (0-9)!",
            minlength: "Mínimo 3 dígitos!"
        },
        // descricao: {
        //     required: "Campo obrigatório!",
        //     minlength: "Descrição insuficiente!",
        //     maxlength: "Tamanha máximo excedido!"
        // },

        resumo_solicitacao: {
            required: "Campo obrigatório!",
        }
    },
    
    submitHandler: async function(form) {
        var script_url = base_url + "chamado/importar_chamado";
        var dados = new FormData(form);
        dados.append('listaEquipamentos', JSON.stringify(g_equips));
        dados.append('num_ticket',g_num_ticket);
        // dados.append('email_triagem',g_email_triagem);
        // let response = await fetch(base_url + "triagem/descricao/" + g_id_triagem);
        // let desc = "";

        // if (response.ok) { 
        //     desc = await response.text();
        // } else {
        //     console.log("HTTP-Error: " + response.status);
        // }
        // var replaced = $("#descricao_triagem").html().replace(/'/g, "\\'" );
        // dados.append('textoTriagem', desc);
        dados.append('g_anexos', JSON.stringify($("#tblAnexos").jsGrid("option","data")));
        dados.append('id_ticket', g_id_ticket);
        $.ajax({

            url: script_url,
            type: 'POST',
            data: dados,
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
                if (confirmado == false) {
                    alert("Verifique a lista de equipamentos!")
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


$('#tblEquipsBr').on('click', 'tbody tr', function () {

    alert('pasiodkpsoadksopadkosa');
 
   // window.open(base_url + 'chamado/' + $("td:nth-child(3)").value());
  });


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


     $("#tblEquipsBr tbody tr td:nth-child(4)").on("click", function() {
 
        window.open(base_url + "chamado/" + $(this).text());
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


$('#modalEndereco').on('show.bs.modal', async function (e) {
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



