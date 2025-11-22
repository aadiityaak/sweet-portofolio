# Sweet Portofolio

Plugin WordPress sederhana untuk menampilkan portofolio website dari websweetstudio.com.

## Deskripsi

Sweet Portofolio adalah plugin WordPress yang memungkinkan Anda menampilkan portofolio website dengan mudah. Plugin ini mengambil data dari API eksternal dan menampilkannya dalam format yang menarik dengan fitur filter kategori, preview, dan integrasi WhatsApp.

## Fitur

- Menampilkan portofolio website dalam grid yang responsif
- Filter berdasarkan kategori website
- Preview portofolio dalam halaman terpisah
- Integrasi WhatsApp untuk pemesanan
- Konfigurasi mudah melalui menu admin
- Support untuk berbagai ukuran gambar
- Opsi tampilan thumbnail standar atau screenshot

## Instalasi

1. Download plugin Sweet Portofolio
2. Upload folder `sweet-portofolio` ke direktori `/wp-content/plugins/`
3. Aktifkan plugin melalui menu "Plugins" di WordPress
4. Konfigurasi plugin melalui menu "Portofolio Option" di sidebar admin

## Konfigurasi

Setelah instalasi, lakukan konfigurasi berikut:

1. Buka menu **Portofolio Option** di dashboard WordPress
2. Isi field berikut:
   - **WhatsApp Number**: Nomor WhatsApp untuk pemesanan
   - **Access Key**: Kunci akses API dari websweetstudio.com
   - **Credit Text**: Teks kredit yang akan ditampilkan di portofolio
   - **Image Size**: Pilih ukuran gambar (Thumbnail 400, Medium 700, Large 1000, atau Full 1080)
   - **Style Thumbnail**: Pilih tampilan thumbnail (Standart atau Screenshot)
   - **Portofolio Page**: Pilih halaman untuk menampilkan daftar portofolio
   - **Preview Page**: Pilih halaman untuk preview portofolio
   - **Portfolio Selection**: Pilih kategori portofolio yang akan ditampilkan

## Penggunaan

### Menampilkan Tombol Filter Kategori

Gunakan shortcode berikut untuk menampilkan tombol filter berdasarkan jenis web:

```
[sweet-portofolio-jenis-web]
```

### Menampilkan Daftar Portofolio

Gunakan shortcode berikut untuk menampilkan daftar thumbnail portofolio:

```
[sweet-portofolio-list default="profil-perusahaan"]
```

Parameter:
- `default`: Kategori default yang akan ditampilkan
- `include`: ID portofolio spesifik yang ingin ditampilkan (dipisahkan dengan koma)
- `title`: Set ke "no" untuk menyembunyikan judul portofolio

Contoh menampilkan portofolio berdasarkan ID:

```
[sweet-portofolio-list include="1982,1670" title="no"]
```

### Setup Halaman Preview

1. Buat halaman baru untuk preview portofolio
2. Pilih template "Preview Portofolio" pada Page Attributes
3. Pilih halaman ini pada pengaturan "Preview Page" di menu Portofolio Option

## Struktur File

```
sweet-portofolio/
├── assets/
│   ├── css/
│   │   ├── style.css
│   │   └── style.min.css
│   └── js/
│       ├── script.js
│       └── script.min.js
├── inc/
│   ├── enqueue.php
│   ├── page-preview.php
│   ├── shortcode.php
│   └── sweet-options.php
├── src/
│   ├── build/
│   ├── js/
│   └── sass/
├── sweet-portofolio.php
├── package.json
└── README.md
```

## Build Process

Plugin ini menggunakan npm untuk proses build:

```bash
# Install dependencies
npm install

# Build CSS dan JS
npm run dist

# Build dan package
npm run package
```

## Versi

Versi saat ini: 1.0.613

## Lisensi

Plugin ini dilisensikan di b GPLv2. Lihat file LICENSE untuk informasi lebih lanjut.

## Dukungan

Untuk dukungan dan pertanyaan, hubungi:
- Website: https://websweetstudio.com
- Author: Aditya K