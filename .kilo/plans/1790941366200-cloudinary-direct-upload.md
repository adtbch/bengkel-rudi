# Plan: Upload langsung Browser → Cloudinary (signed) untuk portfolio

## Masalah yang diselesaikan

Upload video/foto di admin gagal dengan `413 FUNCTION_PAYLOAD_TOO_LARGE`. Penyebabnya terverifikasi: `resources/views/admin/portfolio-edit.blade.php:26` mengirim seluruh byte file sebagai multipart POST ke `POST /admin/portfolio/{id}/save`, dan `vercel.json:17` meneruskan request itu ke serverless function `api/index.php` (`vercel-php@0.9.0`) yang batas payload-nya **4.5 MB**. Validasi 50 MB di `PortfolioController.php:53` dan `PortfolioImageController.php:21` tidak pernah tereksekusi karena request ditolak di edge sebelum PHP jalan.

Solusi: file **tidak pernah melewati server**. Browser meng-upload langsung ke Cloudinary; server hanya menandatangani parameter, mencatat metadata, dan menyimpan baris DB.

## Keputusan yang sudah disepakati

| Keputusan | Pilihan |
|---|---|
| Otorisasi upload | **Signed upload** — server menandatangani tiap file (sesuai kontrak `tests/Feature/DirectMediaUploadTest.php`) |
| Jalur multipart lama | **Dibuang total** — tidak ada fallback; satu jalur kode |
| Orphan (upload sukses, DB gagal) | **Dicatat di tabel `media_uploads`**, dihapus saat save gagal / user menekan Hapus, plus command `media:prune-orphans` |
| Commit DB | **Saat user menekan "Simpan semua perubahan"** (satu transaksi bersama detail + order) |
| Batas ukuran | **Tetap 50 MB video / 10 MB gambar**, divalidasi di client |

Di luar scope: transformasi/optimasi video Cloudinary, penghapusan key `config/cloudinary.php.upload_preset` (sudah tidak dipakai kode mana pun), dan cron Vercel untuk prune (dijalankan manual dari CLI).

## Kontrak API baru

### 1. `POST /admin/portfolio/{portfolio}/uploads/sign`  (middleware: `web`, `admin.jwt`, `throttle:60,1`)

Request: `{ "resource_type": "image|video", "declared_size": 12345 }`

Respons (JSON):

```json
{
  "cloud_name": "dkv2rn5ax",
  "api_key": "1234567890",
  "upload_url": "https://api.cloudinary.com/v1_1/dkv2rn5ax/video/upload",
  "resource_type": "video",
  "params": {
    "public_id": "BengkelRudi/portfolio/9f2c1a7e3b8d4f60.mp4",
    "timestamp": 1790941366,
    "overwrite": "false"
  },
  "signature": "c1d2e3f4...",
  "token": "eyJpdiI6...",
  "media_upload_id": 12
}
```

Aturan:
- `resource_type` hanya `image` atau `video`; selain itu `422` (sesuai asersi `postJson($url, ['resource_type' => 'raw'])->assertUnprocessable()` di test).
- `public_id` **selalu dibuat server** (`{folder}/{uuid}.{ext}`), input `public_id` dari client diabaikan.
- `overwrite` selalu string `'false'` agar cocok dengan `assertJsonPath('params.overwrite', 'false')`.
- Response tidak boleh memuat api secret (test: `assertStringNotContainsString('test-secret', ...)`).
- `token` = `Crypt::encryptString(json_encode([...]))` berisi `user_id`, `portfolio_id`, `resource_type`, `public_id`, `expires_at`. Test meng-decrypt token itu dan memeriksa `user_id`/`portfolio_id`/`resource_type`.
- Sekaligus membuat baris `media_uploads` (state pending) untuk keperluan orphan tracking.
- `declared_size` > batas (50 MB video / 10 MB gambar) → `422`. Ini originated dari client dan tidak bisa diverifikasi server (konsekuensi tak unavoid dari direct upload — dicatat sebagai risiko).

### 2. `DELETE /admin/portfolio/{portfolio}/uploads/{mediaUpload}`

Dipakai saat user menekan "Hapus" pada item yang sudah ter-upload. Server: verifikasi baris milik portfolio + user, `CloudinaryService::delete()`, lalu hapus baris. Balikin redirect/JSON status.

