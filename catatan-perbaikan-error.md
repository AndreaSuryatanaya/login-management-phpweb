# Catatan Perbaikan Error — Belajar PHP MVC

---

## 1. `View::render()` tidak menyertakan layout (header & footer)

**File:** `app/App/View.php`

**Masalah:**
Method `render()` hanya me-require file view konten utama saja, tanpa menyertakan `header.php` dan `footer.php`. Akibatnya halaman tidak memiliki struktur HTML yang lengkap (`<!doctype html>`, Bootstrap CSS, tag penutup `</body></html>`).

**Sebelum:**
```php
public static function render(string $view, $model)
{
    require __DIR__ . '/../View/' . $view . '.php';
}
```

**Sesudah:**
```php
public static function render(string $view, $model)
{
    require __DIR__ . '/../View/header.php';
    require __DIR__ . '/../View/' . $view . '.php';
    require __DIR__ . '/../View/footer.php';
}
```

**Penyebab:** File `header.php` dan `footer.php` baru ditambahkan sebagai template layout bersama. Sebelum ada file ini, setiap view menuliskan struktur HTML sendiri-sendiri.

---

## 2. `app/View/Home/index.php` masih menggunakan HTML lengkap inline

**File:** `app/View/Home/index.php`

**Masalah:**
View lama menyertakan tag `<html>`, `<head>`, dan `<body>` sendiri. Ketika `View::render()` sudah diperbaiki untuk menyertakan layout bersama (`header.php` / `footer.php`), struktur HTML ini menjadi duplikat dan menyebabkan output HTML tidak valid.

**Sebelum:**
```html
<html>
<head>
<title><?= $model['title'] ?></title>
</head>
<body>
<h1><?= $model['content'] ?></h1>
</body>
</html>
```

**Sesudah:**
View hanya berisi konten halaman (tanpa wrapper HTML), karena layout sudah ditangani oleh `header.php` dan `footer.php`.

---

## 3. `HomeController` menyertakan method-method yang belum diimplementasi

**File:** `app/Controller/HomeController.php`

**Masalah:**
Controller lama berisi method `hello()`, `world()`, `about()`, dan `login()` yang belum terhubung ke View maupun Service apa pun. Method `login()` bahkan langsung membaca `$_POST` tanpa validasi, dan hasilnya tidak pernah dikirimkan ke view — berpotensi error `Undefined index` jika diakses tanpa data form.

**Perubahan:**
Semua method yang belum diimplementasi dihapus. Controller disederhanakan hanya menyisakan `index()` yang sudah bekerja dengan benar.

---

## 4. Route di `public/index.php` merujuk ke class yang sudah tidak ada

**File:** `public/index.php`

**Masalah:**
File ini mendaftarkan route untuk `ProductController` dan `AuthMiddleware`, padahal kedua class tersebut sudah dihapus dari project. Autoloader akan melempar error `Class not found` ketika route tersebut dipanggil.

**Sebelum:**
```php
use Belajar\PHP\MVC\Controller\ProductController;
use Belajar\PHP\MVC\Middleware\AuthMiddleware;

Router::add('GET', '/products/([0-9a-zA-Z]*)/categories/([0-9a-zA-Z]*)', ProductController::class, 'categories');
Router::add('GET', '/', HomeController::class, 'index');
Router::add('GET', '/hello', HomeController::class, 'hello', [AuthMiddleware::class]);
Router::add('GET', '/world', HomeController::class, 'world', [AuthMiddleware::class]);
Router::add('GET', '/about', HomeController::class, 'about');
```

**Sesudah:**
```php
Router::add('GET', '/', HomeController::class, 'index', []);
```

Hanya route yang sudah ada implementasinya yang didaftarkan.

---

## 5. PHPUnit versi terlalu lama, tidak kompatibel dengan PHP 8+

**File:** `composer.json`

**Masalah:**
Versi `phpunit/phpunit: 9.5.8` (fixed) tidak mendukung fitur-fitur PHP 8.1+ secara penuh dan tidak menerima update keamanan. Penguncian versi exact juga mencegah patch otomatis.

**Sebelum:**
```json
"phpunit/phpunit": "9.5.8"
```

**Sesudah:**
```json
"phpunit/phpunit": "^11.0"
```

PHPUnit 11 mendukung PHP 8.1+ secara native dan menggunakan atribut PHP 8 (`#[Test]`) menggantikan anotasi DocBlock.

---

## 6. Penambahan fitur baru: Template Layout bersama

**File baru:** `app/View/header.php`, `app/View/footer.php`

Dibuat template layout bersama agar semua halaman berbagi struktur HTML dan Bootstrap CSS yang konsisten. `header.php` membaca judul halaman dari `$model['title']` dengan fallback `'Login Management'`.

```php
// header.php — baris 6
<title><?= $model['title'] ?? 'Login Management' ?></title>
```

---

## 7. Penambahan fitur baru: Konfigurasi Database & class `Database`

**File baru:** `config/database.php`, `app/Config/Database.php`

Dibuat class `Database` dengan pola **Singleton** untuk mengelola koneksi PDO. Konfigurasi disimpan terpisah di `config/database.php` (di luar `app/`) agar tidak ikut di-autoload.

| Environment | Database                         |
|-------------|----------------------------------|
| `test`      | `php_login_management_test`      |
| `prod`      | `php_login_management`           |

---

## 8. Penambahan unit test

**File baru:** `test/App/ViewTest.php`, `test/Config/DatabaseTest.php`

| Test | Yang diuji |
|------|-----------|
| `ViewTest::testRender` | Output HTML dari `View::render()` mengandung judul, tag `<html>`, `<body>`, teks "Login" dan "Register" |
| `DatabaseTest::testGetConnection` | Koneksi PDO tidak null |
| `DatabaseTest::testGetConnectionSingleton` | Dua pemanggilan `getConnection()` mengembalikan objek yang sama (Singleton) |

---

## Ringkasan Perubahan File

| Status | File |
|--------|------|
| Diubah | `app/App/View.php` |
| Diubah | `app/Controller/HomeController.php` |
| Diubah | `app/View/Home/index.php` |
| Diubah | `public/index.php` |
| Diubah | `composer.json` |
| Ditambah | `app/View/header.php` |
| Ditambah | `app/View/footer.php` |
| Ditambah | `app/Config/Database.php` |
| Ditambah | `config/database.php` |
| Ditambah | `test/App/ViewTest.php` |
| Ditambah | `test/Config/DatabaseTest.php` |
| Dihapus | `app/Hello.php` |
| Dihapus | `app/Middleware/AuthMiddleware.php` |
| Dihapus | `app/View/index.php` |
| Dihapus | `app/View/login.php` |
| Dihapus | `app/View/register.php` |
| Dihapus | `test/RegexTest.php` |
