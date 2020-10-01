<?php
include '../uniPHP.php';

uniPHP::instance([
        'entryFile' =>  'index.php',
        'ROOT_DIR'  =>  __DIR__.'/../',
        'WEB_DIR'   =>  __DIR__,
        'APP_DIR'   =>  __DIR__.'/../app',
        'CONF_DIR'  =>  __DIR__.'/../config',
        'ROUTE_DIR' =>  __DIR__.'/../route',
        'MODULE_NAME'   =>  'index',
    ])->onBeforeCreate(function (){echo 'onBeforeCreate<br>';})->onCreated(function (){echo '<br>onCreated';})->run();