### 3. `POST /admin/portfolio/{portfolio}/save` (diubah)

File tidak lagi diterima. Field baru (array, indeks integer):

```
uploads[0][media_upload_id], uploads[0][public_id], uploads[0][secure_url], uploads[0][resource_type]
```

Server untuk tiap item: ambil baris `media_uploads` → cocokkan `portfolio_id`, `user_id` dengan `auth('admin')->id()`, `resource_type`, `public_id`, `created_at` belum melewati TTL → validasi `secure_url` diawali `https://res.cloudinary.com/{cloud_name}/` **dan** memuat `public_id` tersebut (mencegah URL berbahaya masuk ke kolom `image_url`) → buat baris `portfolio_images` (`media_type` = resource_type) → hapus baris `media_uploads`. Semua dalam transaksi yang sama dengan update detail + order.

`resource_type` untuk `portfolio_images.media_type`: `'image'`/`'video'` (kolom enum sudah ada dari `2026_09_30_000001_add_media_type_to_portfolio_images.php`).

### 4. `POST /admin/portfolio/{portfolio}/images` — **dihapus** dari route + `PortfolioImageController::store()`

DIVERIFIKASI: tidak ada view yang mem-post ke endpoint ini (satu-satunya pemakaian di `resources/views` adalah form delete di `portfolio-edit.blade.php:114`). Hanya test yang memakainya. `destroy()` dan `reorder()` tetap.

### 5. `php artisan media:prune-orphans --hours=24`

Menghapus baris `media_uploads` yang `created_at` lebih lama dari TTL dan memanggil `CloudinaryService::delete()` untuk masing-masing `public_id` (permissive: hasil `not found` dianggap sukses). Dijalankan manual dari laptop dengan env production, **bukan** cron Vercel.

## Perubahan per file (urutan pengerjaan)

1. **`config/cloudinary.php`** — tambah `cloud_name`, `api_key`, `api_secret` (override env; fallback parse `CLOUDINARY_URL` via `Cloudinary\Configuration\ConfigUtils::parseCloudinaryUrl()` yang sudah tersedia di `cloudinary/cloudinary_php ^3.0`), tambah `token_ttl_minutes` dan `orphan_ttl_hours`. **Hapus** key `upload_preset` (sudah tidak dipakai kode mana pun).
2. **`.env.example`** — tambah `CLOUDINARY_CLOUD_NAME=`, `CLOUDINARY_API_KEY=`, `CLOUDINARY_API_SECRET=`, `CLOUDINARY_TOKEN_TTL_MINUTES=360`, `CLOUDINARY_ORPHAN_TTL_HOURS=24`. Pastikan Vercel project env punya `CLOUDINARY_URL`, `CLOUDINARY_API_KEY`, `CLOUDINARY_API_SECRET`, `APP_KEY` (wajib untuk `Crypt`). Tidak ada perubahan di `vercel.json` (tidak perlu `bodySizeLimit`).
3. **Migration `database/migrations/2026_10_02_000001_create_media_uploads_table.php`** — tabel `media_uploads`: `id`, `foreignId('portfolio_id')->constrained()->cascadeOnDelete()`, `string('public_id')->unique()`, `string('resource_type')`, `foreignId('user_id')->constrained('users')->cascadeOnDelete()`, `timestamps()`. Plus index komposit `(portfolio_id, created_at)` untuk prune.
4. **`app/Models/MediaUpload.php`** — model baru, `$fillable` `['portfolio_id','public_id','resource_type','user_id']`, relasi `portfolio()`, `user()`.
5. **`app/Services/CloudinaryService.php`** —
   - tambah `credentials(): array` (cache per instance)
   - tambah `signUpload(string $publicId, int $timestamp): array` memakai `Cloudinary\Api\Utils\ApiUtils::signParameters($params, $secret)`
   - tambah `uploadUrl(string $resourceType): string`
   - `destroy()` dibuat permissive: hasil `'ok'` **atau** `'not found'` → `true`
   - **hapus** `upload(UploadedFile ...)` (tanpa pemanggil setelah langkah 12) dan import `UploadedFile`
