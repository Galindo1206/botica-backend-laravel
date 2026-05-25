<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'business_name',
        'ruc',
        'address',
        'phone',
        'email',
        'currency',
        'tax_percentage',
        'logo_path',
    ];

    protected $casts = [
        'tax_percentage' => 'decimal:2',
    ];
}
