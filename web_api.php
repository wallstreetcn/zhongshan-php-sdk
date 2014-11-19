<?php

class ZhongshanCrypt
{
    /*
     *
     */
    protected $publicKey;

    /*
     *
     */
    protected $privateKey;

    public function getPublicKey()
    {
        return $this->publicKey;
    }

    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    public function encode($data)
    {
        return base64_encode($data);
    }

    public function decode($data)
    {
        return base64_decode($data);
    }

    /*
     * 公钥加密
     *
     */
    public function encryptByPublicKey($data, $publicKey = null)
    {
        $publicKey = $publicKey ?: $this->publicKey;
        $encryptedByPublicKey = false;
        openssl_public_encrypt($data, $encryptedByPublicKey, $publicKey);
        return $encryptedByPublicKey;
    }

    /*
     * 私钥加密
     */
    public function encryptByPrivateKey($data, $privateKey = null)
    {
        $privateKey = $privateKey ?: $this->privateKey;
        $encryptedByPrivateKey = false;
        openssl_private_encrypt($data, $encryptedByPrivateKey, $privateKey);
        return $encryptedByPrivateKey;
    }

    /*
     * 公钥解密
     */
    public function decryptByPublicKey($encodedData, $publicKey = null)
    {
        $publicKey = $publicKey ?: $this->publicKey;
        $decryptByPublicKey = false;
        openssl_public_decrypt($encodedData, $decryptByPublicKey, $publicKey);
        return $decryptByPublicKey;
    }

    /*
     * 私钥解密
     */
    public function decryptByPrivateKey($encodedData, $privateKey = null)
    {
        $privateKey = $privateKey ?: $this->privateKey;
        $decryptByPrivateKey = false;
        openssl_private_decrypt($encodedData, $decryptByPrivateKey, $privateKey);
        return $decryptByPrivateKey;
    }

    /*
     * 生成签名
     */
    public function sign($data, $privateKey = null)
    {
        $data = hash('sha512', $data);
        $privateKey = $privateKey ?: $this->privateKey;
        $signature = null;
        openssl_sign($data, $signature, $privateKey);
        //$hex = bin2hex($signature);
        return base64_encode($signature);
    }

    public function verify($data, $sign, $publicKeyString = null)
    {
        $publicKey = $this->stringToPublicKey($publicKeyString);
        return openssl_verify(hash('sha512', $data), base64_decode($sign), $publicKey);
    }

    protected function stringToPublicKey($publicKeyString)
    {
        return openssl_pkey_get_public(
            "-----BEGIN PUBLIC KEY-----\n"
            . chunk_split($publicKeyString, 64, "\n")
            . "-----END PUBLIC KEY-----\n"
        );
    }

    protected function stringToPrivateKey($privateKeyString)
    {
        return openssl_pkey_get_private(
            "-----BEGIN PRIVATE KEY-----\n"
            . chunk_split($privateKeyString, 64, "\n")
            . "-----END PRIVATE KEY-----\n"
        );
    }

    protected function stringToCert($certString)
    {
        return openssl_pkey_get_public(
            "-----BEGIN CERTIFICATE-----\n"
            . chunk_split($certString, 64, "\n")
            . "-----END CERTIFICATE-----\n"
        );
    }

    public function __construct($publicKeyString, $privateKeyString)
    {
        $this->publicKey = $this->stringToPublicKey($publicKeyString);
        $this->privateKey = $this->stringToPrivateKey($privateKeyString);
    }
}


class ZhongshanAPI
{
    protected $uri = 'http://121.15.129.252:8089/servlet/call';

    protected $merchantId = '00000009';

    protected $signType = 'm';

    protected $signKey = '4db7b064f5455b890e1deadc5ef90e6d';

    protected $paramString = '';

    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    public function buildUrl(array $param)
    {
        $merchantId = $this->merchantId;

        $paramString = $this->buildParam($param);
        $sign = $this->sign($paramString);
        $requestParams = array(
            'merid' => $merchantId,
            'param' => $paramString,
            'sign' => $sign,
            'stype' => $this->signType
        );
        return $this->uri . '?' . http_build_query($requestParams);
    }

    public function sign($text)
    {
        $md5Key = strtoupper(md5($this->signKey));
        $sign = strtoupper($text) . $md5Key;
        $sign = strtoupper(md5($sign));
        return $sign;
    }

    public function encode($paramString)
    {
        return base64_encode($paramString);
    }

    public function buildParam(array $param)
    {
        $param['ver'] = 1;
        $param['flowno'] = str_replace('.', '', microtime(true));
        $param['biztime'] = date('Y-m-d H:i:s');
        $param['merid'] = $this->merchantId;
        //p(http_build_query($param));
        return $this->paramString = base64_encode(http_build_query($param));
    }

