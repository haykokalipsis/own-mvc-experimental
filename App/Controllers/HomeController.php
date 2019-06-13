<?php

namespace App\Controllers;

use \Core\View;

class HomeController extends \Core\Controller
{
    public function indexAction()
    {
//        View::render('Home/index.php', [
//            'name' => 'Dave',
//            'colours' => ['red', 'green', 'blue']
//        ]);

        View::renderTemplate('Home/index.twig', [
//            'user' => Auth::getUser()
        ]);
    }

    public function before()
    {
//        echo '(Before)';
    }

    public function after()
    {
//        echo '(After)';
    }
}