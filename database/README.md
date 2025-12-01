# Database Documentation - VetClinic Simplified

## 📁 File Database

Hanya ada **1 file** yang perlu digunakan:

```
database/
└── init.sql    # File utama untuk inisialisasi database
```

---

## 🗄️ Struktur Database

### Tabel yang Digunakan

| No | Tabel | Fungsi | Jumlah Kolom |
|----|-------|--------|--------------|
| 1 | **users** | Admin yang mengelola sistem | 8 |
| 2 | **owner** | Pemilik hewan peliharaan | 9 |
| 3 | **veterinarian** | Dokter hewan | 11 |
| 4 | **pet** | Hewan peliharaan | 13 |
| 5 | **appointment** | Janji temu pemeriksaan | 11 |
| 6 | **medical_record** | Rekam medis/pertemuan | 13 |
| 7 | **doctor_schedule** | Jadwal praktek dokter | 6 |

**Total: 7 tabel**

---

## 📊 Diagram Relasi

```
users (Admin)
    
owner ──┬── pet ──┬── appointment ──── medical_record
        │         │         │
        │         └─────────┘
        │                   │
        └───────────────────┘
                            │
                    veterinarian ──── doctor_schedule
```

---

## 🚀 Cara Instalasi

### Opsi 1: MySQL Command Line

```bash
# Login ke MySQL
mysql -u root -p

# Import database
source /path/to/VetClinic-web-app/database/init.sql

# Atau langsung
mysql -u root -p < database/init.sql
```

### Opsi 2: phpMyAdmin

1. Buka phpMyAdmin
2. Klik tab "Import"
3. Pilih file `init.sql`
4. Klik "Go"

### Opsi 3: MySQL Workbench

1. Buka MySQL Workbench
2. File → Run SQL Script
3. Pilih `init.sql`
4. Execute

---

## 🔑 Login Credentials Default

### Admin
- **Username**: `admin`
- **Password**: `admin123`
- **Email**: `admin@vetclinic.com`
- **Role**: Admin

### Owner (Testing)
- **Email**: `owner@test.com`
- **Password**: `owner123`
- **Nama**: John Doe

- **Email**: `jane@test.com`
- **Password**: `owner123`
- **Nama**: Jane Smith

---

## 📋 Detail Tabel

### 1. users
Tabel untuk admin sistem
```sql
- user_id (PK)
- username (UNIQUE)
- password (hashed)
- nama_lengkap
- email (UNIQUE)
- role (Admin/Staff)
- status (Aktif/Nonaktif)
- created_at
- last_login
```

### 2. owner
Tabel untuk pemilik hewan
```sql
- owner_id (PK)
- nama_lengkap
- alamat
- no_telepon
- email (UNIQUE)
- password (hashed)
- tanggal_registrasi
- catatan
- status (Aktif/Nonaktif)
```

### 3. veterinarian
Tabel untuk dokter hewan
```sql
- dokter_id (PK)
- nama_dokter
- no_lisensi (UNIQUE)
- spesialisasi (Umum/Bedah/Gigi/Kulit/Kardio/Eksotik)
- no_telepon
- email
- jadwal_praktek
- status (Aktif/Cuti/Resign)
- foto_url
- tanggal_bergabung
- created_at
```

### 4. pet
Tabel untuk hewan peliharaan
```sql
- pet_id (PK)
- owner_id (FK → owner)
- nama_hewan
- jenis (Anjing/Kucing/Burung/Kelinci/Hamster/Reptil/Lainnya)
- ras
- jenis_kelamin (Jantan/Betina)
- tanggal_lahir
- berat_badan
- warna
- ciri_khusus
- foto_url
- status (Aktif/Meninggal)
- tanggal_registrasi
```

### 5. appointment
Tabel untuk janji temu
```sql
- appointment_id (PK)
- pet_id (FK → pet)
- owner_id (FK → owner)
- dokter_id (FK → veterinarian)
- tanggal_appointment
- jam_appointment
- jenis_layanan
- status (Pending/Confirmed/Completed/Cancelled/No_Show)
- keluhan
- catatan
- created_at
- updated_at
```