6. **`app/Http/Controllers/Admin/PortfolioUploadController.php`** (baru) — `sign()` dan `destroy()`. `sign()` memvalidasi `resource_type` dengan `Rule::in(['image','video'])`, membangun `public_id` acak, membuat token, menyimpan `media_uploads`, mengembalikan JSON di atas. `destroy()` mengikuti pola `PortfolioImageController::destroy()` (`abort_unless` kepemilikan).
7. **`routes/web.php`** — di dalam group `admin.jwt`, tambahkan `POST /portfolio/{portfolio}/uploads/sign` (dengan `->middleware('throttle:60,1')`) dan `DELETE /portfolio/{portfolio}/uploads/{mediaUpload}`; hapus `POST /portfolio/{portfolio}/images` (sekaligus import `PortfolioImageController` tetap dipakai untuk `destroy`/`reorder`). Ikuti gaya path existing (tanpa `->name()`).
8. **`app/Http/Controllers/Admin/PortfolioController.php::save()`** — ganti blok validasi `images.*` dengan validasi `uploads.*`; hapus validasi mime/size berbasis file (`max:51200`); `stage` menjadi `required_with:uploads`; ganti loop `foreach ($request->file('images', []))` dengan loop verifikasi token + pembuatan baris. Perluas `catch (\Throwable)` agar juga menghapus `media_uploads` yang sudah terpakai.
9. **`app/Http/Controllers/Admin/PortfolioImageController.php`** — hapus method `store()`; sisanya tidak berubah.
10. **`resources/views/layouts/admin.blade.php`** — tambah `<meta name="csrf-token" content="{{ csrf_token() }}">` di `<head>` untuk request AJAX. **Jangan** mengubah `VerifyCsrfToken` (test `AdminAuthTest::test_csrf_is_not_bypassed` mensyaratkan `except` kosong).
11. **`resources/views/admin/portfolio-edit.blade.php`** —
    - ganti `<input type="file" name="images[]" ...>` menjadi input tanpa `name` + atribut `data-media-input`, `data-sign-url`, `data-discard-url-base`, `multiple`, `accept` tetap
    - tambah kontainer `<div data-upload-list>` untuk kartu media yang sudah ter-upload (progress bar, tombol Hapus) dan `<div data-upload-summary>` untuk status/error
    - help text tetap "MP4, WebM, MOV maks. 50 MB" (keputusan: tidak diubah)
    - pertahankan `data-image-input` **dan** `data-image-preview` sebagai penanda agar `Day3ContentTest::test_portfolio_upload_exposes_local_preview_with_cleanup` tetap lulus
12. **`public/js/admin-portfolio.js`** — tambah alur direct upload di samping logika preview yang ada (pertahankan `URL.createObjectURL`/`URL.revokeObjectURL`/`DataTransfer` demi test):
    - validasi client sebelum upload: MIME di `image/jpeg|png|webp|video/mp4|webm|quicktime` dan ukuran ≤ 10 MB (gambar) / ≤ 50 MB (video); file gagal ditampilkan sebagai kartu error tanpa menyentuh jaringan
    - upload **sekuensial** per file: `POST uploads/sign` (JSON + header `X-CSRF-TOKEN` dari meta) → `XMLHttpRequest` POST `FormData` ke `upload_url` berisi `file`, `api_key`, `timestamp`, `signature`, `public_id`, `overwrite`; pakai `xhr.upload.onprogress` untuk progress
    - sukses: tampilkan thumbnail dari `secure_url`, tambahkan hidden input `uploads[i][...]`, tampilkan tombol Hapus (panggil endpoint discard)
    - gagal: tampilkan pesan Cloudinary di kartu, jangan menambahkan hidden input, biarkan user mencoba lagi
    - nonaktifkan tombol submit selama ada upload berjalan; hapus `enctype` dari form karena tidak ada file lagi
    - tetap pakai `textContent` (bukan `innerHTML`) untuk semua nilai dari server
13. **`app/Console/Commands/PruneOrphanMedia.php`** (baru) + registrasi di `app/Console/Kernel.php` — `signature: 'media:prune-orphans {--hours=}'`.
14. **Test** (lihat bagian validasi).

## Validasi

