<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Order;
use App\Models\ProblemReport;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\VerificationApplication;
use App\Support\ShareMealState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function adminDashboard(): View
    {
        $activities = collect();

        // 1. New user registrations (excluding admin)
        $newUsers = User::where('role', '!=', 'admin')->latest()->take(5)->get();
        foreach ($newUsers as $user) {
            $roleLabel = match($user->role) {
                'mitra' => 'Mitra Toko',
                'lembaga' => 'Lembaga Sosial',
                'consumer' => 'Konsumen',
                default => $user->role
            };
            $activities->push([
                'title' => $user->name,
                'description' => 'Registrasi akun baru sebagai ' . $roleLabel,
                'time' => $user->created_at ? $user->created_at->diffForHumans() : '-',
                'type' => 'info',
                'icon' => 'user-plus',
                'timestamp' => $user->created_at
            ]);
        }

        // 2. New verification applications
        $newApps = VerificationApplication::latest()->take(5)->get();
        foreach ($newApps as $app) {
            $typeLabel = $app->type === 'mitra' ? 'Mitra Toko' : 'Lembaga Sosial';
            $statusLabel = match($app->status) {
                'pending' => 'Menunggu verifikasi dokumen',
                'approved' => 'Dokumen verifikasi disetujui',
                'rejected' => 'Dokumen verifikasi ditolak',
                default => $app->status
            };
            $type = match($app->status) {
                'approved' => 'success',
                'rejected' => 'danger',
                default => 'warning'
            };
            $icon = match($app->status) {
                'approved' => 'check-circle',
                'rejected' => 'x-circle',
                default => 'clock'
            };
            $activities->push([
                'title' => $app->name,
                'description' => $statusLabel . ' (' . $typeLabel . ')',
                'time' => $app->created_at ? $app->created_at->diffForHumans() : '-',
                'type' => $type,
                'icon' => $icon,
                'timestamp' => $app->created_at
            ]);
        }

        // 3. New problem reports
        $newReports = ProblemReport::with('reporter')->latest()->take(5)->get();
        foreach ($newReports as $report) {
            $reporterName = $report->reporter ? $report->reporter->name : 'Pengguna';
            $statusLabel = match($report->status) {
                'pending' => 'Laporan masalah baru diajukan oleh ' . $reporterName,
                'resolved' => 'Laporan masalah diselesaikan oleh Admin',
                default => 'Laporan masalah status: ' . $report->status
            };
            $type = $report->status === 'resolved' ? 'success' : 'danger';
            $icon = $report->status === 'resolved' ? 'check-circle' : 'alert-circle';
            
            $activities->push([
                'title' => 'Laporan Masalah: ' . $report->issue_label,
                'description' => $statusLabel . ' - "' . $report->description . '"',
                'time' => $report->created_at ? $report->created_at->diffForHumans() : '-',
                'type' => $type,
                'icon' => $icon,
                'timestamp' => $report->created_at
            ]);
        }

        // 4. User profile updates
        $profileUpdates = UserProfile::with('user')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        foreach ($profileUpdates as $profile) {
            if (!$profile->user) {
                continue;
            }
            $roleLabel = match($profile->user->role) {
                'mitra' => 'Mitra Toko',
                'lembaga' => 'Lembaga Sosial',
                'consumer' => 'Konsumen',
                default => $profile->user->role
            };
            $activities->push([
                'title' => $profile->user->name,
                'description' => 'Memperbarui informasi profil ' . $roleLabel,
                'time' => $profile->updated_at ? $profile->updated_at->diffForHumans() : '-',
                'type' => 'success',
                'icon' => 'user-cog',
                'timestamp' => $profile->updated_at
            ]);
        }

        // Sort all by timestamp descending, take top 8, and transform
        $activities = $activities->sortByDesc('timestamp')->take(8)->values()->all();

        $applications = ShareMealState::get('applications');

        $totalUser = User::count();
        $mitraAktif = User::where('role', 'mitra')->count();
        $lembagaAktif = User::where('role', 'lembaga')->count();
        $totalTransaksi = Order::count();
        
        $makananSavedRaw = Order::where('status', 'completed')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->sum('order_items.quantity');
            
        $co2Raw = $makananSavedRaw * 2.5;
        $gmvRaw = Order::where('status', 'completed')->sum('total_amount');

        if ($makananSavedRaw >= 1000) {
            $makanan_saved = number_format($makananSavedRaw / 1000, 1, ',', '.') . 'k';
        } else {
            $makanan_saved = number_format($makananSavedRaw, 0, ',', '.');
        }

        if ($gmvRaw >= 1000000) {
            $gmv_platform = 'Rp ' . number_format($gmvRaw / 1000000, 1, ',', '.') . 'Jt';
        } else {
            $gmv_platform = 'Rp ' . number_format($gmvRaw, 0, ',', '.');
        }

        return view('pages.admin.dashboard', $this->dashboardData('admin', 'Dashboard Admin', 'Kelola sistem, verifikasi akun, dan moderasi platform') + [
            'applications' => $applications,
            'activities' => $activities,
            'stats' => [
                'total_user' => $totalUser,
                'pending' => count($applications),
                'mitra_aktif' => $mitraAktif,
                'lembaga_aktif' => $lembagaAktif,
                'transaksi' => $totalTransaksi,
                'makanan_saved' => $makanan_saved,
                'co2_dikurangi' => number_format($co2Raw, 0, ',', '.'),
                'gmv_platform' => $gmv_platform,
            ]
        ]);
    }

    public function adminReports(Request $request): View
    {
        $stats = [
            'total_food_saved' => '12.480 Kg',
            'co2_reduction' => '31.200 Kg',
            'meals_distributed' => '8.240',
            'impact_value' => 'Rp 245.8M',
            'waste_reduction_rate' => 24.5,
        ];

        $monthlyData = [
            ['month' => 'Jan', 'saved' => 850, 'target' => 1000],
            ['month' => 'Feb', 'saved' => 1200, 'target' => 1000],
            ['month' => 'Mar', 'saved' => 1500, 'target' => 1000],
            ['month' => 'Apr', 'saved' => 1800, 'target' => 1000],
            ['month' => 'Mei', 'saved' => 2100, 'target' => 1000],
        ];

        $distributions = collect([
            (object)[
                'id' => 1,
                'mitra' => 'Toko Roti Sejahtera',
                'lembaga' => 'Yayasan Kasih Ibu',
                'items' => 'Roti Manis, Brownies',
                'quantity' => '25 Kg',
                'type' => 'Donasi',
                'status' => 'Diterima',
                'date' => now()->subDays(1)->format('d M Y')
            ],
            (object)[
                'id' => 2,
                'mitra' => 'Warung Makan Barokah',
                'lembaga' => 'Panti Asuhan Al-Falah',
                'items' => 'Nasi Bungkus, Lauk Pauk',
                'quantity' => '15 Kg',
                'type' => 'Donasi',
                'status' => 'Diterima',
                'date' => now()->subDays(2)->format('d M Y')
            ],
            (object)[
                'id' => 3,
                'mitra' => 'Healthy Cafe',
                'lembaga' => '-',
                'items' => 'Salad Bowl, Juice',
                'quantity' => '8 Kg',
                'type' => 'Flash Sale',
                'status' => 'Terjual',
                'date' => now()->subDays(3)->format('d M Y')
            ],
            (object)[
                'id' => 4,
                'mitra' => 'Bakery Delight',
                'lembaga' => 'Rumah Singgah',
                'items' => 'Croissant, Danish',
                'quantity' => '12 Kg',
                'type' => 'Donasi',
                'status' => 'Dalam Perjalanan',
                'date' => now()->subDays(1)->format('d M Y')
            ],
            (object)[
                'id' => 5,
                'mitra' => 'Resto Sedap Malam',
                'lembaga' => 'Yayasan Yatim Piatu',
                'items' => 'Ayam Bakar, Nasi',
                'quantity' => '30 Kg',
                'type' => 'Donasi',
                'status' => 'Diterima',
                'date' => now()->subDays(4)->format('d M Y')
            ],
        ]);

        return view('pages.admin.reports', $this->dashboardData('admin', 'Laporan Distribusi & Dampak', 'Evaluasi pengurangan food waste dan dampak sosial platform') + [
            'stats' => $stats,
            'monthlyData' => $monthlyData,
            'distributions' => $distributions,
        ]);
    }

    public function adminExportReportsExcel()
    {
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="Laporan_Distribusi_ShareMeal.xls"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['Laporan Penyaluran & Distribusi ShareMeal']);
            fputcsv($file, []);
            fputcsv($file, ['Mitra', 'Lembaga', 'Item Makanan', 'Jumlah', 'Tipe', 'Status', 'Tanggal']);
            
            $distributions = [
                ['Toko Roti Sejahtera', 'Yayasan Kasih Ibu', 'Roti Manis, Brownies', '25 Kg', 'Donasi', 'Diterima', '2026-03-31'],
                ['Warung Makan Barokah', 'Panti Asuhan Al-Falah', 'Nasi Bungkus, Lauk Pauk', '15 Kg', 'Donasi', 'Diterima', '2026-03-30'],
                ['Healthy Cafe', '-', 'Salad Bowl, Juice', '8 Kg', 'Flash Sale', 'Terjual', '2026-03-29'],
                ['Bakery Delight', 'Rumah Singgah', 'Croissant, Danish', '12 Kg', 'Donasi', 'Dalam Perjalanan', '2026-03-31'],
                ['Resto Sedap Malam', 'Yayasan Yatim Piatu', 'Ayam Bakar, Nasi', '30 Kg', 'Donasi', 'Diterima', '2026-03-28']
            ];
            
            foreach ($distributions as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function adminExportReportsPdf()
    {
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Laporan_Distribusi_ShareMeal.pdf"',
        ];

        $callback = function() {
            echo "%PDF-1.4\n";
            echo "1 0 obj <</Type /Catalog /Pages 2 0 R>> endobj\n";
            echo "2 0 obj <</Type /Pages /Kids [3 0 R] /Count 1>> endobj\n";
            echo "3 0 obj <</Type /Page /Parent 2 0 R /Resources <</Font <</F1 4 0 R>>>> /MediaBox [0 0 595 842] /Contents 5 0 R>> endobj\n";
            echo "4 0 obj <</Type /Font /Subtype /Type1 /BaseFont /Helvetica>> endobj\n";
            
            $content = "BT /F1 12 Tf 50 750 Td (Laporan Penyaluran & Distribusi ShareMeal) Tj ET\n";
            $content .= "BT /F1 10 Tf 50 720 Td (Total Makanan Terselamatkan: 12.480 Kg) Tj ET\n";
            $content .= "BT /F1 10 Tf 50 700 Td (Reduksi Emisi CO2: 31.200 Kg) Tj ET\n";
            $content .= "BT /F1 10 Tf 50 680 Td (Estimasi Nilai Ekonomi: Rp 245.8M) Tj ET\n";
            
            $len = strlen($content);
            echo "5 0 obj <</Length $len>> stream\n" . $content . "endstream\nendobj\n";
            echo "xref\n0 6\n0000000000 65535 f\n";
            echo "trailer <</Size 6 /Root 1 0 R>>\n";
            echo "startxref\n350\n%%EOF\n";
        };

        return response()->stream($callback, 200, $headers);
    }

    public function adminLogs(Request $request): View
    {
        $page = (int) $request->query('page', 1);
        $search = $request->query('search');
        $actionType = $request->query('action_type', 'all');
        $perPage = 15;

        $query = AdminLog::with('admin')->latest();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('details', 'like', "%{$search}%")
                  ->orWhereHas('admin', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($actionType && $actionType !== 'all') {
            if ($actionType === 'verify') {
                $query->whereIn('action', ['verify_approve', 'verify_reject']);
            } elseif ($actionType === 'user') {
                $query->whereIn('action', ['warn_user', 'block_user', 'unblock_user']);
            } elseif ($actionType === 'education') {
                $query->whereIn('action', ['education_create', 'education_update', 'education_delete']);
            } elseif ($actionType === 'report') {
                $query->whereIn('action', ['report_dismiss', 'report_warn', 'report_block']);
            } elseif ($actionType === 'feedback') {
                $query->whereIn('action', ['feedback_delete', 'feedback_update']);
            } else {
                $query->where('action', $actionType);
            }
        }

        $totalLogs = $query->count();
        $totalPages = max(1, (int) ceil($totalLogs / $perPage));
        if ($page < 1) { $page = 1; } elseif ($page > $totalPages) { $page = $totalPages; }

        $logs = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return view('pages.admin.logs', $this->dashboardData('admin', 'Log Aktivitas Admin', 'Jejak audit seluruh tindakan moderasi dan administrasi sistem') + [
            'logs' => $logs,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'actionType' => $actionType,
        ]);
    }
}
