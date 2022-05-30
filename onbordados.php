<?php
date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__."/class/Pipefy.php";
require_once __DIR__."/class/GSheet.php";

$pipefy = new Pipefy();
$gsheet = new GSheet();

//Define um data para utilizar de filtro na API do Pipefy
$dateFilter = date("Y-m-d", strtotime("-80 days"));
$dateFilter .= "T00:00:00-02:00";
$filter = ", filter: {field: \\\"updated_at\\\", operator: gt, value: \\\"".$dateFilter."\\\"}";

$spreadsheetId = '12hvvZlTBVr_eM5DvTPz_cU3_SGbREgLxLoyzhOLs2Hg';
$table = 'Dados % Onbordados!A2';
$gidId = 950062084;

//Puxa todos os cards dos pipefys informados
$array = $pipefy->multiple_pipes([
	'301429408',
	'301453596'
], $filter);

$responsaveis = $pipefy->responsaveis_id();

$dataControle2 = strtotime(date("m")."/01/".date("Y"));
$dataControle = strtotime("- 2 month",$dataControle2);

$values = [];

foreach ($array as $key => $value) {
	$contract = array_key_exists("Data da Contratação:", $value['node']['fields'])?$value['node']['fields']['Data da Contratação:']:$value['node']['fields']['Data da Contratação'];
	$date = explode("/", $contract);
	$timestamp = strtotime($date[1]."/".$date[0]."/".$date[2]);
	if ($timestamp >= $dataControle) {
		$firstTimeFollowUp = '';
		
		//Verifica a primeira vez que entrou em follow up
		foreach ($value['node']['phases_history'] as $x => $y) {
			if ($y['phase']['name'] == 'Follow up - Onboardados') {
				if ($y['firstTimeIn'] != null) {
					$dateTemp = explode("T",$y['firstTimeIn']);
					$dateTemp2 = explode("-",$dateTemp[0]);
					$firstTimeFollowUp = $dateTemp2[2]."/".$dateTemp2[1]."/".$dateTemp2[0];
				}
			}
		}

		$assigned = "";

		if (count($value['node']['assignees']) > 0) {
			if (array_key_exists($value['node']['assignees'][0]['id'], $responsaveis)) {
				$assigned = $responsaveis[$value['node']['assignees'][0]['id']];
			}
		}

		$temp = 
		[
			$value['node']['current_phase']['name'], 
			$value['node']['fields']['Nome da Empresa:'],
			array_key_exists('CNPJ',$value['node']['fields'])?$value['node']['fields']['CNPJ']:$value['node']['fields']['CNPJ:'],
			$contract,
			array_key_exists('Plano Contratado:',$value['node']['fields'])?$value['node']['fields']['Plano Contratado:']:"",
			$assigned,
			$firstTimeFollowUp,
			array_key_exists('Conclusão de Implantação',$value['node']['fields'])?$value['node']['fields']['Conclusão de Implantação']:"",
		];
		array_push($values, $temp);
	}
}

$gsheet->clear_data($spreadsheetId,$table.":H");
$gsheet->insert($table, $values,$spreadsheetId);
