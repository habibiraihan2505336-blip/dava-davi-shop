<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPriceTier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProdukController extends Controller
{
    public function index(): View
    {
        return view('admin.produk.index', [
            'produk' => Product::query()
                ->with('category')
                ->withCount('priceTiers')
                ->orderBy('nama_produk')
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.produk.form', [
            'product' => new Product(['status_aktif' => true, 'stok' => 0]),
            'categories' => Category::query()->orderBy('nama_kategori')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['nama_produk']);
        $data['gambar'] = $this->simpanGambar($request, null);

        $product = Product::create($data);

        return redirect()->route('admin.produk.edit', $product)->with('success', 'Produk ditambahkan.');
    }

    public function edit(Product $product): View
    {
        return view('admin.produk.form', [
            'product' => $product,
            'categories' => Category::query()->orderBy('nama_kategori')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request);

        if ($data['nama_produk'] !== $product->nama_produk) {
            $data['slug'] = $this->uniqueSlug($data['nama_produk'], $product->id);
        }

        $data['gambar'] = $this->simpanGambar($request, $product);

        $product->update($data);

        return redirect()->route('admin.produk.edit', $product)->with('success', 'Produk diperbarui.');
    }

    /**
     * Menyimpan foto baru, menghapus foto lama, atau mengosongkan kolom gambar.
     * Mengembalikan nilai kolom gambar yang akan disimpan.
     */
    private function simpanGambar(Request $request, ?Product $product): ?string
    {
        if ($request->boolean('hapus_gambar')) {
            if ($product?->gambar) {
                Storage::disk('public')->delete($product->gambar);
            }

            return null;
        }

        $berkas = $request->file('gambar');

        if (! $berkas instanceof UploadedFile) {
            return $product?->gambar;
        }

        $jalur = $berkas->store('produk', 'public');

        if ($product?->gambar) {
            Storage::disk('public')->delete($product->gambar);
        }

        return $jalur;
    }

    public function storeTier(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'min_qty' => ['required', 'integer', 'min:2', 'max:100000'],
            'harga_satuan' => ['required', 'integer', 'min:1', 'max:100000000'],
        ], [
            'min_qty.required' => 'Ambang jumlah wajib diisi.',
            'min_qty.min' => 'Ambang grosir minimal 2 bungkus.',
            'harga_satuan.required' => 'Harga grosir wajib diisi.',
            'harga_satuan.min' => 'Harga grosir minimal Rp1.',
        ]);

        if ($product->priceTiers()->where('min_qty', $data['min_qty'])->exists()) {
            throw ValidationException::withMessages([
                'min_qty' => 'Ambang jumlah ini sudah dipakai pada produk yang sama.',
            ]);
        }

        if ($data['harga_satuan'] >= $product->harga) {
            throw ValidationException::withMessages([
                'harga_satuan' => 'Harga grosir harus lebih rendah dari harga satuan Rp'.number_format($product->harga, 0, ',', '.').'.',
            ]);
        }

        $product->priceTiers()->create($data);

        return redirect()->route('admin.produk.edit', $product)
            ->with('success', 'Harga grosir '.$data['min_qty'].' bungkus ditambahkan.');
    }

    public function destroyTier(Product $product, ProductPriceTier $tier): RedirectResponse
    {
        abort_unless($tier->product_id === $product->id, 404);

        $tier->delete();

        return redirect()->route('admin.produk.edit', $product)->with('success', 'Harga grosir dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'nama_produk' => ['required', 'string', 'max:255'],
            'harga' => ['required', 'integer', 'min:1', 'max:100000000'],
            'stok' => ['required', 'integer', 'min:0', 'max:100000'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'status_aktif' => ['nullable', 'boolean'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'hapus_gambar' => ['nullable', 'boolean'],
        ], [
            'category_id.required' => 'Pilih kategori produk.',
            'category_id.exists' => 'Kategori yang dipilih tidak ditemukan.',
            'nama_produk.required' => 'Nama produk wajib diisi.',
            'harga.required' => 'Harga wajib diisi.',
            'harga.min' => 'Harga minimal Rp1.',
            'harga.max' => 'Harga maksimal Rp100.000.000.',
            'stok.required' => 'Stok wajib diisi.',
            'stok.max' => 'Stok maksimal 100.000 bungkus.',
            'deskripsi.max' => 'Keterangan maksimal 2000 karakter.',
            'gambar.image' => 'Berkas yang diunggah harus berupa gambar.',
            'gambar.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'gambar.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $data['status_aktif'] = $request->boolean('status_aktif');

        return Arr::except($data, ['gambar', 'hapus_gambar']);
    }

    private function uniqueSlug(string $nama, ?int $abaikanId = null): string
    {
        $dasar = Str::slug($nama) ?: 'produk';
        $slug = $dasar;
        $nomor = 2;

        while (Product::query()
            ->where('slug', $slug)
            ->when($abaikanId, fn ($query) => $query->where('id', '!=', $abaikanId))
            ->exists()) {
            $slug = $dasar.'-'.$nomor++;
        }

        return $slug;
    }
}
