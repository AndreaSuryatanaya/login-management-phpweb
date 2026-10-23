<?php

namespace Belajar\PHP\MVC\Middleware;

use Belajar\PHP\MVC\App\View;
use Belajar\PHP\MVC\Config\Database;
use Belajar\PHP\MVC\Middleware\Middleware;
use Belajar\PHP\MVC\Repository\SessionRepository;
use Belajar\PHP\MVC\Repository\UserRepository;
use Belajar\PHP\MVC\Service\SessionService;

class MustNotLoginMiddleware implements Middleware
{
   private SessionService $sessionService;

   public function __construct()
   {
      $sessionRepository = new SessionRepository(Database::getConnection());
      $userRepository = new UserRepository(Database::getConnection());
      $this->sessionService = new SessionService($sessionRepository, $userRepository);
   }


   function before(): void
   {
      $user = $this->sessionService->current();
      if ($user != null) {
         View::redirect('/');
      }
   }
}
