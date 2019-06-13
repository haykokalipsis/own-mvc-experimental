<?php
// Autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Error and Exception handling
error_reporting(E_ALL);
set_error_handler('Core\Error::errorhandler');
set_exception_handler('Core\Error::exceptionHandler');

session_start();

// Router
$router = new Core\Router();

$router->get('', ['controller' => 'HomeController', 'action' => 'index']);
$router->get('posts', ['controller' => 'PostController', 'action' => 'index']);
//$router->add('posts/new', ['controller' => 'Post', 'action' => 'new']);
//$router->add('posts', ['controller' => 'Posts', 'action' => 'index']);

$router->get('validate-email', ['controller' => 'Auth\RegisterController', 'action' => 'validate-email']);
$router->get('register/create', ['controller' => 'Auth\RegisterController', 'action' => 'create']);
$router->post('register/store', ['controller' => 'Auth\RegisterController', 'action' => 'store']);
$router->get('register/success', ['controller' => 'Auth\RegisterController', 'action' => 'success']);
$router->get('register/activated', ['controller' => 'Auth\RegisterController', 'action' => 'activated']);
$router->get('register/activate/{token:[\da-f]+}', ['controller' => 'Auth\RegisterController', 'action' => 'activate']);

$router->get('login', ['controller' => 'Auth\LoginController', 'action' => 'create']);
$router->post('login/attempt', ['controller' => 'Auth\LoginController', 'action' => 'attempt']);
$router->get('logout', ['controller' => 'Auth\LoginController', 'action' => 'logout']);
$router->get('logout/show-logout-message', ['controller' => 'Auth\LoginController', 'action' => 'show-logout-message']);

$router->get('password/forgot', ['controller' => 'PasswordController', 'action' => 'forgot']);
$router->post('password/request-reset', ['controller' => 'PasswordController', 'action' => 'request-reset']);
$router->get('password/reset/{token:[\da-f]+}', ['controller' => 'PasswordController', 'action' => 'reset']);
$router->post('password/reset-password', ['controller' => 'PasswordController', 'action' => 'reset-password']);

$router->get('profile/show', ['controller' => 'ProfileController', 'action' => 'show']);
$router->get('profile/edit', ['controller' => 'ProfileController', 'action' => 'edit']);
$router->post('profile/update', ['controller' => 'ProfileController', 'action' => 'update']);

$url = $_SERVER['QUERY_STRING'];
$method = $_SERVER['REQUEST_METHOD'];

$router->dispatch($url, $method);

