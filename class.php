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

