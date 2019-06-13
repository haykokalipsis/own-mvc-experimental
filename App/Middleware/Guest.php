<?php

namespace App\Middleware;

use App\Auth;
use UtilityFlash;

class Guest
{
    public static function handler()
    {
        if( Auth::getUser() ) {
            Auth::rememberRequestedPage();
            header('Location: http://' . $_SERVER['HTTP_HOST'] . '/profile/show',true, 303);
        }

        return true;
    } 
}