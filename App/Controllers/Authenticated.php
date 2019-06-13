<?php

namespace App\Controllers;

// Вывели функционал в мидлверь
abstract class Authenticated extends \Core\Controller
{
    public function before()
    {
        $this->requireLogin();
    }
}