﻿<?php
    
require 'class.php';
/*
//String source = "{\"data\":\"baidutest\",\"stats\":\"2\"}";
$source = "{\"data\":\"baidutest\",\"stats\":\"2\"}";

//byte[] encodedData = BaiduUtil.encryptByPublicKey(data, publicKey);
$encodedData = $crypt->encryptByPublicKey($source);

//System.out.println("加密后：\r\n" + Base64Utils.encode(encodedData) );
p("加密后:" . $crypt->encode($encodedData));

//byte[] decodedData = BaiduUtil.decryptByPrivateKey(encodedData, privateKey);
$decodeData = $crypt->decryptByPrivateKey($encodedData);

//System.err.println("解密后: \r\n" + target);
p("解密后:" . $decodeData);

//String sign = BaiduUtil.sign(encodedData, privateKey);
$sign = $crypt->sign($encodedData);

//System.err.println("签名:\r" + sign);
p("签名:" . $sign);

//boolean status = BaiduUtil.verify(encodedData, bd_publicKeyStr, sign);
$status = $crypt->verify($encodedData, $sign, $publicKeyString);

//System.out.println("验证结果:\r" + status);
p("验证结果:" . $status);
*/



//解密及验签测试数据
/*
$encodedData = <<<'DATA'
ZnKCVkisoBPpr1cxl+oIJD+ItGdYfcEzoZuLsEZb3udVDCvOYjwSaBX8RF6NC1mKq4IXSTqtVPbn425CvQM3PDXL12f1utPkPjlwmg4v3onKoyj9U7f5EfRObuaW2Pp+faZoK/dH5KpU3x3cd7rlHMFoy7APahyy2bLnV9Tqpn/nRGx4yFkADPAy2Bxs5kn5CKYpXgdA+KzwaPEPJ/XKLfrbPqTRHhbrNF4n0ZWyhV0LCIhDej8WnOMQlaR4yLevQ5tmRqqWvg6QlJyllqjTHu193K4ob5Qu/lMxHXdAMxtW2rLNtlBN3pn4St/w/+xPMSKqhlpKrx3jEy9MvDIHyw==
DATA;

//echo $encodedData = urldecode($encodedData);

$decodeData = $crypt->decryptByPrivateKey(base64_decode($encodedData));
p("解密后:" . $decodeData);
//签名
//Oy0e8PHZI1t97tJZXbbHsezKztQzv42N/DXujHFnyqR8klnUYNARK4Djf/9oNKs5QiRZHWGX+B9JPKQG/jDR1/EFnO8cVNblnqXVZAplFwikM4G7MqzSnP4SZ6JaUr6bO+eof47j0Xubv6SyyrI8fBOTShq7zUKOUx6dk7n9ULVJSho7o6wFz9KiD8R/b434xKBS+CpTmKLLtKBXIynZEzfbxojKOQIRkT6wgSqFSsobI53wSKtBMvzonXWQvnwff0G4JJcJvqANIR42+Ziw8F7rSO2QqXRqqiL0wksedcwDhGwK3YVCugoX86CbhWlnRAKItrcuzIRwbIRvafI2Sw==
$sign = $crypt->sign(base64_decode($encodedData));
p("签名:" . $sign);
$status = $crypt->verify(base64_decode($encodedData), $sign, $publicKeyString);
p("验证结果:" . $status);
*/

$api = new ZhongshanAPI();
function generateLink($url, $params, $name)
{
    global $crypt;
    $data = $crypt->encryptByPublicKey($params);
    echo '<p><a href="' . $url . '?data=' . urlencode(base64_encode($data)) . '&sign=' . urlencode($crypt->sign($data)) . '">' . $name . '</a></p>';
}

function generateApi($params, $name)
{
    global $api;
    echo '<p><a href="' . $api->buildUrl($params) . '">' . $name . '</a></p>';
}


p('------------------------------ 网页请求');

generateLink('https://121.15.129.248/', '{"merid":"00000009","custno":"1"}', '开户');

