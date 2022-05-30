<?php

class GSheet
{

    public $googleAccountKeyFilePath = __DIR__ . '/../credentials.json';
    public $client;
    public $service;

    public function __construct(){
        require_once __DIR__ . '/../vendor/autoload.php';
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $this->googleAccountKeyFilePath);
        
        $this->client = new Google_Client();
        $this->client->useApplicationDefaultCredentials();
        $this->client->addScope('https://www.googleapis.com/auth/spreadsheets');
        $this->service = new Google_Service_Sheets($this->client);

    }

	//Insere os valores em uma planilha que tenha sido compartilhada com o e-mail integration@apipipefy.iam.gserviceaccount.com
    public function insert($range,$values,$spreadsheetId){
        $ValueRange = new Google_Service_Sheets_ValueRange();
        $options = ['valueInputOption' => 'USER_ENTERED'];

        $ValueRange->setValues($values);
        $this->service->spreadsheets_values->update($spreadsheetId, $range, $ValueRange, $options);
    }

	//Limpa um determinado range da planilha
    public function clear_data($spreadsheetId, $range){
        $requestBody = new Google_Service_Sheets_ClearValuesRequest();
        $this->service->spreadsheets_values->clear($spreadsheetId, $range, $requestBody);
    }
}
