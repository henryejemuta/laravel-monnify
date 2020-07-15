<?php
/**
 * Created By: Henry Ejemuta
 * Project: laravel-monnify
 * Class Name: MonnifyTransactionList.php
 * Date Created: 7/15/20
 * Time Created: 7:14 AM
 */

namespace HenryEjemuta\LaravelMonnify\Classes;


class MonnifyTransactionList
{

    private $monnifyTransactions = [];

    /**
     * MonnifyTransactionList constructor.
     * @param MonnifyTransaction[] $monnifyTransactions
     */
    public function __construct(MonnifyTransaction ...$monnifyTransactions)
    {
        foreach ($monnifyTransactions as $transaction) {
            $this->$monnifyTransactions["{$transaction->getBankAccount()}"] = $transaction->toArray();
        }
    }

    public function toArray(): array
    {
        return array_values($this->monnifyTransactions);
    }

}
