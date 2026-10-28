<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\StockHistoryModel;
use App\Models\ProductModel;

class StockHistory extends BaseController
{
    protected StockHistoryModel $stockHistoryModel;
    protected ProductModel $productModel;

    public function __construct()
    {
        $this->stockHistoryModel = new StockHistoryModel();
        $this->productModel = new ProductModel();
    }

    public function index()
    {
        $productId = $this->request->getGet('product_id');
        $selectedProduct = $productId ? $this->productModel->find((int)$productId) : null;

        $data = [
            'title'           => 'Riwayat Mutasi Stok Sampel',
            'controller'      => 'StockHistory',
            'history'         => $this->stockHistoryModel->getHistoryWithDetails(200, $productId ? (int)$productId : null),
            'selectedProduct' => $selectedProduct,
        ];

        return view('stock_history/index', $data);
    }
}
