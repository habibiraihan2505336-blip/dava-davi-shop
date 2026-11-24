<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $table = 'orders';

    public const STATUS = [
        'baru' => 'Baru',
        'diproses' => 'Diproses',
        'selesai' => 'Selesai',
        'batal' => 'Dibatalkan',
    ];

    public const PEMBAYARAN = [
        'belum_bayar' => 'Belum dibayar',
        'menunggu' => 'Menunggu pembayaran',
        'dibayar' => 'Sudah dibayar',
        'gagal' => 'Gagal',
        'kedaluwarsa' => 'Kedaluwarsa',
        'dikembalikan' => 'Dana dikembalikan',
    ];

    protected $fillable = [
        'customer_id', 'kode_pesanan', 'tanggal', 'total', 'ongkir', 'status',
        'payment_status', 'payment_method', 'paid_at', 'catatan', 'snap_token', 'payment_url',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'integer',
            'ongkir' => 'integer',
            'tanggal' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function labelStatus(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function labelPembayaran(): string
    {
        return self::PEMBAYARAN[$this->payment_status] ?? $this->payment_status;
    }

    /**
     * Jumlah harga item saja, tanpa ongkir.
     */
    public function subtotalItem(): int
    {
        return (int) $this->items->sum('subtotal');
    }

    /**
     * Total yang seharusnya tersimpan: subtotal item ditambah ongkir.
     */
    public function totalSeharusnya(): int
    {
        return $this->subtotalItem() + $this->ongkir;
    }
}
