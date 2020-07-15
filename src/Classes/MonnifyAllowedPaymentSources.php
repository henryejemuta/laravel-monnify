<?php
/**
 * Created By: Henry Ejemuta
 * Project: laravel-monnify
 * Class Name: MonnifyAllowedPaymentSources.php
 * Date Created: 7/14/20
 * Time Created: 5:11 PM
 */

namespace HenryEjemuta\LaravelMonnify\Classes;


class MonnifyAllowedPaymentSources
{

    private $bankAccounts = [];
    private $accountNames = [];


    /**
     * MonnifyAllowedPaymentSources constructor.
     * @param MonnifyBankAccount[] $monnifyBankAccounts
     */
    private function __construct(MonnifyBankAccount ...$monnifyBankAccounts)
    {
        foreach ($monnifyBankAccounts as $account){
            $this->bankAccounts[] = $account->getBankCodeAndAccountNumber();
            $this->accountNames[] = $account->getAccountName();
        }
    }

    public function toArray()
    {
        return [
            "bankAccounts" => $this->bankAccounts,
            "accountNames" => $this->accountNames,
        ];
    }

}
