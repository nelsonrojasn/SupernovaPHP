<?php

namespace Supernova;

class Auth
{
    public static function setUser($data)
    {
        \Supernova\Session::create('AuthUser', \Supernova\Crypt::encrypt($data));
    }

    public static function getUser()
    {
        $data = \Supernova\Session::read('AuthUser');
        if ($data) {
            $User = \Supernova\Crypt::decrypt($data);
            return $User;
        }
        return false;
    }

    public static function unsetUser()
    {
        \Supernova\Session::destroy('AuthUser');
    }

    public static function checkUser($username, $field, $model)
    {
        $modelCheck = "\App\Model\\".$model;
        $User = $modelCheck::find(
            [
                "where" => [
                    $field => [
                        "=" => $username
                    ]
                ]
            ]
        );
        return (!empty($User)) ? true : false;
    }

    public static function hash($password)
    {
        if (function_exists('password_hash')) {
            $hash = password_hash($password, $numAlgo, $arrOptions);
        } else {
            $salt = mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
            $salt = base64_encode($salt);
            $salt = str_replace('+', '.', $salt);
            $hash = crypt($password, '$2y$10$' . $salt . '$');
        }
        return $hash;
    }

    public static function verifyHash($password, $hash)
    {
        if (function_exists('password_verify')) {
            $boolReturn = password_verify($password, $hash);
        } else {
            $strHash2 = crypt($password, $hash);
            $boolReturn = $hash == $strHash2;
        }
        return $boolReturn;
    }
}
