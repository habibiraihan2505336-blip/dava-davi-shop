<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $table = 'order_items';

    // Tabel hanya punya created_at; subtotal dihitung oleh database.
    public $timestamps = false;

    protected $fillable = ['order_id', 'product_id', 'jumlah', 'harga_satuan'];

    protected function casts(): array
    {
        return ['jumlah' => 'integer', 'harga_satuan' => 'integer', 'subtotal' => 'integer'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
