<?php

namespace App\Http\Controllers\Perpus;

use App\Http\Controllers\Controller;
use App\Models\PerpusBuku;
use App\Models\PerpusKategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BukuController extends Controller
{
    /**
     * Tampilkan daftar katalog buku (Admin & Petugas Perpustakaan)
     */
    public function index(Request $request)
    {
        $search = trim($request->get('q', ''));
        $kategoriId = $request->get('kategori_id');
        $statusStok = $request->get('status_stok');

        $query = PerpusBuku::with('kategori');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('kode_buku', 'like', "%{$search}%")
                  ->orWhere('pengarang', 'like', "%{$search}%")
                  ->orWhere('penerbit', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%")
                  ->orWhere('nomor_rak', 'like', "%{$search}%");
            });
        }

        if ($kategoriId) {
            $query->where('kategori_id', $kategoriId);
        }

        if ($statusStok === 'tersedia') {
            $query->where('stok_tersedia', '>', 0);
        } elseif ($statusStok === 'habis') {
            $query->where('stok_tersedia', '<=', 0);
        }

        $bukus = $query->orderBy('judul')->paginate(15)->withQueryString();
        $kategoris = PerpusKategori::orderBy('nama_kategori')->get();

        $stats = [
            'total_judul' => PerpusBuku::count(),
            'total_eksemplar' => PerpusBuku::sum('stok_total'),
            'total_tersedia' => PerpusBuku::sum('stok_tersedia'),
            'total_kategori' => PerpusKategori::count(),
        ];

        return view('perpus.buku.index', compact('bukus', 'kategoris', 'search', 'kategoriId', 'statusStok', 'stats'));
    }

    /**
     * Simpan buku baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_buku' => 'required|string|max:50|unique:perpus_bukus,kode_buku',
            'judul' => 'required|string|max:255',
            'kategori_id' => 'nullable|exists:perpus_kategoris,id',
            'pengarang' => 'nullable|string|max:150',
            'penerbit' => 'nullable|string|max:150',
            'tahun_terbit' => 'nullable|string|max:10',
            'isbn' => 'nullable|string|max:30',
            'nomor_rak' => 'nullable|string|max:50',
            'stok_total' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('perpus/covers', 'public');
        }

        $stokTotal = (int) $request->stok_total;

        PerpusBuku::create([
            'kode_buku' => strtoupper(trim($request->kode_buku)),
            'judul' => trim($request->judul),
            'kategori_id' => $request->kategori_id,
            'pengarang' => $request->pengarang,
            'penerbit' => $request->penerbit,
            'tahun_terbit' => $request->tahun_terbit,
            'isbn' => $request->isbn,
            'nomor_rak' => $request->nomor_rak,
            'stok_total' => $stokTotal,
            'stok_tersedia' => $stokTotal,
            'deskripsi' => $request->deskripsi,
            'cover_image' => $coverPath,
        ]);

        return redirect()->route('perpus.buku.index')->with('success', 'Buku baru berhasil ditambahkan ke katalog perpustakaan!');
    }

    /**
     * Update buku
     */
    public function update(Request $request, PerpusBuku $buku)
    {
        $request->validate([
            'kode_buku' => 'required|string|max:50|unique:perpus_bukus,kode_buku,' . $buku->id,
            'judul' => 'required|string|max:255',
            'kategori_id' => 'nullable|exists:perpus_kategoris,id',
            'pengarang' => 'nullable|string|max:150',
            'penerbit' => 'nullable|string|max:150',
            'tahun_terbit' => 'nullable|string|max:10',
            'isbn' => 'nullable|string|max:30',
            'nomor_rak' => 'nullable|string|max:50',
            'stok_total' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        $coverPath = $buku->cover_image;
        if ($request->hasFile('cover_image')) {
            if ($coverPath && Storage::disk('public')->exists($coverPath)) {
                Storage::disk('public')->delete($coverPath);
            }
            $coverPath = $request->file('cover_image')->store('perpus/covers', 'public');
        }

        // Sesuaikan stok tersedia jika stok total berubah
        $diff = (int) $request->stok_total - $buku->stok_total;
        $newTersedia = max(0, $buku->stok_tersedia + $diff);

        $buku->update([
            'kode_buku' => strtoupper(trim($request->kode_buku)),
            'judul' => trim($request->judul),
            'kategori_id' => $request->kategori_id,
            'pengarang' => $request->pengarang,
            'penerbit' => $request->penerbit,
            'tahun_terbit' => $request->tahun_terbit,
            'isbn' => $request->isbn,
            'nomor_rak' => $request->nomor_rak,
            'stok_total' => (int) $request->stok_total,
            'stok_tersedia' => $newTersedia,
            'deskripsi' => $request->deskripsi,
            'cover_image' => $coverPath,
        ]);

        return redirect()->route('perpus.buku.index')->with('success', 'Data buku berhasil diperbarui!');
    }

    /**
     * Hapus buku
     */
    public function destroy(PerpusBuku $buku)
    {
        $hasActiveBorrow = $buku->peminjamans()->where('status', 'dipinjam')->exists();
        if ($hasActiveBorrow) {
            return back()->with('error', 'Buku tidak dapat dihapus karena masih ada eksemplar yang sedang dipinjam oleh siswa.');
        }

        if ($buku->cover_image && Storage::disk('public')->exists($buku->cover_image)) {
            Storage::disk('public')->delete($buku->cover_image);
        }

        $buku->delete();

        return redirect()->route('perpus.buku.index')->with('success', 'Buku berhasil dihapus dari katalog perpustakaan.');
    }

    /**
     * Manajemen Kategori Buku
     */
    public function kategoriIndex()
    {
        $kategoris = PerpusKategori::withCount('bukus')->orderBy('nama_kategori')->get();
        return view('perpus.kategori.index', compact('kategoris'));
    }

    public function kategoriStore(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:perpus_kategoris,nama_kategori',
            'kode_kategori' => 'nullable|string|max:20|unique:perpus_kategoris,kode_kategori',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        PerpusKategori::create([
            'nama_kategori' => trim($request->nama_kategori),
            'kode_kategori' => $request->kode_kategori ? strtoupper(trim($request->kode_kategori)) : null,
            'deskripsi' => $request->deskripsi,
        ]);

        return back()->with('success', 'Kategori buku berhasil ditambahkan!');
    }

    public function kategoriUpdate(Request $request, PerpusKategori $kategori)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:perpus_kategoris,nama_kategori,' . $kategori->id,
            'kode_kategori' => 'nullable|string|max:20|unique:perpus_kategoris,kode_kategori,' . $kategori->id,
            'deskripsi' => 'nullable|string|max:255',
        ]);

        $kategori->update([
            'nama_kategori' => trim($request->nama_kategori),
            'kode_kategori' => $request->kode_kategori ? strtoupper(trim($request->kode_kategori)) : null,
            'deskripsi' => $request->deskripsi,
        ]);

        return back()->with('success', 'Kategori buku berhasil diperbarui!');
    }

    public function kategoriDestroy(PerpusKategori $kategori)
    {
        if ($kategori->bukus()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh buku di katalog.');
        }

        $kategori->delete();
        return back()->with('success', 'Kategori buku berhasil dihapus!');
    }
}
