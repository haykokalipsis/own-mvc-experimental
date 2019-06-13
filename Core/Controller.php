<?php
/**
 * Created by PhpStorm.
 * User: Haykokalipsis
 * Date: 12.11.2018
 * Time: 15:17
 */

namespace Core;

use Utility\Auth;
use Utility\Flash;
use App\Middleware\Mauth;

Abstract class Controller
{
    protected $route_params = [];
    protected $route_variables = [];

    public function __construct($route_params, $route_variables)
    {
        $this->route_params = $route_params;
        $this->route_variables = $route_variables;

    }

    public function __call($name, $args)
    {
        $method = $name . 'Action';

        if(method_exists($this, $method) ) {
            if($this->before() !== false) {
                call_user_func_array([$this, $method], $args);
                $this->after();
            }
        } else {
//            echo "Method {$method} not found in controller " . get_class($this);
            throw new \Exception("Method {$method} not found in controller " . get_class($this) );
        }
    }

    public function before()
    {
//        $middlewares = $this->route_params['middleware'] ?? null;
//        if($middlewares) {
////            echo "<pre>";
////            print_r($this->route_params);
////            echo "</pre>";
//
//            foreach ($middlewares as $middleware) {
//                if ( ! call_user_func(array('\App\Middleware\\' . $middleware, 'handler') ) )
//                    return false;
//            }
//            return true;
//        }
    }

    public function after()
    {
    }
    
    public function redirect($url)
    {
        header('Location: http://' . $_SERVER['HTTP_HOST'] . $url,true, 303);
        exit;
    }

    public function requireLogin()
    {

        if( ! Auth::getUser() ) {
            Flash::addMessage('Please login to access that page', Flash::INFO);
            Auth::rememberRequestedPage();
            $this->redirect('/login');
        }
    }

    public function requireGuest()
    {

        if( Auth::getUser() ) {
//            Flash::addMessage('Please login to access that page', Flash::INFO);
//            Auth::rememberRequestedPage();
            $this->redirect('/');
        }
    }
}