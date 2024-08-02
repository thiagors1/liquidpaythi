<?php

namespace App\Services;

use GuzzleHttp\Exception\RequestException;

class LiquidBankService
{
    protected $client;
    
    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client([
            // Base URI is used with relative requests
            'base_uri' => 'https://www.liquidworks.com.br/liquidbank/',
            'timeout' => 140, // ifood = 60
            'connect_timeout' => 15,
            // dont thrown erros on status 400 per example
            'http_errors' => false
        ]);
    }

    public function authorize($credentials)
    {
        try {
            $response = $this->client->request('POST', 'authorize', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => json_encode($credentials)
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            // Log the error or handle it accordingly
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }
}
