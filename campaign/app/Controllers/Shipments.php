<?php

namespace App\Controllers;

use App\Models\ShipmentModel;
use App\Models\CampaignModel;
use App\Models\ProductModel;
use App\Models\VariantModel;
use CodeIgniter\HTTP\ResponseInterface;

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
     * Halaman Utama Pengiriman Sampel Produk ke KOL (CI3 Parity)
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
            'summary'      => $this->shipmentModel->getSummary(),
        ];

        return view('shipments/index', $data);
    }

    /**
     * DataTables Server-Side AJAX Endpoint (CI3 & CI4 Parity)
     */
    public function getDatatable()
    {
        $draw   = (int) ($this->request->getPost('draw') ?? ($this->request->getGet('draw') ?? 1));
        $start  = (int) ($this->request->getPost('start') ?? ($this->request->getGet('start') ?? 0));
        $length = (int) ($this->request->getPost('length') ?? ($this->request->getGet('length') ?? 10));
        $search = (string) ($this->request->getPost('search')['value'] ?? ($this->request->getGet('search')['value'] ?? ''));

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
            'status'      => $this->request->getPost('status') ?: $this->request->getGet('status'),
            'campaign_id' => $this->request->getPost('campaign_id') ?: $this->request->getGet('campaign_id'),
        ];

        $result = $this->shipmentModel->getDatatables($filters, $start, $length, $search, $orderCol, $orderDir);

        $formattedData = [];
        foreach ($result['data'] as $row) {
            $statusVal = strtoupper($row['shipping_status'] ?: ($row['status'] ?: ShipmentModel::STATUS_PENDING));
            $statusBadge = match ($statusVal) {
                ShipmentModel::STATUS_DELIVERED => '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-double me-1"></i>DELIVERED</span>',
                ShipmentModel::STATUS_SHIPPED   => '<span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1"><i class="fas fa-truck me-1"></i>SHIPPED</span>',
                ShipmentModel::STATUS_PROCESSING=> '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i class="fas fa-box me-1"></i>PROCESSING</span>',
                ShipmentModel::STATUS_RETURNED, ShipmentModel::STATUS_RETURN_PENDING => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-undo me-1"></i>' . $statusVal . '</span>',
                default                         => '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="far fa-clock me-1"></i>PENDING</span>',
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

            $courierDisplay = esc($row['courier_name'] ?: ($row['courier'] ?: 'Kurir'));
            $trackingDisplay = esc($row['tracking_number'] ?: ($row['tracking_no'] ?: 'Draft Belum Resi'));

            $formattedData[] = [
                'id'       => $row['id'],
                'resi'     => '
                    <div class="fw-bold text-dark font-monospace">' . $trackingDisplay . '</div>
                    <small class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">' . $courierDisplay . '</small>
                ',
                'campaign' => '<div class="fw-semibold text-primary">' . esc($row['campaign_title'] ?? '-') . '</div>',
                'kol'      => '
                    <div class="fw-semibold text-dark" style="font-size: 13px;">' . esc($row['recipient_name'] ?: ($row['kol_name'] ?? '-')) . '</div>
                    <small class="text-muted"><i class="fas fa-map-marker-alt text-danger me-1"></i>' . esc($row['kol_city'] ?: '-') . '</small>
                ',
                'items'    => $itemsSummary,
                'status'   => $statusBadge,
                'action'   => '
                    <div class="d-flex gap-1 justify-content-center">
                        <a href="' . base_url('shipments/view/' . $row['id']) . '" class="btn btn-outline-info btn-sm rounded-2 py-1 px-2" title="Detail Pengiriman"><i class="fas fa-eye"></i></a>
                        <a href="' . base_url('shipments/edit/' . $row['id']) . '" class="btn btn-outline-primary btn-sm rounded-2 py-1 px-2" title="Edit Data Pengiriman"><i class="fas fa-edit"></i></a>
                        <a href="' . base_url('shipments/single-pdf/' . $row['id']) . '" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2 py-1 px-2" title="Cetak Surat Jalan"><i class="fas fa-print"></i></a>
                        <button type="button" class="btn btn-outline-success btn-sm rounded-2 py-1 px-2" title="Update Status Cepat" onclick="updateStatusModal(' . $row['id'] . ')"><i class="fas fa-truck-loading"></i></button>
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2" title="Hapus Pengiriman" onclick="deleteShipment(' . $row['id'] . ')"><i class="fas fa-trash"></i></button>
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
    public function getOne(int $id): ResponseInterface
    {
        $shipment = $this->shipmentModel->getShipmentWithItems($id);
        if (!$shipment) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Data pengiriman tidak ditemukan']);
        }

        return $this->response->setJSON(['success' => true, 'data' => $shipment]);
    }

    /**
     * Standalone Detail View Page (CI3 Parity)
     */
    public function view(int $id)
    {
        $shipment = $this->shipmentModel->getShipmentWithItems($id);
        if (!$shipment) {
            return redirect()->to(base_url('shipments'))->with('error', 'Data pengiriman #' . $id . ' tidak ditemukan.');
        }

        $data = [
            'title'         => 'Detail Pengiriman Sampel #' . $id . ' - ' . ($shipment['recipient_name'] ?: $shipment['kol_name']),
            'controller'    => 'Shipments',
            'shipment'      => $shipment,
            'kol_shipments' => (object)$shipment, // CI3 backward compatibility
        ];

        return view('shipments/view', $data);
    }

    /**
     * Standalone Add Form (CI3 Parity)
     */
    public function add()
    {
        $db = \Config\Database::connect();
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
            'title'        => 'Tambah Pengiriman Sampel Baru',
            'controller'   => 'Shipments',
            'assignedKols' => $assignedKols,
            'allProducts'  => $allProducts,
        ];

        return view('shipments/add', $data);
    }

    /**
     * Standalone Add Save (CI3 Parity)
     */
    public function add_save()
    {
        $rules = [
            'campaign_kol_id'  => 'required|integer',
            'recipient_name'   => 'permit_empty|max_length[150]',
            'recipient_phone'  => 'permit_empty|max_length[30]',
            'shipping_address' => 'permit_empty',
            'courier'          => 'permit_empty|max_length[50]',
            'courier_name'     => 'permit_empty|max_length[50]',
            'tracking_no'      => 'permit_empty|max_length[100]',
            'tracking_number'  => 'permit_empty|max_length[100]',
            'shipping_fee'     => 'permit_empty|numeric',
            'status'           => 'permit_empty',
            'shipping_status'  => 'permit_empty',
        ];

        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => implode(', ', $this->validator->getErrors()),
                ]);
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $campaignKolId  = (int)$this->request->getPost('campaign_kol_id');
        $recipientName  = trim((string)$this->request->getPost('recipient_name'));
        $recipientPhone = trim((string)$this->request->getPost('recipient_phone'));
        $shippingAddress= trim((string)$this->request->getPost('shipping_address'));
        $courier        = trim((string)($this->request->getPost('courier') ?: $this->request->getPost('courier_name')));
        $trackingNo     = trim((string)($this->request->getPost('tracking_no') ?: $this->request->getPost('tracking_number')));
        $shippingFee    = (float)($this->request->getPost('shipping_fee') ?? 0);
        $status         = strtoupper(trim((string)($this->request->getPost('status') ?: ($this->request->getPost('shipping_status') ?? ShipmentModel::STATUS_PENDING))));
        $notes          = trim((string)$this->request->getPost('notes'));
        $shippedAt      = $this->request->getPost('shipped_at') ?: null;
        $deliveredAt    = $this->request->getPost('delivered_at') ?: null;

        if (in_array($status, [ShipmentModel::STATUS_SHIPPED, ShipmentModel::STATUS_DELIVERED], true) && empty($shippedAt)) {
            $shippedAt = date('Y-m-d H:i:s');
        }
        if ($status === ShipmentModel::STATUS_DELIVERED && empty($deliveredAt)) {
            $deliveredAt = date('Y-m-d H:i:s');
        }

        $session = session();
        $userId  = (int)($session->get('user_id') ?? ($session->get('user')['id'] ?? 1));

        $payload = [
            'campaign_kol_id'  => $campaignKolId,
            'recipient_name'   => $recipientName,
            'recipient_phone'  => $recipientPhone,
            'shipping_address' => $shippingAddress,
            'courier'          => $courier ?: 'Kurir',
            'courier_name'     => $courier ?: 'Kurir',
            'tracking_no'      => $trackingNo,
            'tracking_number'  => $trackingNo,
            'shipping_fee'     => $shippingFee,
            'status'           => $status,
            'shipping_status'  => $status,
            'notes'            => $notes ?: null,
            'created_by'       => $userId,
            'shipped_at'       => $shippedAt,
            'delivered_at'     => $deliveredAt,
        ];

        // Process shipped items if provided
        $itemsRaw = $this->request->getPost('items');
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

        $newId = $this->shipmentModel->saveShipment($payload, $items, $userId);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success'  => true,
                'id'       => $newId,
                'message'  => 'Data pengiriman sampel berhasil ditambahkan.',
                'redirect' => base_url('shipments'),
            ]);
        }

        if ($this->request->getPost('save_type') === 'stay') {
            return redirect()->to(base_url('shipments/edit/' . $newId))->with('success', 'Data pengiriman berhasil disimpan.');
        }

        return redirect()->to(base_url('shipments'))->with('success', 'Data pengiriman sampel berhasil ditambahkan.');
    }

    /**
     * Standalone Edit Form (CI3 Parity)
     */
    public function edit(int $id)
    {
        $shipment = $this->shipmentModel->getShipmentWithItems($id);
        if (!$shipment) {
            return redirect()->to(base_url('shipments'))->with('error', 'Data pengiriman #' . $id . ' tidak ditemukan.');
        }

        $db = \Config\Database::connect();
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
            'title'         => 'Edit Pengiriman Sampel #' . $id,
            'controller'    => 'Shipments',
            'shipment'      => $shipment,
            'kol_shipments' => (object)$shipment, // CI3 backward compatibility
            'assignedKols'  => $assignedKols,
            'allProducts'   => $allProducts,
        ];

        return view('shipments/edit', $data);
    }

    /**
     * Standalone Edit Save (CI3 Parity)
     */
    public function edit_save(int $id)
    {
        $oldShipment = $this->shipmentModel->find($id);
        if (!$oldShipment) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Data pengiriman tidak ditemukan.']);
            }
            return redirect()->to(base_url('shipments'))->with('error', 'Data pengiriman tidak ditemukan.');
        }

        $rules = [
            'campaign_kol_id'  => 'required|integer',
            'recipient_name'   => 'permit_empty|max_length[150]',
            'recipient_phone'  => 'permit_empty|max_length[30]',
            'shipping_address' => 'permit_empty',
            'courier'          => 'permit_empty|max_length[50]',
            'courier_name'     => 'permit_empty|max_length[50]',
            'tracking_no'      => 'permit_empty|max_length[100]',
            'tracking_number'  => 'permit_empty|max_length[100]',
            'shipping_fee'     => 'permit_empty|numeric',
            'status'           => 'permit_empty',
            'shipping_status'  => 'permit_empty',
        ];

        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => implode(', ', $this->validator->getErrors()),
                ]);
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $campaignKolId  = (int)$this->request->getPost('campaign_kol_id');
        $recipientName  = trim((string)$this->request->getPost('recipient_name'));
        $recipientPhone = trim((string)$this->request->getPost('recipient_phone'));
        $shippingAddress= trim((string)$this->request->getPost('shipping_address'));
        $courier        = trim((string)($this->request->getPost('courier') ?: $this->request->getPost('courier_name')));
        $trackingNo     = trim((string)($this->request->getPost('tracking_no') ?: $this->request->getPost('tracking_number')));
        $shippingFee    = (float)($this->request->getPost('shipping_fee') ?? 0);
        $status         = strtoupper(trim((string)($this->request->getPost('status') ?: ($this->request->getPost('shipping_status') ?? ShipmentModel::STATUS_PENDING))));
        $notes          = trim((string)$this->request->getPost('notes'));
        $shippedAt      = $this->request->getPost('shipped_at') ?: null;
        $deliveredAt    = $this->request->getPost('delivered_at') ?: null;

        if (in_array($status, [ShipmentModel::STATUS_SHIPPED, ShipmentModel::STATUS_DELIVERED], true) && empty($shippedAt)) {
            $shippedAt = $oldShipment['shipped_at'] ?: date('Y-m-d H:i:s');
        }
        if ($status === ShipmentModel::STATUS_DELIVERED && empty($deliveredAt)) {
            $deliveredAt = $oldShipment['delivered_at'] ?: date('Y-m-d H:i:s');
        }

        $session = session();
        $userId  = (int)($session->get('user_id') ?? ($session->get('user')['id'] ?? 1));

        $payload = [
            'id'               => $id,
            'campaign_kol_id'  => $campaignKolId,
            'recipient_name'   => $recipientName,
            'recipient_phone'  => $recipientPhone,
            'shipping_address' => $shippingAddress,
            'courier'          => $courier ?: 'Kurir',
            'courier_name'     => $courier ?: 'Kurir',
            'tracking_no'      => $trackingNo,
            'tracking_number'  => $trackingNo,
            'shipping_fee'     => $shippingFee,
            'status'           => $status,
            'shipping_status'  => $status,
            'notes'            => $notes ?: null,
            'shipped_at'       => $shippedAt,
            'delivered_at'     => $deliveredAt,
        ];

        // Process shipped items if provided
        $itemsRaw = $this->request->getPost('items');
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

        $this->shipmentModel->saveShipment($payload, $items, $userId);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success'  => true,
                'id'       => $id,
                'message'  => 'Data pengiriman sampel berhasil diperbarui.',
                'redirect' => base_url('shipments'),
            ]);
        }

        if ($this->request->getPost('save_type') === 'stay') {
            return redirect()->to(base_url('shipments/edit/' . $id))->with('success', 'Perubahan berhasil disimpan.');
        }

        return redirect()->to(base_url('shipments'))->with('success', 'Data pengiriman sampel berhasil diperbarui.');
    }

    /**
     * Quick save from modal in index view
     */
    public function save(): ResponseInterface
    {
        return $this->add_save();
    }

    /**
     * Quick status update endpoint (CI3 update_status parity)
     */
    public function update_status(): ResponseInterface
    {
        $id        = (int)$this->request->getPost('id');
        $newStatus = strtoupper(trim((string)$this->request->getPost('status')));
        $notes     = trim((string)$this->request->getPost('notes'));

        $validStatuses = [
            ShipmentModel::STATUS_PENDING,
            ShipmentModel::STATUS_PROCESSING,
            ShipmentModel::STATUS_SHIPPED,
            ShipmentModel::STATUS_DELIVERED,
            ShipmentModel::STATUS_RETURN_PENDING,
            ShipmentModel::STATUS_RETURNED,
        ];

        if (!$id || !in_array($newStatus, $validStatuses, true)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Status atau ID pengiriman tidak valid.',
            ]);
        }

        $oldShipment = $this->shipmentModel->find($id);
        if (!$oldShipment) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Data pengiriman tidak ditemukan.',
            ]);
        }

        $oldStatus = strtoupper($oldShipment['shipping_status'] ?: ($oldShipment['status'] ?: ShipmentModel::STATUS_PENDING));

        $updateData = [
            'status'          => $newStatus,
            'shipping_status' => $newStatus,
            'updated_at'      => date('Y-m-d H:i:s'),
        ];
        if (!empty($notes)) {
            $updateData['notes'] = $notes;
        }

        if (in_array($newStatus, [ShipmentModel::STATUS_PENDING, ShipmentModel::STATUS_PROCESSING], true)) {
            $updateData['shipped_at']   = null;
            $updateData['delivered_at'] = null;
        } elseif ($newStatus === ShipmentModel::STATUS_SHIPPED) {
            if (empty($oldShipment['shipped_at'])) {
                $updateData['shipped_at'] = date('Y-m-d H:i:s');
            }
            $updateData['delivered_at'] = null;
        } elseif ($newStatus === ShipmentModel::STATUS_DELIVERED) {
            if (empty($oldShipment['shipped_at'])) {
                $updateData['shipped_at'] = date('Y-m-d H:i:s');
            }
            if (empty($oldShipment['delivered_at'])) {
                $updateData['delivered_at'] = date('Y-m-d H:i:s');
            }
        }

        $session = session();
        $userId  = (int)($session->get('user_id') ?? ($session->get('user')['id'] ?? 1));

        $this->shipmentModel->update($id, $updateData);

        if ($oldStatus !== $newStatus) {
            $this->shipmentModel->logStockChange($id, $oldStatus, $newStatus, $userId);
        }

        return $this->response->setJSON([
            'success'      => true,
            'message'      => 'Status pengiriman berhasil diperbarui menjadi ' . $newStatus,
            'status'       => $newStatus,
            'shipped_at'   => $updateData['shipped_at'] ?? $oldShipment['shipped_at'],
            'delivered_at' => $updateData['delivered_at'] ?? $oldShipment['delivered_at'],
            'summary'      => $this->shipmentModel->getSummary(),
        ]);
    }

    /**
     * Alias for update_status
     */
    public function updateStatus(int $id): ResponseInterface
    {
        $_POST['id'] = $id;
        return $this->update_status();
    }

    /**
     * Bulk update status (CI3 bulk_update_status parity)
     */
    public function bulk_update_status(): ResponseInterface
    {
        $ids       = $this->request->getPost('id') ?: $this->request->getPost('ids');
        $newStatus = strtoupper(trim((string)$this->request->getPost('status')));

        $validStatuses = [
            ShipmentModel::STATUS_PENDING,
            ShipmentModel::STATUS_PROCESSING,
            ShipmentModel::STATUS_SHIPPED,
            ShipmentModel::STATUS_DELIVERED,
            ShipmentModel::STATUS_RETURN_PENDING,
            ShipmentModel::STATUS_RETURNED,
        ];

        if (empty($ids) || !is_array($ids) || !in_array($newStatus, $validStatuses, true)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Status target atau data pengiriman tidak valid.',
            ]);
        }

        $session = session();
        $userId  = (int)($session->get('user_id') ?? ($session->get('user')['id'] ?? 1));

        $successCount = 0;
        foreach ($ids as $id) {
            $id = (int)$id;
            if (!$id) continue;

            $oldShipment = $this->shipmentModel->find($id);
            if (!$oldShipment) continue;

            $oldStatus = strtoupper($oldShipment['shipping_status'] ?: ($oldShipment['status'] ?: ShipmentModel::STATUS_PENDING));

            $updateData = [
                'status'          => $newStatus,
                'shipping_status' => $newStatus,
                'updated_at'      => date('Y-m-d H:i:s'),
            ];

            if (in_array($newStatus, [ShipmentModel::STATUS_PENDING, ShipmentModel::STATUS_PROCESSING], true)) {
                $updateData['shipped_at']   = null;
                $updateData['delivered_at'] = null;
            } elseif ($newStatus === ShipmentModel::STATUS_SHIPPED) {
                if (empty($oldShipment['shipped_at'])) {
                    $updateData['shipped_at'] = date('Y-m-d H:i:s');
                }
                $updateData['delivered_at'] = null;
            } elseif ($newStatus === ShipmentModel::STATUS_DELIVERED) {
                if (empty($oldShipment['shipped_at'])) {
                    $updateData['shipped_at'] = date('Y-m-d H:i:s');
                }
                if (empty($oldShipment['delivered_at'])) {
                    $updateData['delivered_at'] = date('Y-m-d H:i:s');
                }
            }

            $this->shipmentModel->update($id, $updateData);

            if ($oldStatus !== $newStatus) {
                $this->shipmentModel->logStockChange($id, $oldStatus, $newStatus, $userId);
            }

            $successCount++;
        }

        return $this->response->setJSON([
            'success'       => true,
            'message'       => "Berhasil memperbarui status {$successCount} pengiriman menjadi {$newStatus}.",
            'updated_count' => $successCount,
            'status'        => $newStatus,
            'summary'       => $this->shipmentModel->getSummary(),
        ]);
    }

    /**
     * Delete shipment (CI3 Parity)
     */
    public function delete($id = null)
    {
        $arrId = $this->request->getGet('id') ?: ($this->request->getPost('id') ?: null);
        if (empty($id) && !empty($arrId) && !is_array($arrId)) {
            $id = (int)$arrId;
        }

        $session = session();
        $userId  = (int)($session->get('user_id') ?? ($session->get('user')['id'] ?? 1));

        if (!empty($id)) {
            $shipment = $this->shipmentModel->find((int)$id);
            if (!$shipment) {
                if ($this->request->isAJAX()) {
                    return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Pengiriman tidak ditemukan.']);
                }
                return redirect()->to(base_url('shipments'))->with('error', 'Pengiriman tidak ditemukan.');
            }

            // If shipment was shipped or delivered, return items to inventory before deleting
            $status = strtoupper($shipment['shipping_status'] ?: ($shipment['status'] ?: ''));
            if (in_array($status, [ShipmentModel::STATUS_SHIPPED, ShipmentModel::STATUS_DELIVERED], true)) {
                $this->shipmentModel->logStockChange((int)$id, $status, ShipmentModel::STATUS_RETURNED, $userId);
            }

            $db = \Config\Database::connect();
            $db->table('kol_shipment_items')->where('shipment_id', (int)$id)->delete();
            $this->shipmentModel->delete((int)$id);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Data pengiriman sampel berhasil dihapus.',
                    'summary' => $this->shipmentModel->getSummary(),
                ]);
            }
            return redirect()->to(base_url('shipments'))->with('success', 'Data pengiriman sampel berhasil dihapus.');
        }

        if (is_array($arrId) && count($arrId) > 0) {
            $db = \Config\Database::connect();
            foreach ($arrId as $singleId) {
                $s = $this->shipmentModel->find((int)$singleId);
                if ($s) {
                    $status = strtoupper($s['shipping_status'] ?: ($s['status'] ?: ''));
                    if (in_array($status, [ShipmentModel::STATUS_SHIPPED, ShipmentModel::STATUS_DELIVERED], true)) {
                        $this->shipmentModel->logStockChange((int)$singleId, $status, ShipmentModel::STATUS_RETURNED, $userId);
                    }
                    $db->table('kol_shipment_items')->where('shipment_id', (int)$singleId)->delete();
                    $this->shipmentModel->delete((int)$singleId);
                }
            }

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => count($arrId) . ' data pengiriman berhasil dihapus.',
                    'summary' => $this->shipmentModel->getSummary(),
                ]);
            }
            return redirect()->to(base_url('shipments'))->with('success', count($arrId) . ' data pengiriman berhasil dihapus.');
        }

        if ($this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Tidak ada pengiriman yang dipilih.']);
        }
        return redirect()->to(base_url('shipments'))->with('error', 'Tidak ada pengiriman yang dipilih.');
    }

    /**
     * Bulk Delete AJAX Endpoint
     */
    public function bulkDelete(): ResponseInterface
    {
        $ids = $this->request->getPost('ids') ?: $this->request->getPost('id');
        if (empty($ids) || !is_array($ids)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Pilih setidaknya satu pengiriman untuk dihapus.']);
        }

        $session = session();
        $userId  = (int)($session->get('user_id') ?? ($session->get('user')['id'] ?? 1));

        $db = \Config\Database::connect();
        $deletedCount = 0;

        foreach ($ids as $singleId) {
            $singleId = (int)$singleId;
            if (!$singleId) continue;

            $s = $this->shipmentModel->find($singleId);
            if ($s) {
                $status = strtoupper($s['shipping_status'] ?: ($s['status'] ?: ''));
                if (in_array($status, [ShipmentModel::STATUS_SHIPPED, ShipmentModel::STATUS_DELIVERED], true)) {
                    $this->shipmentModel->logStockChange($singleId, $status, ShipmentModel::STATUS_RETURNED, $userId);
                }
                $db->table('kol_shipment_items')->where('shipment_id', $singleId)->delete();
                $this->shipmentModel->delete($singleId);
                $deletedCount++;
            }
        }

        return $this->response->setJSON([
            'success'       => true,
            'message'       => $deletedCount . ' data pengiriman berhasil dihapus.',
            'deleted_count' => $deletedCount,
            'summary'       => $this->shipmentModel->getSummary(),
        ]);
    }

    /**
     * Export to CSV (CI3 Parity)
     */
    public function export()
    {
        $filterStatus   = $this->request->getGet('status') ?: ($this->request->getGet('shipping_status') ?? null);
        $filterCampaign = $this->request->getGet('campaign_id') ? (int)$this->request->getGet('campaign_id') : null;

        $items = $this->shipmentModel->getAllForExport($filterStatus, $filterCampaign);
        $filename = 'kol_shipments_export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM

        fputcsv($output, [
            'ID',
            'No. Resi / Tracking',
            'Jasa Ekspedisi',
            'Kampanye',
            'Nama Penerima / KOL',
            'No. WhatsApp',
            'Kota Tujuan',
            'Alamat Lengkap',
            'Daftar Barang Sampel',
            'Total Qty',
            'Biaya Ongkir (Rp)',
            'Status Pengiriman',
            'Tanggal Dikirim',
            'Tanggal Diterima',
            'Catatan',
            'Waktu Pendaftaran'
        ]);

        foreach ($items as $row) {
            $itemList = [];
            $totalQty = 0;
            if (!empty($row['items'])) {
                foreach ($row['items'] as $it) {
                    $itemList[] = $it['product_name'] . ($it['variant_name'] ? ' (' . $it['variant_name'] . ')' : '') . ' x' . $it['qty'];
                    $totalQty += (int)$it['qty'];
                }
            }

            fputcsv($output, [
                $row['id'],
                $row['tracking_number'] ?: ($row['tracking_no'] ?: '-'),
                $row['courier_name'] ?: ($row['courier'] ?: '-'),
                $row['campaign_title'] ?? '-',
                $row['recipient_name'] ?: ($row['kol_name'] ?? '-'),
                $row['recipient_phone'] ?: ($row['kol_whatsapp'] ?? '-'),
                $row['kol_city'] ?? '-',
                $row['shipping_address'] ?? '-',
                implode('; ', $itemList),
                $totalQty,
                (float)($row['shipping_fee'] ?? 0),
                $row['shipping_status'] ?: ($row['status'] ?: 'PENDING'),
                $row['shipped_at'] ?? '-',
                $row['delivered_at'] ?? '-',
                $row['notes'] ?? '-',
                $row['created_at'] ?? '-'
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Printable PDF Report of Shipments Catalog (CI3 Parity)
     */
    public function export_pdf()
    {
        $filterStatus   = $this->request->getGet('status') ?: ($this->request->getGet('shipping_status') ?? null);
        $filterCampaign = $this->request->getGet('campaign_id') ? (int)$this->request->getGet('campaign_id') : null;

        $items = $this->shipmentModel->getAllForExport($filterStatus, $filterCampaign);

        return view('shipments/export_pdf', [
            'items'     => $items,
            'title'     => 'Rekapitulasi Pengiriman Sampel Produk ke KOL',
            'generated' => date('d F Y, H:i:s'),
            'filters'   => [
                'status'      => $filterStatus,
                'campaign_id' => $filterCampaign,
            ],
        ]);
    }

    /**
     * Printable Surat Jalan / Delivery Packing Slip (CI3 single_pdf Parity)
     */
    public function single_pdf($id = null)
    {
        $id = (int)$id;
        $shipment = $this->shipmentModel->getShipmentWithItems($id);

        if (!$shipment) {
            return redirect()->to(base_url('shipments'))->with('error', 'Data pengiriman #' . $id . ' tidak ditemukan.');
        }

        return view('shipments/single_pdf', [
            'shipment'      => $shipment,
            'kol_shipments' => (object)$shipment, // CI3 backward compatibility
            'title'         => 'Surat Jalan Pengiriman Sampel #' . $shipment['id'] . ' - Resi: ' . ($shipment['tracking_number'] ?: $shipment['tracking_no'] ?: 'Draft'),
            'generated'     => date('d F Y, H:i:s'),
        ]);
    }
}
