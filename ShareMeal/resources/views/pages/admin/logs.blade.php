@extends('layouts.dashboard')

@section('content')
@php
    function getActionDetails($action) {
        return match($action) {
            'verify_approve' => ['label' => 'Verifikasi Disetujui', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200/50', 'icon' => 'check-circle'],
            'verify_reject' => ['label' => 'Verifikasi Ditolak', 'class' => 'bg-red-50 text-red-700 border-red-200/50', 'icon' => 'x-circle'],
            'warn_user' => ['label' => 'Peringatan User', 'class' => 'bg-orange-50 text-orange-600 border-orange-200/30', 'icon' => 'alert-triangle'],
            'block_user' => ['label' => 'User Diblokir', 'class' => 'bg-red-50 text-red-700 border-red-200/50', 'icon' => 'ban'],
            'unblock_user' => ['label' => 'Blokir Dibuka', 'class' => 'bg-green-50 text-green-700 border-green-200/50', 'icon' => 'check'],
            'education_create' => ['label' => 'Tambah Edukasi', 'class' => 'bg-blue-50 text-blue-700 border-blue-200/50', 'icon' => 'plus-circle'],
            'education_update' => ['label' => 'Update Edukasi', 'class' => 'bg-indigo-50 text-indigo-700 border-indigo-200/50', 'icon' => 'edit-3'],
            'education_delete' => ['label' => 'Hapus Edukasi', 'class' => 'bg-red-50 text-red-700 border-red-200/50', 'icon' => 'trash-2'],
            'report_dismiss' => ['label' => 'Laporan Diabaikan', 'class' => 'bg-gray-100 text-gray-750 border-gray-300/50', 'icon' => 'eye-off'],
            'report_warn' => ['label' => 'Peringatan Laporan', 'class' => 'bg-orange-50 text-orange-700 border-orange-200/50', 'icon' => 'alert-circle'],
            'report_block' => ['label' => 'Blokir Laporan', 'class' => 'bg-red-50 text-red-700 border-red-200/50', 'icon' => 'shield-alert'],
            'feedback_delete' => ['label' => 'Hapus Feedback', 'class' => 'bg-red-50 text-red-700 border-red-200/50', 'icon' => 'trash-2'],
            'feedback_update' => ['label' => 'Update Feedback', 'class' => 'bg-indigo-50 text-indigo-700 border-indigo-200/50', 'icon' => 'edit-3'],
            default => ['label' => ucwords(str_replace('_', ' ', $action)), 'class' => 'bg-gray-50 text-gray-700 border-gray-200/50', 'icon' => 'info']
        };
    }
@endphp

<div class="space-y-8" x-data="{ 
    isDetailOpen: false,
    selectedLog: {},
    
    openDetail(log) {
        this.selectedLog = log;
        this.isDetailOpen = true;
    }
}">
    <!-- Decorative Glow Top Right -->
    <div class="absolute top-0 right-0 w-[30rem] h-[30rem] bg-emerald-100/10 rounded-full blur-[120px] pointer-events-none z-0"></div>

    <!-- Title Header -->
    <div class="relative z-10 mb-12 reveal">
        <h1 class="text-5xl font-serif font-black text-luxury-forest leading-tight tracking-tight">Log Aktivitas Admin</h1>
        <p class="text-luxury-slate font-medium mt-3 text-lg leading-relaxed max-w-3xl">Jejak audit real-time atas seluruh tindakan administratif, moderasi akun, verifikasi dokumen, edukasi, dan penanganan laporan sistem.</p>
    </div>

    <!-- Filters & Search -->
    <div class="glass-card rounded-[2rem] p-6 mb-8 relative z-10 reveal">
        <form action="{{ route('admin.logs') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-center">
            <div class="relative flex-1 w-full">
                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-luxury-slate/60"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari aksi, detail, atau nama admin..." 
                       class="w-full pl-12 pr-4 py-4 border border-luxury-alabas/85 rounded-2xl focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-600 outline-none bg-white/40 font-medium text-luxury-forest placeholder:text-luxury-slate/40 transition duration-300">
            </div>
            <div class="flex gap-4 w-full md:w-auto shrink-0">
                <select name="action_type" class="flex-1 md:flex-none px-5 py-4 border border-luxury-alabas/85 rounded-2xl bg-white/40 text-xs font-black uppercase tracking-wider text-luxury-forest focus:ring-2 focus:ring-emerald-500/30 outline-none cursor-pointer" onchange="this.form.submit()">
                    <option value="all" {{ $actionType === 'all' ? 'selected' : '' }}>Semua Kategori</option>
                    <option value="verify" {{ $actionType === 'verify' ? 'selected' : '' }}>Verifikasi Akun</option>
                    <option value="user" {{ $actionType === 'user' ? 'selected' : '' }}>Moderasi User</option>
                    <option value="education" {{ $actionType === 'education' ? 'selected' : '' }}>Kelola Edukasi</option>
                    <option value="report" {{ $actionType === 'report' ? 'selected' : '' }}>Laporan Masalah</option>
                    <option value="feedback" {{ $actionType === 'feedback' ? 'selected' : '' }}>Feedback Pengguna</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Table Logs List -->
    <div class="glass-card rounded-[2.5rem] overflow-hidden border border-luxury-alabas/60 relative z-10 reveal shadow-xl shadow-emerald-950/[0.02]">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-luxury-alabas/80 bg-white/30">
                        <th class="px-8 py-6 text-[10px] font-black text-luxury-slate uppercase tracking-wider">Admin</th>
                        <th class="px-8 py-6 text-[10px] font-black text-luxury-slate uppercase tracking-wider">Tindakan</th>
                        <th class="px-8 py-6 text-[10px] font-black text-luxury-slate uppercase tracking-wider">Detail Kegiatan</th>
                        <th class="px-8 py-6 text-[10px] font-black text-luxury-slate uppercase tracking-wider">IP Address</th>
                        <th class="px-8 py-6 text-[10px] font-black text-luxury-slate uppercase tracking-wider">Waktu</th>
                        <th class="px-8 py-6 text-right text-[10px] font-black text-luxury-slate uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-luxury-alabas/50 bg-white/10">
                    @forelse($logs as $log)
                        @php
                            $actionConfig = getActionDetails($log->action);
                        @endphp
                        <tr class="hover:bg-white/40 transition duration-300 group">
                            <!-- Admin Profile -->
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 shrink-0 rounded-xl bg-gradient-to-tr from-[#174413] to-emerald-600 text-white flex items-center justify-center font-serif font-black text-sm border border-white shadow-sm">
                                        {{ substr($log->admin->name ?? 'A', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-serif font-bold text-sm text-luxury-forest leading-snug">{{ $log->admin->name ?? 'Admin ShareMeal' }}</div>
                                        <div class="text-[10px] font-semibold text-luxury-slate">{{ $log->admin->email ?? 'admin@sharemeal.com' }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Action Badge -->
                            <td class="px-8 py-5 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $actionConfig['class'] }}">
                                    <i data-lucide="{{ $actionConfig['icon'] }}" class="w-3.5 h-3.5 stroke-[2.5]"></i>
                                    {{ $actionConfig['label'] }}
                                </span>
                            </td>

                            <!-- Details description -->
                            <td class="px-8 py-5">
                                <p class="text-xs font-semibold text-luxury-forest line-clamp-1 max-w-[280px]" title="{{ $log->details }}">
                                    {{ $log->details }}
                                </p>
                            </td>

                            <!-- IP Address -->
                            <td class="px-8 py-5">
                                <span class="text-xs font-mono font-bold text-luxury-slate bg-white/50 border border-luxury-alabas/80 px-2.5 py-1 rounded-lg">
                                    {{ $log->ip_address ?? '127.0.0.1' }}
                                </span>
                            </td>

                            <!-- Timestamp -->
                            <td class="px-8 py-5 whitespace-nowrap">
                                <div class="text-xs font-bold text-luxury-forest">{{ $log->created_at->format('d M Y H:i') }}</div>
                                <div class="text-[10px] font-semibold text-luxury-slate mt-0.5">{{ $log->created_at->diffForHumans() }}</div>
                            </td>

                            <!-- Action Button -->
                            <td class="px-8 py-5 text-right whitespace-nowrap">
                                <button type="button" 
                                        @click="openDetail({
                                            admin_name: '{{ addslashes($log->admin->name ?? 'Admin ShareMeal') }}',
                                            admin_email: '{{ addslashes($log->admin->email ?? 'admin@sharemeal.com') }}',
                                            action_label: '{{ $actionConfig['label'] }}',
                                            action_class: '{{ $actionConfig['class'] }}',
                                            action_icon: '{{ $actionConfig['icon'] }}',
                                            details: '{{ addslashes($log->details) }}',
                                            ip_address: '{{ $log->ip_address ?? '127.0.0.1' }}',
                                            time: '{{ $log->created_at->format('d F Y - H:i:s') }}',
                                            relative_time: '{{ $log->created_at->diffForHumans() }}',
                                            target_id: '{{ $log->target_id ?? 'N/A' }}'
                                        })"
                                        class="flex items-center gap-1.5 ml-auto px-4 py-2 border border-luxury-alabas/85 hover:border-emerald-200 hover:bg-emerald-50 text-luxury-forest rounded-xl transition duration-300 text-[10px] font-black uppercase tracking-wider active:scale-95 cursor-pointer">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i> Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-20 text-center">
                                <div class="h-16 w-16 bg-gradient-to-tr from-[#174413]/5 to-emerald-55 text-luxury-forest rounded-2xl flex items-center justify-center mx-auto mb-4 border border-luxury-alabas/80 shadow-inner">
                                    <i data-lucide="scroll" class="w-8 h-8 text-luxury-forest"></i>
                                </div>
                                <h3 class="text-xl font-serif font-black text-luxury-forest mb-1">Log Tidak Ditemukan</h3>
                                <p class="text-xs text-luxury-slate font-medium max-w-sm mx-auto">Belum ada catatan aktivitas admin yang sesuai dengan kriteria penyaringan Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($logs->count() > 0)
        <div class="px-8 py-5 border-t border-luxury-alabas/50 flex items-center justify-between gap-4">
            <p class="text-xs text-luxury-slate font-medium">
                Menampilkan halaman <span class="font-black text-luxury-forest">{{ $page }}</span> dari <span class="font-black text-luxury-forest">{{ $totalPages }}</span> halaman
            </p>
            <div class="flex items-center gap-2">
                @if($page == 1)
                    <span class="px-4 py-2 border border-luxury-alabas/60 rounded-xl text-xs font-black text-luxury-slate/40 cursor-not-allowed bg-white/20">
                        <i data-lucide="chevron-left" class="w-3.5 h-3.5 inline"></i> Prev
                    </span>
                @else
                    <a href="?page={{ $page - 1 }}{{ $search ? '&search=' . urlencode($search) : '' }}{{ $actionType !== 'all' ? '&action_type=' . $actionType : '' }}"
                       class="px-4 py-2 border border-luxury-alabas/85 rounded-xl text-xs font-black text-luxury-forest hover:bg-emerald-50 hover:border-emerald-200 transition cursor-pointer">
                        <i data-lucide="chevron-left" class="w-3.5 h-3.5 inline"></i> Prev
                    </a>
                @endif

                @for($i = 1; $i <= $totalPages; $i++)
                <a href="?page={{ $i }}{{ $search ? '&search=' . urlencode($search) : '' }}{{ $actionType !== 'all' ? '&action_type=' . $actionType : '' }}"
                   class="w-9 h-9 flex items-center justify-center rounded-xl text-xs font-black transition cursor-pointer
                          {{ $page == $i ? 'bg-gradient-to-br from-[#174413] to-emerald-600 text-white shadow-md shadow-emerald-950/10' : 'border border-luxury-alabas/85 text-luxury-forest hover:bg-emerald-50' }}">{{ $i }}</a>
                @endfor

                @if($page == $totalPages)
                    <span class="px-4 py-2 border border-luxury-alabas/60 rounded-xl text-xs font-black text-luxury-slate/40 cursor-not-allowed bg-white/20">
                        Next <i data-lucide="chevron-right" class="w-3.5 h-3.5 inline"></i>
                    </span>
                @else
                    <a href="?page={{ $page + 1 }}{{ $search ? '&search=' . urlencode($search) : '' }}{{ $actionType !== 'all' ? '&action_type=' . $actionType : '' }}"
                       class="px-4 py-2 border border-luxury-alabas/85 rounded-xl text-xs font-black text-luxury-forest hover:bg-emerald-50 hover:border-emerald-200 transition cursor-pointer">
                        Next <i data-lucide="chevron-right" class="w-3.5 h-3.5 inline"></i>
                    </a>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- ===== DETAIL LOG MODAL ===== -->
    <div x-show="isDetailOpen"
         class="fixed inset-0 z-[110] flex items-center justify-center p-4 sm:p-6"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-[#0d1f0d]/60 backdrop-blur-md" @click="isDetailOpen = false"></div>

        <!-- Panel -->
        <div x-show="isDetailOpen"
             x-transition:enter="ease-out duration-500 delay-100"
             x-transition:enter-start="opacity-0 translate-y-10 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-10 scale-95"
             class="relative w-full max-w-lg bg-white rounded-[3rem] shadow-2xl z-10 overflow-hidden border border-white/70"
             @click.stop>

            <!-- Header gradient -->
            <div class="h-32 bg-gradient-to-br from-[#174413] via-emerald-700 to-emerald-55 relative overflow-hidden">
                <div class="absolute inset-0 opacity-20"
                     style="background-image: radial-gradient(circle at 20% 50%, white 1px, transparent 1px), radial-gradient(circle at 80% 20%, white 1px, transparent 1px); background-size: 40px 40px;"></div>
                <!-- Close -->
                <button type="button" @click="isDetailOpen = false"
                        class="absolute top-5 right-5 w-9 h-9 flex items-center justify-center rounded-full bg-white/20 hover:bg-white/30 text-white transition cursor-pointer border border-white/30">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
                <div class="absolute bottom-5 left-8 text-white">
                    <h3 class="text-xl font-serif font-black">Detail Log Aktivitas</h3>
                    <p class="text-[9px] uppercase tracking-widest text-emerald-100/80 font-bold">Audit Trails</p>
                </div>
            </div>

            <!-- Body -->
            <div class="p-8 space-y-6">
                <!-- Admin info card -->
                <div class="bg-gray-50 border border-luxury-alabas/80 p-5 rounded-[2rem] flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-emerald-700 text-white flex items-center justify-center font-serif font-black text-xl shadow-md">
                        <span x-text="selectedLog.admin_name ? selectedLog.admin_name.charAt(0) : 'A'"></span>
                    </div>
                    <div>
                        <span class="text-[9px] font-black text-luxury-gold uppercase tracking-widest block mb-0.5">Admin Pelaksana</span>
                        <h4 class="font-serif font-bold text-base text-luxury-forest" x-text="selectedLog.admin_name"></h4>
                        <p class="text-xs text-luxury-slate font-medium" x-text="selectedLog.admin_email"></p>
                    </div>
                </div>

                <!-- Log fields -->
                <div class="space-y-4">
                    <!-- Action -->
                    <div class="flex items-center justify-between py-3 border-b border-luxury-alabas/60">
                        <span class="text-[10px] font-black text-luxury-slate uppercase tracking-wider">Jenis Tindakan</span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border"
                              :class="selectedLog.action_class">
                            <i data-lucide="check-circle" class="w-3 h-3 stroke-[2.5]" x-show="selectedLog.action_icon === 'check-circle'"></i>
                            <i data-lucide="x-circle" class="w-3 h-3 stroke-[2.5]" x-show="selectedLog.action_icon === 'x-circle'"></i>
                            <i data-lucide="alert-triangle" class="w-3 h-3 stroke-[2.5]" x-show="selectedLog.action_icon === 'alert-triangle'"></i>
                            <i data-lucide="ban" class="w-3 h-3 stroke-[2.5]" x-show="selectedLog.action_icon === 'ban'"></i>
                            <i data-lucide="check" class="w-3 h-3 stroke-[2.5]" x-show="selectedLog.action_icon === 'check'"></i>
                            <i data-lucide="plus-circle" class="w-3 h-3 stroke-[2.5]" x-show="selectedLog.action_icon === 'plus-circle'"></i>
                            <i data-lucide="edit-3" class="w-3 h-3 stroke-[2.5]" x-show="selectedLog.action_icon === 'edit-3'"></i>
                            <i data-lucide="trash-2" class="w-3 h-3 stroke-[2.5]" x-show="selectedLog.action_icon === 'trash-2'"></i>
                            <i data-lucide="eye-off" class="w-3 h-3 stroke-[2.5]" x-show="selectedLog.action_icon === 'eye-off'"></i>
                            <i data-lucide="alert-circle" class="w-3 h-3 stroke-[2.5]" x-show="selectedLog.action_icon === 'alert-circle'"></i>
                            <i data-lucide="shield-alert" class="w-3 h-3 stroke-[2.5]" x-show="selectedLog.action_icon === 'shield-alert'"></i>
                            <i data-lucide="info" class="w-3 h-3 stroke-[2.5]" x-show="!['check-circle', 'x-circle', 'alert-triangle', 'ban', 'check', 'plus-circle', 'edit-3', 'trash-2', 'eye-off', 'alert-circle', 'shield-alert'].includes(selectedLog.action_icon)"></i>
                            <span x-text="selectedLog.action_label"></span>
                        </span>
                    </div>

                    <!-- Target ID -->
                    <div class="flex items-center justify-between py-3 border-b border-luxury-alabas/60">
                        <span class="text-[10px] font-black text-luxury-slate uppercase tracking-wider">Target ID</span>
                        <span class="text-xs font-bold text-luxury-forest" x-text="selectedLog.target_id"></span>
                    </div>

                    <!-- IP address -->
                    <div class="flex items-center justify-between py-3 border-b border-luxury-alabas/60">
                        <span class="text-[10px] font-black text-luxury-slate uppercase tracking-wider">IP Address</span>
                        <span class="text-xs font-mono font-bold text-luxury-forest bg-gray-55 border border-luxury-alabas/80 px-2 py-0.5 rounded-lg" x-text="selectedLog.ip_address"></span>
                    </div>

                    <!-- Timestamp -->
                    <div class="flex items-center justify-between py-3 border-b border-luxury-alabas/60">
                        <span class="text-[10px] font-black text-luxury-slate uppercase tracking-wider">Waktu Kejadian</span>
                        <div class="text-right">
                            <span class="text-xs font-bold text-luxury-forest block" x-text="selectedLog.time"></span>
                            <span class="text-[10px] font-semibold text-luxury-slate" x-text="selectedLog.relative_time"></span>
                        </div>
                    </div>
                    
                    <!-- Details description -->
                    <div class="space-y-2 pt-2">
                        <span class="text-[10px] font-black text-luxury-slate uppercase tracking-wider block">Catatan Detail</span>
                        <div class="bg-gray-50 border border-luxury-alabas/80 p-5 rounded-[2rem] text-xs font-medium text-luxury-forest leading-relaxed" x-text="selectedLog.details"></div>
                    </div>
                </div>

                <!-- Footer button -->
                <button type="button" @click="isDetailOpen = false"
                        class="w-full py-4 bg-gradient-to-r from-[#174413] to-emerald-600 hover:from-emerald-700 hover:to-green-700 text-white rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] transition active:scale-95 cursor-pointer shadow-md">
                    Tutup Detail
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });

    // Watch for Alpine changes to re-trigger Lucide icon loading in modal
    document.addEventListener('alpine:initialized', () => {
        Alpine.effect(() => {
            if (window.lucide) {
                setTimeout(() => window.lucide.createIcons(), 50);
            }
        });
    });
</script>
@endsection
