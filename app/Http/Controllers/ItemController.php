<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // <-- Tambahan penting untuk ngatur file

class ItemController extends Controller
{
    // GET /api/items — semua orang (termasuk yang belum login) boleh lihat
    // Parameter opsional: status, search, page, per_page
    public function index(Request $request)
    {
        $query = Item::query()->latest()->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', (string) $request->query('status'));
        }

        if ($request->filled('search')) {
            $keyword = '%' . (string) $request->query('search') . '%';

            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', $keyword)
                    ->orWhere('location', 'like', $keyword)
                    ->orWhere('description', 'like', $keyword);
            });
        }

        // Jumlah per halaman: default 8, minimal 1, maksimal 50
        $perPage = min(max($request->integer('per_page', 8), 1), 50);

        return response()->json($query->paginate($perPage));
    }

    // POST /api/items — harus login (user atau admin)
    public function store(Request $request)
    {
        // 1. Ubah validasi: kita validasi field 'image' (file), BUKAN 'image_url' (teks)
        $data =$request->validate([
            'status' => ['required', 'in:lost,found'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'contact' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'], // Maks 2MB
        ]);

        $imageUrl = null;

        // 2. Cek apakah ada file yang di-upload
        if ($request->hasFile('image')) {
            // Simpan gambar ke folder storage/app/public/items
            $path =$request->file('image')->store('items', 'public');
            // Bikin URL lengkap (http://tres-project-api.test/storage/items/namagambar.jpg)
            $imageUrl = asset('storage/' .$path);
        }

        // 3. Hapus field 'image' dari array $data supaya nggak error saat di-insert ke DB
        unset($data['image']);

        // 4. Simpan ke database
        $item = Item::create([
            ...$data,
            'image_url' => $imageUrl, // URL fotonya masuk sini!
            'user_id' => $request->user()->id,
        ]);

        return response()->json($item, 201);
    }

    // PATCH /api/items/{item} — tandai selesai, khusus admin (dijaga middleware role:admin di routes)
    public function markResolved(Item $item)
    {
        $item->update(['status' => 'selesai']);

        return response()->json($item);
    }

    // DELETE /api/items/{item} — khusus admin
    public function destroy(Item $item)
    {
        // Opsional: Hapus file gambar dari server kalau barang dihapus
        if ($item->image_url) {
            $path = str_replace(asset('storage') . '/', '',$item->image_url);
            Storage::disk('public')->delete($path);
        }

        $item->delete();

        return response()->json(['message' => 'Laporan dihapus']);
    }
}
