# TODO: Konversi Plugin ke Alpine.js

## Prioritas Tinggi

### 1. Setup Alpine.js
- [ ] Tambahkan Alpine.js CDN ke enqueue.php
- [ ] Konfigurasi Alpine.js initialization di script.js
- [ ] Pastikan tidak ada konflik dengan jQuery yang sudah ada

### 2. Konversi Modal Kategori
- [ ] Analisis modal kategori saat ini (di shortcode.php)
- [ ] Buat komponen Alpine.js untuk modal kategori
- [ ] Implementasi x-show, x-data untuk state management
- [ ] Konversi event handler click ke Alpine.js

### 3. Konversi Filter Portofolio
- [ ] Analisis sistem filter berdasarkan kategori
- [ ] Implementasi reactive data untuk filter
- [ ] Konversi URL parameter handling ke Alpine.js
- [ ] Implementasi x-model untuk form filter

## Prioritas Sedang

### 4. Konversi Grid Portofolio
- [ ] Analisis grid portofolio saat ini
- [ ] Implementasi x-for untuk rendering list portofolio
- [ ] Konversi pagination ke Alpine.js
- [ ] Implementasi loading state dengan Alpine.js

### 5. Konversi Preview Portofolio
- [ ] Analisis halaman preview saat ini
- [ ] Implementasi Alpine.js untuk preview modal
- [ ] Konversi navigasi preview ke Alpine.js
- [ ] Implementasi state management untuk preview

### 6. Konversi Integrasi WhatsApp
- [ ] Analisis tombol WhatsApp saat ini
- [ ] Implementasi Alpine.js untuk form WhatsApp
- [ ] Konversi dinamis pesan WhatsApp
- [ ] Implementasi validasi form dengan Alpine.js

## Prioritas Rendah

### 7. Optimasi Performance
- [ ] Implementasi Alpine.js lazy loading untuk gambar
- [ ] Optimasi API calls dengan Alpine.js
- [ ] Implementasi caching dengan Alpine.js
- [ ] Minify Alpine.js components

### 8. Testing
- [ ] Testing cross-browser compatibility
- [ ] Testing responsive design dengan Alpine.js
- [ ] Testing accessibility
- [ ] Testing performance

## File yang Perlu Dimodifikasi

### 1. inc/enqueue.php
- Tambahkan Alpine.js CDN
- Konfigurasi Alpine.js initialization

### 2. inc/shortcode.php
- Konversi modal kategori ke Alpine.js
- Konversi grid portofolio ke Alpine.js
- Konversi pagination ke Alpine.js

### 3. inc/page-preview.php
- Konversi preview portofolio ke Alpine.js
- Implementasi Alpine.js components

### 4. assets/js/script.js
- Inisialisasi Alpine.js
- Konversi jQuery functions ke Alpine.js
- Implementasi Alpine.js components

### 5. assets/css/style.css
- Adjust CSS untuk Alpine.js compatibility
- Remove jQuery-specific CSS
- Add Alpine.js transition classes

## Implementasi Detail

### Modal Kategori dengan Alpine.js
```html
<div x-data="{ 
    modalOpen: false,
    categories: [],
    selectedCategory: ''
}" x-init="loadCategories()">
    <button @click="modalOpen = true">Pilih Kategori</button>
    
    <div x-show="modalOpen" x-transition>
        <!-- Modal content -->
    </div>
</div>
```

### Grid Portofolio dengan Alpine.js
```html
<div x-data="{ 
    portfolios: [],
    filteredPortfolios: [],
    currentPage: 1,
    itemsPerPage: 12
}" x-init="loadPortfolios()">
    <template x-for="portfolio in filteredPortfolios" :key="portfolio.id">
        <!-- Portfolio card -->
    </template>
</div>
```

### Preview dengan Alpine.js
```html
<div x-data="{ 
    previewOpen: false,
    currentPreview: null
}">
    <template x-if="previewOpen">
        <!-- Preview content -->
    </template>
</div>
```

## Catatan Penting

1. **Backward Compatibility**: Pastikan semua fitur existing tetap berfungsi
2. **Progressive Enhancement**: Implementasi Alpine.js harus meningkatkan UX tanpa menghapus fitur existing
3. **Performance**: Monitor performance impact dari Alpine.js
4. **Testing**: Lakukan testing menyeluruh setiap komponen yang dikonversi
5. **Documentation**: Update dokumentasi untuk penggunaan Alpine.js

## Timeline Estimasi

- **Minggu 1**: Setup Alpine.js dan konversi modal kategori
- **Minggu 2**: Konversi filter dan grid portofolio
- **Minggu 3**: Konversi preview dan integrasi WhatsApp
- **Minggu 4**: Testing, optimasi, dan dokumentasi

## Dependencies

- Alpine.js 3.x
- Tetap mempertahankan jQuery untuk compatibility dengan WordPress core
- Testing framework untuk Alpine.js components