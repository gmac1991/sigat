var listaVerificada = false;
var timeout;
const url = 'https://sistemas.sorocaba.sp.gov.br/acesso_equipamento/api/patrimonio/'; //API web do SIM (patrimônio)
const patrimonio_regex = /[1-9]\d{5}\b/g
var toggle = 0;
// var g_requer_patri = null;
var fila_atual = null;
var p_equips = [, ];




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

$(function() {

    // PAINEL

    painel(g_fila_usuario); //incializa o painel na fila preferencial do usuario
    $('#slctFila').val(g_fila_usuario); //seleciona a fila preferencial do usuario

    // TRIAGEM
    triagem(); //incializa o painel de triagem

    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    const view = urlParams.get('v');

    if (view == 'triagem') {

        $('#triagem-tab').tab('show');
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


// --------------- PAINEL ---------------------------

var enc = false

var table_painel = null;

function painel(id_fila) {



    table_painel = $('#tblPainel').DataTable({ //  inicializacao do painel

        "rowClick": function(args) {
            
            //$linha = this.rowByItem(args.item)

            document.location.href = base_url;
            
        },
        
        "autoWidth": false,

        "columnDefs": [{
            "orderable": false,
            "targets": [7]


        }, {
            "width": "10%",
            "targets": 4,
            "render": $.fn.dataTable.render.moment('YYYY-MM-DD HH:mm:ss', 'DD/MM/YYYY H:mm:ss')
        }, ],

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

        "order": [],

        "processing": true,

        "drawCallback": function(settings) {



            var p_id_chamado = null;

            $('.PopoverPainel').each(function() {


                p_id_chamado = $(this).attr('data-chamado');

                $.ajax({

                    type: 'post',
                    url: base_url + 'json/texto_ultima_interacao',
                    data: {
                        id_chamado: p_id_chamado
                    },
                    dataType: 'json',
                    success: interacao => {

                        $(this).popover({
                            content: interacao.nome_usuario + interacao.texto_interacao,
                            trigger: 'focus',
                            placement: "left",
                            html: true
                        });

                    }
                })
            })
        }
    });
}


$('#tblPainel').on('click', 'tbody tr', function () {
    var row = table_painel.row($(this)).data();
    document.location.href = base_url + 'chamado/' + row[0];
  });


function mudaFila(p_id_fila) { //troca de fila no painel => destroi o painel e reconstroi no onChange do $('#slctFila')

    if (enc === true) {

        $('#tblPainel').DataTable().clear().ajax.url(base_url + 'chamado/listar_encerrados_painel/' + p_id_fila).load();

    } else {

        //$('#tblPainel').DataTable().clear().draw();
        
        $('#tblPainel').DataTable().ajax.url(base_url + 'chamado/listar_chamados_painel/' + p_id_fila).load();

    }

}

setInterval(function() { //atualiza o painel de chamados


    $('#tblPainel').DataTable().ajax.reload(null, false);


}, 30000);


function painelEncerrados(id_fila) {

    if (enc === false) {
        $('#slctFila').val(0);

        $('#btnChamados').html('<i class="fas fa-binoculars"></i> Chamados Abertos');

        $('#tituloPainel').append(' (encerrados)');
        $('#tblPainel').DataTable().ajax.url(base_url + 'chamado/listar_encerrados_painel/' + id_fila).load();
        enc = true;

    } else {
        $('#slctFila').val(0);

        $('#btnChamados').html('<i class="fas fa-binoculars"></i> Chamados Encerrados');
        $('#tituloPainel').html('Painel de chamados');
        $('#tblPainel').DataTable().ajax.url(base_url + 'chamado/listar_chamados_painel/' + id_fila).load();
        enc = false;
    }




}

table_triagem = null;

// ------------  PAINEL TRIAGEM

function triagem() {

    table_triagem = $('#tblTriagem').DataTable({ //  inicializacao do painel

        "autoWidth": false,

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

$('#tblTriagem').on('click', 'tbody tr', function () {
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

    

    if (num_equipamentos.length > 0) {

        $('#divEquipamentos').empty();

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

        } else if (p_id_fila_ant == 6) { // no caso da fila estar como 'Solicitacao de Equipamento'
            $('#divEquipamentos').prepend("<p>Para este tipo de interação, altere a fila para <b>Manutenção de Hardware</b></p>");
            if (!p_alt_fila) {
                $('#btnRegistrarInteracao').prop('disabled', 'true');
            }
        } else {

            if (tem_equipamentos == 0) {

                $('#divEquipamentos').prepend("<p>Não existem equipamentos disponíveis para isso!</p>");
                if (!p_alt_fila) {
                    $('#btnRegistrarInteracao').prop('disabled', 'true');

                }
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
            buscaEquipamentos(id_chamado, fila_ant, false, false, true);
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
        height: 200,
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




async function carregaChamado(p_id_chamado, sem_equipamentos) {

    //atualiza os dados do chamado

    document.title = "#" + p_id_chamado + " - Sigat";

    var p_id_responsavel = null;

    $("#tblEquipamentosChamado").jsGrid({

        height: "auto",
        width: "100%",
        inserting: false,
        editing: false,
        autoload: true,
        invalidMessage: "Dados inválidos inseridos!",
        loadMessage: "Aguarde...",
        deleteConfirm: "Tem certeza?",
        noDataContent: "Vazio",
    
        onInit: function(args) {
            tblEquipsChamado = args.grid;
        },
    
        fields: [
            {
                name: "num_equipamento",
                title: "ID",
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
                title: "Tag",
                validate: [
                    { validator: "pattern", param: /^[a-zA-Z0-9]+$/, message: "Atenção!\nCaracteres permitidos:\nA-Z, a-z e 0-1" },
                ],
            },
            {
                name: "status_equipamento_chamado",
                title: "Status",
                width: 30,
            }, 
            {
                type:"control",
                editButton: false,
                deleteButton: false,
            }
        ],
    
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
    
                var aberto = null;
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
             
                if(parseInt(res.id_chamado) !== g_id_chamado) {
                    alert("O item " + num_equip + " já está em atendimento!\nChamado: " + res.id_chamado + "\n" + res.ticket_chamado);
                    d.reject();
                    return d.promise();
                }
    
                else {
                    return $.ajax({
                            url: base_url + "edit_equip_chamado",
                            dataType: "json",
                            method: "post",
                            data: item,
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
    
                console.log(res);
             
                if(typeof res !== 'undefined'){
                    alert("O item " + num_equip + " já está em atendimento!\nChamado: " + res.id_chamado + "\n" + res.ticket_chamado);
                    d.reject();
                    return d.promise();
                }
                else {
                    return $.ajax({
                        url: base_url + "add_equip_chamado",
                        dataType: "json",
                        method: "post",
                        data: {item,g_id_chamado},
                    });
    
                }
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
                        data: item,
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


    $('#botoesAtendimento #btnModalRegistroEntrega').remove();
    $('#botoesAtendimento #baixarTermoEntrega').remove();
    $('#botoesAtendimento #baixarTermoResp').remove();
    $('#botoesAtendimento #baixarLaudoIns').remove();
    $('#botoesAtendimento #btnModalRegistro').remove();
    $('#botoesAtendimento #btnEncerrarChamado').remove();
    $('#botoesAtendimento #btnModalEquipamentos').remove();

    $('#botoesChamado hr').hide();

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
            $('div[name=descricao]').html(data.descricao_chamado);
            $("#sipLink").attr("href","sip:"+data.telefone_chamado);

            if (data.id_responsavel == null) {
                $('select[name=id_responsavel]').empty();
            } else {
                p_id_responsavel = data.id_responsavel;
                $('select[name=id_responsavel]').html('<option value="' + data.id_responsavel + '">' + data.nome_responsavel + '</option>');
            }

            fila_atual = data.id_fila; //variavel global fila_atual
            var entrega = data.entrega_chamado;


            var botoes = "<button type=\"button\" id=\"btnModalRegistro\" class=\"btn btn-primary\"" +
                            " data-toggle=\"modal\" data-target=\"#modalRegistro\" data-chamado=\"" + data.id_chamado +
                            "\"><i class=\"fas fa-asterisk\"></i> Nova Ação</button> ";

            data.status_equipamentos.forEach(function(item) {

                

                if ((item.status_equipamento_chamado == 'ABERTO' || item.status_equipamento_chamado == 'ESPERA' ||
                    item.status_equipamento_chamado == 'FALHA') &&
                (data.id_responsavel == g_id_usuario)) {

                    
                    
                    
                    


                    $('#botoesAtendimento').html(botoes);
                }

                if ((item.status_equipamento_chamado == 'ENTREGA' || entrega == 1) && data.status_chamado == 'ABERTO' ) {

                    botoes = botoes +  "<button type=\"button\" id=\"btnModalRegistroEntrega\" class=\"btn btn-success\" data-toggle=\"modal\" data-chamado=\"" +
                                        data.id_chamado + "\" data-target=\"#modalRegistroEntrega\"><i class=\"fas fa-file-signature\"></i> Registrar entrega</button> " +
                                        "<a href=\"" + base_url + "chamado/gerar_termo/" +
                                        data.id_chamado + "\" id=\"baixarTermoEntrega\" role=\"button\" class=\"btn btn-info\">" +
                                        "<i class=\"fas fa-file-download\"></i> Termo de Entrega</a> " +
                                        "<a href=\"" + base_url + "chamado/gerar_termo_resp/" +
                                        + data.id_chamado + "\" id=\"baixarTermoResp\" role=\"button\" class=\"btn btn-info\">" +
                                        "<i class=\"fas fa-file-download\"></i> Termo de Responsabilidade</a>"

                    $('#botoesAtendimento').html(botoes);
                
                }
            })


            // -------------------- PERMISSOES ----------------------------


            if (data.id_responsavel == g_id_usuario) {

               

                tblEquipsChamado.option("editing",true);
                tblEquipsChamado.option("inserting",true);
                tblEquipsChamado.fieldOption(4,"editButton",true);
                tblEquipsChamado.fieldOption(4,"deleteButton",true);
                tblEquipsChamado.fieldOption(0,"readOnly",false);
                tblEquipsChamado.fieldOption(1,"readOnly",false);
               
                if (data.id_fila == 3) {

                    tblEquipsChamado.fieldOption(2,"editing",true);
                
                }

                if (g_auto_usuario > 3) {

                    tblEquipsChamado.fieldOption(0,"readOnly",false);
                    tblEquipsChamado.fieldOption(1,"readOnly",false);
                    tblEquipsChamado.fieldOption(4,"deleteButton",true);
                }
            
                


    

            }
            
            
                    
            
            if (g_auto_usuario > 3 && data.status_chamado == 'FECHADO') { //somente ADM+ encerra o chamado

                if (!$('#btnEncerrarChamado').length) {

                    $('#botoesAtendimento').prepend(
                        '<button id="btnEncerrarChamado" onclick="encerrarChamado()" class="btn btn-success"><i class=\"far fa-check-circle\"></i> Encerrar chamado</button>');
                };
                //$('#btnReabrirChamado').show();

            }

            if (data.status_chamado != 'ABERTO') { //se o chamado não estiver ABERTO, remover o botao Registrar Atendimento e Editar Chamado

                $('#btnEditarChamado').hide();
                $('#btnDesbloquearChamado').hide();
                $('#botoesChamado hr').hide();

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
}








// ---------------- INTERACOES --------------------

function removeInteracao(p_id_interacao, p_id_chamado) {

    var bloqueado = false;


    $.ajax({

        url: base_url + 'interacao/remover_interacao',
        type: 'POST',
        data: {
            id_interacao: p_id_interacao
        },

        beforeSend: function() {

            $('#btnDesfazer').prop('disabled', 'true');



            $.ajax({
                url: base_url + 'json/interacao',
                type: 'GET',
                dataType: 'text',
                async: false, //necessario para evitar remoção nao autorizada
                data: {
                    id_chamado: g_id_chamado,
                    id_usuario: g_id_usuario,
                    id_interacao: p_id_interacao
                },
                success: function(data) {

                    if (data === '1') {
                        bloqueado = true;

                    }
                }
            });

            if (bloqueado && g_auto_usuario <= 3) {

                $('#btnDesfazer').removeAttr('disabled');
                alert('Operação não permitida!');
                return false;
            }



        },

        success: function() {

            atualizaInteracoes(p_id_chamado);
            //$('#btnDesfazer').removeAttr('disabled');
            carregaChamado(p_id_chamado);
            tblEquipsChamado.loadData();



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
            //console.log(p_equips)
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

        $('#divEntrega').show();
        $('#divFalhaEntrega').hide();
        $('#btnRegistrarEntrega').show();
        $('#btnRegistrarEntrega span').html(' Registrar entrega');
    } else {

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



$('#frmRegistroEntrega').on('submit', function(e) { //submit do registro de entrega

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

            beforeSend: function() {

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

                $('#btnRegistrarEntrega').prop("disabled", "true");

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

            beforeSend: function() {

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

                $('#btnRegistrarEntrega').prop("disabled", "true");

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
                success: function(msg) {
                    if (msg != 0) { // se for autorizado
                        console.log('ok');
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
        url: base_url + 'json/atualiza_responsavel',
        data: {
            id_chamado: g_id_chamado,
            id_usuario: g_id_usuario,
            tipo: 'b'
        },
        beforeSend: function() {

            $('#btnBloquearChamado').prop('disabled', 'true');

            $.ajax({
                type: "GET",
                async: false, //
                url: base_url + 'json/chamado',
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
            if (bloqueado) {
                return;
            }
        },

        success: function() {
            $('#btnBloquearChamado').removeAttr('disabled');
            carregaChamado(g_id_chamado, true);
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
            tipo: 'd'
        },
        beforeSend: function() {
            $('#btnDesbloquearChamado').prop('disabled', 'true');
        },
        success: function(msg) {

            if (msg == 0) {
                $('#btnDesbloquearChamado').removeAttr('disabled');
                $('#btnDesbloquearChamado').hide();
                carregaChamado(g_id_chamado, true);
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


}, 15000);

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
        {
            type: "control",
            deleteButton: true,
            editButton: false,
        }
    ],
    rowClick: function(args) {
        window.open(base_url + 'anexo_otrs/' + args.item.id_anexo_otrs,'_blank ');
    }
});

async function carregaTriagem(p_id_triagem) {


    $('div[name=descricao_triagem]').html('<div class="d-flex align-items-center"><strong>Carregando..</strong><div class="spinner-border ml-auto" role="status" aria-hidden="true"></div></div>');

    //traz os dados do chamado MIGRADO (OTRS)

    document.title = "Triagem #" + p_id_triagem + " - Sigat";

    var p_id_responsavel = null;
    var anexos = [];
    await $.ajax({
        url: base_url + 'json/triagem',
        dataType: 'json',
        async: true,
        data: {
            id_triagem: p_id_triagem
        },
        success: function(data) {

            //preencher os campos conforme o json

            $('input[name=nome_solicitante]').val(data.triagem.nome_solicitante_triagem);
            $('input[name=id_triagem]').val(p_id_triagem);
            $('#descricao_triagem').html(UTF8.decode(data.triagem.descricao_triagem));

            if (data.anexos_otrs.length > 0) {

                data.anexos_otrs.forEach(function(item){
                    anexos.push({id_anexo_otrs:item.id_anexo_otrs,nome_arquivo_otrs:item.nome_arquivo_otrs})

                })

                
               
                
            }

            
        }
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

async function verificaStatusEquip(p_e) {
    out = null;
    await $.ajax({
        method: "post",
        url: base_url + "json/status_equipamento",
        data: { e_status: p_e}
      })
        .done(function( res ) {
            if (res !== false) {
                out = res;
            }
        });
    return out;
}

async function verificaDescEquip(p_e) {
    out = null;
    await $.ajax({
        method: "post",
        url: base_url + "desc_equipamento/" + p_e,
      })
        .done(function( res ) {
            if (res !== null) {
                out = res;
            }
        });
    return out;
}

async function verificaAutoEquip() {
    var nums_equip = [];
    var out = [];
    var ocorrencias = [];
    var textoTriagem = null;
    $("#btnValidaEquip").prop("disabled","true");
    $("#pbEquips").css("width","0%");
    if (($("#chkSoSelecaoTriagem").is(':checked'))) {
        textoTriagem = window.getSelection().toString();
    } else {
        textoTriagem = $('#descricao_triagem').html();
    }
    nums_equip = textoTriagem.match(patrimonio_regex);
    if (nums_equip.length > 0) {
        nums_equip = uniq_fast(nums_equip);
        
        percentage = (100*1)/nums_equip.length;
        total_percentage = 0;
        for (i=0;i<nums_equip.length;i++) {

            var status = await verificaStatusEquip(nums_equip[i]);

            if (status.status_equipamento_chamado !== 'ATENDIDO') {
                ocorrencias.push({"Número":nums_equip[i],"Status":status.status_equipamento_chamado,"ID":status.id_chamado,"Ticket":status.ticket_chamado})
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

    confirmado = false;

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
                    if (status !== "" && status.status_equipamento_chamado !== 'ATENDIDO') {
                        ocorrencias.push({"Número":grid_equips[i].Número,"Status":status.status_equipamento_chamado,"ID":status.id_chamado,"Ticket":status.ticket_chamado})
                    }
                }

                // var res = await verificaDescEquip(grid_equips[i].Número);
                // if (res.descricao !== null)
                //     grid_equips[i].Descrição = res.descricao;

                if (grid_equips[i].Descrição === null) {
                    erros.push("O item "+grid_equips[i].Número+" está sem descrição!\n");
                }
            }
            
            total_percentage = total_percentage + percentage;
            $("#pbEquips").css("width",total_percentage+"%"); 
        }

        if (erros.length == 0 && g_equips.length == 0 ) {
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
        alert("A lista está vazia!");
        $(this).removeAttr("disabled");
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
            window.open(base_url + '/chamado/' + args.item.ID,'_blank ');
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

    $(this).prop("disabled","true");
    if ($("#radLoteFaixa").is(':checked')) {

        var inicio = Number($("#txtInicioFaixaLote").val());
        var fim = Number($("#txtFimFaixaLote").val());

        if((fim - inicio) < 1 || isNaN(fim - inicio))  {

            alert("Faixa inválida");
            $(this).removeAttr("disabled");
        }
        else {

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
            
        }
        
    
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
                    id_triagem: g_id_chamado,
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


$('#frmImportarChamado').on('submit',

    function(e) {

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
        descricao: {
            required: true,
            // maxlength: 2000,
            minlength: 10,
            normalizer: function(value) {
                return $.trim(value);
            }
        },
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
        descricao: {
            required: "Campo obrigatório!",
            minlength: "Descrição insuficiente!",
            maxlength: "Tamanha máximo excedido!"
        },

        resumo_solicitacao: {
            required: "Campo obrigatório!",
        }
    },
    submitHandler: function(form) {
        var script_url = base_url + "chamado/importar_chamado";
        var dados = new FormData(form);
        dados.append('listaEquipamentos', JSON.stringify(g_equips));
        dados.append('ticket_triagem',g_ticket_triagem);
        dados.append('email_triagem',g_email_triagem);
        var replaced = $("#descricao_triagem").html().replace(/'/g, "\\'" );
        dados.append('textoTriagem', replaced);
        dados.append('g_anexos', JSON.stringify($("#tblAnexos").jsGrid("option","data")));
        dados.append('id_triagem', g_id_triagem);
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
                    $("#msg").append(msg);
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