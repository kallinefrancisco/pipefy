<?php

require_once __DIR__."/class/Pipefy.php";
require_once __DIR__."/class/GSheet.php";

$pipefy = new Pipefy();
$gsheet = new GSheet();

//Define um data para utilizar de filtro na API do Pipefy
$dateFilter = date("Y-m-d", strtotime("-30 days"));
$dateFilter .= "T00:00:00-02:00";
$filter = ", filter: {field: \\\"updated_at\\\", operator: gt, value: \\\"".$dateFilter."\\\"}";

$spreadsheetId = '12hvvZlTBVr_eM5DvTPz_cU3_SGbREgLxLoyzhOLs2Hg';
$table = 'Dados Satisfação!A2';

//Puxa todos os cards dos pipefys informados
$array = $pipefy->multiple_pipes([
	'301465482',
	'302358471'
], $filter);

$values = [];

//Id de cada responsavel no Pipefy, serve para que possamos atribuir responsavel ao card
$responsaveis = [
	'Yana' => 301106175,
	'Rodrigo' => 301171094,
	'Julio' => 301216849,
	'Karol' => 301251944,
	'Letícia' => 301106749,
	'Joice' => 301106750,
	'Alysson' => 301197948,
	'Stephani' => 301132679,
	'Natacha' => 301113784,
	'Thalya' => 301263939,
	'Luana' => 301314885,
	'Manuela' => 301194324,
	'Ane' => 301894334,
	'Julianne' => 302027532,
	'Talita Veiga' => 302000842,
	'Nátally' => 302027533,
];

//Id de cada phase no Pipefy, serve para que possamos mover o card para a phase responsavel
$responsaveisPhase = [
	'Yana' => 314940537,
	'Rodrigo' => 314940544,
	'Julio' => 314940536,
	'Karol' => 314940535,
	'Letícia' => 309742214,
	'Joice' => 309742216,
	'Alysson' => 310883940,
	'Stephani' => 310883957,
	'Natacha' => 310883979,
	'Thalya' => 311345124,
	'Luana' => 311903490,
	'Manuela' => 312346306,
	'Ane' => 312922561,
	'Julianne' => 314940526,
	'Talita Veiga' => 314640907,
	'Nátally' => 314940534,
	'Anna' => 315029568
];

foreach ($array as $key => $value) {
	$dateTemp = explode("-",substr($value['node']['createdAt'],0,10));
	$date = $dateTemp[2]."/".$dateTemp[1]."/".$dateTemp[0];
	$temp = 
	[
		$value['node']['id'],
		$value['node']['current_phase']['name'], 
		$value['node']['title'], 
		$value['node']['fields']['Empresa'],
		array_key_exists('CNPJ de sua empresa:', $value['node']['fields'])? $value['node']['fields']['CNPJ de sua empresa:'] : '',
		$value['node']['fields']["E-mail da conta"],
		$value['node']['fields']["Seu nome:"],
		$value['node']['fields']["Quem foi o responsável por ministrar o treinamento?"],
		$value['node']['fields']["Qual etapa do treinamento você realizou?"],
		$value['node']['fields']["1. Como você avalia o treinamento realizado?"],
		$value['node']['fields']["1.1. Deixe aqui seu feedback sobre o treinamento:"],
		$date,
	];
	
	if (array_key_exists($value['node']['fields']["Quem foi o responsável por ministrar o treinamento?"], $responsaveis)) {
		$assignee = $responsaveis[$value['node']['fields']["Quem foi o responsável por ministrar o treinamento?"]];
		//Se a phase atual for diferente de quem ministrou o treinamento, mova de phase
		if ( $value['node']['current_phase']['name'] != $value['node']['fields']["Quem foi o responsável por ministrar o treinamento?"]) {
			$pipefy->moveCardToPhase($value['node']['id'],$responsaveisPhase[$value['node']['fields']["Quem foi o responsável por ministrar o treinamento?"]]);
		}

		//Se não tiver responsavel coloque quem ministrou o treinamento como responsavel
		if (count($value['node']['assignees']) == 0) {
			$pipefy->updateCardInput($value['node']['id'],$assignee);
		}
	}

	array_push($values, $temp);
}

$gsheet->clear_data($spreadsheetId,$table.":L");
$gsheet->insert($table, $values,$spreadsheetId);
