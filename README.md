# 💊 Eczane Stok Takip Sistemi · Pharmacy Stock Tracking System · Apotheken-Lagerverwaltungssystem

**🇹🇷 [Türkçe](#-türkçe) · 🇬🇧 [English](#-english) · 🇩🇪 [Deutsch](#-deutsch)**

![Ekran görüntüsü](screenshots/Ekran%20Resmi%202026-11-23%2019.49.21.png)

---

## 🇹🇷 Türkçe

Eczanelerde ilaç stoklarını takip etmek, manuel hataları ve veri kayıplarını önlemek için geliştirdiğim web tabanlı bir otomasyon sistemi. Lefke Avrupa Üniversitesi Bilgisayar Programcılığı bölümündeki bitirme projemdir.

### ✨ Özellikler
- **Rol bazlı yetkilendirme:** Yönetici ve eczacı için ayrı yetkiler, güvenli giriş/çıkış
- **İlaç yönetimi (CRUD):** İlaç ekleme, listeleme, güncelleme ve silme
- **Stok hareket takibi:** Her giriş/çıkış işlemi tarih, kullanıcı ve miktarla kaydedilir
- **Otomatik uyarılar:** Kritik stok seviyesi (≤15) ve son kullanma tarihi 60 gün içinde olan ilaçlar dashboard'da gösterilir
- **Arama ve filtreleme:** İlaç adına ve kategoriye göre
- **Raporlar ve aylık analiz:** Kategori, stok değeri ve hareket özet raporları
- **Çoklu dil desteği:** Türkçe / İngilizce arayüz
- **Modern arayüz:** Tailwind CSS ile

### 🛠️ Kullanılan Teknolojiler
PHP · MySQL · PDO · HTML · CSS · Tailwind CSS · JavaScript

### 📸 Ekran Görüntüleri
| | |
|---|---|
| ![Ekran 1](screenshots/Ekran%20Resmi%202026-11-23%2019.49.21.png) | ![Ekran 2](screenshots/Ekran%20Resmi%202026-11-23%2019.50.29.png) |
| ![Ekran 3](screenshots/Ekran%20Resmi%202026-11-23%2019.50.48.png) | ![Ekran 4](screenshots/Ekran%20Resmi%202026-11-23%2019.53.39.png) |

### ⚙️ Kurulum
1. Projeyi `htdocs` (XAMPP) veya `www` (WAMP) klasörüne kopyalayın.
2. MySQL'de `sql_files/eczane_stok.sql` dosyasını içe aktarın (import).
3. `config/database.example.php` dosyasını aynı klasöre `database.php` adıyla kopyalayın ve içindeki veritabanı bilgilerini kendi ortamınıza göre düzenleyin (port, kullanıcı adı, şifre).
4. Tarayıcıda proje adresini açın, örneğin `http://localhost/Eczane_Stok`.

### 🔑 Örnek Giriş Bilgileri
| Rol | Kullanıcı adı | Şifre |
|---|---|---|
| Yönetici | `admin` | `Admin123*` |
| Eczacı | `eczaci` | `Eczaci123*` |

> Not: Güvenlik için bu şifreler ilk girişten sonra hash'lenerek veritabanında saklanır.

### 📁 Klasör Yapısı
- `config/` : veritabanı ve oturum ayarları (`database.php` güvenlik için `.gitignore` ile repoya eklenmez)
- `includes/` : ortak fonksiyonlar ve sayfa düzeni
- `assets/` : CSS ve JS dosyaları
- `sql_files/` : veritabanı şeması ve örnek veriler
- `*.php` : uygulama ekranları

---

## 🇬🇧 English

A web-based automation system I built to track medicine stock in pharmacies and prevent manual errors and data loss. It is my graduation project for the Computer Programming program at European University of Lefke.

### ✨ Features
- **Role-based authorization:** Separate permissions for admin and pharmacist, secure login/logout
- **Medicine management (CRUD):** Create, list, update and delete medicines
- **Stock movement tracking:** Every stock in/out is recorded with date, user and quantity
- **Automatic alerts:** Critical stock (≤15) and medicines expiring within 60 days are shown on the dashboard
- **Search and filtering:** By medicine name and category
- **Reports and monthly analysis:** Category, stock value and movement summary reports
- **Multi-language support:** Turkish / English interface
- **Modern UI:** Built with Tailwind CSS

### 🛠️ Tech Stack
PHP · MySQL · PDO · HTML · CSS · Tailwind CSS · JavaScript

### 📸 Screenshots
See the [screenshots above](#-ekran-görüntüleri).

### ⚙️ Installation
1. Copy the project into `htdocs` (XAMPP) or `www` (WAMP).
2. Import `sql_files/eczane_stok.sql` into MySQL.
3. Copy `config/database.example.php` to `config/database.php` and edit the database settings to match your environment (port, user, password).
4. Open the project URL in your browser, e.g. `http://localhost/Eczane_Stok`.

### 🔑 Demo Credentials
| Role | Username | Password |
|---|---|---|
| Admin | `admin` | `Admin123*` |
| Pharmacist | `eczaci` | `Eczaci123*` |

> Note: For security, these passwords are hashed and stored in the database after the first login.

### 📁 Project Structure
- `config/` : database and session settings (`database.php` is excluded via `.gitignore` for security)
- `includes/` : shared functions and layout
- `assets/` : CSS and JS files
- `sql_files/` : database schema and sample data
- `*.php` : application pages

---

## 🇩🇪 Deutsch

Ein webbasiertes Automatisierungssystem zur Verwaltung von Medikamentenbeständen in Apotheken, das manuelle Fehler und Datenverluste vermeiden soll. Es ist mein Abschlussprojekt im Studiengang Computerprogrammierung an der Europäischen Universität Lefke.

### ✨ Funktionen
- **Rollenbasierte Autorisierung:** Getrennte Berechtigungen für Administrator und Apotheker, sicheres An- und Abmelden
- **Medikamentenverwaltung (CRUD):** Medikamente anlegen, auflisten, bearbeiten und löschen
- **Erfassung von Lagerbewegungen:** Jeder Zu- und Abgang wird mit Datum, Benutzer und Menge gespeichert
- **Automatische Warnungen:** Kritischer Bestand (≤15) und Medikamente, die innerhalb von 60 Tagen ablaufen, werden im Dashboard angezeigt
- **Suche und Filter:** Nach Medikamentenname und Kategorie
- **Berichte und Monatsanalyse:** Berichte zu Kategorien, Lagerwert und Bewegungen
- **Mehrsprachigkeit:** Türkische / englische Benutzeroberfläche
- **Moderne Oberfläche:** Umgesetzt mit Tailwind CSS

### 🛠️ Technologien
PHP · MySQL · PDO · HTML · CSS · Tailwind CSS · JavaScript

### 📸 Screenshots
Siehe die [Screenshots oben](#-ekran-görüntüleri).

### ⚙️ Installation
1. Das Projekt in den Ordner `htdocs` (XAMPP) oder `www` (WAMP) kopieren.
2. Die Datei `sql_files/eczane_stok.sql` in MySQL importieren.
3. `config/database.example.php` nach `config/database.php` kopieren und die Datenbankeinstellungen (Port, Benutzer, Passwort) anpassen.
4. Die Projekt-URL im Browser öffnen, z. B. `http://localhost/Eczane_Stok`.

### 🔑 Demo-Zugangsdaten
| Rolle | Benutzername | Passwort |
|---|---|---|
| Administrator | `admin` | `Admin123*` |
| Apotheker | `eczaci` | `Eczaci123*` |

> Hinweis: Aus Sicherheitsgründen werden diese Passwörter nach der ersten Anmeldung gehasht in der Datenbank gespeichert.

### 📁 Projektstruktur
- `config/` : Datenbank- und Sitzungseinstellungen (`database.php` wird aus Sicherheitsgründen per `.gitignore` ausgeschlossen)
- `includes/` : gemeinsame Funktionen und Layout
- `assets/` : CSS- und JS-Dateien
- `sql_files/` : Datenbankschema und Beispieldaten
- `*.php` : Anwendungsseiten

---

👩‍💻 **Nurhan Akça** · [GitHub](https://github.com/NurhanAkca) · nrhnakc@icloud.com
