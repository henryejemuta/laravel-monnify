<?php
/**
 * Created By: Henry Ejemuta
 * Project: laravel-monnify
 * Class Name: MonnifyIncomeSplitConfig.php
 * Date Created: 7/14/20
 * Time Created: 7:44 PM
 */

namespace HenryEjemuta\LaravelMonnify\Classes;


class MonnifyIncomeSplitConfig
{

    private $incomeSplitConfig = [];

    /**
     * MonnifyIncomeSplitConfig constructor.
     * @param MonnifyReservedAccountSplit[] $reservedAccountSplits
     */
    public function __construct(MonnifyReservedAccountSplit ...$reservedAccountSplits)
    {
        foreach ($reservedAccountSplits as $split) {
            $this->incomeSplitConfig[] = $split->toArray();
        }
    }

    public function toArray(): array
    {
        return $this->incomeSplitConfig;
    }
}
