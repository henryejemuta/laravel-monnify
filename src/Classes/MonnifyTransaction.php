<?php
/**
 * Created By: Henry Ejemuta
 * Project: laravel-monnify
 * Class Name: MonnifyTransaction.php
 * Date Created: 7/15/20
 * Time Created: 7:13 AM
 */

namespace HenryEjemuta\LaravelMonnify\Classes;


class MonnifyTransaction
{
    /**
     * @var float
     */
    private $amount;

    /**
     * @var string
     */
    private $reference;

    /**
     * @var string
     */
    private $narration;

    /**
     * @var MonnifyBankAccount
     */
    private $bankAccount;

    /**
     * @var string
     */
    private $currencyCode;

    /**
     * MonnifyReservedAccountSplit constructor.
     * @param float $amount The amount to be disbursed to the beneficiary
     * @param string $reference The unique reference for a transaction. Also to be specified for each transaction in a bulk transaction request.
     * @param string $narration The Narration for the transactions being processed
     * @param MonnifyBankAccount $bankAccount The MonnifyBankAccount object holds and do slight validation on account number and bank code, pass empty string for account name here
     * @param string $currencyCode The currency of the transaction being initialized. You can use the preset by call with config('
     */
    public function __construct(float $amount, string $reference, string $narration, MonnifyBankAccount $bankAccount, string $currencyCode)
    {
        $this->amount = $amount;
        $this->reference = trim($reference);
        $this->narration = trim($narration);
        $this->bankAccount = $bankAccount;
        $this->currencyCode = $currencyCode;
    }

    public function getBankAccount() : MonnifyBankAccount{
        return $this->bankAccount;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @return string
     */
    public function getNarration(): string
    {
        return $this->narration;
    }

    /**
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    public function toArray(): array
    {
        return [
            "subAccountCode" => $this->subAccountCode,
            "feePercentage" => $this->feePercentage,
            "splitPercentage" => $this->splitPercentage,
            "feeBearer" => $this->feeBearer
        ];
    }
}
