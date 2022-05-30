<?php

class Pipefy
{

	private $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJ1c2VyIjp7ImlkIjozMDEyNjM5MzgsImVtYWlsIjoia2FsbGluZS5mcmFuY2lzY29AcG9udG9tYWlzLmNvbS5iciIsImFwcGxpY2F0aW9uIjozMDAxMDMxMDZ9fQ.qC7Atgk4-3ws8vD8nvWPLbtTaY5Eme7BQr_2sru_Jue7TMMyXGMv_xUjjeXDqwAnB05BxtZmxrS-5hqyCjMQuQ';
	private $curl;

	public function __construct()
	{
		$this->curl = curl_init();

		curl_setopt_array($this->curl, array(
			CURLOPT_URL => "https://app.pipefy.com/graphql",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => false,
			CURLOPT_POST => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_HTTPHEADER => array(
				"Authorization: Bearer ".$this->token,
				"Content-Type: application/json",
			),
		));
	}

	//Executa uma query na API do Pipefy
	private function query($query):string
	{
		curl_setopt_array($this->curl, array(CURLOPT_POSTFIELDS => $query));
		$response = curl_exec($this->curl);
		return $response;
	}

	//Transforma um array de index em associativo
	private function index_name($nodes):array
	{
		foreach ($nodes as $key => $value) {
			$temp = $value['node']['fields'];
			unset($nodes[$key]['node']['fields']);
			foreach ($temp as $y => $x) {
				$nodes[$key]['node']['fields'][$x['name']] = $x['value'];
			}
		}
		return $nodes;
	}
	
	//Executa diversas vezes a função all_cards
	public function multiple_pipes($pipes_id, $filter = ""):array
	{
		foreach ($pipes_id as $key => $value) {
			$arr[] = $this->all_cards($value, $filter);
		}

		$pipes = array_merge([], ...$arr);

		return $pipes;
	}
	
	//Executa a query de todos os cards de um pipe
	public function all_cards($pipe_id, $filter = "")
	{
		$mid = '';
		$start = "{\"query\": \"{ allCards(pipeId: {$pipe_id} {$filter}";
		$end = ") { edges { node { id title assignees { id } current_phase { name } phases_history { phase { name } firstTimeIn lastTimeOut } createdAt fields { name value } labels { name } } } pageInfo { endCursor startCursor hasNextPage} } }\"}";

		$response = $this->query($start.$mid.$end);
		$nodes = [];

		do{
			$obj = json_decode($response, true);
			$controle = $obj['data']['allCards']['pageInfo']['hasNextPage'];
			$mid = ", after: \\\"".$obj['data']['allCards']['pageInfo']['endCursor']."\\\"";
			$response = $this->query($start.$mid.$end);
			foreach ($obj['data']['allCards']['edges'] as $value) {
				array_push($nodes, $value);
			}
		}while($controle);

		return $this->index_name($nodes);
	}

	//Move um card de phase
	public function moveCardToPhase($card_id, $phase_id)
	{
		$query = "{\"query\":\"mutation { moveCardToPhase(input: {card_id: ".$card_id.", destination_phase_id: ".$phase_id."}) { clientMutationId }}\"}";
		return $this->query($query);
	}

	//Altera o responsavel do card
	public function updateCardInput($card_id, $assignee_ids)
	{
		$query = "{\"query\":\"mutation{updateCard(input:{id:".$card_id.", assignee_ids: [".$assignee_ids."]}) { clientMutationId card{ pipe { id } assignees { id } } } }\"}";
		return $this->query($query);
	}

	//retornar o id dos responsaveis
	public function responsaveis_id(){
		$responsaveis = [
			301314885 => 'Luana Bornancin',
			301171094 => 'Rodrigo Cruz',
			301894334 => 'Ane',
			301327264 => 'Bruna Jesus',
			302005337 => 'Cesar Scoth',
			301169270 => 'Financeiro',
			415387 => 'Flavia Andrade',
			301033264 => 'Fran',
			301106750 => 'Joice',
			301263938 => 'Kalline',
			301251944 => 'Karolina Spindola',
			301106749 => 'Leticia',
			301194324 => 'Manuela',
			301230244 => 'Marketing',
			301113784 => 'Natacha Karolyne Adão',
			301849915 => 'Rafaella Machado',
			301132679 => 'Stephani Arrais',
			302000842 => 'Talita Veiga',
			301451441 => 'Tamara Fiuza',
			301263939 => 'Thalya Cordova',
			301894333 => 'Thiago Henrique Buhrer da Rocha',
			301157662 => 'Tiago França Fernandes',
			301106175 => 'Yana Isis Rosales',
			301197948 => 'Alysson Santana Maingue',
			301216849 => 'Júlio Matheus Garcia Mendes de Camargo',
			302027532 => 'Julianne',
			302027533 => 'Nátally',
		];

		return $responsaveis;
	}
}

