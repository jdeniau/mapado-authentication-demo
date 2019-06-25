<?php

require __DIR__.'/vendor/autoload.php';

use Mapado\Component\TicketingModel\Model\Ticketing;
use Mapado\LeagueOAuth2Provider\Provider\MapadoOAuth2Provider;
use Mapado\RestClientSdk\Mapping;
use Mapado\RestClientSdk\Mapping\Driver\AnnotationDriver;
use Mapado\RestClientSdk\RestClient;
use Mapado\RestClientSdk\SdkClient;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

// create client authentication
$provider = new MapadoOAuth2Provider([
    'clientId'          => $_ENV['CLIENT_ID'],
    'clientSecret'      => $_ENV['CLIENT_SECRET'],
]);

// get access token
$accessToken = $provider->getAccessToken('client_credentials', [
    'scope' => 'ticketing:events:read',
]);

// dump access token data
dump('access token is "' . $accessToken->getToken() . '"');
dump('The full token informations are:');
dump($accessToken);


// create custom guzzle client
$guzzleClient = new GuzzleHttp\Client([
    'headers' => [
        'Authorization' => 'Bearer ' . $accessToken->getToken(),
    ],
]);

// create rest client
$restClient = new RestClient($guzzleClient, 'https://ticketing.mapado.net');

$annotationDriver = new AnnotationDriver(__DIR__ . '/cache/', true);

$mapping = new Mapping('/v1'); // /v2 is the prefix of your routes
$mapping->setMapping($annotationDriver->loadDirectory(__DIR__ . '/entities/'));

$sdkClient = new SdkClient($restClient, $mapping);

// call ticketing repository
$repository = $sdkClient->getRepository(Ticketing::class);

// by id
$piscineAGlacon = $repository->find(601, ['fields' => '@id,title,slug']);
dump($piscineAGlacon);

// by contract
$mapadoLandTicketingList = $repository->findBy(
    [
        'contract' => '/v1/contracts/237',
        'itemsPerPage' => 3,
        'fields' => '@id,title,slug'
    ]
);

dump($mapadoLandTicketingList);
