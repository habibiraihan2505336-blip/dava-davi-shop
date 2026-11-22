<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AkunController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Customer $customer */
        $customer = Auth::guard('pelanggan')->user();

        return view('pelanggan.akun', [
            'customer' => $customer,
            'pesanan' => $customer->orders()
                ->with('items.product')
                ->latest('id')
                ->paginate(10),
        ]);
    }

    /**
     * Mengisi keranjang dari pesanan lama, melewati produk yang sudah tidak aktif
     * atau stoknya tidak cukup.
     */
    public function pesanUlang(Request $request, Order $order): RedirectResponse
    {
        /** @var Customer $customer */
        $customer = Auth::guard('pelanggan')->user();

        abort_unless($order->customer_id === $customer->id, 404);

        $keranjang = $request->session()->get('cart', []);
        $dilewati = [];

        foreach ($order->items as $item) {
            $product = Product::query()->aktif()->find($item->product_id);

            if ($product === null) {
                $dilewati[] = 'produk sudah tidak dijual';

                continue;
            }

            $jumlah = min($item->jumlah, $product->stok);

            if ($jumlah < 1) {
                $dilewati[] = $product->nama_produk.' (stok habis)';

                continue;
            }

            $keranjang[$product->id] = min(20, ($keranjang[$product->id] ?? 0) + $jumlah);
        }

        $request->session()->put('cart', $keranjang);

        $pesan = 'Keranjang diisi ulang dari pesanan '.$order->kode_pesanan.'.';
        if ($dilewati !== []) {
            $pesan .= ' Tidak dimasukkan: '.implode(', ', $dilewati).'.';
        }

        return redirect()->route('cart.index')->with('success', $pesan);
    }
}
