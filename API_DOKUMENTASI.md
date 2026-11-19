# Dokumen Rancangan & Pengujian REST API — Toko Mungil Marketplace

REST API ini dibangun dengan **PHP Native + MySQLi**, memakai database marketplace Soal 11
(database `pasar_tradisional`) dan dijalankan di **XAMPP (localhost)**. Semua response berformat **JSON**.

## A. Analisis dan Perancangan API

### a. Fungsi Web Service/API dan alasan dibutuhkan
Web Service/API adalah antarmuka yang membuat data dan fungsi sebuah aplikasi dapat diakses aplikasi lain lewat
protokol HTTP dengan format data standar (JSON), tanpa harus membuka halaman HTML atau database-nya langsung.
Pada marketplace, API berfungsi:
1. **Menyediakan data produk, kategori, dan pesanan** untuk aplikasi mobile, aplikasi kasir, atau pihak ketiga (mis. agregator/komparasi harga).
2. **Memisahkan tampilan dari data** — satu sumber data dipakai banyak klien (web, Android, iOS) tanpa menulis ulang logika bisnis.
3. **Menjaga keamanan** — klien tidak pernah mengakses database langsung; semua input divalidasi di server (harga dihitung dari database, stok dicek, query memakai prepared statement).
4. **Interoperabilitas** — platform dan bahasa pemrograman berbeda dapat berkomunikasi karena sama-sama memahami HTTP dan JSON.

### b. Rancangan endpoint
Base URL: `http://localhost/pasar_tradisional/api`

| No | Metode | Endpoint | Fungsi | Status sukses |
|----|--------|----------|--------|---------------|
| 1 | GET | `/api/produk.php` | Menampilkan seluruh produk (opsional `?kategori_id=`, `?cari=`) | 200 |
| 2 | GET | `/api/produk.php?id=1` | Menampilkan detail produk | 200 |
| 3 | GET | `/api/kategori.php` | Menampilkan daftar kategori + jumlah produk | 200 |
| 4 | POST | `/api/pesanan.php` | Membuat pesanan baru (body JSON) | 201 |
| 5 | GET | `/api/pesanan.php?kode=...&no_hp=...` | Cek status pesanan | 200 |
| 6 | GET | `/api/index.php` | Informasi API & daftar endpoint | 200 |

**Kode status error yang ditangani**

| Kode | Arti | Contoh penyebab |
|------|------|-----------------|
| 400 | Bad Request | `id` bukan angka, JSON rusak, parameter wajib kosong |
| 404 | Not Found | produk/kategori/pesanan tidak ada |
| 405 | Method Not Allowed | memakai DELETE/PUT pada endpoint |
| 409 | Conflict | stok tidak mencukupi |
| 422 | Unprocessable Entity | data pesanan tidak valid (nama kosong, no. HP salah, items kosong) |
| 500 | Server Error | database tidak terhubung / tabel belum di-import |

**Format body POST /api/pesanan.php** (`Content-Type: application/json`)
```json
{
  "nama_pembeli": "Siti Aminah",
  "no_hp": "081234567890",
  "alamat": "Jl. Merdeka No. 10, Bengkulu",
  "items": [
    {"id_produk": 1, "jumlah": 2},
    {"id_produk": 4, "jumlah": 1}
  ]
}
```
Harga dan total **selalu dihitung server dari database**, bukan dari data yang dikirim klien. Status awal pesanan: `Menunggu Konfirmasi` (sama dengan checkout di website; stok baru berkurang saat admin mengonfirmasi).

### c. Struktur JSON response
Pola umum: `{"status": "success|error", "message": "...", "data": ...}`.

**Daftar produk** — `GET /api/produk.php` (200 OK; contoh ditampilkan 2 dari 8 produk)
```json
{
  "status": "success",
  "message": "Daftar produk berhasil diambil",
  "data": {
    "jumlah": 8,
    "produk": [
      {
        "id_produk": 1,
        "nama": "Kue Putu Ayu",
        "deskripsi": "Kue tradisional lembut dengan kelapa parut. Isi 10 pcs.",
        "harga": 15000,
        "stok": 40,
        "emoji": "🍰",
        "gambar_url": null,
        "kategori": {
          "id_kategori": 1,
          "nama_kategori": "Makanan"
        },
        "penjual": {
          "id_penjual": 2,
          "nama_toko": "Dapur Nenek"
        }
      },
      {
        "id_produk": 2,
        "nama": "Klepon Manis",
        "deskripsi": "Bola ketan isi gula merah, meletus di mulut!",
        "harga": 12000,
        "stok": 50,
        "emoji": "🍡",
        "gambar_url": null,
        "kategori": {
          "id_kategori": 1,
          "nama_kategori": "Makanan"
        },
        "penjual": {
          "id_penjual": 2,
          "nama_toko": "Dapur Nenek"
        }
      }
    ]
  }
}
```

**Detail produk** — `GET /api/produk.php?id=1` (200 OK)
```json
{
    "status": "success",
    "message": "Detail produk ditemukan",
    "data": {
        "id_produk": 1,
        "nama": "Kue Putu Ayu",
        "deskripsi": "Kue tradisional lembut dengan kelapa parut. Isi 10 pcs.",
        "harga": 15000,
        "stok": 40,
        "emoji": "🍰",
        "gambar_url": null,
        "kategori": {
            "id_kategori": 1,
            "nama_kategori": "Makanan"
        },
        "penjual": {
            "id_penjual": 2,
            "nama_toko": "Dapur Nenek"
        }
    }
}
```

