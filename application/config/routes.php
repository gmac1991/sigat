<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'acesso';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
$route['erro'] = 'acesso/index/erro';
$route['painel'] = 'acesso/painel/';
$route['chamado/(:num)'] = 'chamado/index/ver_chamado/$1';
$route['chamado/registrar_interacao'] = 'interacao/registrar_interacao';
$route['chamado/gerar_termo/(:num)'] = 'interacao/gerar_termo/$1';
$route['chamado/gerar_termo_resp/(:num)'] = 'interacao/gerar_termo_resp/$1';
$route['chamado/gerar_laudo/(:num)'] = 'interacao/gerar_laudo/$1';
$route['chamado/listar_chamados_painel/(:num)'] = 'chamado/listar_chamados_painel/$1';
$route['chamado/listar_encerrados_painel/(:num)'] = 'chamado/listar_encerrados_painel/$1';
$route['chamado/historico/(:num)'] = 'json/carrega_historico/$1';
$route['triagem/listar_triagem'] = 'triagem/listar_triagem';
$route['triagem/(:num)'] = 'triagem/index/$1';
$route['anexo_otrs/(:num)'] = 'json/anexo_otrs/$1';
$route['listar_equips_chamado/(:num)'] = 'json/listar_equipamentos_chamado/$1';
$route['edit_equip_chamado'] = 'json/atualizar_equipamento_chamado';
$route['add_equip_chamado'] = 'json/inserir_equipamento_chamado';
$route['del_equip_chamado'] = 'json/remover_equipamento_chamado';
$route['desc_equipamento/(:any)'] = 'json/desc_equipamento/$1';
$route['anexos_chamado/(:num)'] = 'json/anexos_chamado/$1';
$route['endereco_local/(:num)'] = 'json/endereco_local/$1';
$route['listar_servicos_chamado/(:num)'] = 'json/listar_servicos_chamado/$1';

