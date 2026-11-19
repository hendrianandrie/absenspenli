<?php

namespace App\Http\Controllers\Perpus;

use App\Http\Controllers\Controller;
use App\Models\PerpusBuku;
use App\Models\PerpusPeminjaman;
use App\Models\PerpusPoinLog;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SirkulasiController extends Controller
{
    /**
     * Tampilkan data transaksi peminjaman & pengembalian
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'dipinjam');
        $search = trim($request->get('q', ''));
        $kelas = $request->get('kelas');

        $query = PerpusPeminjaman::with(['siswa', 'buku', 'petugas']);

        if ($status && $status !== 'all') {
            if ($status === 'terlambat') {
                $query->where('status', 'dipinjam')
                      ->where('tanggal_jatuh_tempo', '<', Carbon::today());
            } else {
                $query->where('status', $status);
            }
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_transaksi', 'like', "%{$search}%")
                  ->orWhereHas('siswa', function ($sq) use ($search) {
                      $sq->where('nama', 'like', "%{$search}%")
                         ->orWhere('nis', 'like', "%{$search}%");
                  })
                  ->orWhereHas('buku', function ($bq) use ($search) {
                      $bq->where('judul', 'like', "%{$search}%")
                         ->orWhere('kode_buku', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($kelas)) {
            $query->whereHas('siswa', function ($sq) use ($kelas) {
                $sq->where('kelas', $kelas);
            });
        }

        $peminjamans = $query->orderBy('tanggal_pinjam', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        $daftarKelas = Siswa::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');

        // Statistik Counter
        $today = Carbon::today();
        $stats = [
            'total_aktif' => PerpusPeminjaman::where('status', 'dipinjam')->count(),
            'total_terlambat' => PerpusPeminjaman::where('status', 'dipinjam')->where('tanggal_jatuh_tempo', '<', $today)->count(),
            'total_kembali_bulan_ini' => PerpusPeminjaman::where('status', 'kembali')->whereYear('tanggal_kembali', $today->year)->whereMonth('tanggal_kembali', $today->month)->count(),
            'total_peminjaman_all' => PerpusPeminjaman::count(),
        ];

        return view('perpus.sirkulasi.index', compact('peminjamans', 'daftarKelas', 'status', 'search', 'kelas', 'stats'));
    }

    /**
     * Halaman Meja Sirkulasi Kilat (Peminjaman Tanpa Kartu)
     */
    public function create()
    {
        $bukus = PerpusBuku::where('stok_tersedia', '>', 0)->orderBy('judul')->get();
        return view('perpus.sirkulasi.create', compact('bukus'));
    }

    /**
     * AJAX Live Search Siswa (Pencarian Cepat Nama / NIS Tanpa Kartu)
     */
    public function searchSiswa(Request $request)
    {
        $query = trim($request->get('q', ''));
        if (strlen($query) < 1) {
            return response()->json([]);
        }

        $siswas = Siswa::where(function ($q) use ($query) {
            $q->where('nama', 'like', "%{$query}%")
              ->orWhere('nis', 'like', "%{$query}%")
              ->orWhere('nisn', 'like', "%{$query}%");
        })
        ->orderBy('nama')
        ->limit(10)
        ->get();

        $today = Carbon::today();
        $result = $siswas->map(function ($s) use ($today) {
            // Hitung buku yang sedang dipinjam
            $activeLoans = PerpusPeminjaman::where('siswa_id', $s->id)->where('status', 'dipinjam')->count();
            $overdueLoans = PerpusPeminjaman::where('siswa_id', $s->id)->where('status', 'dipinjam')->where('tanggal_jatuh_tempo', '<', $today)->count();
            
            // Total Poin Literasi
            $totalPoin = PerpusPoinLog::where('siswa_id', $s->id)->sum('poin');

            return [
                'id' => $s->id,
                'nama' => $s->nama,
                'nis' => $s->nis,
                'kelas' => $s->kelas,
                'active_loans' => $activeLoans,
                'overdue_loans' => $overdueLoans,
                'total_poin' => (int) $totalPoin,
            ];
        });

        return response()->json($result);
    }

    /**
     * AJAX Live Search Buku (Pencarian Cepat Judul / Kode Buku)
     */
    public function searchBuku(Request $request)
    {
        $query = trim($request->get('q', ''));
        if (strlen($query) < 1) {
            return response()->json([]);
        }

        $bukus = PerpusBuku::with('kategori')
            ->where(function ($q) use ($query) {
                $q->where('judul', 'like', "%{$query}%")
                  ->orWhere('kode_buku', 'like', "%{$query}%")
                  ->orWhere('isbn', 'like', "%{$query}%");
            })
            ->orderBy('judul')
            ->limit(10)
            ->get();

        $result = $bukus->map(function ($b) {
            return [
                'id' => $b->id,
                'kode_buku' => $b->kode_buku,
                'judul' => $b->judul,
                'pengarang' => $b->pengarang ?? '-',
                'nomor_rak' => $b->nomor_rak ?? 'Umum',
                'kategori' => optional($b->kategori)->nama_kategori ?? '-',
                'stok_tersedia' => $b->stok_tersedia,
                'is_available' => $b->stok_tersedia > 0,
            ];
        });

        return response()->json($result);
    }

    /**
     * Simpan Transaksi Peminjaman Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'buku_id' => 'required|exists:perpus_bukus,id',
            'durasi_hari' => 'nullable|integer|min:1|max:30',
            'catatan' => 'nullable|string|max:255',
        ]);

        $buku = PerpusBuku::findOrFail($request->buku_id);

        if ($buku->stok_tersedia < 1) {
            return back()->with('error', 'Maaf, stok eksemplar buku ini sedang habis dipinjam!');
        }

        // Cek batas maksimal pinjam (maksimal 3 buku per siswa)
        $activeBorrowCount = PerpusPeminjaman::where('siswa_id', $request->siswa_id)
            ->where('status', 'dipinjam')
            ->count();

        if ($activeBorrowCount >= 3) {
            return back()->with('error', 'Siswa ini sudah meminjam batas maksimal 3 buku. Mohon kembalikan buku sebelumnya terlebih dahulu.');
        }

        $durasiHari = (int) ($request->durasi_hari ?? 7);
        $tglPinjam = Carbon::today();
        $tglJatuhTempo = Carbon::today()->addDays($durasiHari);

        DB::transaction(function () use ($request, $buku, $tglPinjam, $tglJatuhTempo) {
            // Catat peminjaman
            PerpusPeminjaman::create([
                'kode_transaksi' => PerpusPeminjaman::generateKodeTransaksi(),
                'siswa_id' => $request->siswa_id,
                'buku_id' => $buku->id,
                'tanggal_pinjam' => $tglPinjam,
                'tanggal_jatuh_tempo' => $tglJatuhTempo,
                'status' => 'dipinjam',
                'petugas_id' => auth()->id(),
                'catatan' => $request->catatan,
            ]);

            // Kurangi stok tersedia
            $buku->decrement('stok_tersedia');
        });

        return redirect()->route('perpus.sirkulasi.index')->with('success', 'Peminjaman buku berhasil dicatat! Batas waktu pengembalian: ' . $tglJatuhTempo->translatedFormat('d F Y'));
    }

    /**
     * Proses Pengembalian Buku
     */
    public function kembalikan(Request $request, PerpusPeminjaman $peminjaman)
    {
        if ($peminjaman->status === 'kembali') {
            return back()->with('info', 'Buku ini sudah tercatat dikembalikan sebelumnya.');
        }

        $tglKembali = Carbon::today();
        $isTepatWaktu = $tglKembali->lte($peminjaman->tanggal_jatuh_tempo);
        $denda = (float) ($request->denda ?? 0);

        DB::transaction(function () use ($peminjaman, $tglKembali, $isTepatWaktu, $denda, $request) {
            // Update status peminjaman
            $peminjaman->update([
                'tanggal_kembali' => $tglKembali,
                'status' => 'kembali',
                'denda' => $denda,
                'catatan' => $request->catatan ?: $peminjaman->catatan,
            ]);

            // Kembalikan stok buku
            $peminjaman->buku->increment('stok_tersedia');

            // Skema Poin Literasi:
            if ($isTepatWaktu) {
                // Berikan +10 Poin Literasi untuk pengembalian tepat waktu
                PerpusPoinLog::catatPoin(
                    $peminjaman->siswa_id,
                    'pinjam_tepat_waktu',
                    10,
                    $peminjaman->id,
                    'Pengembalian tepat waktu: ' . $peminjaman->buku->judul
                );
            } else {
                // Pengembalian terlambat: penalti -5 poin
                PerpusPoinLog::catatPoin(
                    $peminjaman->siswa_id,
                    'terlambat',
                    -5,
                    $peminjaman->id,
                    'Keterlambatan pengembalian: ' . $peminjaman->buku->judul
                );
            }
        });

        $pesan = $isTepatWaktu 
            ? 'Buku berhasil dikembalikan tepat waktu! Siswa mendapatkan +10 Poin Literasi.'
            : 'Buku berhasil dikembalikan (Terlambat). Status poin dan sirkulasi telah diperbarui.';

        return back()->with('success', $pesan);
    }

    /**
     * Perpanjang Masa Peminjaman (Default +7 hari)
     */
    public function perpanjang(PerpusPeminjaman $peminjaman)
    {
        if ($peminjaman->status !== 'dipinjam') {
            return back()->with('error', 'Hanya peminjaman aktif yang dapat diperpanjang.');
        }

        $newDueDate = Carbon::parse($peminjaman->tanggal_jatuh_tempo)->addDays(7);
        $peminjaman->update([
            'tanggal_jatuh_tempo' => $newDueDate,
            'catatan' => trim($peminjaman->catatan . ' (Diperpanjang 7 hari)'),
        ]);

        return back()->with('success', 'Masa pinjam berhasil diperpanjang sampai: ' . $newDueDate->translatedFormat('d F Y'));
    }
}
