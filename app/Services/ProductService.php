<?php
namespace App\Services;
require_once __DIR__ . '/../../bootstrap.php';
use App\Services\ApiClient;

class ProductService
{
    protected $products;
    private $apiClient;
    private $token;
    private $headers;
    public function __construct()
    {
        $this->apiClient = new ApiClient();
        $this->token = $_SESSION['token'];
        $this->headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->token
        ];
    }

    public function products()
    {
        try {
            $products = $this->apiClient->get('products', [] ,$this->headers);
            return $products;
        } catch(\GuzzleHttp\Exception\RequestException $e) {
             echo "Error: " . $e->getMessage();
        }
       
    }

    public function delete(int $id)
    {
        try {
            $res = $this->apiClient->delete('products/'.$id, $this->headers);
            return $res;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return $e;
        }
    }
}
