<?php

namespace App\Http\Controllers;

use App\Support\ShareMealState;
use Carbon\Carbon;

abstract class Controller
{
    protected function currentUser(): array
    {
        return ShareMealState::currentUser();
    }

    protected function dashboardNavigation(string $type): array
    {
        return match ($type) {
            'mitra' => [
                ['label' => 'Dashboard', 'route' => 'mitra.dashboard', 'icon' => 'layout-dashboard'],
                ['label' => 'Inventaris', 'route' => 'mitra.inventory', 'icon' => 'package'],
                ['label' => 'Pesanan', 'route' => 'mitra.orders', 'icon' => 'shopping-cart'],
                ['label' => 'Riwayat', 'route' => 'mitra.history', 'icon' => 'history'],
                ['label' => 'Donasi', 'route' => 'mitra.donations', 'icon' => 'heart'],
            ],
            'consumer' => [
                ['label' => 'Dashboard', 'route' => 'consumer.dashboard', 'icon' => 'layout-dashboard'],
                ['label' => 'Cari Makanan', 'route' => 'consumer.search', 'icon' => 'search'],
                ['label' => 'Riwayat', 'route' => 'consumer.history', 'icon' => 'history'],
                ['label' => 'Edukasi', 'route' => 'consumer.education', 'icon' => 'book-open'],
            ],
            'lembaga' => [
                ['label' => 'Dashboard', 'route' => 'lembaga.dashboard', 'icon' => 'layout-dashboard'],
                ['label' => 'Donasi', 'route' => 'lembaga.donations', 'icon' => 'heart'],
                ['label' => 'Riwayat Donasi', 'route' => 'lembaga.history', 'icon' => 'history'],
            ],
            'admin' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'layout-dashboard'],
                ['label' => 'Verifikasi', 'route' => 'admin.verification', 'icon' => 'shield'],
                ['label' => 'Kelola User', 'route' => 'admin.users', 'icon' => 'users'],
                ['route' => 'admin.problem-reports.index', 'label' => 'Laporan Masalah', 'icon' => 'alert-triangle'],
                ['label' => 'Transaksi', 'route' => 'admin.transactions', 'icon' => 'shopping-cart'],
                ['label' => 'Laporan', 'route' => 'admin.reports', 'icon' => 'bar-chart'],
                ['label' => 'Edukasi', 'route' => 'admin.education', 'icon' => 'book-open'],
                ['label' => 'Log Admin', 'route' => 'admin.logs', 'icon' => 'activity'],
            ],
            default => [],
        };
    }

    protected function dashboardData(string $type, string $title, string $subtitle): array
    {
        $user = $this->currentUser();

        return [
            'user' => $user,
            'shell' => [
                'type' => $type,
                'title' => $title,
                'subtitle' => $subtitle,
                'userName' => (isset($user['type']) && $user['type'] === $type) ? $user['name'] : match ($type) {
                    'mitra' => 'Toko Roti Barokah',
                    'consumer' => 'Budi Santoso',
                    'lembaga' => 'Yayasan Peduli Anak',
                    'admin' => 'Admin ShareMeal',
                    default => 'ShareMeal',
                },
                'navigation' => $this->dashboardNavigation($type),
            ],
        ];
    }

    protected function parseLocalDateTime(string $value): Carbon
    {
        return Carbon::createFromFormat('Y-m-d\TH:i', $value, config('app.timezone'));
    }
}
