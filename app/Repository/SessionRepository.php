<?php

namespace Belajar\PHP\MVC\Repository;

use Belajar\PHP\MVC\Domain\Session;

class SessionRepository
{
   private \PDO $connection;

   public function __construct(\PDO $connection)
   {
      $this->connection = $connection;
   }

   public function save(Session $session): Session
   {
      $statement = $this->connection->prepare("INSERT INTO sessions (id, user_id) VALUES (?, ?)");
      $statement->execute([$session->id, $session->userId]);
      return $session;
   }

   public function findById(string $id): ?Session
   {
      // 1. Siapkan query SQL untuk mencari data sesi berdasarkan ID-nya
      $statement = $this->connection->prepare("SELECT id, user_id FROM sessions WHERE id = ?");

      // 2. Eksekusi query dengan memasukkan parameter $id ke dalam array
      $statement->execute([$id]);

      try {
         // 3. Ambil data baris pertama hasil query dari database
         if ($row = $statement->fetch()) {

            // 4. Jika data ditemukan, buat objek Session baru
            $session = new Session();

            // 5. Masukkan data dari database ke dalam properti objek session
            $session->id = $row['id'];
            $session->userId = $row['user_id'];

            // 6. Kembalikan objek session tersebut
            return $session;
         } else {
            // 7. Jika data tidak ditemukan di database, kembalikan nilai null
            return null;
         }
      } finally {
         // 8. Blok finally akan selalu dijalankan untuk menutup kursor database secara bersih, 
         // baik proses di atas berhasil maupun terjadi error/exception
         $statement->closeCursor();
      }
   }

   public function deleteById(string $id): void
   {
      $statement = $this->connection->prepare("DELETE FROM sessions WHERE id = ?");
      $statement->execute([$id]);
   }

   public function deleteAll(): void
   {
      $this->connection->exec("DELETE FROM sessions");
   }
}