## B. Implementasi & Menjalankan di XAMPP
Struktur folder:
```
pasar_tradisional/
├── api/
│   ├── koneksi.php   (koneksi MySQLi + fungsi bantu JSON)
│   ├── index.php     (info API)
│   ├── produk.php    (GET list/detail)
│   ├── kategori.php  (GET list/detail)
│   └── pesanan.php   (POST buat pesanan, GET cek status)
├── database_xampp.sql
├── koneksi.local.php (pengaturan XAMPP, tidak diunggah ke GitHub)
└── ... (file website marketplace)
```
Langkah:
1. Jalankan **Apache** dan **MySQL** di XAMPP Control Panel.
2. Salin folder `pasar_tradisional` ke `C:\xampp\htdocs\`.
3. Buka `http://localhost/phpmyadmin` → tab **Import** → pilih `database_xampp.sql` → **Go** (database `pasar_tradisional` dibuat otomatis).
4. Pastikan `koneksi.local.php` berisi: host `localhost`, user `root`, password kosong, database `pasar_tradisional`.
5. Buka `http://localhost/pasar_tradisional/api/produk.php` — JSON daftar produk harus tampil.

## C. Pengujian
Pengujian dilakukan di komputer sendiri; screenshot yang dikumpulkan harus hasil nyata dari XAMPP milikmu.

| No | Skenario | Request | Hasil yang diharapkan |
|----|----------|---------|-----------------------|
| 1 | Semua produk | GET `/api/produk.php` | 200, `jumlah` = 8 |
| 2 | Detail produk | GET `/api/produk.php?id=1` | 200, data Kue Putu Ayu |
| 3 | ID tidak ada | GET `/api/produk.php?id=999` | 404 |
| 4 | ID tidak valid | GET `/api/produk.php?id=abc` | 400 |
| 5 | Daftar kategori | GET `/api/kategori.php` | 200, 3 kategori |
| 6 | Filter | GET `/api/produk.php?kategori_id=1&cari=kue` | 200, 1 produk |
| 7 | Metode salah | DELETE `/api/produk.php?id=1` | 405 |
| 8 | Buat pesanan | POST `/api/pesanan.php` (body di atas) | 201, kode pesanan + total 75000 |
| 9 | Data tidak valid | POST body kosong/salah | 422 |
| 10 | JSON rusak | POST `{rusak` | 400 |
| 11 | Stok kurang | POST jumlah 500 | 409 |
| 12 | Produk tidak ada | POST `id_produk` 999 | 404 |
| 13 | Cek status | GET `/api/pesanan.php?kode=...&no_hp=...` | 200 |

Contoh hasil nyata dari pengujian skenario 8 (201 Created):
```json
{
    "status": "success",
    "message": "Pesanan berhasil dibuat dan menunggu konfirmasi admin. Simpan kode pesanan.",
    "data": {
        "id_pesanan": 1,
        "kode": "MG261119499",
        "nama_pembeli": "Siti Aminah",
        "no_hp": "081234567890",
        "alamat": "Jl. Merdeka No. 10, Bengkulu",
        "total": 75000,
        "status": "Menunggu Konfirmasi",
        "tanggal": "2026-11-19 07:02:14",
        "items": [
            {
                "id_produk": 1,
                "nama": "Kue Putu Ayu",
                "jumlah": 2,
                "harga": 15000,
                "subtotal": 30000
            },
            {
                "id_produk": 4,
                "nama": "Tas Anyaman Mini",
                "jumlah": 1,
                "harga": 45000,
                "subtotal": 45000
            }
        ]
    }
}
```
Contoh error stok (skenario 11):
```json
{
    "status": "error",
    "message": "Stok produk \"Kue Putu Ayu\" tidak mencukupi.",
    "stok_tersedia": 40,
    "jumlah_diminta": 500
}
```

### Screenshot yang perlu diambil (untuk laporan)
1. Browser: URL `http://localhost/pasar_tradisional/api/produk.php` dan `...?id=1` beserta JSON-nya.
2. Postman: request GET produk, GET detail, dan POST pesanan (tampilkan tab **Body**, status code, dan response). Import `Postman_Collection_Toko_Mungil.json` agar semua request siap pakai (tab **Tests** otomatis menandai Pass/Fail).
3. phpMyAdmin: tabel `pesanan` dan `detail_pesanan` berisi data hasil POST, plus struktur database.
4. Terminal XAMPP/Control Panel yang menunjukkan Apache & MySQL berjalan (hijau).

Contoh pengujian lewat command line (opsional):
```
curl http://localhost/pasar_tradisional/api/produk.php
curl -X POST -H "Content-Type: application/json" -d "{\"nama_pembeli\":\"Siti\",\"no_hp\":\"081234567890\",\"alamat\":\"Bengkulu\",\"items\":[{\"id_produk\":1,\"jumlah\":2}]}" http://localhost/pasar_tradisional/api/pesanan.php
```

## D. GitHub
```
git init
git add .
git commit -m "Marketplace + REST API"
git branch -M main
git remote add origin https://github.com/USERNAME/NAMA-REPO.git
git push -u origin main
```
`koneksi.local.php` sudah ada di `.gitignore`, jadi tidak ikut terunggah. Cantumkan URL repository di laporan.
