<?php

// +----------------------------------------------------------------------
// | [wallstreetcn]
// +----------------------------------------------------------------------
// | Author: Mr.5 <mr5.simple@gmail.com>
// +----------------------------------------------------------------------
// + Datetime: 14/11/14 10:34
// +----------------------------------------------------------------------
// + Encryptor.php
// +----------------------------------------------------------------------

namespace Eva\EvaZhongshanSecurities\SDK\Utils;

use Eva\EvaEngine\IoC;

class Encryptor
{
    /**
     * @var string 中山公钥字符串
     */
    protected $zhongshanPublicKeyString = '';
    /**
     * @var string 我方私钥字符串
     */
    protected $privateKeyString = '';
    /**
     * @var string 我方公钥字符串
     */
    protected $publicKeyString = '
';
    /**
     * RSA最大加密明文大小
     */
    const MAX_ENCRYPT_BLOCK = 245;
    /**
     * RSA最大解密密文大小
     */
    const MAX_DECRYPT_BLOCK = 256;
    /**
     * @var string 用于后端请求签名的 md5Key
     */
    protected $md5Key;
    /**
     * @var resource 我方公钥「资源」
     */
    protected $publicKey;
    /**
     * @var bool|resource 我方私钥「资源」
     */
    protected $privateKey;
    /**
     * @var resource 中山公钥「资源」
     */
    protected $zhongshanPublicKey;


    public function __construct()
    {
        $config = IoC::get('config')->zhongshan;
        $this->publicKey = openssl_pkey_get_public($this->stringToKey($config->publicKey));
        $this->privateKey = openssl_pkey_get_private($this->stringToKey($config->privateKey, false));
        $this->zhongshanPublicKey = openssl_pkey_get_public($this->stringToKey($config->zhongshanPublicKey));
        $this->md5Key = $config->md5Key;
    }

    /**
     * 格式化证书，证书必须为拼接在一起、不带头尾注释的字符串
     *
     * @param string $keyString
     * @param bool $public 是否是公钥，默认为 true
     * @return string
     */
    public function stringToKey($keyString, $public = true)
    {
        return true === $public ?
            "-----BEGIN PUBLIC KEY-----\n" . chunk_split($keyString, 64, "\n") . "-----END PUBLIC KEY-----\n"
            :
            "-----BEGIN PRIVATE KEY-----\n" . chunk_split($keyString, 64, "\n") . "-----END PRIVATE KEY-----\n";
    }

    /**
     * 使用中山公钥加密待发送字符串
     *
     * @param $text
     * @return string
     */
    public function encrypt($text)
    {
        $encrypteds = '';
        $textLen = strlen($text);
        for ($i = 0; $i <= $textLen; $i += static::MAX_ENCRYPT_BLOCK) {
            $str = substr($text, $i, static::MAX_ENCRYPT_BLOCK);
            if ($str) {
                $encrypted = '';
                p($str);
                openssl_public_encrypt($str, $encrypted, $this->zhongshanPublicKey);
                $encrypteds .= $encrypted;
            }
        }
        return $encrypteds;
    }

    /**
     * 使用我方私钥解密中山发来的字符串
     *
     * @param string $data
     * @return string
     */
    public function decrypt($data)
    {
        $decrypteds = '';
        $data = base64_decode($data);
        $textLen = strlen($data);
        for ($i = 0; $i <= $textLen; $i += static::MAX_DECRYPT_BLOCK) {
            $str = substr($data, $i, static::MAX_ENCRYPT_BLOCK);
            if ($str) {
                $decrypted = '';
                openssl_private_decrypt($str, $decrypted, $this->privateKey);
                $decrypteds .= $decrypted;
            }
        }
        return $decrypteds;
    }

    /**
     * 验证中山发来的签名
     *
     * @param $data
     * @param $sign
     * @return int
     */
    public function verifySign($data, $sign)
    {
        return openssl_verify(hash('sha512', $data), base64_decode($sign), $this->zhongshanPublicKey);
    }

    /**
     * 使用我方私钥签名欲发往中山的消息
     *
     * @param string $data
     * @return string
     */
    public function rsaSign($data)
    {
        $data = hash('sha512', $data);
        $signature = null;
        openssl_sign($data, $signature, $this->privateKey);
        return $signature;
    }

    /**
     * 使用 md5Key 签名欲发往中山的消息
     *
     * @param $text
     * @return string
     */
    public function md5Sign($text)
    {
        $md5Key = strtoupper(md5($this->md5Key));
        $sign = strtoupper($text) . $md5Key;
        $sign = strtoupper(md5($sign));
        return $sign;
    }

    public function __destruct()
    {
        openssl_pkey_free($this->privateKey);
        openssl_pkey_free($this->publicKey);
        openssl_pkey_free($this->zhongshanPublicKey);
    }
}

