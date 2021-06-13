<?php
/**
 * @author Chuwen <wenzhouchan@gmail.com>
 * @date   2021/6/13 23:30
 */

namespace app\library;

class AES
{
    //设置AES秘钥
    private static $aes_key; //此处填写前后端共同约定的秘钥

    public function __construct($aes_key)
    {
        self::$aes_key = $aes_key;
    }

    /**
     * 加密
     *
     * @param string $str 要加密的数据
     *
     * @return string   加密后的数据
     */
    public static function encrypt(string $str): string
    {
        $data = openssl_encrypt($str, 'AES-128-ECB', self::$aes_key, OPENSSL_RAW_DATA);
        return base64_encode($data);
    }

    /**
     * 解密
     *
     * @param string $str 要解密的数据
     *
     * @return string        解密后的数据
     */
    public static function decrypt(string $str): string
    {
        return openssl_decrypt(base64_decode($str), 'AES-128-ECB', self::$aes_key, OPENSSL_RAW_DATA);
    }

}