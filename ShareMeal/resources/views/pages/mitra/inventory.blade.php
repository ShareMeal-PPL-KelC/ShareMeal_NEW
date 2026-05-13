@extends('layouts.dashboard')

@section('content')
<div class="space-y-6" x-data="inventoryData()">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manajemen Inventaris Surplus</h1>
            <p class="text-gray-600 mt-1">Kelola stok makanan near-expired</p>
        </div>
        <button @click="openAddDialog()" class="bg-[#174413] text-white px-6 py-3 rounded-xl font-bold flex items-center gap-2 hover:bg-[#256020] transition shadow-lg shadow-green-100">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Tambah Produk
        </button>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <template x-for="product in products" :key="product.id">
            <div class="rounded-3xl border shadow-sm overflow-hidden group hover:shadow-md transition duration-300" :class="product.status === 'expired' ? 'bg-red-50/40 border-red-200' : (product.status === 'donation' ? 'bg-emerald-50/30 border-emerald-200' : 'bg-white border-gray-100')">
                <div class="relative h-48 overflow-hidden">
                    <img :src="product.image" :alt="product.name" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" :class="['donation', 'expired'].includes(product.status) ? 'opacity-70 grayscale' : ''">
                    <template x-if="product.status === 'flash-sale'">
                        <span class="absolute top-4 right-4 bg-red-600 text-white px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest shadow-lg">Flash Sale</span>
                    </template>
                    <template x-if="product.status === 'donation'">
                        <span class="absolute top-4 right-4 bg-emerald-600 text-white px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest shadow-lg">Sudah Didonasikan</span>
                    </template>
                    <template x-if="product.status === 'expired'">
                        <span class="absolute top-4 right-4 bg-red-700 text-white px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest shadow-lg">Kedaluwarsa</span>
                    </template>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <h3 class="text-xl font-black text-gray-900 leading-tight" x-text="product.name"></h3>
                        <p class="text-gray-400 font-bold text-xs uppercase tracking-widest" x-text="product.category"></p>
                    </div>

                    <div class="flex items-center gap-2 text-sm mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-orange-600"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span class="text-orange-600 font-black uppercase text-[10px] tracking-wider" x-text="'Expired: ' + product.expires_at_display"></span>
                    </div>

                    <div class="flex items-end justify-between border-t border-gray-50 pt-4 mb-6">
                        <div>
                            <template x-if="product.discount_price > 0">
                                <div>
                                    <div class="text-2xl font-black text-green-600 leading-none" x-text="'Rp ' + parseInt(product.discount_price).toLocaleString('id-ID')"></div>
                                    <div class="text-xs text-gray-400 line-through mt-1" x-text="'Rp ' + parseInt(product.price).toLocaleString('id-ID')"></div>
                                </div>
                            </template>
                            <template x-if="!(product.discount_price > 0)">
                                <div class="text-2xl font-black text-gray-900 leading-none" x-text="'Rp ' + parseInt(product.price).toLocaleString('id-ID')"></div>
                            </template>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-black text-gray-400 uppercase tracking-widest">Stok</div>
                            <div class="text-lg font-black text-gray-900" x-text="product.stock"></div>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <template x-if="product.status === 'normal'">
                            <button @click="setFlashSale(product.id)" class="flex-1 bg-orange-100 text-orange-700 py-3 px-4 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-orange-200 transition flex items-center justify-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 inline mr-1"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg> Flash Sale
                            </button>
                        </template>
                        <template x-if="product.status === 'flash-sale'">
                            <div class="flex-1 bg-green-50 text-green-700 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Aktif
                            </div>
                        </template>
                        <template x-if="product.status === 'donation'">
                            <div class="flex-1 bg-emerald-100 text-emerald-700 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><path d="M20 12v10H4V12"></path><path d="M2 7h20v5H2z"></path><path d="M12 22V7"></path><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path></svg> Masuk Donasi
                            </div>
                        </template>
                        <template x-if="product.status === 'expired'">
                            <div class="flex-1 min-h-12 bg-red-100 text-red-700 px-3 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest flex items-center justify-center gap-2 text-center leading-tight">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 shrink-0"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg> Tidak Layak
                            </div>
                        </template>
                        
                        <button dusk="edit-button" @click="openEditDialog(product)" :disabled="product.status === 'expired'" class="w-12 h-12 bg-gray-50 text-gray-400 rounded-xl flex items-center justify-center hover:bg-green-50 hover:text-green-600 transition disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-gray-50 disabled:hover:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        </button>

                        <button @click="deleteProduct(product.id)" class="w-12 h-12 bg-gray-50 text-gray-400 rounded-xl flex items-center justify-center hover:bg-red-50 hover:text-red-600 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2-2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Info Section -->
    <div class="bg-blue-50 border border-blue-100 p-8 rounded-3xl flex gap-6 shadow-sm shadow-blue-50">
        <div class="w-14 h-14 bg-blue-100 rounded-2xl flex items-center justify-center text-blue-600 flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        </div>
        <div>
            <h3 class="text-xl font-black text-blue-900 mb-2 uppercase tracking-tight">Sistem Klasifikasi Otomatis</h3>
            <p class="text-blue-800 font-medium leading-relaxed">
                Produk akan otomatis dikategorikan ke <span class="font-black">"Jual" (Flash Sale)</span> atau <span class="font-black">"Donasi"</span> berdasarkan waktu expired dan kelayakan. 
                Produk yang mendekati batas waktu namun masih layak konsumsi akan masuk sistem donasi untuk lembaga sosial.
            </p>
        </div>
    </div>

    <!-- Add/Edit Product Modal -->
    <div x-show="isDialogOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak>
        <div class="bg-white w-full max-w-xl rounded-3xl p-8 shadow-2xl space-y-6" @click.away="isDialogOpen = false">
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-black text-gray-900" x-text="isEditing ? 'Edit Produk' : 'Tambah Produk Baru'"></h3>
                <button type="button" @click="isDialogOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <form :action="isEditing ? `/mitra/inventory/${formData.id}` : '/mitra/inventory'" method="POST" enctype="multipart/form-data" x-ref="productForm" class="space-y-5">
                @csrf
                <template x-if="isEditing">
                    <input type="hidden" name="_method" value="POST"> {{-- We use POST for update in this case as per routes --}}
                </template>
                <input type="hidden" name="status" :value="formData.status">

                <div class="space-y-2">
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest">Gambar Produk</label>
                    <input type="file" name="image" accept="image/*" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-4 outline-none focus:ring-2 focus:ring-[#174413] transition text-sm">
                    <p class="text-[10px] text-gray-400 mt-1 italic">Unggah foto produk baru (kosongkan jika tidak ingin mengubah gambar).</p>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest">Nama Produk</label>
                    <input type="text" name="name" x-model="formData.name" required placeholder="Contoh: Roti Tawar" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-4 outline-none focus:ring-2 focus:ring-[#174413] transition">
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest">Kategori</label>
                        <select name="category" x-model="formData.category" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-4 outline-none focus:ring-2 focus:ring-[#174413] transition">
                            <option>Bakery</option>
                            <option>Healthy</option>
                            <option>Meal</option>
                            <option>Snack</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest">Stok</label>
                        <input type="number" name="stock" x-model="formData.stock" required placeholder="20" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-4 outline-none focus:ring-2 focus:ring-[#174413] transition">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest">Harga Normal</label>
                        <input type="number" name="price" x-model="formData.price" required placeholder="15000" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-4 outline-none focus:ring-2 focus:ring-[#174413] transition">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest">Waktu Expired</label>
                        <input type="datetime-local" name="expires_at" x-model="formData.expires_at" required class="w-full bg-gray-50 border border-gray-100 rounded-xl p-4 outline-none focus:ring-2 focus:ring-[#174413] transition">
                    </div>
                </div>

                <div class="pt-4 flex gap-4">
                    <button type="button" @click="isDialogOpen = false" class="flex-1 border border-gray-100 py-4 rounded-xl font-bold text-gray-400 hover:bg-gray-50 transition">Batal</button>
                    <button type="submit" class="flex-1 bg-[#174413] text-white py-4 rounded-xl font-black shadow-xl shadow-green-100 hover:bg-[#256020] transition">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden Forms for Actions -->
    <form id="flash-sale-form" method="POST" class="hidden">
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
            isDialogOpen: false,
            isEditing: false,
            formData: {
                id: null,
                name: '',
                category: 'Bakery',
                price: '',
                discount_price: 0,
                stock: '',
                expires_at: '',
                status: 'normal',
                image: 'https://images.unsplash.com/photo-1666114170628-b34b0dcc21aa?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxiYWtlcnklMjBicmVhZCUyMHBhc3RyeSUyMHNob3B8ZW58MXx8fHwxNzc0OTc0Mzg5fDA&ixlib=rb-4.1.0&q=80&w=1080'
            },
            
            openAddDialog() {
                this.isEditing = false;
                this.formData = {
                    id: null,
                    name: '',
                    category: 'Bakery',
                    price: '',
                    discount_price: 0,
                    stock: '',
                    expires_at: '',
                    status: 'normal',
                    image: 'https://images.unsplash.com/photo-1666114170628-b34b0dcc21aa?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxiYWtlcnklMjBicmVhZCUyMHBhc3RyeSUyMHNob3B8ZW58MXx8fHwxNzc0OTc0Mzg5fDA&ixlib=rb-4.1.0&q=80&w=1080'
                };
                this.isDialogOpen = true;
            },
            
            openEditDialog(product) {
                this.isEditing = true;
                this.formData = { ...product };
                this.formData.expires_at = product.expires_at_input || '';
                this.isDialogOpen = true;
            },
            
            setFlashSale(id) {
                if (confirm('Aktifkan Flash Sale untuk produk ini?')) {
                    const form = document.getElementById('flash-sale-form');
                    form.action = `/mitra/inventory/${id}/flash-sale`;
                    form.submit();
                }
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