```bash
php artisan test
./vendor/bin/pint --test
```

Test yang harus ditulis ulang / diperbarui:

- `tests/Feature/DirectMediaUploadTest.php` — **harus lulus apa adanya** setelah route `uploads/sign` ada (sudah menguji auth, scoping, `'raw'` → 422, public_id diabaikan, overwrite `'false'`, secret tidak bocor)
- `tests/Feature/PortfolioVideoTest.php` — ganti test yang meng-expect `CloudinaryService::upload()` menjadi alur sign + save metadata; ganti test "oversized media rejected" menjadi test `declared_size` ditolak di endpoint sign (batas ukuran file kini hanya client-side — catat eksplisit di nama test)
- `tests/Feature/ImageUploadAndCtaTest.php` — test yang mem-post `UploadedFile` ke `/save` dan `/images` diganti payload `uploads[...]`; test upload gagal + rollback Cloudinary tetap berlaku
- `tests/Feature/Day3ContentTest.php` — biarkan; penanda view/JS dipertahankan
- Test baru:
  - `uploads/sign` menolak `declared_size` melebihi batas per resource type
  - `save()` menolak token milik portfolio/user lain, token kedaluwarsa, dan `public_id` yang tidak cocok dengan token
  - `save()` menolak `secure_url` dari domain selain cloud milik proyek
  - `DELETE uploads/{id}` menghapus asset (mock `CloudinaryService::destroy`) dan barisnya
  - `save()` gagal → asset baru di-destroy dan baris `media_uploads` dibersihkan
  - `media:prune-orphans` menghapus baris pending yang basi dan tidak menyentuh yang baru

Uji manual di environment ter-deploy (Vercel):

1. Login admin → buka `/admin/portfolio/{id}` → pilih video 30 MB → pastikan **tidak ada** request POST ke domain Vercel yang membawa file (cek DevTools → Network → filter `save`, `Content-Length` harus < 50 KB)
2. Video > 50 MB ditolak client-side dengan pesan jelas, tanpa request ke Cloudinary
3. Tekan Simpan → baris muncul di `portfolio_images` dengan `media_type = video` → video bisa diputar di halaman publik
4. Matikan jaringan saat upload → kartu error muncul, tidak ada hidden input, submit tetap bisa dilakukan tanpa file itu
5. Tekan Hapus pada item ter-upload → asset hilang dari Cloudinary (cek di dashboard)
6. `php artisan media:prune-orphans --hours=1` dari laptop dengan env production → asset orphan benar hilang, asset yang sudah dipakai tidak tersentuh

## Risiko & konsekuensi yang harus disadari

- **Batas ukuran hanya client-side.** Server tidak pernah menerima byte file, jadi tidak bisa memverifikasi ukuran/mime asli. Mitigasi: UI menolak, dan `public_id` dikunci server sehingga client tidak bisa menulis ke lokasi Cloudinary arbitrer. Batas keras tetap ada di sisi Cloudinary.
- **`secure_url` berasal dari client.** Divalidasi prefix `https://res.cloudinary.com/{cloud_name}/` + memuat `public_id` yang sama dengan token. Kalau ini dianggap tidak cukup, alternatifnya: server memanggil `uploadApi()->resource()` untuk verifikasi eksistensi (meng Adds 1 request per file) — putuskan saat implementasi.
- **Token expiry** (default 6 jam): kalau user membiarkan form ter-open semalaman lalu menekan Simpan, asset yang sudah ter-upload perlu dihapus agar tidak jadi orphan.
- **CORS**: upload browser → `api.cloudinary.com` didukung Cloudinary, tapi harus diverifikasi saat implementasi (pada langkah 11). Kalau ternyata diblokir, alternatifnya upload lewat unsigned preset dari domain sendiri — hanya relevan jika pengujian gagal.
- **Orphan dari tab yang ditutup** hanya tertangani oleh `media:prune-orphans` manual. Kalau kuota Cloudinary jadi masalah, tambahkan cron Vercel kemudian (Hobby: 1×/hari).
- Test lama yang memverifikasi upload via server tidak boleh dihapus hanya agar hijau — asersi soal perilaku rollback dan validasi perlu dipetakan ulang ke alur metadata, bukan diabaikan.