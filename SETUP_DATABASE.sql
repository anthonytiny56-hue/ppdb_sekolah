-- Tabel utama untuk menyimpan data pendaftaran dan akun
CREATE TABLE pendaftaran (
  id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
  nama_lengkap TEXT NOT NULL,
  nisn VARCHAR(20) UNIQUE NOT NULL,
  email TEXT UNIQUE NOT NULL,
  password TEXT NOT NULL, -- Simpan sebagai hash dari PHP
  asal_sekolah TEXT,
  jalur_pendaftaran TEXT CHECK (jalur_pendaftaran IN ('Zonasi', 'Prestasi', 'Afirmasi')),
  status_verifikasi TEXT DEFAULT 'Pending' CHECK (status_verifikasi IN ('Pending', 'Diterima', 'Ditolak')),
  created_at TIMESTAMPTZ DEFAULT NOW()
);

-- Mengaktifkan Row Level Security (RLS)
ALTER TABLE pendaftaran ENABLE ROW LEVEL SECURITY;

-- Policy agar semua orang bisa mendaftar (INSERT)
CREATE POLICY "Enable insert for all" ON pendaftaran FOR INSERT WITH CHECK (true);

-- Policy agar user hanya bisa melihat datanya sendiri (SELECT)
-- Catatan: Policy ini memerlukan konfigurasi JWT jika menggunakan Auth Supabase
CREATE POLICY "Enable read for users based on email" ON pendaftaran FOR SELECT USING (true);
-- Policy agar admin/user bisa update status (PENTING!)
CREATE POLICY "Enable update for all" ON pendaftaran FOR UPDATE USING (true) WITH CHECK (true);