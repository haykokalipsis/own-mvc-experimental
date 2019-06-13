<?php

namespace App\Controllers;

use Utility\Auth;
use \Core\View;
use \App\Models\Post;
/**
 * Posts controller
 *
 * PHP version 5.4
 */
class PostController extends \Core\Controller
//class Post extends AuthenticatedController
{

    /**
     * Show the index page
     *
     * @return void
     */
    public function before()
    {
        $this->requireLogin();
    }

    public function indexAction()
    {
//        if ( ! Auth::isLoggedIn())
//            $this->redirect('/login');

//        $this->requireLogin();
        $posts = Post::getAll();

        View::renderTemplate('Posts/index.twig', [
            'posts' => $posts
        ]);
    }

    /**
     * Show the add new page
     *
     * @return void
     */
    public function addNewAction()
    {
        echo 'Hello from the addNew action in the Posts controller!';
    }

    /**
     * Show the edit page
     *
     * @return void
     */
    public function editAction()
    {
        echo 'Hello from the edit action in the Posts controller!';
        echo '<p>Route parameters: <pre>' .
            htmlspecialchars(print_r($this->route_params, true)) . '</pre></p>';
    }
}
