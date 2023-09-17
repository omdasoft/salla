<?php
namespace App\Services;

require_once __DIR__ . '/../../bootstrap.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ApiClient
{
    private $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.salla.dev/admin/v2/', // Replace with your API's base URL
        ]);
    }

    public function get($endpoint, $queryParams = [], $headers = [])
    {
        try {
            $response = $this->client->request('GET', $endpoint, [
                'query' => $queryParams,
                'headers' => $headers,
            ]);

            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            throw $e;
        }
    }

    public function post($endpoint, $data = [], $headers = [])
    {
        try {
            $response = $this->client->request('POST', $endpoint, [
                'json' => $data,
                'headers' => $headers,
            ]);

            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            throw $e;
        }
    }

    public function put($endpoint, $data = [], $headers = [])
    {
        try {
            $response = $this->client->request('PUT', $endpoint, [
                'json' => $data,
                'headers' => $headers,
            ]);

            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            throw $e;
        }
    }

    public function delete($endpoint, $headers = [])
    {
        try {
            $response = $this->client->request('DELETE', $endpoint, [
                'headers' => $headers,
            ]);

            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            throw $e;
        }
    }
}

// Usage example:
// $api = new ApiClient();
// try {
//     // GET request with custom headers
//     $getHeaders = ['Authorization' => 'Bearer your_access_token'];
//     $getResponse = $api->get('example-endpoint', ['param1' => 'value1'], $getHeaders);

//     // POST request with custom headers
//     $postHeaders = ['Authorization' => 'Bearer your_access_token', 'Content-Type' => 'application/json'];
//     $postData = ['key' => 'value'];
//     $postResponse = $api->post('example-endpoint', $postData, $postHeaders);

//     // PUT request with custom headers
//     $putHeaders = ['Authorization' => 'Bearer your_access_token', 'Content-Type' => 'application/json'];
//     $putData = ['key' => 'new_value'];
//     $putResponse = $api->put('example-endpoint', $putData, $putHeaders);

//     // DELETE request with custom headers
//     $deleteHeaders = ['Authorization' => 'Bearer your_access_token'];
//     $deleteResponse = $api->delete('example-endpoint', $deleteHeaders);

//     // Handle responses as needed
//     echo "GET Response: " . $getResponse . PHP_EOL;
//     echo "POST Response: " . $postResponse . PHP_EOL;
//     echo "PUT Response: " . $putResponse . PHP_EOL;
//     echo "DELETE Response: " . $deleteResponse . PHP_EOL;
// } catch (GuzzleHttp\Exception\RequestException $e) {
//     // Handle exceptions
//     echo "Error: " . $e->getMessage();
// }
