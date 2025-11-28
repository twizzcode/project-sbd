# Sistem Booking Appointment - Owner Portal

## Fitur
✅ Owner dapat membuat appointment untuk hewan peliharaan mereka
✅ Sistem menampilkan jadwal dokter yang tersedia (dari database veterinarian)
✅ Slot waktu yang sudah dibooking tidak bisa dipilih lagi
✅ Slot waktu yang sudah lewat hari ini tidak bisa dipilih
✅ Owner bisa pilih pet, tanggal, dokter, dan waktu
✅ Otomatis set jenis layanan sebagai "Pemeriksaan Umum"

## File yang Dibuat

### 1. `/database/migrations/add_doctor_schedule.sql`
- Membuat tabel `doctor_schedule` untuk jadwal praktek dokter
- Insert jadwal default: Senin-Sabtu, jam 08:00-12:00 dan 13:00-17:00
- Durasi per slot: 30 menit
- **Sinkron dengan data dokter dari tabel `veterinarian`**

### 2. `/owners/portal/book_appointment.php`
- Form booking appointment untuk owner
- 4 step process (sudah disederhanakan):
  1. Pilih hewan peliharaan
  2. Pilih tanggal
  3. Pilih dokter & waktu
  4. Tambahkan keluhan/catatan
- Real-time validation slot availability
- **Jenis layanan otomatis: "Pemeriksaan Umum"**

### 3. `/owners/portal/get_available_slots.php`
- API endpoint untuk mendapatkan jadwal dokter yang tersedia
- Parameter: tanggal & hari
- Return: List dokter dengan slot waktu mereka
- Menandai slot yang sudah dibooking atau sudah lewat
- **Mengambil data dokter dari tabel `veterinarian`**

### 4. `/owners/portal/add_pet.php`
- Form untuk owner mendaftarkan pet baru
- Upload foto pet
- Self-service tanpa perlu bantuan staff

### 5. `/owners/portal/edit_pet.php`
- Form untuk owner update informasi pet
- Update foto pet

## Cara Kerja

1. **Owner membuka halaman book appointment**
   - Sistem menampilkan semua pet milik owner yang aktif
   - Owner pilih pet

2. **Owner pilih tanggal**
   - Sistem menghitung hari (Senin, Selasa, dst)
   - Memanggil API `get_available_slots.php`

3. **Sistem menampilkan dokter & slot waktu**
   - Query jadwal dokter dari tabel `doctor_schedule` untuk hari tersebut
   - Join dengan tabel `veterinarian` untuk data dokter (nama, spesialisasi)
   - Generate slot waktu berdasarkan jam_mulai, jam_selesai, durasi_slot
   - Check appointment yang sudah ada di tanggal tersebut
   - Tandai slot yang available/tidak

4. **Owner pilih dokter & waktu**
   - Klik slot waktu yang tersedia
   - Slot yang dipilih highlight dengan warna indigo

5. **Submit booking**
   - Final check: apakah slot masih available
   - Insert ke tabel `appointment` dengan:
     - status: 'Pending'
     - jenis_layanan: 'Pemeriksaan Umum' (auto-set)
   - Redirect ke halaman appointments

## Database Schema

### Tabel `doctor_schedule`
```sql
- schedule_id (PK)
- dokter_id (FK to veterinarian)
- hari (ENUM: Senin-Minggu)
- jam_mulai (TIME)
- jam_selesai (TIME)
- durasi_slot (INT, default 30 menit)
- status (ENUM: Aktif/Tidak Aktif)
```

### Tabel `veterinarian` (existing)
```sql
- dokter_id (PK)
- nama_dokter (VARCHAR)
- spesialisasi (ENUM)
- status (ENUM: Aktif/Cuti/Resign)
- dll
```

### Tabel `appointment` (existing)
```sql
- appointment_id (PK)
- pet_id (FK)
- owner_id (FK)
- dokter_id (FK)
- tanggal_appointment (DATE)
- jam_appointment (TIME)
- jenis_layanan (VARCHAR) - auto-set: "Pemeriksaan Umum"
- status (ENUM: Pending, Confirmed, Completed, Cancelled, No_Show)
- keluhan_awal (TEXT)
- catatan (TEXT)
```

### Tabel `pet` (existing)
```sql
- pet_id (PK)
- owner_id (FK)
- nama_hewan (VARCHAR)
- jenis (ENUM: Anjing, Kucing, Burung, dll)
- status (ENUM: Aktif, Meninggal)
- dll
```

## Sinkronisasi Data

### ✅ **Data yang Disinkronkan:**

1. **Dokter**
   - Booking system menggunakan data dari tabel `veterinarian`
   - Hanya dokter dengan status 'Aktif' yang ditampilkan
   - Jadwal otomatis dibuat saat ada dokter baru dengan status 'Aktif'

2. **Pet**
   - Owner bisa add/edit pet sendiri
   - Data pet tersinkron dengan sistem utama
   - Hanya pet dengan status 'Aktif' yang bisa booking

3. **Appointment**
   - Data appointment tersinkron antara owner portal dan admin panel
   - Staff bisa confirm/update appointment dari admin panel
   - Owner bisa lihat status real-time

### 🔄 **Alur Data:**

```
Admin menambah Dokter → Tabel veterinarian
                      ↓
          Jadwal otomatis dibuat → Tabel doctor_schedule
                      ↓
         Owner lihat jadwal available → get_available_slots.php
                      ↓
            Owner booking → Tabel appointment (status: Pending)
                      ↓
         Staff confirm → Update status: Confirmed
                      ↓
         Owner lihat status updated
```

## Navigasi
- Menu navbar: "Book Now"
- Tombol di halaman appointments: "Book New Appointment"
- Empty state appointments: "Book Appointment"
- Tombol di dashboard: "Add New Pet"

## Status Flow
1. **Pending** - Baru dibuat oleh owner, menunggu konfirmasi staff
2. **Confirmed** - Dikonfirmasi oleh staff/admin
3. **Completed** - Appointment selesai dilakukan
4. **Cancelled** - Dibatalkan
5. **No_Show** - Owner tidak datang

## Testing
1. Login sebagai owner (andi_owner / password123)
2. Klik "Add Pet" jika belum ada pet
3. Klik "Book Now" di navbar
4. Pilih pet dan tanggal
5. Lihat jadwal dokter yang muncul (dari database veterinarian)
6. Pilih slot waktu yang available
7. Submit dan cek di halaman "Appointments"

## Admin Panel Integration
- Admin bisa tambah/edit/delete dokter → Otomatis update jadwal available
- Admin bisa confirm/update appointment yang dibuat owner
- Admin bisa lihat semua appointment dari semua owner
- Sinkronisasi real-time antara admin panel dan owner portal
