<?php
/**
 * Created By: Henry Ejemuta
 * Project: laravel-monnify
 * Class Name: MonnifyReservedAccountSplit.php
 * Date Created: 7/14/20
 * Time Created: 7:33 PM
 */

namespace HenryEjemuta\LaravelMonnify\Classes;

/**
 * Class MonnifyReservedAccountSplit
 * @package HenryEjemuta\LaravelMonnify\Classes
 *
 * Object containing specifications on how payments to this reserve account should be split.
 */
class MonnifyReservedAccountSplit
{

    private $subAccountCode;
    private $feePercentage;
    private $splitPercentage;
    private $feeBearer;

    /**
     * MonnifyReservedAccountSplit constructor.
     * @param string $subAccountCode The unique reference for the sub account that should receive the split.
     * @param float $feePercentage Boolean to determine if the sub account should bear transaction fees or not
     * @param bool $feeBearer The percentage of the transaction fee to be borne by the sub account
     * @param float $splitPercentage The percentage of the amount paid to be split into the sub account.
     */
    public function __construct(string $subAccountCode, float $feePercentage, bool $feeBearer, float $splitPercentage)
    {
        $this->subAccountCode = trim($subAccountCode);
        $this->feePercentage = $feePercentage;
        $this->feeBearer = $feeBearer;
        $this->splitPercentage = $splitPercentage;
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
