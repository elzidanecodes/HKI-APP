# 🏗️ HSE Asset Management System

HSE Asset Management System adalah aplikasi web berbasis **Laravel** dan **Filament v2** untuk mengelola **alat berat, operator, serta dokumen keselamatan (SIO & SILO)** dengan aturan operasional HSE yang ketat dan terkontrol.

Sistem ini dirancang untuk memastikan:
- kepatuhan terhadap regulasi HSE
- integritas data operasional
- histori penugasan yang aman untuk audit

---

## ✨ Fitur Utama

### 🚜 Manajemen Alat Berat
- Master data alat berat (tanpa relasi langsung ke operator)
- Atribut lokasi proyek (STA)
- Monitoring status dokumen SILO

### 👷 Manajemen Operator
- Master data operator independen
- Operator dapat berpindah alat melalui mekanisme assignment
- Validasi kepemilikan dan masa berlaku SIO

### 🔁 Assignment Operator ↔ Alat Berat
- Penugasan operator ke alat berat melalui **RelationManager**
- **Hanya satu assignment aktif** per operator dan per alat berat
- Assignment otomatis **diblokir** jika:
  - Operator tidak memiliki SIO aktif
  - Alat berat tidak memiliki SILO aktif
  - Alat berat masih memiliki assignment aktif
- Aksi **End Assignment** manual dengan pencatatan tanggal selesai
- Assignment aktif selalu ditampilkan di urutan teratas

---

## 🧾 Validasi Dokumen (SIO & SILO)

- SIO dan SILO dimodelkan sebagai **dokumen legal dengan masa berlaku**
- Sistem mencegah proses bisnis jika dokumen expired
- Riwayat dokumen tetap tersimpan untuk keperluan audit
- Status dokumen ditampilkan secara read-only di UI

---

## ⏱️ Otomasi & Scheduler

- Sistem menggunakan **Artisan Command terjadwal**
- Assignment aktif akan **otomatis diakhiri** jika:
  - SIO operator expired
  - SILO alat berat expired
- Scheduler dijalankan secara berkala untuk menjaga konsistensi data

---

## 📊 Dashboard & Monitoring

Dashboard Filament menyediakan ringkasan kondisi operasional:
- Total alat berat
- Total operator
- Assignment aktif
- Dokumen expired (SIO & SILO)
- Distribusi assignment aktif per STA

Widget dirancang untuk **quick awareness** dan **pengambilan keputusan cepat**.

---

## 🧱 Prinsip Arsitektur

- Alat berat dan operator adalah **master data terpisah**
- Relasi operator–alat hanya melalui **assignment**
- Aturan bisnis ditegakkan di **backend**, bukan hanya UI
- Tidak mencampur konteks **Forms** dan **Tables** (sesuai Filament v2)
- Fokus pada clean, maintainable, dan scalable code

---

## 🚀 Instalasi Singkat

```bash
git clone https://github.com/elzidanecodes/HKI-APP.git
cd REPO_NAME

composer install
npm install
npm run build

cp .env.example .env
php artisan key:generate

php artisan migrate
php artisan serve
```
---

## 📄 Lisensi

© 2025 Laita Zidan — Politeknik Negeri Malang (POLINEMA)  

Project ini dirilis dan dilindungi di bawah **Lisensi MIT**.  
Silakan gunakan, modifikasi, dan distribusikan sesuai ketentuan lisensi.

---

## 👨‍💻 Tentang Pengembang

**Laita Zidan**  
Program Studi Sistem Informasi Bisnis  
Politeknik Negeri Malang (POLINEMA)  

GitHub: https://github.com/elzidanecodes

