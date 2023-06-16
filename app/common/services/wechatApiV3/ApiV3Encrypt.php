<?php

namespace app\common\services\wechatApiV3;

class ApiV3Encrypt
{
    const AUTH_TAG_LENGTH_BYTE = 16;

    /**
     * @var ApiV3Config
     */
    private $config;

    public function __construct(ApiV3Config $config)
    {
        $this->config = $config;
    }

    public function decrypt($associatedData, $nonceStr, $ciphertext)
    {
        $ciphertext = \base64_decode($ciphertext);
        if (strlen($ciphertext) <= self::AUTH_TAG_LENGTH_BYTE) {
            return false;
        }

        if (function_exists('\sodium_crypto_aead_aes256gcm_is_available') && \sodium_crypto_aead_aes256gcm_is_available()) {
            return \sodium_crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $this->config->secretV3());
        }

        if (function_exists('\Sodium\crypto_aead_aes256gcm_is_available') && \Sodium\crypto_aead_aes256gcm_is_available()) {
            return \Sodium\crypto_aead_aes256gcm_decrypt($ciphertext, $associatedData, $nonceStr, $this->config->secretV3());
        }

        // openssl (PHP >= 7.1 support AEAD)
        if (PHP_VERSION_ID >= 70100 && in_array('aes-256-gcm', \openssl_get_cipher_methods())) {
            $ctext = substr($ciphertext, 0, -self::AUTH_TAG_LENGTH_BYTE);
            $authTag = substr($ciphertext, -self::AUTH_TAG_LENGTH_BYTE);

            return \openssl_decrypt($ctext, 'aes-256-gcm', $this->config->secretV3(), \OPENSSL_RAW_DATA, $nonceStr,
                $authTag, $associatedData);
        }

        throw new \Exception('AEAD_AES_256_GCM需要PHP 7.1以上或者安装libsodium-php');
    }

    /**
     * 隐私数据提供加密方法
     * @param $str
     * @return string
     * @throws \Exception
     */
    public function encrypt($str)
    {
        $public_key_path = $this->config->platformCert();
        $public_key = file_get_contents($public_key_path);
        $encrypted = '';
        if (openssl_public_encrypt($str, $encrypted, $public_key, OPENSSL_PKCS1_OAEP_PADDING)) {
            //base64编码
            $sign = base64_encode($encrypted);
        } else {
            throw new \Exception('encrypt failed');
        }
        return $sign;
    }
}