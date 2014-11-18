<?php

$callbackData = <<<'C'
a:4:{s:3:"GET";N;s:4:"POST";a:1:{s:5:"hello";s:5:"World";}s:8:"POST_RAW";s:11:"hello=World";s:6:"HEADER";a:7:{s:4:"Host";s:9:"localhost";s:14:"Content-Length";s:2:"11";s:15:"Accept-Encoding";s:13:"gzip, deflate";s:6:"Accept";s:3:"*/*";s:10:"User-Agent";s:12:"HTTPie/0.8.0";s:10:"Connection";s:10:"keep-alive";s:12:"Content-Type";s:48:"application/x-www-form-urlencoded; charset=utf-8";}}
C;

function p($r)
{
    if (function_exists('xdebug_var_dump')) {
        echo '<pre>';
        xdebug_var_dump($r);
        echo '</pre>';
        //(new \Phalcon\Debug\Dump())->dump($r, true);
    } else {
        echo '<pre>';
        var_dump($r);
        echo '</pre>';
    }
}

p(unserialize($callbackData));
