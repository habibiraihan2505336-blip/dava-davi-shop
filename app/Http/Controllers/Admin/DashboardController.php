<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'jumlahBaru' => Order::query()->where('status', 'baru')->count(),
            'jumlahBelumBayar' => Order::query()->where('payment_status', 'belum_bayar')->count(),
            'jumlahProduk' => Product::query()->count(),
            'produkHabis' => Product::query()->where('stok', '<', 1)->count(),
            'pesananTerbaru' => Order::query()->with('customer')->latest('id')->limit(8)->get(),
        ]);
    }
}
