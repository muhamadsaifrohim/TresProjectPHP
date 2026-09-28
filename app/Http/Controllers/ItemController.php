<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    // GET /api/items — semua orang (termasuk yang belum login) boleh lihat
    public function index(Request $request)
    {
        $query = Item::query()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return response()->json($query->get());
    }

    // POST /api/items — harus login (user atau admin)
    public function store(Request $request)
    {
        $data = $request->validate([
            'status' => ['required', 'in:lost,found'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'contact' => ['required', 'string', 'max:255'],
            'image_url' => ['nullable', 'url'],
        ]);

        $item = Item::create([
            ...$data,
            'user_id' => $request->user()->id,
        ]);

        return response()->json($item, 201);
    }

    // PATCH /api/items/{item} — tandai selesai, khusus admin
    public function markResolved(Request $request, Item $item)
    {
        $this->authorizeAdmin($request);

        $item->update(['status' => 'selesai']);

        return response()->json($item);
    }

    // DELETE /api/items/{item} — khusus admin
    public function destroy(Request $request, Item $item)
    {
        $this->authorizeAdmin($request);

        $item->delete();

        return response()->json(['message' => 'Laporan dihapus']);
    }

    private function authorizeAdmin(Request $request): void
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Hanya admin yang boleh melakukan aksi ini.');
        }
    }
}