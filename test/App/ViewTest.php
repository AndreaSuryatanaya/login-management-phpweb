<?php

use PHPUnit\Framework\TestCase;
use Belajar\PHP\MVC\App\View;

class ViewTest extends TestCase
{
   public function testRender()
   {
      // Tangkap output dari View::render
      ob_start();
      View::render('Home/index', [
         "title" => "PHP Login Management"
      ]);
      $output = ob_get_clean();

      // Pastikan string-string ini benar-benar ada di dalam output HTML
      $this->assertStringContainsString('PHP Login Management', $output);
      $this->assertStringContainsString('<html>', $output);
      $this->assertStringContainsString('<body>', $output);
      $this->assertStringContainsString('Login', $output);
      $this->assertStringContainsString('Register', $output);
   }
}
