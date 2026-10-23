<?php

namespace Belajar\PHP\MVC\Service;

use Belajar\PHP\MVC\Config\Database;
use Belajar\PHP\MVC\Domain\User;
use Belajar\PHP\MVC\Exception\ValidationException;
use Belajar\PHP\MVC\Model\UserRegisterRequest;
use Belajar\PHP\MVC\Model\UserRegisterResponse;
use Belajar\PHP\MVC\Model\UserLoginRequest;
use Belajar\PHP\MVC\Model\UserLoginResponse;
use Belajar\PHP\MVC\Model\UserPasswordUpdateRequest;
use Belajar\PHP\MVC\Model\UserPasswordUpdateResponse;
use Belajar\PHP\MVC\Model\UserProfileUpdateRequest;
use Belajar\PHP\MVC\Model\UserProfileUpdateResponse;
use Belajar\PHP\MVC\Repository\UserRepository;

class UserService
{
   private UserRepository $userRepository;

   public function __construct(UserRepository $userRepository)
   {
      $this->userRepository = $userRepository;
   }

   private function validateUserRegistrationRequest(UserRegisterRequest $request)
   {
      if (
         $request->id == null || $request->name == null || $request->password == null ||
         trim($request->id) == "" || trim($request->name) == "" || trim($request->password) == ""
      ) {
         throw new ValidationException("Id, Name, Password can not blank");
      }
   }

   public function register(UserRegisterRequest $request): UserRegisterResponse
   {
      // 1. Lakukan validasi terhadap data request yang dikirimkan
      $this->validateUserRegistrationRequest($request);

      try {
         // 2. Mulai transaksi database (agar data konsisten dan bisa dibatalkan jika ada error)
         Database::beginTransaction();

         // 3. Cek apakah user dengan ID tersebut sudah ada di database
         $user = $this->userRepository->findById($request->id);

         // 4. Jika user sudah terdaftar, lempar exception karena ID tidak boleh kembar
         if ($user != null) {
            throw new ValidationException("User Is already exists");
         }

         // 5. Buat instance User baru dan mapping data dari request (password di-hash menggunakan bcrypt)
         $user = new User();
         $user->id = $request->id;
         $user->name = $request->name;
         $user->password = password_hash($request->password, PASSWORD_BCRYPT);

         // 6. Simpan data user baru ke dalam database melalui repository
         $this->userRepository->save($user);

         // 7. Siapkan objek DTO response dan masukkan data user ke dalamnya
         $response = new UserRegisterResponse();
         $response->user = $user;

         // 8. Commit transaksi database karena seluruh proses berhasil
         Database::commitTransaction();

         // 9. Kembalikan objek response
         return $response;
      } catch (\Exception $exception) {
         // 10. Jika terjadi error di tengah jalan, batalkan semua perubahan database (rollback)
         Database::rollbackTransaction();

         // Lempar kembali exception-nya agar bisa ditangkap oleh Controller
         throw $exception;
      }
   }

   private function validateUserLoginRequest(UserLoginRequest $request)
   {
      if (
         $request->id == null || $request->password == null ||
         trim($request->id) == "" || trim($request->password) == ""
      ) {
         throw new ValidationException("Id, Password can not blank");
      }
   }

   public function login(UserLoginRequest $request): UserLoginResponse
   {
      // 1. Lakukan validasi terhadap format data request login (misal: memastikan field tidak kosong)
      $this->validateUserLoginRequest($request);

      // 2. Cari data user di database berdasarkan ID yang dimasukkan
      $user = $this->userRepository->findById($request->id);

      // 3. Jika user tidak ditemukan, lempar exception dengan pesan error umum (demi keamanan)
      if ($user == null) {
         throw new ValidationException("Id or password is wrong");
      }

      // 4. Cocokkan password yang diinput user dengan hash password yang tersimpan di database
      if (password_verify($request->password, $user->password)) {

         // 5. Jika password valid/cocok, buat objek response login
         $response = new UserLoginResponse();

         // 6. Masukkan data user ke dalam objek response
         $response->user = $user;

         // 7. Kembalikan data response tanda login berhasil
         return $response;
      } else {
         // 8. Jika password salah, lempar exception yang sama
         throw new ValidationException("Id or password is wrong");
      }
   }

   public function updateProfile(UserProfileUpdateRequest $request): UserProfileUpdateResponse
   {
      $this->validateUserProfileUpdateRequest($request);

      try {
         Database::beginTransaction();

         $user = $this->userRepository->findById($request->id);
         if ($user == null) {
            throw new ValidationException("User is not found");
         }

         $user->name = $request->name;
         $this->userRepository->update($user);

         Database::commitTransaction();

         $response = new UserProfileUpdateResponse();
         $response->user = $user;
         return $response;
      } catch (\Exception $exception) {
         Database::rollbackTransaction();
         throw $exception;
      }
   }

   public function validateUserProfileUpdateRequest(UserProfileUpdateRequest $request)
   {
      if (
         $request->id == null || $request->name == null ||
         trim($request->id) == "" || trim($request->name) == ""
      ) {
         throw new ValidationException("Id, Name can not blank");
      }
   }

   public function updatePassword(UserPasswordUpdateRequest $request): UserPasswordUpdateResponse
   {
      $this->validateUserPasswordUpdateRequest($request);
      try {
         Database::beginTransaction();

         $user = $this->userRepository->findById($request->id);
         if ($user == null) {
            throw new ValidationException("User is not found");
         }

         if (!password_verify($request->oldPassword, $user->password)) {
            throw new ValidationException("Old password is wrong");
         }

         $user->password = password_hash($request->newPassword, PASSWORD_BCRYPT);
         $this->userRepository->update($user);

         Database::commitTransaction();

         $response = new UserPasswordUpdateResponse();
         $response->user = $user;
         return $response;
      } catch (\Exception $exception) {
         Database::rollbackTransaction();
         throw $exception;
      }
   }

   private function validateUserPasswordUpdateRequest(UserPasswordUpdateRequest $request)
   {
      if (
         $request->id == null || $request->oldPassword == null || $request->newPassword == null ||
         trim($request->id) == "" || trim($request->oldPassword) == "" || trim($request->newPassword) == ""
      ) {
         throw new ValidationException("Id, Old Password, New Password can not blank");
      }
   }
}
