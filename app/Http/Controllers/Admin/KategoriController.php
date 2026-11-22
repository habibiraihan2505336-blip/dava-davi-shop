<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KategoriController extends Controller
{
    public function index(): View
    {
        return view('admin.kategori.index', [
            'kategori' => Category::query()->withCount('products')->orderBy('nama_kategori')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255', Rule::unique('categories', 'nama_kategori')],
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique' => 'Kategori dengan nama ini sudah ada.',
        ]);

        $data['slug'] = $this->slugUnik($data['nama_kategori']);
        Category::create($data);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori ditambahkan.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255', Rule::unique('categories', 'nama_kategori')->ignore($category->id)],
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique' => 'Kategori dengan nama ini sudah ada.',
        ]);

        if ($data['nama_kategori'] !== $category->nama_kategori) {
            $data['slug'] = $this->slugUnik($data['nama_kategori'], $category->id);
        }

        $category->update($data);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return redirect()->route('admin.kategori.index')
                ->with('error', 'Kategori masih dipakai produk. Pindahkan produknya lebih dulu.');
        }

        $category->delete();

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori dihapus.');
    }

    private function slugUnik(string $nama, ?int $abaikanId = null): string
    {
        $dasar = Str::slug($nama) ?: 'kategori';
        $slug = $dasar;
        $nomor = 2;

        while (Category::query()
            ->where('slug', $slug)
            ->when($abaikanId, fn ($query) => $query->where('id', '!=', $abaikanId))
            ->exists()) {
            $slug = $dasar.'-'.$nomor++;
        }

        return $slug;
    }
}
