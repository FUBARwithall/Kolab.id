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

    /**
     * DataTables Server-Side AJAX Endpoint
     */
    public function getDatatable()
    {
        $productId = $this->request->getPost('product_id');
        $draw   = (int) ($this->request->getPost('draw') ?? 1);
        $start  = (int) ($this->request->getPost('start') ?? 0);
        $length = (int) ($this->request->getPost('length') ?? 10);
        $search = (string) ($this->request->getPost('search')['value'] ?? '');

        $builder = $this->stockHistoryModel->builder()
            ->select('stock_history.*, products.name as product_name, products.sku as product_sku, variants.name as variant_name, users.name as user_name')
            ->join('products', 'products.id = stock_history.product_id', 'left')
            ->join('variants', 'variants.id = stock_history.variant_id', 'left')
            ->join('users', 'users.id = stock_history.created_by', 'left');

        if (!empty($productId)) {
            $builder->where('stock_history.product_id', (int)$productId);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('products.name', $search)
                ->orLike('products.sku', $search)
                ->orLike('stock_history.notes', $search)
                ->orLike('users.name', $search)
                ->groupEnd();
        }

        $recordsFiltered = (clone $builder)->countAllResults(false);
        $builder->orderBy('stock_history.id', 'DESC');
        if ($length > 0) {
            $builder->limit($length, $start);
        }

        $data = $builder->get()->getResultArray();
        $recordsTotal = $this->stockHistoryModel->countAllResults();

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }
}
