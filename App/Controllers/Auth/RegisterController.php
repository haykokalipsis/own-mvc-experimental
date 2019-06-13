<?php
/**
 * Created by PhpStorm.
 * User: Haykokalipsis
 * Date: 13.11.2018
 * Time: 15:34
 */

namespace App\Controllers\Auth;

use App\Models\User;
use \Core\View;

class RegisterController extends \Core\Controller
{
    public function createAction()
    {
        View::renderTemplate('Auth/register.twig');
    }
    
    public function store()
    {
        $user = new User($_POST);
        if($user->store() ) {
            $user->sendActivationEmail();
            $this->redirect('/register/success');
            // recommended redirect method
        } else {
            View::renderTemplate('Auth/register.twig',[
                'user' => $user
            ]);
        }
    }

    public function successAction()
    {
        View::renderTemplate('Auth/success.twig');
    }

    public function validateEmailAction()
    {
        // Move this method elsewhere
        $is_valid = ! User::emailExists($_GET['email'], $_GET['ignore_id'] ?? null);
        header('Content-Type: application/json');
        echo json_encode($is_valid);
    }

    public function activateAction($token)
    {
//        User::activate($this->route_params['token']);
        User::activate($token);
        $this->redirect('/register/activated');
    }

    public function activatedAction()
    {
        View::renderTemplate('Auth/activated.twig');
    }
}