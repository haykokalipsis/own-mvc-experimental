<?php

namespace App\Controllers\Auth;

use Utility\Auth;
use App\Controllers\Authenticated;
use Utility\Flash;
use App\Models\User;
use \Core\View;

class LoginController extends \Core\Controller
{

//    public function before()
//    {
//        $this->requireGuest();
//    }

    public function createAction()
    {
        $this->requireGuest();
        View::renderTemplate('Auth/login.twig');
    }

    public function attempt()
    {
        $this->requireGuest();
        $user = User::authenticate($_POST['email'], $_POST['password']);

        $remember_me = isset($_POST['remember_me']);

        if($user) {
            Auth::login($user, $remember_me);
            Flash::addMessage('Login successful',Flash::SUCCESS);
            $this->redirect(Auth::getReturnToPage() );
        } else {
            Flash::addMessage('Login unsuccessful, please check your credentials', Flash::WARNING);
            View::renderTemplate('Auth/login.twig', [
                'email' => $_POST['email'],
                'remember_me' => $remember_me
            ]);
        }
    }

    public function logoutAction()
    {
        Auth::logout();
        $this->redirect('/logout/show-logout-message');
    }

    public function showLogoutMessageAction()
    {
//        $this->requireGuest();
        Flash::addMessage('Logout successful');
        $this->redirect('/');
    }

}