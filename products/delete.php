<?php
require_once __DIR__.'/../bootstrap.php';
use App\Services\ProductService;

if (isset($_POST['submit'])) {
    try {
        $productService = new ProductService();
        $res = $productService->delete($_POST['id']);
        $res = json_decode($res);
        if($res->data->code == 202) {
            $message = 'Product deleted successfully';
            header('Location: ../../products.php?message='.$message);
            exit();
        }
    } catch (\Exception $e) {
        $message = $e->getMessage();
        header('Location: ../../products.php?message='.$message);
        exit();
    }
}