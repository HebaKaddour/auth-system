<?php
namespace App\Helpers;

class VerificationCodeHelper
{
    public static function generate(): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        return substr(str_shuffle($characters), 0, 6);
    }
}
