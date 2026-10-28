<?php

namespace App\Models;

use CodeIgniter\Model;

class ShipmentItemModel extends Model
{
    protected $table            = 'kol_shipment_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'shipment_id',
        'product_id',
        'variant_id',
        'qty',
        'created_at',
    ];

    protected $useTimestamps = false;
}
