<?php

namespace HenryEjemuta\LaravelMonnify\Classes;

use HenryEjemuta\LaravelMonnify\Utils\CustomTransactionHashUtil;

class WebhookSignature
{

    /**
     * Verifies the signature header sent by Monify. Throws an
     * Exception\SignatureVerificationException exception if the verification fails for
     * any reason.
     *
     * @param string $payload the payload sent by Stripe
     * @param string $secret secret used to generate the signature
     * @param string $monnifySignature the contents of the signature header sent by
     *  Stripe
     * @return bool
     */
    public static function verifyHeader(string $payload, string $secret, string $monnifySignature): bool
    {
        $calculatedSignature = CustomTransactionHashUtil::computeSHA512TransactionHash($payload, $secret);

        return "$monnifySignature" === "$calculatedSignature";
    }
}