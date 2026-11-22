<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPriceTier extends Model
{
    protected $table = 'product_price_tiers';

    protected $fillable = ['product_id', 'min_qty', 'harga_satuan'];

    protected function casts(): array
    {
        return ['min_qty' => 'integer', 'harga_satuan' => 'integer'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
