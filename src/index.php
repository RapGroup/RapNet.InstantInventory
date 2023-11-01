<?php 

namespace Rapnet\RapnetInstantInventory;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\HandlerStack;
use GuzzleRetry\GuzzleRetryMiddleware;

require('env.php');

class Index {

    private $config;

    public function __construct($clientId = null, $clientSecret = null) {
        $this->config = 
            [              
              'base_path' => $_ENV['RAPNET_GATEWAY_BASE_URL'],
              'authorization_url' => $_ENV['RAPNET_AUTH_URL'],
              'machine_auth_url' => $_ENV['RAPNET_MACHINE_TO_MACHINE_AUTH_URL'],
              'client_id' => $clientId,
              'client_secret' => $clientSecret,
              'redirect_uri' => null,              
              'token_callback' => null,
              'instant_inventory_url' => $_ENV['RAPNET_GATEWAY_BASE_URL'].'/instant-inventory/api',
              'jwt' => null,
              'scope' => 'manageListings priceListWeekly instantInventory',
              'audience' => $_ENV['RAPNET_GATEWAY_AUDIENCE']
            ];
    }

    public function createAuthorizationCodeToken()
    {
        try {
            $stack = HandlerStack::create();
            $stack->push(GuzzleRetryMiddleware::factory([
                'max_retry_attempts' => 2,
                'retry_on_status' => [429, 503, 500]
            ]));


            $client = new GuzzleClient(['verify' => false, 'handler' => $stack]);
            $url = "{$this->config['machine_auth_url']}/api/get";

            $response = $client->request('GET',  $url, [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'client_id' => $this->config['client_id'],
                        'client_secret' => $this->config['client_secret']
                    ],
                ]
            );
            return json_decode($response->getBody());
        } catch (RequestException $e) {
            return $e->getMessage();
        }        
    }

    /*
        Filter Model:

        {
          "sortBy": "string",
          "currency": "string",
          "priceTotalTo": 0,
          "priceTotalFrom": 0,
          "labs": [
            "string"
          ],
          "symmetryTo": "string",
          "symmetryFrom": "string",
          "polishTo": "string",
          "sortDirection": "string",
          "polishFrom": "string",
          "clarityFrom": "string",
          "fluorescenceIntensities": [
            "string"
          ],
          "cutTo": "string",
          "cutFrom": "string",
          "colors": {
            "searchType": "string",
            "colorFrom": "string",
            "colorTo": "string",
            "fancyColors": [
              "string"
            ]
          },
          "sizeTo": 0,
          "sizeFrom": 0,
          "shapes": [
            "string"
          ],
          "clarityTo": "string",
          "pagination": {
            "pageNumber": 0,
            "pageSize": 0,
            "totalRecordsFound": 0
          }
        }

    */

    
    public function getDiamondInfo($token, $feedId, $filters = null)
    {
        try {
            $stack = HandlerStack::create();
            $stack->push(GuzzleRetryMiddleware::factory([
                'max_retry_attempts' => 2,
                'retry_on_status' => [429, 503, 500]
            ]));

            $client = new GuzzleClient(['verify' => false, 'handler' => $stack]);
            $url = "{$this->config['instant_inventory_url']}/Feeds/{$feedId}/DiamondsListings/v2";

            $response = $client->request('POST', $url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'authorization' => "Bearer {$token}"
                ],
                'json' => $filters
            ]);

            return json_decode($response->getBody());
        } catch (RequestException $e) {
            return $e->getMessage();
        }        
    }

    /*
        Filter model:
        {
          "jewelryTypes": [
            "string"
          ],
          "jewelryMetalTypes": [
            "string"
          ],
          "jewelryGemTypes": [
            "string"
          ],
          "jewelryGemShapes": [
            "string"
          ],
          "jewelryCondition": [
            "string"
          ],
          "priceTotalFrom": 0,
          "priceTotalTo": 0,
          "currency": "string",
          "pagination": {
            "recordsReturned": 0,
            "totalRecordsFound": 0,
            "currentPage": 0,
            "recordsPerPage": 0
          },
          "sorting": "string"
        }
    */

    public function getJewelryInfo($token, $feedId, $filters = null)
    {
        try {
            $stack = HandlerStack::create();
            $stack->push(GuzzleRetryMiddleware::factory([
                'max_retry_attempts' => 2,
                'retry_on_status' => [429, 503, 500]
            ]));
            
            $client = new GuzzleClient(['verify' => false, 'handler' => $stack]);
            $url = "{$this->config['instant_inventory_url']}/Feeds/{$feedId}/JewelryListingsV2";

            $response = $client->request('POST', $url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'authorization' => "Bearer {$token}"
                ],
                'json' => $filters
            ]);

            return json_decode($response->getBody());
        } catch (RequestException $e) {
            return $e->getMessage();
        }        
    }
}