<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'category_id', 'nama_produk', 'slug', 'harga', 'deskripsi', 'stok', 'gambar', 'status_aktif',
    ];

    protected function casts(): array
    {
        return ['harga' => 'integer', 'stok' => 'integer', 'status_aktif' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function priceTiers(): HasMany
    {
        return $this->hasMany(ProductPriceTier::class, 'product_id')->orderBy('min_qty');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status_aktif', true);
    }

    /**
     * Harga satuan yang berlaku: ambang grosir tertinggi yang sudah terpenuhi.
     */
    public function hargaUntuk(int $jumlah): int
    {
        $tier = $this->priceTiers
            ->filter(fn (ProductPriceTier $tier): bool => $tier->min_qty <= $jumlah)
            ->sortByDesc('min_qty')
            ->first();

        return $tier?->harga_satuan ?? $this->harga;
    }

    public function getHargaRupiahAttribute(): string
    {
        return 'Rp'.number_format($this->harga, 0, ',', '.');
    }

    public function getGrosirTerendahAttribute(): ?ProductPriceTier
    {
        return $this->priceTiers->sortBy('min_qty')->first();
    }
}
