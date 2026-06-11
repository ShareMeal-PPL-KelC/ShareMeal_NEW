@extends('layouts.dashboard')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto py-4">
    <!-- Header -->
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 font-serif">Kirim Masukan & Feedback</h1>
            <p class="text-gray-600 mt-1">Bantu kami meningkatkan platform ShareMeal dengan memberikan masukan Anda.</p>
        </div>
        <a href="{{ route(Auth::user()->role . '.dashboard') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-luxury-forest transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Dashboard
        </a>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="rounded-xl border border-green-150 bg-green-50 p-4 text-sm font-medium text-green-800 flex items-center gap-3 shadow-sm animate-in fade-in slide-in-from-top duration-350">
            <div class="bg-green-100 p-1.5 rounded-lg text-green-700">
                <i data-lucide="check" class="w-5 h-5"></i>
            </div>
            <div>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-xl border border-red-150 bg-red-50 p-4 text-sm font-medium text-red-800 flex items-center gap-3 shadow-sm">
            <div class="bg-red-100 p-1.5 rounded-lg text-red-700">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
            </div>
            <div>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Form Card -->
    <div class="bg-white rounded-3xl border border-gray-100 luxury-shadow p-6 sm:p-8" x-data="feedbackForm()">
        <form action="{{ route(Auth::user()->role . '.feedback.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" @submit.prevent="handleSubmit($event)">
            @csrf

            <!-- Category Field (Interactive Card Radio Options) -->
            <div class="space-y-3">
                <label class="text-sm font-bold text-gray-700 uppercase tracking-wider">Kategori Feedback</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <!-- Fitur -->
                    <label class="relative flex flex-col items-center justify-center p-4 bg-gray-100/40 border rounded-2xl cursor-pointer hover:bg-gray-100/70 transition-all group"
                           :class="category === 'fitur' ? 'border-luxury-gold ring-2 ring-luxury-gold bg-luxury-gold/5' : 'border-gray-200'">
                        <input type="radio" name="category" value="fitur" class="sr-only" x-model="category" required>
                        <div class="p-2.5 rounded-xl bg-blue-50 text-blue-600 mb-2 transition-transform group-hover:scale-110">
                            <i data-lucide="sparkles" class="w-5 h-5"></i>
                        </div>
                        <span class="text-xs font-bold text-gray-800">Fitur Baru</span>
                    </label>

                    <!-- Bug -->
                    <label class="relative flex flex-col items-center justify-center p-4 bg-gray-100/40 border rounded-2xl cursor-pointer hover:bg-gray-100/70 transition-all group"
                           :class="category === 'bug' ? 'border-luxury-gold ring-2 ring-luxury-gold bg-luxury-gold/5' : 'border-gray-200'">
                        <input type="radio" name="category" value="bug" class="sr-only" x-model="category">
                        <div class="p-2.5 rounded-xl bg-red-50 text-red-600 mb-2 transition-transform group-hover:scale-110">
                            <i data-lucide="bug" class="w-5 h-5"></i>
                        </div>
                        <span class="text-xs font-bold text-gray-800">Laporan Bug</span>
                    </label>

                    <!-- UI/UX -->
                    <label class="relative flex flex-col items-center justify-center p-4 bg-gray-100/40 border rounded-2xl cursor-pointer hover:bg-gray-100/70 transition-all group"
                           :class="category === 'ui_ux' ? 'border-luxury-gold ring-2 ring-luxury-gold bg-luxury-gold/5' : 'border-gray-200'">
                        <input type="radio" name="category" value="ui_ux" class="sr-only" x-model="category">
                        <div class="p-2.5 rounded-xl bg-purple-50 text-purple-600 mb-2 transition-transform group-hover:scale-110">
                            <i data-lucide="palette" class="w-5 h-5"></i>
                        </div>
                        <span class="text-xs font-bold text-gray-800">Tampilan / UI</span>
                    </label>

                    <!-- Lain-lain -->
                    <label class="relative flex flex-col items-center justify-center p-4 bg-gray-100/40 border rounded-2xl cursor-pointer hover:bg-gray-100/70 transition-all group"
                           :class="category === 'other' ? 'border-luxury-gold ring-2 ring-luxury-gold bg-luxury-gold/5' : 'border-gray-200'">
                        <input type="radio" name="category" value="other" class="sr-only" x-model="category">
                        <div class="p-2.5 rounded-xl bg-amber-50 text-amber-600 mb-2 transition-transform group-hover:scale-110">
                            <i data-lucide="help-circle" class="w-5 h-5"></i>
                        </div>
                        <span class="text-xs font-bold text-gray-800">Lain-lain</span>
                    </label>
                </div>
                @error('category')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Subject / Subjek -->
            <div class="space-y-2">
                <label for="subject" class="text-sm font-bold text-gray-700 uppercase tracking-wider">Subjek Masukan</label>
                <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required 
                       placeholder="Contoh: Kesulitan saat memproses checkout pesanan"
                       class="w-full rounded-2xl border-gray-300/40 bg-gray-100/70 p-3.5 text-sm focus:border-luxury-forest focus:ring-luxury-forest focus:bg-white transition-all duration-200 @error('subject') border-red-500 @enderror">
                @error('subject')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Rating Bintang -->
            <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700 uppercase tracking-wider block">Seberapa Puas Anda dengan ShareMeal?</label>
                <input type="hidden" name="rating" :value="rating" required>
                <div class="flex items-center gap-2 py-1">
                    <template x-for="star in 5">
                        <button type="button" @click="rating = star" @mouseenter="hoverRating = star" @mouseleave="hoverRating = 0"
                                class="transition-transform duration-150 active:scale-95 focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 class="w-8 h-8 transition-colors duration-150"
                                 :class="(hoverRating || rating) >= star ? 'text-amber-400 fill-amber-400' : 'text-gray-300 fill-none'">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                            </svg>
                        </button>
                    </template>
                    <span class="text-sm font-bold text-gray-500 ml-3" x-text="getRatingLabel()"></span>
                </div>
                @error('rating')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description / Deskripsi Detail -->
            <div class="space-y-2">
                <label for="description" class="text-sm font-bold text-gray-700 uppercase tracking-wider">Detail Masukan</label>
                <textarea name="description" id="description" rows="5" required 
                          placeholder="Jelaskan secara detail masukan, kendala, atau saran yang Anda miliki..."
                          class="w-full rounded-2xl border-gray-300/40 bg-gray-100/70 p-3.5 text-sm focus:border-luxury-forest focus:ring-luxury-forest focus:bg-white transition-all duration-200 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Image Upload / Screenshot (Optional, MAX 2MB) -->
            <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700 uppercase tracking-wider block">Lampiran Gambar (Opsional, Maks. 2 MB per berkas)</label>
                
                <div class="flex flex-col gap-4">
                    <!-- Hidden real file inputs that will be submitted.
                         We will bind Alpine's files collection and keep this input updated with DataTransfer. -->
                    <input type="file" name="screenshots[]" id="screenshot-input" multiple accept="image/*" class="hidden" @change="handleFileSelect($event)">
                    
                    <!-- Dropzone (Visible only when no files are chosen yet) -->
                    <div x-show="files.length === 0"
                         class="relative border-2 border-dashed border-gray-300/70 rounded-2xl p-6 text-center hover:border-luxury-gold transition-all duration-200 bg-gray-100/50 flex flex-col items-center justify-center cursor-pointer"
                         @click="document.getElementById('screenshot-input').click()"
                         :class="fileError ? 'border-red-400 bg-red-50/30' : ''">
                        
                        <div class="bg-white p-3 rounded-full shadow-sm text-gray-400 mb-3 border border-gray-100">
                            <i data-lucide="image" class="w-6 h-6"></i>
                        </div>
                        <p class="text-sm font-semibold text-gray-700">Pilih atau Seret Gambar di sini</p>
                        <p class="text-xs text-gray-500 mt-1">Format: PNG, JPG, JPEG (Maks. 2 MB)</p>
                    </div>

                    <!-- Selected Files Grid (Visible when files.length > 0) -->
                    <div x-show="files.length > 0" class="flex flex-wrap gap-4 items-center" x-cloak>
                        <!-- Thumbnails loop -->
                        <template x-for="(f, index) in files" :key="f.id">
                            <div class="relative w-24 h-24 rounded-2xl border border-gray-200 overflow-hidden shadow-sm group">
                                <img :src="f.previewUrl" class="w-full h-full object-cover" alt="Pratinjau screenshot">
                                <button type="button" @click="removeFile(index)"
                                        class="absolute top-1 right-1 p-1 bg-red-600 hover:bg-red-700 text-white rounded-full shadow transition-all duration-150 transform hover:scale-105">
                                    <i data-lucide="x" class="w-3 h-3"></i>
                                </button>
                                <!-- File Name Tooltip or small overlay on hover -->
                                <div class="absolute inset-x-0 bottom-0 bg-black/60 py-0.5 text-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <span class="text-[9px] text-white block truncate px-1" x-text="f.file.name"></span>
                                </div>
                            </div>
                        </template>

                        <!-- The "+" Button to add more files -->
                        <button type="button" 
                                @click="document.getElementById('screenshot-input').click()"
                                class="relative border-2 border-dashed border-gray-300 hover:border-luxury-gold hover:text-luxury-gold transition-all duration-200 rounded-2xl w-24 h-24 flex flex-col items-center justify-center bg-gray-100/40 text-gray-400 group cursor-pointer">
                            <i data-lucide="plus" class="w-6 h-6 transition-transform group-hover:scale-110"></i>
                            <span class="text-[10px] font-bold mt-1">Tambah</span>
                        </button>
                    </div>

                    <!-- File Error Notification -->
                    <p x-show="fileError" class="text-xs text-red-600 font-bold" x-text="fileError" x-cloak></p>
                </div>
                @error('screenshots')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
                @error('screenshots.*')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="pt-4 flex justify-end">
                <button type="submit" :disabled="submitState !== 'idle' || fileError"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 text-white font-bold rounded-2xl transition-all duration-300 shadow-md shadow-emerald-950/10 active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="{
                            'bg-luxury-forest hover:bg-emerald-950': submitState === 'idle',
                            'bg-emerald-700': submitState === 'loading',
                            'bg-green-600': submitState === 'success'
                        }">
                    <!-- State: Idle -->
                    <span x-show="submitState === 'idle'" class="flex items-center gap-2">
                        <span>Kirim Feedback</span>
                        <i data-lucide="send" class="w-4 h-4"></i>
                    </span>

                    <!-- State: Loading -->
                    <span x-show="submitState === 'loading'" class="flex items-center gap-2" x-cloak>
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Mengirim...</span>
                    </span>

                    <!-- State: Success -->
                    <span x-show="submitState === 'success'" class="flex items-center gap-2" x-cloak>
                        <svg class="h-5 w-5 text-white animate-bounce" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Terkirim!</span>
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function feedbackForm() {
        return {
            category: '{{ old("category", "fitur") }}',
            rating: {{ old("rating", 5) }},
            hoverRating: 0,
            files: [],
            fileError: null,
            submitState: 'idle',
            nextFileId: 1,

            getRatingLabel() {
                switch(this.rating) {
                    case 1: return 'Sangat Kecewa';
                    case 2: return 'Kecewa';
                    case 3: return 'Biasa Saja';
                    case 4: return 'Puas';
                    case 5: return 'Sangat Puas';
                    default: return '';
                }
            },

            handleFileSelect(event) {
                const newFiles = Array.from(event.target.files);
                this.fileError = null;

                if (newFiles.length === 0) return;

                newFiles.forEach(file => {
                    // Check size (2 MB = 2097152 Bytes)
                    if (file.size > 2097152) {
                        this.fileError = `Berkas "${file.name}" melebihi batas maksimal 2 MB.`;
                        return;
                    }

                    // Check type
                    if (!file.type.match('image.*')) {
                        this.fileError = `Berkas "${file.name}" harus berupa gambar.`;
                        return;
                    }

                    // Check duplicates
                    const exists = this.files.some(f => f.file.name === file.name && f.file.size === file.size);
                    if (exists) return;

                    const previewUrl = URL.createObjectURL(file);
                    this.files.push({
                        id: this.nextFileId++,
                        file: file,
                        previewUrl: previewUrl
                    });
                });

                this.syncFilesToInput();

                // Re-trigger lucide icons
                setTimeout(() => {
                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                }, 50);
            },

            removeFile(index) {
                URL.revokeObjectURL(this.files[index].previewUrl);
                this.files.splice(index, 1);
                this.syncFilesToInput();
            },

            syncFilesToInput() {
                const input = document.getElementById('screenshot-input');
                if (!input) return;

                try {
                    const dt = new DataTransfer();
                    this.files.forEach(f => dt.items.add(f.file));
                    input.files = dt.files;
                } catch (e) {
                    console.error("DataTransfer error: ", e);
                }
            },

            handleSubmit(event) {
                if (this.submitState !== 'idle') return;

                this.submitState = 'loading';

                setTimeout(() => {
                    this.submitState = 'success';
                    setTimeout(() => {
                        event.target.submit();
                    }, 800);
                }, 1200);
            }
        }
    }
</script>
@endsection