    public function __construct($uri = null, $merchantId = null, $signType = null, $signKey = null)
    {
        $this->uri = $uri ?: $this->uri;
        $this->merchantId = $merchantId ?: $this->merchantId;
        $this->signType = $signType ?: $this->signType;
        $this->signKey = $signKey ?: $this->signKey;
    }
}


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

//中山公钥
$publicKeyString = <<<'KEY'
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAjgtpdc1AOWFZKb8u5fHDIQYK4eptO40lJVPF+u/P2A/ekfl1L3UT2Y7ZUdrOHd4DKCKTJ65JqYZS8FgKF79jP25/XUzTBDN2kmq9czxCw94JfwcNklSnH8eqLozMZDK1B7P2y51zpTdcJUml9341rJ00WXge9mEYOEZ9Wk8sW2UNZp1LzFgBPaksTrB4BDNYtULrr9H8+qaiQasrNlXQVgSlJff6XWIPqwo5cJBdWkV30tNkOBi3HYcq2uh4x55AJf/rR1t3k36nTWlf+IEKFKxfEY7/IGF0axGopQcM6jRcAECuY7dIOnSetbnFFNwoyq0F9OqHfHTCMym7gbre0QIDAQAB
KEY;

//商户私钥
$privateKeyString = <<<'KEY'
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDBHwNt5WLHsI7npjw1Qw/EU5Ke5IzAVum1gY5cysCzN2BwMgux3Rm3UiDG1QtG76Kjd9sMt+i1EetFOX2N9BPk5Afw24tsqWkqoq+kroK0fyg7J2t1Qx/7ASecsyYAkRkfd/+rrDi5ao7OOd03wdlOLZx3PzGQVjUgzcnd3xjF2rTOZSbPhkz3zCkz22PJv3Qrft9XzzE/8nMO7KPN/rS70ZIRQZ9KasdzqMiDa3gZTeAQM69R9PfE/iikBBUNFeMePhTmVIif6V3qlOvzbmxSUgq0tJHyZCsON2O4v8i3TkXDSXduALflDpK5XZ3Ny7RgJRHLnF3guY/IebhN4B0hAgMBAAECggEAK9O2+oS5Qyo9dDLUrR24AU0aFDc3/hp7VXa+cS6ORt3FZBDneIj94g2gZJ9KvOF7Xm+/5YYDKLyVURN3+/QtY5+gcbfRT2qu9D8Rb2UvQnktDyemCmmeY19itvwtHqnPMsYP3szp3qJhIEofexziDQzq2mEcBW6bBgPN0S1lONlA1KumEM9XvhbOVsj2Hc+h75Y2fWtf7WMk8SQSBetTziC2X4G3YI2mCsl1rLahkW4B274Weu8Ts+4OegsQg5AIt9x5zHu2Cb/NoPI2yZgHLDDqDZBJZJpTc8RD2LPPCQPbX1CISyLqCtXkN/Xb1MbnjGwW2Ekz27hNJ90aeq9wpQKBgQD6kJ6ON0wg2sZVOSBdRkdOY6J6L1CdoFDUvCyO6k/trJyMa1+B/VWqrBY3PDvctBLHgiIXopayQQJj4sbRkgWlO7BotUcmwuEnEFpkN/bJciQCvdrOvoMFVd6vsXBnDOMGLRLZ7dwA0mZKlIKoXEGtomgcgcw8BKarKeq5fuIUnwKBgQDFT2kETUttRabcIL9hwqkNZ3gLqPhZMQpZqHfE/VcjBHUp/y39G/mdLTP+oB3+bz0SBL/HNUVn5+NsWam5/pdWRtjCSwRaoFOh6vqgCh0sAMu6Fk8eDTVDbVb4sHq+el0gpzQgTaN/otRcoRfTTOYFYDHpbCsLsQFxGqfPSey2PwKBgQC+JV2Nwz0MebmlFvMOlbSrMkUswBdHZ0+wARU3Z208io9KqHkUJnIKUQmS5Szwcd8GdCT3FrWRlHAB6SjEBTIvkDpxW5AhRCalBG30O9wWR344bmdMGZtIQz/4yIjqSwdFupLhuvYH0aM62bTFmy1kXSjw6TdaHJV2sxqjpjxfNQKBgCd4STO5GpuTUVu7mU0/GX468oKynGuLKdzhnIPlgebZX261Q0fcrsRIZJxM/3MEYZ0XIh4BhA7TDmWAUjmIulFh/r5vL0HJzLEZRmV5YHiK+DYTfGQwlDUWzE0XUJaF99cuW3kSzuAbfIvDFfcI7QKqSZG+N6YxFG7BvEv1J8sVAoGBAMTbjJUfAsKKEE5GgHFVLFKkxqiesvRjIi7LaNJxNgXQM0ps5Sd05LnLAWQcwrX8bVu1FqBqnrk3QDRTf9TcYO5oHjHJQLBi+uh93Z+p5quYL5IJe94kQz1P+GgpEy54p+xVzZCc48k6gQneTI+IoLBlLrA4ycIzeXoSJ4zeSJaW
KEY;


