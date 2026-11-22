<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class ShopController extends Controller
{
    public function index(Request $request): View
    {
        $kategori = $request->query('kategori');

        $products = Product::query()
            ->aktif()
            ->with(['category', 'priceTiers'])
            ->when($kategori, fn ($query) => $query->whereHas('category', fn ($q) => $q->where('slug', $kategori)))
            ->orderBy('id')
            ->get();

        return view('shop.index', [
            'products' => $products,
            'categories' => Category::query()->orderBy('nama_kategori')->get(),
            'kategoriAktif' => $kategori,
        ]);
    }

    public function cart(Request $request): View
    {
        $lines = $this->lines($request);

        return view('shop.cart', [
            'lines' => $lines,
            'total' => $lines->sum('subtotal'),
        ]);
    }

    public function addToCart(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $product = Product::query()->aktif()->findOrFail($data['product_id']);
        $cart = $request->session()->get('cart', []);
        $newQuantity = (int) ($cart[$product->id] ?? 0) + (int) $data['quantity'];

        if ($newQuantity > $product->stok) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Jumlah melebihi stok yang tersedia.'], 422);
            }

            return back()->withErrors(['quantity' => 'Jumlah melebihi stok yang tersedia.']);
        }

        $cart[$product->id] = $newQuantity;
        $request->session()->put('cart', $cart);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Produk ditambahkan ke keranjang.',
                'cart_count' => array_sum($cart),
            ]);
        }

        return redirect('/keranjang')->with('success', 'Produk ditambahkan ke keranjang.');
    }

    public function removeFromCart(Request $request, int $product): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$product]);
        $request->session()->put('cart', $cart);

        return redirect('/keranjang')->with('success', 'Produk dihapus dari keranjang.');
    }

    public function checkout(Request $request): View|RedirectResponse
    {
        $lines = $this->lines($request);

        if ($lines->isEmpty()) {
            return redirect('/keranjang')->with('error', 'Keranjang masih kosong.');
        }

        return view('shop.checkout', [
            'lines' => $lines,
            'total' => $lines->sum('subtotal'),
            'pelanggan' => Auth::guard('pelanggan')->user(),
        ]);
    }

    public function placeOrder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'no_hp' => ['required', 'string', 'regex:/^[0-9+() -]{10,20}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'alamat' => ['required', 'string', 'max:1000'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ], [
            'nama.required' => 'Nama penerima wajib diisi.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'no_hp.regex' => 'Nomor HP hanya boleh angka, spasi, tanda +, tanda hubung, atau tanda kurung, 10 sampai 20 karakter.',
            'email.email' => 'Format email tidak sah.',
            'alamat.required' => 'Alamat penerima wajib diisi.',
            'catatan.max' => 'Catatan maksimal 500 karakter.',
        ]);

        $cart = $request->session()->get('cart', []);

        if ($cart === []) {
            return redirect('/keranjang')->with('error', 'Keranjang masih kosong.');
        }

        try {
            $order = DB::transaction(function () use ($data, $cart): Order {
                $products = Product::query()
                    ->aktif()
                    ->with('priceTiers')
                    ->whereIn('id', array_keys($cart))
                    ->lockForUpdate()
                    ->get();

                $total = 0;
                $orderLines = [];

                foreach ($products as $product) {
                    $jumlah = (int) ($cart[$product->id] ?? 0);

                    if ($jumlah < 1 || $jumlah > $product->stok) {
                        throw new \DomainException('Stok produk berubah. Periksa keranjang dan coba lagi.');
                    }

                    $hargaSatuan = $product->hargaUntuk($jumlah);
                    $total += $hargaSatuan * $jumlah;
                    $orderLines[] = [
                        'product_id' => $product->id,
                        'jumlah' => $jumlah,
                        'harga_satuan' => $hargaSatuan,
                    ];
                }

                if ($orderLines === []) {
                    throw new \DomainException('Produk dalam keranjang sudah tidak tersedia.');
                }

                $customer = $this->customerUntuk($data);

                $order = Order::create([
                    'customer_id' => $customer->id,
                    'total' => $total,
                    'ongkir' => 0,
                    'status' => 'baru',
                    'payment_status' => 'belum_bayar',
                    'catatan' => $data['catatan'] ?? null,
                ]);
                $order->items()->createMany($orderLines);

                foreach ($products as $product) {
                    $product->decrement('stok', (int) $cart[$product->id]);
                }

                return $order->refresh();
            });
        } catch (\DomainException $exception) {
            return redirect('/keranjang')->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);

            return redirect('/checkout')->with('error', 'Pesanan belum tersimpan. Coba lagi.');
        }

        $request->session()->forget('cart');

        return redirect('/pesanan/'.$order->kode_pesanan)
            ->with('success', 'Pesanan tersimpan dengan kode '.$order->kode_pesanan.'.');
    }

    public function order(string $kode): View
    {
        return view('shop.order', [
            'order' => Order::query()
                ->with(['items.product', 'customer'])
                ->where('kode_pesanan', $kode)
                ->firstOrFail(),
        ]);
    }

    /**
     * Pembeli yang masuk memakai baris pelanggan miliknya; tamu dicocokkan
     * lewat nomor HP. Email hanya diisi bila belum dipakai pelanggan lain,
     * karena basis data menolak email ganda.
     */
    private function customerUntuk(array $data): Customer
    {
        $email = $data['email'] ?? null;
        $emailDipakaiLain = $email !== null && Customer::query()
            ->whereRaw('lower(email) = ?', [mb_strtolower($email)])
            ->exists();

        $pelanggan = Auth::guard('pelanggan')->user();

        if ($pelanggan instanceof Customer) {
            $pelanggan->fill([
                'nama' => $data['nama'],
                'no_hp' => $data['no_hp'],
                'alamat' => $data['alamat'],
            ]);

            if ($pelanggan->email === null && $email !== null && ! $emailDipakaiLain) {
                $pelanggan->email = $email;
            }

            $pelanggan->save();

            return $pelanggan;
        }

        $customer = Customer::query()->firstOrCreate(
            ['no_hp' => $data['no_hp']],
            ['nama' => $data['nama'], 'alamat' => $data['alamat'], 'email' => $emailDipakaiLain ? null : $email]
        );

        $customer->fill(['nama' => $data['nama'], 'alamat' => $data['alamat']]);

        if ($customer->email === null && $email !== null && ! $emailDipakaiLain) {
            $customer->email = $email;
        }

        $customer->save();

        return $customer;
    }

    /**
     * Baris keranjang beserta harga satuan yang berlaku (termasuk harga grosir).
     */
    private function lines(Request $request)
    {
        $cart = $request->session()->get('cart', []);

        $products = Product::query()
            ->aktif()
            ->with('priceTiers')
            ->whereIn('id', array_keys($cart))
            ->get();

        return $products->map(function (Product $product) use ($cart): array {
            $jumlah = (int) ($cart[$product->id] ?? 0);
            $hargaSatuan = $product->hargaUntuk($jumlah);

            return [
                'product' => $product,
                'jumlah' => $jumlah,
                'harga_satuan' => $hargaSatuan,
                'grosir' => $hargaSatuan < $product->harga,
                'subtotal' => $hargaSatuan * $jumlah,
            ];
        })->filter(fn (array $line): bool => $line['jumlah'] > 0);
    }
}
