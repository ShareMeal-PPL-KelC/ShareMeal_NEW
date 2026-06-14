@extends('layouts.dashboard')

@section('content')
<div class="space-y-8" x-data="inventoryData()">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">Manajemen Inventaris Surplus</h1>
            <p class="text-gray-500 mt-2 text-sm font-medium">Kelola dan optimalkan stok makanan near-expired Anda dengan sistem klasifikasi otomatis.</p>
        </div>
        @if(auth()->user()->is_verified)
        <button @click="openAddDialog()" dusk="tambah-produk-btn" class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-[#174413] to-[#256020] hover:from-[#1b4e16] hover:to-[#2b6d25] text-white px-6 py-3.5 rounded-2xl font-black text-sm uppercase tracking-wider shadow-lg shadow-green-950/10 active:scale-95 transition-all duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Tambah Produk
        </button>
        @else
        <button disabled title="Akun Anda belum terverifikasi oleh admin." class="inline-flex items-center justify-center gap-2 bg-gray-300 text-gray-500 cursor-not-allowed px-6 py-3.5 rounded-2xl font-black text-sm uppercase tracking-wider shadow-none opacity-60">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            Tambah Produk
        </button>
        @endif
    </div>

    @if(!auth()->user()->is_verified)
    <div class="bg-amber-50/80 border border-amber-200/50 backdrop-blur-sm text-amber-800 px-6 py-4 rounded-2xl flex items-center gap-3.5 shadow-sm animate-fade-in">
        <div class="p-1.5 bg-amber-100/60 rounded-lg text-amber-600">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        </div>
        <span class="text-sm font-semibold text-left">Akun Anda belum terverifikasi oleh admin. Anda tidak dapat menambahkan produk baru ke inventaris saat ini.</span>
    </div>
    @endif

    <!-- Alert Banners -->
    @if(session('success'))
    <div class="bg-emerald-50/80 border border-emerald-200/50 backdrop-blur-sm text-emerald-800 px-6 py-4 rounded-2xl flex items-center gap-3.5 shadow-sm animate-fade-in">
        <div class="p-1.5 bg-emerald-100/60 rounded-lg text-emerald-600">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        </div>
        <span class="text-sm font-semibold">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50/80 border border-red-200/50 backdrop-blur-sm text-red-800 px-6 py-4 rounded-2xl flex items-center gap-3.5 shadow-sm animate-fade-in">
        <div class="p-1.5 bg-red-100/60 rounded-lg text-red-600">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
        </div>
        <span class="text-sm font-semibold">{{ session('error') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50/80 border border-red-200/50 backdrop-blur-sm text-red-800 px-6 py-4 rounded-2xl shadow-sm animate-fade-in">
        <ul class="list-disc list-inside text-sm font-semibold space-y-1.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Info Section (Eco-Glass Box) -->
    <div class="bg-gradient-to-r from-emerald-500/5 to-teal-500/10 backdrop-blur-md border border-white/40 p-8 rounded-[2rem] flex flex-col md:flex-row gap-6 shadow-sm">
        <div class="w-14 h-14 bg-emerald-100/60 rounded-2xl flex items-center justify-center text-emerald-700 flex-shrink-0 border border-emerald-200/30">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        </div>
        <div>
            <h3 class="text-xl font-bold text-[#174413] mb-2 uppercase tracking-wide">Sistem Klasifikasi Otomatis</h3>
            <p class="text-emerald-950/80 font-semibold leading-relaxed text-sm">
                Produk akan otomatis dikategorikan ke <span class="font-bold text-emerald-800">"Jual" (Flash Sale)</span> atau <span class="font-bold text-emerald-800">"Donasi"</span> berdasarkan waktu expired dan kelayakan. 
                Produk yang mendekati batas waktu namun masih layak konsumsi akan masuk sistem donasi untuk lembaga sosial.
            </p>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <template x-for="product in products" :key="product.id">
            <div class="rounded-3xl border backdrop-blur-md shadow-sm overflow-hidden group hover:scale-[1.02] hover:shadow-md transition-all duration-300 flex flex-col justify-between" 
                 :class="product.status === 'expired' || product.stock <= 0 ? 'bg-red-50/30 border-red-200/40' : (product.status === 'donation' ? 'bg-emerald-50/30 border-emerald-200/40' : 'bg-white/80 border-white/40')">
                
                <!-- Product Image Area -->
                <div class="relative h-48 overflow-hidden bg-gray-100">
                    <img :src="product.image" :alt="product.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :class="['donation', 'expired'].includes(product.status) || product.stock <= 0 ? 'opacity-60 grayscale' : ''">
                    
                    <!-- Badges -->
                    <template x-if="product.status === 'flash-sale' && product.stock > 0">
                        <span class="absolute top-4 right-4 bg-red-600 text-white px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-wider shadow-lg flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span>
                            Flash Sale
                        </span>
                    </template>
                    <template x-if="product.status === 'donation' && product.stock > 0">
                        <span class="absolute top-4 right-4 bg-emerald-600 text-white px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-wider shadow-lg flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                            Sudah Didonasikan
                        </span>
                    </template>
                    <template x-if="product.status === 'expired'">
                        <span class="absolute top-4 right-4 bg-red-700 text-white px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-wider shadow-lg">
                            Kedaluwarsa
                        </span>
                    </template>
                    <template x-if="product.status !== 'expired' && product.stock <= 0">
                        <span class="absolute top-4 right-4 bg-neutral-800 text-white px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-wider shadow-lg">
                            HABIS
                        </span>
                    </template>
                    <template x-if="product.status !== 'expired' && product.stock > 0 && product.stock < 5">
                        <span class="absolute top-4 left-4 bg-amber-500 text-white px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-wider shadow-lg flex items-center gap-1 animate-pulse">
                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="stroke-[3]"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
                            Stok Menipis
                        </span>
                    </template>
                </div>

                <!-- Product Details -->
                <div class="p-6 flex-1 flex flex-col justify-between">
                    <div>
                        <div class="mb-4">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest text-[#174413] bg-green-50 border border-green-100/50 mb-2" x-text="product.category"></span>
                            <h3 class="text-xl font-bold text-gray-900 leading-tight" x-text="product.name"></h3>
                        </div>

                        <div class="flex items-center gap-2 text-sm mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-orange-600"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            <span class="text-orange-600 font-bold uppercase text-[9px] tracking-wider" x-text="'Expired: ' + product.expires_at_display"></span>
                        </div>
                    </div>

                    <div>
                        <!-- Price & Stock Info -->
                        <div class="flex items-end justify-between border-t border-gray-100 pt-4 mb-6">
                            <div>
                                <template x-if="product.discount_price > 0">
                                    <div>
                                        <div class="text-2xl font-black text-emerald-700 leading-none" x-text="'Rp ' + parseInt(product.discount_price).toLocaleString('id-ID')"></div>
                                        <div class="text-xs text-gray-400 line-through mt-1.5" x-text="'Rp ' + parseInt(product.price).toLocaleString('id-ID')"></div>
                                    </div>
                                </template>
                                <template x-if="!(product.discount_price > 0)">
                                    <div class="text-2xl font-black text-gray-900 leading-none" x-text="'Rp ' + parseInt(product.price).toLocaleString('id-ID')"></div>
                                </template>
                            </div>
                            <div class="text-right">
                                <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Stok Tersedia</div>
                                <div class="text-xl font-black mt-1 flex items-center justify-end gap-1" :class="product.stock > 0 && product.stock < 5 ? 'text-amber-600' : 'text-gray-900'">
                                    <template x-if="product.stock > 0 && product.stock < 5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-amber-500 animate-bounce stroke-[3]"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
                                    </template>
                                    <span x-text="product.stock + ' Pcs'"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            <template x-if="product.status === 'normal' && product.stock > 0">
                                <button dusk="flash-sale-btn" @click="setFlashSale(product.id)" class="flex-1 bg-orange-50 hover:bg-orange-100 text-orange-700 py-3.5 px-4 rounded-xl font-black text-xs uppercase tracking-widest transition flex items-center justify-center gap-1.5 border border-orange-200/30">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                                    Flash Sale
                                </button>
                            </template>
                            <template x-if="product.status === 'flash-sale' && product.stock > 0">
                                <div class="flex-1 bg-emerald-50 text-emerald-700 py-3.5 rounded-xl font-black text-[10px] uppercase tracking-widest flex items-center justify-center gap-2 border border-emerald-100/50">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                    Aktif Jual
                                </div>
                            </template>
                            <template x-if="product.status === 'donation' && product.stock > 0">
                                <div class="flex-1 bg-emerald-100/60 text-emerald-800 py-3.5 rounded-xl font-black text-[10px] uppercase tracking-widest flex items-center justify-center gap-2 border border-emerald-200/30">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M20 12v10H4V12"></path><path d="M2 7h20v5H2z"></path><path d="M12 22V7"></path><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path></svg>
                                    Masuk Donasi
                                </div>
                            </template>
                            <template x-if="product.status === 'expired'">
                                <div class="flex-1 bg-red-50 text-red-700 px-3 py-3.5 rounded-xl font-black text-[10px] uppercase tracking-widest flex items-center justify-center gap-2 border border-red-200/20 text-center leading-tight">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 shrink-0"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                                    Tidak Layak
                                </div>
                            </template>
                            <template x-if="product.status !== 'expired' && product.stock <= 0">
                                <div class="flex-1 bg-gray-100 text-gray-500 px-3 py-3.5 rounded-xl font-black text-[10px] uppercase tracking-widest flex items-center justify-center gap-2 border border-gray-200 text-center leading-tight">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 shrink-0"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                                    HABIS
                                </div>
                            </template>

                            <!-- Donatable Auto Toggle -->
                            <button dusk="toggle-donation-btn" @click="toggleDonation(product.id)" 
                                    :class="product.status === 'expired' || product.stock <= 0 ? 'bg-gray-50 text-gray-300 border-gray-200 cursor-not-allowed opacity-50' : (product.donatable ? 'bg-emerald-100 hover:bg-emerald-200 text-emerald-700 border-emerald-200' : 'bg-gray-50 hover:bg-gray-100 text-gray-400 border-gray-200')" 
                                    :disabled="product.status === 'expired' || product.stock <= 0"
                                    class="w-12 h-12 rounded-xl flex items-center justify-center border transition" 
                                    :title="product.status === 'expired' || product.stock <= 0 ? 'Produk habis atau kedaluwarsa' : (product.donatable ? 'Donasi otomatis diaktifkan' : 'Aktifkan donasi otomatis')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l8.84-8.84 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                            </button>
                            
                            <!-- Edit Button -->
                            <button dusk="edit-produk-btn" @click="openEditDialog(product)" 
                                    :disabled="product.status === 'expired' || product.stock <= 0" 
                                    class="w-12 h-12 bg-gray-50 text-gray-400 border border-gray-200 rounded-xl flex items-center justify-center hover:bg-green-50 hover:text-green-700 hover:border-green-200 transition disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-gray-50 disabled:hover:text-gray-400 disabled:hover:border-gray-200">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            </button>

                            <!-- Delete Button -->
                            <button dusk="delete-produk-btn" @click="deleteProduct(product.id)" class="w-12 h-12 bg-gray-50 text-gray-400 border border-gray-200 rounded-xl flex items-center justify-center hover:bg-red-50 hover:text-red-700 hover:border-red-200 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2-2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>



    <!-- Add/Edit Product Modal -->
    <div x-show="isDialogOpen" class="fixed inset-0 z-[60] flex items-start justify-center overflow-y-auto p-4 bg-black/60 backdrop-blur-sm" x-cloak x-transition>
        <div class="relative my-auto bg-white/95 backdrop-blur-lg w-full max-w-lg rounded-[2rem] p-6 shadow-2xl space-y-4 border border-white/50" @click.away="isDialogOpen = false">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-black text-gray-900" x-text="isEditing ? 'Edit Produk' : 'Tambah Produk Baru'"></h3>
                <button type="button" @click="isDialogOpen = false" class="p-2 hover:bg-gray-100 rounded-full transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-gray-400"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <form :action="isEditing ? `/mitra/inventory/${formData.id}` : '/mitra/inventory'" method="POST" enctype="multipart/form-data" x-ref="productForm" class="space-y-5">
                @csrf
                <template x-if="isEditing">
                    <input type="hidden" name="_method" value="POST">
                </template>
                <input type="hidden" name="status" :value="formData.status">

                <!-- Image Upload Zone -->
                <div class="space-y-2">
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest block">Gambar Produk</label>
                    <div class="relative group">
                        <input type="file" name="image" accept="image/*" 
                               @change="
                                   const file = $el.files[0];
                                   if (file) {
                                       if (file.size > 2 * 1024 * 1024) {
                                           imageError = 'Ukuran gambar kebesaran! Maksimal ukuran gambar adalah 2MB.';
                                           $el.value = '';
                                       } else {
                                           imageError = '';
                                       }
                                   } else {
                                       imageError = '';
                                   }
                               "
                               class="w-full bg-gray-50/50 border border-gray-200/50 rounded-2xl p-4 outline-none focus:ring-2 focus:ring-[#174413] transition text-sm">
                    </div>
                    <p x-show="imageError" class="text-xs text-red-600 font-bold mt-1.5 flex items-center gap-1.5 animate-pulse">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        <span x-text="imageError"></span>
                    </p>
                    <p x-show="!imageError" class="text-[10px] text-gray-400 mt-1 italic">Unggah foto produk baru (maksimal 2MB. Kosongkan jika tidak ingin mengubah gambar).</p>
                </div>

                <!-- Product Name -->
                <div class="space-y-2">
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest block">Nama Produk</label>
                    <input type="text" name="name" x-model="formData.name" required placeholder="Contoh: Roti Tawar Gandum" class="w-full bg-gray-50/50 border border-gray-200/50 rounded-2xl p-4 outline-none focus:ring-2 focus:ring-[#174413] transition">
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <!-- Category -->
                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block">Kategori</label>
                        <select name="category" x-model="formData.category" class="w-full bg-gray-50/50 border border-gray-200/50 rounded-2xl p-4 outline-none focus:ring-2 focus:ring-[#174413] transition text-sm font-semibold">
                            <option>Bakery</option>
                            <option>Healthy</option>
                            <option>Meal</option>
                            <option>Snack</option>
                        </select>
                    </div>
                    <!-- Stock -->
                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block">Stok</label>
                        <input type="number" name="stock" x-model="formData.stock" required placeholder="20" class="w-full bg-gray-50/50 border border-gray-200/50 rounded-2xl p-4 outline-none focus:ring-2 focus:ring-[#174413] transition">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <!-- Normal Price -->
                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block">Harga Normal</label>
                        <input type="number" name="price" x-model="formData.price" required placeholder="15000" class="w-full bg-gray-50/50 border border-gray-200/50 rounded-2xl p-4 outline-none focus:ring-2 focus:ring-[#174413] transition">
                    </div>
                    <!-- Expiry Time -->
                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block">Waktu Expired</label>
                        <input type="datetime-local" name="expires_at" x-model="formData.expires_at" required class="w-full bg-gray-50/50 border border-gray-200/50 rounded-2xl p-4 outline-none focus:ring-2 focus:ring-[#174413] transition text-sm font-semibold">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <!-- Pickup Start Time -->
                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block">Jam Mulai Pengambilan</label>
                        <input type="time" name="pickup_start_time" x-model="formData.pickup_start_time" required class="w-full bg-gray-50/50 border border-gray-200/50 rounded-2xl p-4 outline-none focus:ring-2 focus:ring-[#174413] transition text-sm font-semibold">
                    </div>
                    <!-- Pickup End Time -->
                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest block">Jam Akhir Pengambilan</label>
                        <input type="time" name="pickup_end_time" x-model="formData.pickup_end_time" required class="w-full bg-gray-50/50 border border-gray-200/50 rounded-2xl p-4 outline-none focus:ring-2 focus:ring-[#174413] transition text-sm font-semibold">
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="pt-4 flex gap-4">
                    <button type="button" @click="isDialogOpen = false" class="flex-1 border border-gray-200 py-4 rounded-2xl font-bold text-gray-400 hover:bg-gray-50 transition">Batal</button>
                    <button type="submit" class="flex-1 bg-[#174413] hover:bg-[#256020] text-white py-4 rounded-2xl font-black shadow-xl shadow-green-100 transition active:scale-95">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden Forms for Actions -->
    <form id="flash-sale-form" method="POST" class="hidden">
        @csrf
    </form>

    <form id="toggle-donation-form" method="POST" class="hidden">
        @csrf
    </form>

    <form id="delete-form" method="POST" class="hidden">
        @csrf
    </form>
</div>

<script>
    function inventoryData() {
        return {
            products: @json($products),
            isDialogOpen: {{ $errors->any() ? 'true' : 'false' }},
            isEditing: false,
            imageError: '',
            formData: {
                id: null,
                name: '',
                category: 'Bakery',
                price: '',
                discount_price: 0,
                stock: '',
                expires_at: '',
                pickup_start_time: '{{ $defaultPickupStart }}',
                pickup_end_time: '{{ $defaultPickupEnd }}',
                status: 'normal',
                image: 'https://images.unsplash.com/photo-1666114170628-b34b0dcc21aa?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxiYWtlcnklMjBicmVhZCUyMHBhc3RyeSUyMHNob3B8ZW58MXx8fHwxNzc0OTc0Mzg5fDA&ixlib=rb-4.1.0&q=80&w=1080'
            },
            
            openAddDialog() {
                this.isEditing = false;
                this.imageError = '';
                this.formData = {
                    id: null,
                    name: '',
                    category: 'Bakery',
                    price: '',
                    discount_price: 0,
                    stock: '',
                    expires_at: '',
                    pickup_start_time: '{{ $defaultPickupStart }}',
                    pickup_end_time: '{{ $defaultPickupEnd }}',
                    status: 'normal',
                    image: 'https://images.unsplash.com/photo-1666114170628-b34b0dcc21aa?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxiYWtlcnklMjBicmVhZCUyMHBhc3RyeSUyMHNob3B8ZW58MXx8fHwxNzc0OTc0Mzg5fDA&ixlib=rb-4.1.0&q=80&w=1080'
                };
                this.isDialogOpen = true;
            },
            
            openEditDialog(product) {
                this.isEditing = true;
                this.imageError = '';
                this.formData = { ...product };
                this.formData.expires_at = product.expires_at_input || '';
                this.formData.pickup_start_time = product.pickup_start_time_input || '18:00';
                this.formData.pickup_end_time = product.pickup_end_time_input || '20:00';
                this.isDialogOpen = true;
            },
            
            setFlashSale(id) {
                if (confirm('Aktifkan Flash Sale untuk produk ini?')) {
                    const form = document.getElementById('flash-sale-form');
                    form.action = `/mitra/inventory/${id}/flash-sale`;
                    form.submit();
                }
            },

            toggleDonation(id) {
                const form = document.getElementById('toggle-donation-form');
                form.action = `/mitra/inventory/${id}/toggle-donation`;
                form.submit();
            },
            
            deleteProduct(id) {
                if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
                    const form = document.getElementById('delete-form');
                    form.action = `/mitra/inventory/${id}/delete`;
                    form.submit();
                }
            }
        }
    }
</script>
@endsection
