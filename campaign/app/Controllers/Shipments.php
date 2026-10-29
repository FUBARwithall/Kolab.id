<?php

namespace App\Controllers;

use App\Models\ShipmentModel;
use App\Models\CampaignModel;
use App\Models\ProductModel;
use App\Models\VariantModel;

class Shipments extends BaseController
{
    protected ShipmentModel $shipmentModel;
    protected CampaignModel $campaignModel;
    protected ProductModel $productModel;
    protected VariantModel $variantModel;

    public function __construct()
    {
        $this->shipmentModel = new ShipmentModel();
        $this->campaignModel = new CampaignModel();
        $this->productModel  = new ProductModel();
        $this->variantModel  = new VariantModel();
    }

    /**
     * Halaman Utama Pengiriman Sampel Produk ke KOL
     */
    public function index()
    {
        $db = \Config\Database::connect();

        // Ambil daftar penugasan campaign-kol yang aktif
        $assignedKols = $db->table('campaign_kols')
            ->select('campaign_kols.id, campaign_kols.campaign_id, campaigns.title as campaign_title, kols.name as kol_name, kols.city, kols.address, kols.postal_code, kols.whatsapp')
            ->join('campaigns', 'campaigns.id = campaign_kols.campaign_id', 'inner')
            ->join('kols', 'kols.id = campaign_kols.kol_id', 'inner')
            ->orderBy('campaign_kols.id', 'DESC')
            ->get()
            ->getResultArray();

        $allProducts = $this->productModel->where('is_active', 1)->findAll();
        foreach ($allProducts as &$prod) {
            $prod['variants'] = $this->variantModel->where('product_id', $prod['id'])->findAll();
        }
        unset($prod);

        $data = [
            'title'        => 'Logistik & Pengiriman Sampel Produk KOL',
            'controller'   => 'Shipments',
            'assignedKols' => $assignedKols,
            'allProducts'  => $allProducts,
            'allCampaigns' => $this->campaignModel->select('id, title')->orderBy('id', 'DESC')->findAll(),
        ];

        return view('shipments/index', $data);
    }

