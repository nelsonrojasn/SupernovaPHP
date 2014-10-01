<?php

    namespace Supernova;

    class Crypt
    {
        private static $key = "E4HD9h4DhS23DYfhHemkS3Nf";// 24 bit Key
        private static $iv = "fYfhHeDm"; // 8 bit IV
        private static $bit_check = 8; // bit amount for diff algor.

        public static function encrypt($text)
        {
            $text = print_r($text, true);
            $text_num =str_split($text,self::$bit_check);
            $text_num = self::$bit_check-strlen($text_num[count($text_num)-1]);
            for ($i=0;$i<$text_num; $i++) {$text = $text . chr($text_num);}
            $cipher = mcrypt_module_open(MCRYPT_TRIPLEDES,'','cbc','');
            mcrypt_generic_init($cipher, self::$key, self::$iv);
            $decrypted = mcrypt_generic($cipher,$text);
            mcrypt_generic_deinit($cipher);

            return base64_encode(base64_encode($decrypted));
        }

        public static function decrypt($encrypted_text)
        {
            $cipher = mcrypt_module_open(MCRYPT_TRIPLEDES,'','cbc','');
            mcrypt_generic_init($cipher, self::$key, self::$iv);
            $decrypted = mdecrypt_generic($cipher,base64_decode(base64_decode($encrypted_text)));
            mcrypt_generic_deinit($cipher);
            $last_char=substr($decrypted,-1);
            for ($i=0;$i<(self::$bit_check-1); $i++) {
                if (chr($i)==$last_char) {
                    $decrypted=substr($decrypted,0,strlen($decrypted)-$i);
                    break;
                }
            }

            return $decrypted;
        }

    }
