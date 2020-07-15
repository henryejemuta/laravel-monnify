<?php
/**
 * Created By: Henry Ejemuta
 * Project: laravel-monnify
 * Class Name: MonnifyPaymentMethod.php
 * Date Created: 7/15/20
 * Time Created: 12:05 AM
 */

namespace HenryEjemuta\LaravelMonnify\Classes;


class MonnifyPaymentMethod
{
    private $id;
    private $method;

    private static $cache = [];
    private const CARD = "CARD";
    private const ACCOUNT_TRANSFER = "ACCOUNT_TRANSFER";

    /**
     * MonnifyPaymentMethod constructor.
     * @param int $id
     * @param string $method
     */
    private function __construct(int $id, string $method)
    {
        $this->id = $id;
        $this->method = $method;
    }

    public static function CARD(): MonnifyPaymentMethod
    {
        if (!key_exists(self::CARD, self::$cache))
            self::$cache[self::CARD] = new MonnifyPaymentMethod(1, self::CARD);
        return self::$cache[self::CARD];
    }

    public static function ACCOUNT_TRANSFER(): MonnifyPaymentMethod
    {
        if (!key_exists(self::ACCOUNT_TRANSFER, self::$cache))
            self::$cache[self::ACCOUNT_TRANSFER] = new MonnifyPaymentMethod(2, self::ACCOUNT_TRANSFER);
        return self::$cache[self::ACCOUNT_TRANSFER];
    }

    public function getID(): int
    {
        return $this->id;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public static function findPaymentMethod(int $id): MonnifyPaymentMethod
    {
        switch ($id) {
            case 1:
                return self::CARD();
            case 2:
                return self::ACCOUNT_TRANSFER();
            default:
                return null;
        }
    }

    public function __toString(): string
    {
        return $this->getMethod();
    }
}
