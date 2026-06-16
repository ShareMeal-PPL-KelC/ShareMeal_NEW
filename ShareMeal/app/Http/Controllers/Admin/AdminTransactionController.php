<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminTransactionController extends Controller
{
    public function adminTransactions(Request $request): View
    {
        $page = (int) $request->query('page', 1);
        $search = $request->query('search');
        $perPage = 10;

        $query = Order::with(['customer', 'mitra'])->latest();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('mitra', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $totalTransactions = $query->count();
        $totalPages = max(1, (int) ceil($totalTransactions / $perPage));

        if ($page < 1) {
            $page = 1;
        } elseif ($page > $totalPages) {
            $page = $totalPages;
        }

        $transactions = $query->skip(($page - 1) * $perPage)->take($perPage)->get();
        
        $totalSelesai = Order::where('status', 'completed')->count();
        $totalPending = Order::where('status', 'pending')->count();
        $gmvRaw = Order::where('status', 'completed')->sum('total_amount');

        if ($gmvRaw >= 1000000000) {
            $gmv = 'Rp ' . number_format($gmvRaw / 1000000000, 1, ',', '.') . 'M';
        } elseif ($gmvRaw >= 1000000) {
            $gmv = 'Rp ' . number_format($gmvRaw / 1000000, 1, ',', '.') . 'Jt';
        } else {
            $gmv = 'Rp ' . number_format($gmvRaw, 0, ',', '.');
        }

        $stats = [
            'total_transaksi' => Order::count(),
            'total_selesai' => $totalSelesai,
            'total_pending' => $totalPending,
            'gmv' => $gmv
        ];

        return view('pages.admin.transactions', $this->dashboardData('admin', 'Pemantauan Transaksi', 'Pantau seluruh aktivitas transaksi di platform ShareMeal') + [
            'transactions' => $transactions,
            'stats' => $stats,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search
        ]);
    }

    public function adminExportTransactionsCsv(): StreamedResponse
    {
        $allTransactions = Order::with(['customer', 'mitra'])->latest()->get();

        $filename = 'transaksi_sharemeal_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ];

        $callback = function () use ($allTransactions) {
            $file = fopen('php://output', 'w');

            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'ID Transaksi',
                'Konsumen',
                'Mitra',
                'Total (Rp)',
                'Status',
                'Tanggal',
                'Jam (WIB)',
            ]);

            foreach ($allTransactions as $trx) {
                $statusLabel = match ($trx->status) {
                    'completed' => 'Selesai',
                    'pending'   => 'Menunggu',
                    'cancelled' => 'Dibatalkan',
                    default     => $trx->status,
                };

                fputcsv($file, [
                    'TRX-' . str_pad($trx->id, 5, '0', STR_PAD_LEFT),
                    $trx->customer->name ?? '-',
                    $trx->mitra->name ?? '-',
                    $trx->total_amount,
                    $statusLabel,
                    $trx->created_at ? $trx->created_at->format('d/m/Y') : '-',
                    $trx->created_at ? $trx->created_at->format('H:i') : '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function adminReviews(): View
    {
        $reviews = Review::with(['customer', 'mitra.profile', 'order.items.product'])
            ->latest()
            ->paginate(15);

        $stats = [
            'total_reviews' => Review::count(),
            'avg_rating' => round(Review::avg('rating'), 1) ?: 0,
            'recent_reviews_count' => Review::where('created_at', '>=', now()->subDays(7))->count(),
        ];

        return view('pages.admin.reviews', $this->dashboardData('admin', 'Pemantauan Ulasan', 'Pantau kualitas layanan mitra melalui ulasan konsumen') + [
            'reviews' => $reviews,
            'stats' => $stats,
        ]);
    }
}