### 6. medical_record
Tabel untuk rekam medis
```sql
- record_id (PK)
- pet_id (FK → pet)
- dokter_id (FK → veterinarian)
- appointment_id (FK → appointment, nullable)
- tanggal_kunjungan
- keluhan
- diagnosis
- tindakan
- resep
- catatan_dokter
- berat_badan
- suhu_tubuh
- biaya
- created_at
- updated_at
```

### 7. doctor_schedule
Tabel untuk jadwal dokter
```sql
- schedule_id (PK)
- dokter_id (FK → veterinarian)
- hari (Senin/Selasa/Rabu/Kamis/Jumat/Sabtu/Minggu)
- jam_mulai
- jam_selesai
- status (Aktif/Nonaktif)
```

---

## 🔍 Views yang Tersedia

### v_appointment_detail
View untuk melihat appointment dengan detail lengkap (pet, owner, dokter)

### v_medical_record_detail
View untuk melihat medical record dengan detail lengkap

---

## ⚙️ Stored Procedures

### sp_approve_appointment(appointment_id)
Procedure untuk approve appointment (ubah status ke Confirmed)

```sql
CALL sp_approve_appointment(1);
```

### sp_reject_appointment(appointment_id)
Procedure untuk reject appointment (ubah status ke Cancelled)

```sql
CALL sp_reject_appointment(2);
```

---

## 🎯 Sample Data

File `init.sql` sudah include sample data:

- ✅ 1 Admin user
- ✅ 3 Dokter hewan
- ✅ Jadwal praktek dokter
- ✅ 2 Owner (untuk testing)
- ✅ 4 Hewan peliharaan
- ✅ 4 Appointment
- ✅ 1 Medical record

---

## 🔒 Security Features

1. **Password Hashing**: Menggunakan bcrypt (`$2y$10$`)
2. **Foreign Key Constraints**: Menjaga integritas data
3. **Indexes**: Untuk performa query yang lebih baik
4. **Triggers**: Auto-update timestamp
5. **Cascade Delete**: Data terkait otomatis terhapus

---

## 📝 Status Appointment

| Status | Keterangan |
|--------|------------|
| **Pending** | Menunggu persetujuan admin |
| **Confirmed** | Disetujui admin |
| **Completed** | Pemeriksaan selesai |
| **Cancelled** | Ditolak/dibatalkan |
| **No_Show** | Pasien tidak datang |

---

## 🔄 Workflow Database

### Owner membuat appointment:
```sql
INSERT INTO appointment (pet_id, owner_id, dokter_id, ...) 
VALUES (...);
-- Status otomatis: Pending
```

### Admin approve appointment:
```sql
CALL sp_approve_appointment(appointment_id);
-- Status berubah: Confirmed
```

### Dokter buat medical record:
```sql
INSERT INTO medical_record (pet_id, dokter_id, appointment_id, ...) 
VALUES (...);

UPDATE appointment SET status = 'Completed' 
WHERE appointment_id = ...;
```

---

## 🛠️ Maintenance

### Backup Database
```bash
mysqldump -u root -p vetclinic > backup_$(date +%Y%m%d).sql
```

### Restore Database
```bash
mysql -u root -p vetclinic < backup_20251201.sql
```

### Reset Database
```bash
mysql -u root -p < database/init.sql
```

---

## ⚠️ Catatan Penting

1. **Password Default**: Ganti password default setelah instalasi!
2. **Production**: Hapus sample data sebelum deploy ke production
3. **Backup**: Selalu backup database sebelum update
4. **Indexes**: Sudah dioptimasi untuk performa

---

## 📞 Troubleshooting

### Error: Database already exists
```sql
DROP DATABASE vetclinic;
-- Lalu jalankan init.sql lagi
```

### Error: Foreign key constraint fails
Pastikan urutan import benar (sudah diatur di init.sql)

### Error: Access denied
```sql
GRANT ALL PRIVILEGES ON vetclinic.* TO 'your_user'@'localhost';
FLUSH PRIVILEGES;
```

---

**Database Version**: 2.0 (Simplified)  
**Last Updated**: 2025-12-01  
**Status**: ✅ Ready to Use
