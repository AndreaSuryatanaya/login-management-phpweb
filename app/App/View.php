<?php

namespace Belajar\PHP\MVC\App;

class View
{
    /**
     * Render view template dengan header dan footer
     * 
     * @param string $view Nama path view (contoh: 'User/register')
     * @param mixed $model Data yang ingin dikirimkan ke view
     * @return void
     */
    public static function render(string $view, $model = []): void
    {
        // 1. Ekstrak array model agar bisa diakses langsung sebagai variabel di dalam file view
        if (is_array($model)) {
            extract($model);
        }

        $headerPath = __DIR__ . '/../View/header.php';
        $viewPath   = __DIR__ . '/../View/' . $view . '.php';
        $footerPath = __DIR__ . '/../View/footer.php';

        // 2. Sertakan header jika file-nya ada
        if (file_exists($headerPath)) {
            require $headerPath;
        }

        // 3. Cek apakah file view utama benar-benar ada sebelum di-require
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            throw new \Exception("View file not found: [{$view}] at path: {$viewPath}");
        }

        // 4. Sertakan footer jika file-nya ada
        if (file_exists($footerPath)) {
            require $footerPath;
        }
    }

    /**
     * Melakukan redirect URL dengan aman (mengabaikan exit saat mode testing)
     * 
     * @param string $url Tujuan URL redirect
     * @return void
     */
    public static function redirect(string $url): void
    {
        header("Location: $url");
        if (getenv("mode") != "test") {
            exit();
        }
    }
}
