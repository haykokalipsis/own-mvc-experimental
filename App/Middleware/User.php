<?php

namespace App\Middleware;

use App\Auth;
use UtilityFlash;

class User
{
    public static function handler()
    {
        if( ! Auth::getUser() ) {
            Flash::addMessage('Please login to access that page', Flash::INFO);
            Auth::rememberRequestedPage();
            header('Location: http://' . $_SERVER['HTTP_HOST'] . '/login',true, 303);
        }

        return true;
    } 
}