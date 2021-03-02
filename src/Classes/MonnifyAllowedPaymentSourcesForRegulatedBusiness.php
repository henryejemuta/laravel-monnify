<?php
/**
 * Created By: Henry Ejemuta
 * Project: laravel-monnify
 * Class Name: MonnifyAllowedPaymentSources.php
 * Date Created: 7/14/20
 * Time Created: 5:11 PM
 */

namespace HenryEjemuta\LaravelMonnify\Classes;


class MonnifyAllowedPaymentSourcesForRegulatedBusiness
{

    private $bvns = [];


    /**
     * MonnifyAllowedPaymentSources constructor.
     * @param array $bvns
     */
    private function __construct(...$bvns)
    {
        $this->bvns = $bvns;
    }

    public function toArray()
    {
        return [
            "bvns" => $this->bvns,
        ];
    }

}