generateLink('http://121.15.129.252:8089/zswww/views/account/accountbind.html',
    '{"merid":"00000008","custno":"00000001","idno":"371523198106017349","token":""}', '已绑定');

generateLink('http://121.15.129.252:8089/zswww/views/account/accountbind.html',
    '{"merid":"00000009","custno":"1","idno":"620102198408174312","token":""}', '未绑定');

generateLink('http://121.15.129.252:8089/zswww/views/commerce/buy.html',
    '{"merid":"00000008","custno":"00000001","orderno":"123","stkcode":"600153","price":"8.0","amt":200}', '买入');

generateLink('http://121.15.129.252:8089/zswww/views/trade/sell.html',
    '{"merid":"00000008","custno":"00000001","orderno":"123","stkcode":"600153","price":"8.0","amt":200}', '卖出');

generateLink('http://121.15.129.252:8089/zswww/views/trade/cancellation.html',
    '{"merid":"00000008","custno":"00000001","orderno":"123","stkcode":"600153","price":"8.0","amt":200}', '撤单');


p('------------------------------ API请求');

generateApi(array(
    'id_type' => '00',
    'id_code' => '371523198106017349',
    'bizcode' => '31010013',
), '查询开户');

generateApi(array(
    'custno' => '00000001',
    'bizcode' => '410502',
), '查询资产');

generateApi(array(
    'custno' => '00000001',
    'bizcode' => '410503',
), '查询持仓');

generateApi(array(
    'custno' => '00000001',
    'bizcode' => '410410',
    'stkcode' => '600153',
    'price' => '8.0',
    'market' => '1', //沪A为1 深A为0
    'bsflag' => 'B'
), '查询最大可交易数量');


p('------------------------------ 通知');

//开户通知原文
$createCallback = <<<'S'
a:4:{s:3:"GET";N;s:4:"POST";N;s:8:"POST_RAW";s:784:"{"sign":"B4iM22f7w6%2F70wFkGJef%2FXZcgPrJCOgqvNA%2B%2F4nD4EpTBDWhqfTksxCYRB9A7Ro3uweObvnh5mrMnWl7b7blqj5F3dv4iXDW3AY0nJDeIq6YV6nS9IYEjnZf%2BdxDdyhasfu0jzydIHOgB9sH%2B28%2BAc7r3kvCimftGzBRuSTKYYdvYdAdlZvr1UxJboY%2Bs7JAWd3NuDnHcYoBN1BPRRGRNJL%2FiBh0Qwn8KGOuBIIGG%2BkJMhKLdIx8mhKDpMUOckOzOoNd6GVV%2FLPPRKoi1XPSFIF29kBkWU4d5vaOfjmbUIO8At2U05d7UH7KTFP6coMB0%2FGvLnWkC6CLqXpWEaDFYQ%3D%3D","data":"EmT06rBetMvmS%2B%2B5N7zMJhGpA%2FeIUEHW54vmDtTzvLLI9JFaCHy6hc3FS0TNq1y4IaZvn1%2BsrGFTsXN%2FaMh9tJRcPY4VW5cEiodBBn6I7i7bOIVoWX%2FoZs8cExtJUR%2B7DZ4UvX2wAxfPHs%2FjUnHtZkKsuR1W3Kl3g50U5zsNnt%2F3sIza%2FlVzM4x5BCz%2BfgFmLeJ2KFSE7G7o4MurkS0MbFurQz2aEE7dYgOAEAEXZcs%2Bmc0YJO1crwyvjIeUV6bvG9Q5pextKDxCebiZY%2BcITsLFuVLIorTSnd5F64IO1mQb2vGVoJxMu0QIMi6PLODpfUYsC27ERGwub0QwqIgJaw%3D%3D","op_type":"open"}";s:6:"HEADER";a:8:{s:11:"Contenttype";s:22:"text/xml;charset=utf-8";s:7:"Charset";s:5:"utf-8";s:10:"User-Agent";s:13:"Java/1.6.0_45";s:4:"Host";s:22:"zhongshan.wallstcn.com";s:6:"Accept";s:52:"text/html, image/gif, image/jpeg, *; q=.2, */*; q=.2";s:10:"Connection";s:10:"keep-alive";s:12:"Content-Type";s:33:"application/x-www-form-urlencoded";s:14:"Content-Length";s:3:"784";}}
S;

