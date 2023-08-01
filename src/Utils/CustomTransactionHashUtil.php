<?php

namespace HenryEjemuta\LaravelMonnify\Utils;

class CustomTransactionHashUtil
{
    public static function computeSHA512TransactionHash($stringifiedData, $clientSecret) {
        $computedHash = hash_hmac('sha512', $stringifiedData, $clientSecret);
        return $computedHash;
    }
}