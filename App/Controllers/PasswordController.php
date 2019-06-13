<?php

namespace App\Controllers;

use App\Models\User;
use \Core\View;

class PasswordController extends \Core\Controller
{
    public function forgotAction()
    {
        View::renderTemplate('Password/forgot.twig');
    }

    public function requestResetAction()
    {
        User::sendPasswordReset($_POST['email']);
        View::renderTemplate('Password/reset_requested.twig');
    }
    
    public function resetAction($token)
    {
//        $token = $this->route_params['token'];
        $user = $this->getUserOrExit($token);

        View::renderTemplate('Password/reset.twig', [
            'token' => $token
        ]);

    } 
    
    public function resetPasswordAction()
    {
        $token = $_POST['token'];
        $user = $this->getUserOrExit($token);
        if($user->resetPassword($_POST['password'], $_POST['passwordConfirmation']) ) {
            View::renderTemplate('Password/reset_success.twig');
        } else {
            View::renderTemplate('Password/reset.twig', [
                'token' => $token,
                'user' => $user
            ]);
        }
    } 
    
    public function getUserOrExit($token)
    {
        $user = User::findByPasswordReset($token);
        if ($user) {
            return $user;
        } else {
            View::renderTemplate('Password/token_expired.twig');
            exit;
        }
    } 
}