//绑定通知原文
$bindCallback = <<<'S'
a:4:{s:3:"GET";N;s:4:"POST";N;s:8:"POST_RAW";s:780:"{"sign":"G0%2BLUgLdxxzKF1clYu3KBVtSd4yhBDrQSLGXt%2BTK6msTybmZL%2F3RDLOxRoG49pfbt%2B%2BPluLGlu3OUZ%2FXrij8l56AJD8fzrQtYZRBUAh8Ey0EI4xAoU50ukNfKrwt2OEwHMu4uNPJifrOI00%2FOawYsl%2BzSfc2VX8f1ECNt1oiXYehwNyaK2HlxgNuSV2jj3YTMSn75YL4WbfPMPtyVLQUqJKFMP0%2FNHHGCcUHXvQgP%2FMeVQ1o50tZRqmG4r6ewl64AQcV8rSHL5EOzOi7qsgXd1srdlDeoTAqcf9esiAkati353XPusrVYWrQDdvGAPhCzNadt4g3EU%2BIrla%2FX4Spzg%3D%3D","data":"AWHDzx0T7RMBiFM6YnY0CjSYTrs84%2F5Ovnwd4SdO9yu1%2FKxqFgaIhJjJXubY6U9tV6HaVFfiBXcI6f8pRgUzmV0H%2F0tnTKV6EkJjR1Pm%2BHlvuED1M1ZvQav408FSTWsJiPnRHrXItmkPhS6N5YzgBaNdoNogP2NZ5fYszkewWbne9RfV1QyNEJBxuv0iM2sDbCOZi0IarPetMb0LPcvyj5Ts5MSoRSIhO4iOsYICAYnSygthFY%2ByPjcL%2Fj6O2%2B%2BO1sFGjBt%2FxN9ftBix2IBSymGvtXmRpuAOC6Ll0FZe2Bw5HwfO%2ByZIyAx5fSAupqe4YiTr9ZJB44QKBgYjAdG%2Fww%3D%3D","op_type":"open"}";s:6:"HEADER";a:8:{s:11:"Contenttype";s:22:"text/xml;charset=utf-8";s:7:"Charset";s:5:"utf-8";s:10:"User-Agent";s:13:"Java/1.6.0_45";s:4:"Host";s:22:"zhongshan.wallstcn.com";s:6:"Accept";s:52:"text/html, image/gif, image/jpeg, *; q=.2, */*; q=.2";s:10:"Connection";s:10:"keep-alive";s:12:"Content-Type";s:33:"application/x-www-form-urlencoded";s:14:"Content-Length";s:3:"780";}}
S;

#开户回调
$createCallback = unserialize($createCallback);
$createCallback = $createCallback['POST_RAW'];
$createCallback = json_decode($createCallback);
$data = base64_decode(urldecode($createCallback->data));
p('开户通知商户私钥解密：');
p($crypt->decryptByPrivateKey($data));
p('开户通知使用中山公钥验签：');
p($crypt->verify($data, urldecode($createCallback->sign), $publicKeyString));

//绑定回调
$bindCallback = unserialize($bindCallback);
$bindCallback = $bindCallback['POST_RAW'];
$bindCallback = json_decode($bindCallback);
$data = base64_decode(urldecode($bindCallback->data));
p('绑定通知商户私钥解密：');
p($crypt->decryptByPrivateKey($data));
p('绑定通知使用中山公钥验签：');
p($crypt->verify($data, urldecode($bindCallback->sign), $publicKeyString));