    /**
     * DataTables Server-Side AJAX Endpoint
     */
    public function getDatatable()
    {
        $draw   = (int) ($this->request->getPost('draw') ?? 1);
        $start  = (int) ($this->request->getPost('start') ?? 0);
        $length = (int) ($this->request->getPost('length') ?? 10);
        $search = (string) ($this->request->getPost('search')['value'] ?? '');

        $orderColIdx = (int) ($this->request->getPost('order')[0]['column'] ?? 0);
        $orderDir    = strtoupper((string) ($this->request->getPost('order')[0]['dir'] ?? 'DESC'));

        $columns = [
            0 => 'kol_shipments.id',
            1 => 'kol_shipments.tracking_number',
            2 => 'campaigns.title',
            3 => 'kols.name',
            4 => 'kol_shipments.shipping_status',
            5 => 'kol_shipments.created_at',
            6 => 'kol_shipments.id',
        ];
        $orderCol = $columns[$orderColIdx] ?? 'kol_shipments.id';

        $filters = [
            'status'      => $this->request->getPost('status'),
            'campaign_id' => $this->request->getPost('campaign_id'),
        ];

        $result = $this->shipmentModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $statusBadge = match ($row['shipping_status']) {
                ShipmentModel::STATUS_DELIVERED => '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-double me-1"></i>DELIVERED</span>',
                ShipmentModel::STATUS_SHIPPED => '<span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1"><i class="fas fa-truck me-1"></i>SHIPPED</span>',
                ShipmentModel::STATUS_RETURNED => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-undo me-1"></i>RETURNED</span>',
                default => '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="far fa-clock me-1"></i>PENDING</span>',
            };

            // Format items
            $itemsSummary = '';
            if (!empty($row['items'])) {
                foreach ($row['items'] as $it) {
                    $itemsSummary .= '<span class="badge bg-light text-dark border me-1 mb-1">' . esc($it['product_name']) . ($it['variant_name'] ? ' (' . esc($it['variant_name']) . ')' : '') . ' &times; ' . $it['qty'] . '</span>';
                }
            } else {
                $itemsSummary = '<span class="text-muted small">Tidak ada item</span>';
            }

            $formattedData[] = [
                'id'       => $row['id'],
                'resi'     => '
                    <div class="fw-bold text-dark font-monospace">' . esc($row['tracking_number'] ?: 'Draft Belum Resi') . '</div>
                    <small class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">' . esc($row['courier_name']) . '</small>
                ',
                'campaign' => '<div class="fw-semibold text-primary">' . esc($row['campaign_title']) . '</div>',
                'kol'      => '
                    <div class="fw-semibold text-dark" style="font-size: 13px;">' . esc($row['kol_name']) . '</div>
                    <small class="text-muted"><i class="fas fa-map-marker-alt text-danger me-1"></i>' . esc($row['kol_city'] ?: '-') . '</small>
                ',
                'items'    => $itemsSummary,
                'status'   => $statusBadge,
                'action'   => '
                    <div class="d-flex gap-1 justify-content-center">
                        <button type="button" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Detail Pengiriman" onclick="viewShipment(' . $row['id'] . ')"><i class="fas fa-eye"></i></button>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-2 py-1 px-2" title="Edit Resi & Barang" onclick="editShipment(' . $row['id'] . ')"><i class="fas fa-edit"></i></button>
                        <button type="button" class="btn btn-outline-success btn-sm rounded-2 py-1 px-2" title="Update Status Pengiriman" onclick="updateStatusModal(' . $row['id'] . ')"><i class="fas fa-truck-loading"></i></button>
                    </div>
                ',
            ];
        }

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data'            => $formattedData,
        ]);
    }

    /**
     * Get single shipment details JSON
     */
    public function getOne(int $id)
    {
        $shipment = $this->shipmentModel->getShipmentWithItems($id);
        if (!$shipment) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Data pengiriman tidak ditemukan']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $shipment]);
    }

    /**
     * Save / create shipment
     */
    public function save()
    {
        $session = session();
        $userId  = (int) ($session->get('user_id') ?? 1);

        $id             = $this->request->getPost('id');
        $campaignKolId  = (int) $this->request->getPost('campaign_kol_id');
        $courierName    = trim((string)$this->request->getPost('courier_name'));
        $trackingNumber = trim((string)$this->request->getPost('tracking_number'));
        $shippingStatus = strtoupper(trim((string)($this->request->getPost('shipping_status') ?? ShipmentModel::STATUS_PENDING)));
        $notes          = trim((string)$this->request->getPost('notes'));
        $itemsRaw       = $this->request->getPost('items');

        if (empty($campaignKolId) || empty($courierName) || empty($trackingNumber)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Pilih penugasan KOL, jasa ekspedisi/kurir, dan nomor resi pengiriman',
            ]);
        }

        $items = [];
        if (!empty($itemsRaw) && is_array($itemsRaw)) {
            foreach ($itemsRaw as $it) {
                if (!empty($it['product_id'])) {
                    $items[] = [
                        'product_id' => (int)$it['product_id'],
                        'variant_id' => !empty($it['variant_id']) ? (int)$it['variant_id'] : null,
                        'qty'        => max(1, (int)($it['qty'] ?? 1)),
                    ];
                }
            }
        }

        $data = [
            'campaign_kol_id' => $campaignKolId,
            'courier_name'    => $courierName,
            'tracking_number' => $trackingNumber,
            'shipping_status' => $shippingStatus,
            'notes'           => $notes ?: null,
        ];

        if ($shippingStatus === ShipmentModel::STATUS_SHIPPED) {
            $data['shipped_at'] = date('Y-m-d H:i:s');
        } elseif ($shippingStatus === ShipmentModel::STATUS_DELIVERED) {
            $data['delivered_at'] = date('Y-m-d H:i:s');
        }

        if (!empty($id)) {
            $data['id'] = (int)$id;
        }

        $shipmentId = $this->shipmentModel->saveShipment($data, $items, $userId);

        return $this->response->setJSON([
            'success'     => true,
            'message'     => 'Pengiriman sampel berhasil disimpan!',
            'shipment_id' => $shipmentId,
        ]);
    }

    /**
     * Update shipment status (e.g. SHIPPED -> DELIVERED)
     */
    public function updateStatus(int $id)
    {
        $status = strtoupper(trim((string)$this->request->getPost('status')));
        $notes  = trim((string)$this->request->getPost('notes'));

        $valid = [
            ShipmentModel::STATUS_PENDING,
            ShipmentModel::STATUS_SHIPPED,
            ShipmentModel::STATUS_DELIVERED,
            ShipmentModel::STATUS_RETURNED,
        ];

        if (!in_array($status, $valid, true)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Status pengiriman tidak valid']);
        }

        $update = [
            'shipping_status' => $status,
            'updated_at'      => date('Y-m-d H:i:s'),
        ];
        if (!empty($notes)) {
            $update['notes'] = $notes;
        }

        if ($status === ShipmentModel::STATUS_SHIPPED) {
            $update['shipped_at'] = date('Y-m-d H:i:s');
        } elseif ($status === ShipmentModel::STATUS_DELIVERED) {
            $update['delivered_at'] = date('Y-m-d H:i:s');
        }

        $this->shipmentModel->update($id, $update);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Status pengiriman berhasil diubah ke ' . $status,
        ]);
    }
}
