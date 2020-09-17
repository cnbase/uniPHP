<?php
$router = \uniPHP\core\Router::instance();
$router->add('get','/',function (){
    (new app\index\welcome())->hello();
});