<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'generic_name',
        'concentration',
        'pharmaceutical_form',
        'presentation',
        'laboratory',
        'barcode',
        'health_registration',
        'description',
        'image_path',
        'stock',
        'sale_price',
        'requires_prescription',
        'is_active',
    ];
    protected $appends = [
        'image_url',
    ];

    protected $casts = [
        'requires_prescription' => 'boolean',
        'is_active' => 'boolean',
        'sale_price' => 'decimal:2',
    ];
    public function getImageUrlAttribute()
    {
        return $this->image_path
            ? asset('storage/' . $this->image_path)
            : null;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
