<?php

require 'class.php';

use Phalcon\Logger\Adapter\File as FileAdapter;

if (!function_exists('getallheaders')) 
{ 
    function getallheaders() 
    { 
        $headers = ''; 
        foreach ($_SERVER as $name => $value) 
        { 
            if (substr($name, 0, 5) == 'HTTP_') 
            { 
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
            } 
        } 
        return $headers; 
    } 
} 

$logger = new FileAdapter(__DIR__ . "/create_callback.log");

$request = array(
    'GET' => empty($_GET) ? null : $_GET,
    'POST' => empty($_POST) ? null : $_POST,
    'POST_RAW' => @file_get_contents('php://input'),
    'HEADER' => @getallheaders()
);
$logger->log(serialize($request));

$params = array(
    'status' => '001',
    'data' => array(
        'result' => true
    ),
    'msg' => 'OK'
);

$data = $crypt->encryptByPublicKey(json_encode($params));
$response = array(
    'data' => urlencode(base64_encode($data)),
    'sign' => urlencode($crypt->sign($data))
);
echo json_encode($response);
