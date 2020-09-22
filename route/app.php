<?php
return [
    [
        'method'    =>  'get',
        'path'      =>  '/',
        'callback'  =>  function(){
            (new app\index\welcome())->hello();
        },
        'isRegular' =>  false
    ],
];