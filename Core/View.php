<?php

namespace Core;

class View
{
    /**
     * @param $view
     */
//    public static function render($view, $args = [])
//    {
//        extract($args, EXTR_SKIP);
//
//        $file = "../App/Views/$view"; // relative to Core directory
//
//        if(is_readable($file) )
//            require $file;
//        else
//            //echo "$file not found";
//            throw new \Exception("$file not found");
//
//    }

    public static function renderTemplate($template, $args = [])
    {
        echo static::getTemplate($template, $args);
    }

    public static function getTemplate($template, $args = [])
    {
        static $twig = null;

        if ($twig === null) {
            $loader = new \Twig_Loader_Filesystem('../App/Views');
            $twig = new \Twig_Environment($loader);
//            $twig->addGlobal('session', $_SESSION);
//            $twig->addGlobal('is_logged_in',\App\Auth::isLoggedIn() );
            $twig->addGlobal('current_user',\Utility\Auth::getUser() );
            $twig->addGlobal('flash_messages',\Utility\Flash::getMessages() );
        }

        return $twig->render($template, $args);

    }
}