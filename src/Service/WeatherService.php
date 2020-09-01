<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;

class WeatherService
{
    private $client;
    private $apiKey;

    public function __construct($apiKey)
    {
        $this->client = HttpClient::create();
        $this->apiKey = $apiKey;
    }


    public function getWeather($ville)
    {
        try {
            $response = $this->client->request(
                'GET', 
                'https://api.openweathermap.org/data/2.5/weather?q=' . $ville . '&units=metric&lang=fr&exclude=hourly,daily&appid=' . $this->apiKey
            );
            $content = $response->getContent();
            return json_decode($content, true);
        } catch (\Exception $error) {
            $statusCode = 0;
            if (method_exists($error, 'getMessage')) {
                $statusCode = $error->getCode();
                echo 'Erreur '.$statusCode.' <br>';
            }
            if ($statusCode === 0) {
                return "Une erreur est survenue";
            }
            if(401 === $statusCode) {
                return "Vérifié votre Clé API";
            }
            if(404 === $statusCode) {
                return "Cette ville n'existe pas dans la base de donnée Openweathermap.org.
                Vérifiez que la ville demandée est bien orthographiée.
                Les majuscules et les accents ne sont pas prises en comptes.
                (ex: Genève = geneve)
                Pas d'abreviations 
                (ex: pour St Étienne il faut écrire saint-etienne)
                Pour une ville composée de plusieurs mots,ajoutez un tiret entre chaque mot.
                (ex: pour Saint Malo il faut écrire Saint-Malo)
                Pour les villes hors France, veuillez écrire la ville en anglais et pas de tiret.
                (ex: saint petersburg, san diego) ";
            }
            if(429 === $statusCode) {
                return "Votre Clé API est restreinte à 60 demandes par minutes, veuillez consulter la page de tarification directement sur Openweathermap.org";
            }
        }
    }

    public function getDefaultVille()
    {
            $response = $this->client->request('GET', 
            'https://api.openweathermap.org/data/2.5/weather?q=toulouse&units=metric&lang=fr&appid=' . $this->apiKey
            );
            $content = $response->getContent();
            return json_decode($content, true);
    }
}
