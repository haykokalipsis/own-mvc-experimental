<?php

namespace App\Controllers;

use Utility\Auth;
use Utility\Flash;
use Core\Controller;
use Core\View;

class ProfileController extends Authenticated
{
//    public function before()
//    {
//        parent::before();
//        $this->user = Auth::getUser();
//        $this->requireLogin();
//    }

    public function showAction()
    {
        View::renderTemplate('Profile/show.twig', [
            'user' => Auth::getUser()
        ]);
    }

    public function editAction()
    {
        View::renderTemplate('Profile/edit.twig', [
            'user' => Auth::getUser()
        ]);
    }

    public function updateAction()
    {
        $user = Auth::getUser();

        if($user->updateProfile($_POST)) {
            Flash::addMessage('Changes saved');
            $this->redirect('/profile/show');
        } else {
            View::renderTemplate('Profile/edit.twig', [
                'user' => $user
            ]);
        }
    }



}