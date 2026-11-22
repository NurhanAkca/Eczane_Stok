# Web Tabanli Eczane Stok Takip Sistemi

Bu proje PHP, MySQL, HTML, CSS ve JavaScript kullanilarak gelistirilmis bir eczane stok takip uygulamasidir.

## Ozellikler

- Kullanici girisi ve rol bazli yetkilendirme (admin / eczaci)
- Ilac ekleme, guncelleme, silme
- Stok giris-cikis hareket kayitlari
- Son kullanma tarihi yaklasan ilaclarin listelenmesi
- Kritik stok seviyesi uyarilari
- Ilac arama ve kategori filtreleme
- Kategori, stok degeri ve hareket ozet raporlari
- Tailwind CSS tabanli modern arayuz

## Kurulum

1. Projeyi `htdocs` (XAMPP) veya `www` (WAMP) altina kopyalayin.
2. MySQL'de `sql_files/eczane_stok.sql` dosyasini import edin.
3. Gerekirse `config/database.php` icindeki veritabani baglanti bilgilerini duzenleyin.
4. Tarayicida proje URL'sini acin.

## Ornek Giris Bilgileri

- Yonetici: `admin` / `Admin123*`
- Eczaci: `eczaci` / `Eczaci123*`

Not: Guvenlik sebebiyle bu sifreler ilk giristen sonra hash'e cevrilerek veritabaninda saklanir.

## Klasor Yapisi

- `config/`: veritabani ve oturum ayarlari
- `includes/`: ortak fonksiyonlar ve layout
- `assets/`: CSS ve JS dosyalari
- `sql_files/`: veritabani semasi ve ornek veri
- `*.php`: uygulama ekranlari