$crypt = new ZhongshanCrypt($publicKeyString, $privateKeyString);

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



//测试数据
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

$api = new ZhongshanAPI();

p('------------------------------ 网页请求');

generateLink('https://121.15.129.248/', '{"merid":"00000009","custno":"00000001"}', '开户');

generateLink('http://121.15.129.252:8089/zswww/views/account/accountbind.html',
    '{"merid":"00000008","custno":"00000001","idno":"371523198106017349","token":""}', '已绑定');

generateLink('http://121.15.129.252:8089/zswww/views/account/accountbind.html',
    '{"merid":"00000009","custno":"00000001","idno":"371523198106017348","token":""}', '未绑定');

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
{"sign":"G0%2BLUgLdxxzKF1clYu3KBVtSd4yhBDrQSLGXt%2BTK6msTybmZL%2F3RDLOxRoG49pfbt%2B%2BPluLGlu3OUZ%2FXrij8l56AJD8fzrQtYZRBUAh8Ey0EI4xAoU50ukNfKrwt2OEwHMu4uNPJifrOI00%2FOawYsl%2BzSfc2VX8f1ECNt1oiXYehwNyaK2HlxgNuSV2jj3YTMSn75YL4WbfPMPtyVLQUqJKFMP0%2FNHHGCcUHXvQgP%2FMeVQ1o50tZRqmG4r6ewl64AQcV8rSHL5EOzOi7qsgXd1srdlDeoTAqcf9esiAkati353XPusrVYWrQDdvGAPhCzNadt4g3EU%2BIrla%2FX4Spzg%3D%3D","data":"AWHDzx0T7RMBiFM6YnY0CjSYTrs84%2F5Ovnwd4SdO9yu1%2FKxqFgaIhJjJXubY6U9tV6HaVFfiBXcI6f8pRgUzmV0H%2F0tnTKV6EkJjR1Pm%2BHlvuED1M1ZvQav408FSTWsJiPnRHrXItmkPhS6N5YzgBaNdoNogP2NZ5fYszkewWbne9RfV1QyNEJBxuv0iM2sDbCOZi0IarPetMb0LPcvyj5Ts5MSoRSIhO4iOsYICAYnSygthFY%2ByPjcL%2Fj6O2%2B%2BO1sFGjBt%2FxN9ftBix2IBSymGvtXmRpuAOC6Ll0FZe2Bw5HwfO%2ByZIyAx5fSAupqe4YiTr9ZJB44QKBgYjAdG%2Fww%3D%3D","op_type":"open"}
S;

//绑定通知原文
$bindCallback = <<<'S'
{"sign":"hP189uh2j63Jqg6vRnylEbLLzSP6Qra7vXaN%2BQj9JHnwkpr1fSMaJeqNHTunJChOm7WFRy0AResgVrcDLjzq26uuA5HRyvVAgO6ucrkc0WlHa6MPnFr89vVTZjHBMl0pxUkak0h5aaWKVyz3no77U0J5qm%2BejLnS3qFq5wmsZkjMg3A6JePcaU5oQkLtQP2QjCs3D3e2MP5vtZ8H39DfulystJJxXxJcPK5g2O3qZd%2BbmkuOXcopo%2FGR8GK274VwCc9zz%2BD6ZlxlCRICqpTTC9U9nDnMwmtD37YXxbJO7AOu3k%2Bdsvh9yff6e%2BUNRtBYZJMFk8DrNuhNCw24w6CBTA%3D%3D","data":"LfVX1yryo4hTwBD0xx7kk3tpWzWOFrEDr1MsYrb29VtHC6X8%2BiSWl8IMvVTDJohfGAnkJ4JqHYBDC9D8pAloItzmddhCz05mPnZJ0uGfumJaJHYxWnT%2BJOHGCpBKuZiolXkyytCEWIbdHjmPmTLUAIdMk7rOVawe83GgmdYiyNga2EM7pL49luWjzDHB5xdofq9726hOQexfUW1FMWDYxz6JcRjaDbcIwAkfVLkQK3WPUaznwzpSOfi1Zb6R68aG99x%2Flf4bnLNTNsqttg%2FojpRZfHpNInICNyCu8aInkCf0MxiIRdJXMY2WgeBNsZqZuYR67VoedeuWNlZ6zRaCqQ%3D%3D","op_type":"open"}
S;

#开户回调
$createCallback = json_decode($createCallback);
$data = base64_decode(urldecode($createCallback->data));
p('开户通知商户私钥解密：');
p($crypt->decryptByPrivateKey($data));
p('开户通知使用中山公钥验签：');
p($crypt->verify($data, urldecode($createCallback->sign), $publicKeyString));

//绑定回调
$bindCallback = json_decode($bindCallback);
$data = base64_decode(urldecode($bindCallback->data));
