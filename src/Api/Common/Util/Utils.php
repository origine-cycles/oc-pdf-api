<?php
namespace App\Api\Common\Util;

class Utils {

    private static $SSL_METHOD = 'aes-256-cbc';
    private static $SSL_KEY = '581ug2y3c5yd155r2eevvaysy5ktvbcqa3cgv29ywx2otx6l2b7obw71shlejnvjhyp3qosddksw9o91q7fsucjdkuit953gtjaayse24wp3cpusm1b84lbe6ky5fscy';
    private static $SSL_IV = 'o6jx8sk2pt8d155u';
    public static function encryptString($string){
        return openssl_encrypt($string, self::$SSL_METHOD, self::$SSL_KEY, 0, self::$SSL_IV );
    }

    public static function decryptString($string){
        return openssl_decrypt($string, self::$SSL_METHOD, self::$SSL_KEY, 0, self::$SSL_IV );
    }

    public static function isJson($string) {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}