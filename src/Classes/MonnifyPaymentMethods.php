<?php
/**
 * Created By: Henry Ejemuta
 * Project: laravel-monnify
 * Class Name: MonnifyPaymentMethods.php
 * Date Created: 7/15/20
 * Time Created: 12:42 AM
 */

namespace HenryEjemuta\LaravelMonnify\Classes;


class MonnifyPaymentMethods
{

    private $monnifyPaymentMethods = [];

    /**
     * MonnifyPaymentMethods constructor.
     * @param MonnifyPaymentMethod[] $monnifyPaymentMethods
     */
    public function __construct(MonnifyPaymentMethod ...$monnifyPaymentMethods)
    {
        foreach ($monnifyPaymentMethods as $method) {
            $this->monnifyPaymentMethods[$method->getID()] = $method->getMethod();
        }
    }

    public function toArray(): array
    {
        return array_values($this->monnifyPaymentMethods);
    }
}
