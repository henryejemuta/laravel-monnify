<?php
/**
 * Created By: Henry Ejemuta
 * Project: laravel-monnify
 * Class Name: MonnifyOnFailureValidate.php
 * Date Created: 7/15/20
 * Time Created: 8:06 AM
 */

namespace HenryEjemuta\LaravelMonnify\Classes;


class MonnifyOnFailureValidate
{
    private $onFailureValidate;

    private static $cache = [];
    private const BREAK = "BREAK";
    private const CONTINUE = "CONTINUE";

    /**
     * MonnifyOnFailureValidate constructor.
     * @param string $onFailureValidate
     */
    private function __construct(string $onFailureValidate)
    {
        $this->onFailureValidate = $onFailureValidate;
    }

    public static function BREAK(): MonnifyOnFailureValidate
    {
        if (!key_exists(self::BREAK, self::$cache))
            self::$cache[self::BREAK] = new MonnifyOnFailureValidate( self::BREAK);
        return self::$cache[self::BREAK];
    }

    public static function CONTINUE(): MonnifyOnFailureValidate
    {
        if (!key_exists(self::CONTINUE, self::$cache))
            self::$cache[self::CONTINUE] = new MonnifyOnFailureValidate( self::CONTINUE);
        return self::$cache[self::CONTINUE];
    }

    public static function findOnFailureValidate(string $onFailureValidate): MonnifyOnFailureValidate
    {
        switch (strtoupper($onFailureValidate)) {
            case self::BREAK:
                return self::BREAK();
            case self::CONTINUE:
                return self::CONTINUE();
            default:
                return null;
        }
    }

    public function __toString(): string
    {
        return $this->onFailureValidate;
    }

}
