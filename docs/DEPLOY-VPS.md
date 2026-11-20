# Deploy ASIC Web ke VPS (Ubuntu + Nginx + PHP-FPM + MySQL)

Panduan untuk server demo/uji coba mitra. Sesuaikan nama domain dan path. Jangan menaruh kata sandi atau data pribadi asli di repo.

## 1. Paket server

```bash
sudo apt update
sudo apt install -y nginx mysql-server git unzip curl \
  php8.3-fpm php8.3-cli php8.3-mysql php8.3-sqlite3 php8.3-mbstring php8.3-xml php8.3-curl \
  php8.3-zip php8.3-gd php8.3-intl php8.3-bcmath
curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash - && sudo apt install -y nodejs
```

Ekstensi wajib: `zip` (ekspor Excel), `gd` (encode ulang foto galeri), `intl`, `pdo_mysql`, `mbstring`, `xml`, `fileinfo`.

**Batas unggah PHP** (abstrak/template sampai 10 MB): di `/etc/php/8.3/fpm/php.ini` set `upload_max_filesize = 12M`, `post_max_size = 20M`, lalu `sudo systemctl restart php8.3-fpm`. Di Nginx: `client_max_body_size 20M;`.

## 2. Database

```sql
CREATE DATABASE asic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'asic'@'localhost' IDENTIFIED BY '<sandi-kuat>';
GRANT ALL PRIVILEGES ON asic.* TO 'asic'@'localhost';
```

## 3. Kode dan `.env`

```bash
cd /var/www && sudo git clone https://github.com/hasyfizharfan/asic-web.git asic && cd asic
sudo git switch <cabang-yang-dipakai>   # mis. feat/fe-be-integration setelah PR #3 di-merge
composer install --no-dev --optimize-autoloader
npm ci && npm run build
cp .env.example .env && php artisan key:generate
```

Nilai penting di `.env`:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<domain>
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
ADMIN_DEFAULT_PASSWORD=<sandi-admin-kuat>     # ganti setelah login pertama
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=asic
DB_USERNAME=asic
DB_PASSWORD=<sandi-db>
SESSION_DRIVER=database          # atau file
SESSION_DOMAIN=<domain>
SESSION_SECURE_COOKIE=true       # bila sudah HTTPS
SANCTUM_STATEFUL_DOMAINS=<domain>
MAIL_MAILER=log                  # email dokumen belum masuk lingkup
ASIC_CONTACT_EMAIL=              # opsional
ASIC_CHAIR_NAME=<nama ketua>     # penanda tangan LoA/penolakan/sertifikat
ASIC_TREASURER_NAME=<nama bendahara>
```

**Penting:** nilai `.env` yang mengandung `#`, spasi, atau `$` wajib diberi tanda kutip ganda (mis. `ADMIN_DEFAULT_PASSWORD="abc#123"`); tanpa kutip, `#` dibaca sebagai awal komentar sehingga nilai terpotong.

Informasi rekening tidak perlu ditaruh di `.env`: Bendahara mengisinya lewat menu **Tarif → Informasi pembayaran**.

```bash
php artisan migrate --force
php artisan db:seed --force          # peran, matriks izin, akun Admin awal (tanpa data demo karena APP_ENV=production)
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

## 4. Nginx

```nginx
server {
    listen 80;
    server_name <domain>;
    root /var/www/asic/public;
    index index.php;
    client_max_body_size 20M;

    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }
    location ~ /\.(?!well-known) { deny all; }
}
```

HTTPS: `sudo apt install certbot python3-certbot-nginx && sudo certbot --nginx -d <domain>` (butuh domain/subdomain yang mengarah ke IP VPS). Tanpa HTTPS, `SESSION_SECURE_COOKIE` harus `false`.

## 5. Penjadwal

Penutupan periode kedaluwarsa berjalan tiap jam:

```
* * * * * cd /var/www/asic && php artisan schedule:run >> /dev/null 2>&1
```

Aplikasi belum memakai antrean (queue) untuk pekerjaan penting; worker tidak wajib.

## 6. Setelah deploy

1. Login Admin (`admin@asic.unand.ac.id` / `ADMIN_DEFAULT_PASSWORD`) lalu ganti kata sandi.
2. Buat akun Sekretariat, Bendahara, Tim Abstrak lewat menu Pengguna.
3. Isi event, periode pendaftaran, tarif (IDR dan USD), informasi pembayaran, tanda tangan & stempel, konten publik (ruang lingkup, tanggal penting, FAQ, kontak, syarat & privasi), dan template unduhan.
4. Uji cepat: daftar akun peserta → registrasi → abstrak → keputusan → invoice → bukti bayar → verifikasi → QR → sertifikat.

## 7. Keamanan

- `APP_DEBUG=false` di server publik. Rute pratinjau `/_fe` hanya aktif bila `APP_ENV=local`.
- Jangan unggah berkas template panitia yang berisi tanda tangan, rekening, atau data pribadi asli ke repo.
- Batasi akses panel hosting (password kuat, firewall `ufw allow 'Nginx Full'` dan SSH saja).
- Cadangkan database harian: `mysqldump asic > backup-$(date +%F).sql`.

## 8. Catatan server aaPanel (VPS tim)

- Situs dibuat lewat aaPanel (Website → PHP 8.5), *running directory* `/public`, rewrite Laravel, SSL Let's Encrypt dari panel.
- PHP 8.5 aaPanel perlu ekstensi `fileinfo` (pasang dari App Store/PHP → Install extensions). Fungsi yang dimatikan aaPanel (`putenv`, `proc_open`, `symlink`) tidak mengganggu web karena konfigurasi di-cache; perintah CLI memakai `php-cli.ini` tanpa pembatasan.
- Composer bawaan server bisa terlalu lama (2.0.x) untuk `composer.lock`; pakai Composer terbaru terpisah (`composer.phar`) tanpa mengganti milik proyek lain.
- `public/.user.ini` membatasi `open_basedir` ke folder situs dan menaikkan batas unggah.
