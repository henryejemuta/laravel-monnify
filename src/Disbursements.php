<?php
/**
 * Created By: Henry Ejemuta
 * PC: Enrico Systems
 * Project: laravel-monnify
 * Company: Stimolive Technologies Limited
 * Class Name: Disbursements.php
 * Date Created: 3/2/21
 * Time Created: 5:52 PM
 */

namespace HenryEjemuta\LaravelMonnify;


use HenryEjemuta\LaravelMonnify\Classes\MonnifyBankAccount;
use HenryEjemuta\LaravelMonnify\Classes\MonnifyOnFailureValidate;
use HenryEjemuta\LaravelMonnify\Classes\MonnifyTransaction;
use HenryEjemuta\LaravelMonnify\Classes\MonnifyTransactionList;
use HenryEjemuta\LaravelMonnify\Exceptions\MonnifyFailedRequestException;

abstract class Disbursements
{

    private $monnify;
    /**
     * Flexible handle to the Monnify Configuration
     *
     * @var
     */
    private $config;

    public function __construct(Monnify $monnify, $config)
    {
        $this->config = $config;
        $this->monnify = $monnify;
    }


    /**
     * To initiate a single transfer,  you will need to send a request to the endpoint below:
     *
     * If the merchant does not have Two Factor Authentication (2FA) enabled, the transaction will be processed instantly and the response will be as follows:
     *
     * If the merchant has Two Factor Authentication (2FA) enabled, a One Time Password (OTP) will be sent to the designated email address(es). That OTP will need to be supplied via the VALIDATE OTP REQUEST before the transaction can be approved. If 2FA is enabled,
     *
     * @param float $amount
     * @param string $reference
     * @param string $narration
     * @param MonnifyBankAccount $bankAccount
     * @param string|null $currencyCode
     * @return array
     *
     * @throws MonnifyFailedRequestException
     *
     * @see https://docs.teamapt.com/display/MON/Initiate+Transfer
     */
    public function initiateTransferSingle(float $amount, string $reference, string $narration, MonnifyBankAccount $bankAccount, string $currencyCode = null)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}disbursements/single";
        $response = $this->monnify->withBasicAuth()->post($endpoint, [
            "amount" => $amount,
            "reference" => trim($reference),
            "narration" => trim($narration),
            "bankCode" => $bankAccount->getBankCode(),
            "accountNumber" => $bankAccount->getAccountNumber(),
            "currency" => $currencyCode ?? $this->config['default_currency_code'],
            "walletId" => $this->config['wallet_id']
        ]);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->initiateTransferSingle;
    }


    /**
     * To initiate a single transfer,  you will need to send a request to the endpoint below:
     *
     * If the merchant does not have Two Factor Authentication (2FA) enabled, the transaction will be processed instantly and the response will be as follows:
     *
     * If the merchant has Two Factor Authentication (2FA) enabled, a One Time Password (OTP) will be sent to the designated email address(es). That OTP will need to be supplied via the VALIDATE OTP REQUEST before the transaction can be approved. If 2FA is enabled,
     *
     *
     * @param MonnifyTransaction $monnifyTransaction Transactions Object
     * @return array
     *
     * @throws MonnifyFailedRequestException
     * @see https://docs.teamapt.com/display/MON/Initiate+Transfer
     */
    public function initiateTransferSingleWithMonnifyTransaction(MonnifyTransaction $monnifyTransaction)
    {
        return $this->initiateTransferSingle($monnifyTransaction->getAmount(), $monnifyTransaction->getReference(), $monnifyTransaction->getNarration(), $monnifyTransaction->getBankAccount(), $monnifyTransaction->getCurrencyCode());
    }


    /**
     * To initiate a single transfer,  you will need to send a request to the endpoint below:
     * Bulk transfers allows you send a single request with a list of disbursements you want to be processed. Below is a sample request for initiating a bulk transfer
     *
     * If the merchant does not have Two Factor Authentication (2FA) enabled, the transaction will be processed instantly and the response will be as follows:
     *
     * If the merchant has Two Factor Authentication (2FA) enabled, a One Time Password (OTP) will be sent to the designated email address(es). That OTP will need to be supplied via the VALIDATE OTP REQUEST before the transaction can be approved. If 2FA is enabled,
     *
     * @param string $title
     * @param string $batchReference The unique reference for the entire batch of transactions being sent.
     * @param string $narration The Narration for the transactions being processed
     * @param MonnifyOnFailureValidate $onFailureValidate Used to determine how Monnify should handle failed transaction validations in a batch. The two options are MonnifyOnFailureValidate::BREAK() & MonnifyOnFailureValidate::CONTINUE(). Use MonnifyOnFailureValidate::BREAK() to tell Monnify to reject the entire batch and use MonnifyOnFailureValidate::CONTINUE() to tell Monnify to process the valid transactions.
     * @param int $notificationInterval Used to determine how often Monnify should notify the merchant of progress when processing a batch transfer. The options are 10, 20, 50 and 100 and they represent percentages. i.e. 20 means notify me at intervals of 20% (20%, 40%, 60%, 80% ,100%).
     * @param MonnifyTransactionList $transactionList
     * @return array
     *
     * @throws MonnifyFailedRequestException
     * @see https://docs.teamapt.com/display/MON/Initiate+Transfer
     */
    public function initiateTransferBulk(string $title, string $batchReference, string $narration, MonnifyOnFailureValidate $onFailureValidate, int $notificationInterval, MonnifyTransactionList $transactionList)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}disbursements/batch";
        $response = $this->monnify->withBasicAuth()->post($endpoint, [
            "title" => $title,
            "batchReference" => trim($batchReference),
            "narration" => trim($narration),
            "walletId" => $this->config['wallet_id'],
            "onValidationFailure" => "$onFailureValidate",
            "notificationInterval" => $notificationInterval,
            "transactionList" => $transactionList->toArray()
        ]);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }

    /**
     * @param string $authorizationCode The One Time Password sent to the specified email to be used to authenticate the transaction
     * @param string $reference The unique reference for a transaction. Also to be specified for each transaction in a bulk transaction request.
     * @param string $path
     * @return object
     *
     * @throws MonnifyFailedRequestException
     * @link https://docs.teamapt.com/pages/viewpage.action?pageId=4587995
     */
    private function authorizeTransfer2FA(string $authorizationCode, string $reference, string $path)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}disbursements/$path/validate-otp";
        $response = $this->monnify->withBasicAuth()->post($endpoint, [
            "authorizationCode" => $authorizationCode,
            "reference" => trim($reference),
        ]);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }

    /**
     * To authorize a single transfer, you will need to send a request to using this
     *
     * @param string $authorizationCode The One Time Password sent to the specified email to be used to authenticate the transaction
     * @param string $reference The unique reference for a transaction. Also to be specified for each transaction in a bulk transaction request.
     * @return object
     *
     * @throws MonnifyFailedRequestException
     * @link https://docs.teamapt.com/pages/viewpage.action?pageId=4587995
     */
    public function authorizeSingleTransfer2FA(string $authorizationCode, string $reference)
    {
        return $this->authorizeTransfer2FA($authorizationCode, $reference, 'single');
    }

    /**
     * To authorize a bulk transfer, you will need to send a request to using this
     *
     * @param string $authorizationCode The One Time Password sent to the specified email to be used to authenticate the transaction
     * @param string $reference The unique reference for a transaction. Also to be specified for each transaction in a bulk transaction request.
     * @return object
     *
     * @throws MonnifyFailedRequestException
     * @link https://docs.teamapt.com/pages/viewpage.action?pageId=4587995
     */
    public function authorizeBulkTransfer2FA(string $authorizationCode, string $reference)
    {
        return $this->authorizeTransfer2FA($authorizationCode, $reference, 'batch');
    }


    /**
     * @param string $reference The unique reference for a transaction. Also to be specified for each transaction in a bulk transaction request.
     * @param string $path
     * @return object
     *
     * @throws MonnifyFailedRequestException
     * @link https://docs.teamapt.com/display/MON/Get+Transfer+Details
     */
    private function getTransferDetails(string $reference, string $path)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}disbursements/$path/summary?reference=$reference";
        $response = $this->monnify->withBasicAuth()->get($endpoint);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


    /**
     * Single Disbursements
     * To get the details of a single transfer
     *
     * @param string $reference The unique reference for a transaction. Also to be specified for each transaction in a bulk transaction request.
     * @return object
     *
     * @throws MonnifyFailedRequestException
     * @link https://docs.teamapt.com/display/MON/Get+Transfer+Details
     */
    public function getSingleTransferDetails(string $reference)
    {
        return $this->getTransferDetails($reference, 'single');
    }


    /**
     * Bulk Disbursements
     * To get the details of a bulk transfer
     *
     * @param string $batchReference The unique reference for a transaction. Also to be specified for each transaction in a bulk transaction request.
     * @return object
     *
     * @throws MonnifyFailedRequestException
     * @link https://docs.teamapt.com/display/MON/Get+Transfer+Details
     */
    public function getBulkTransferDetails(string $batchReference)
    {
        return $this->getTransferDetails($batchReference, 'batch');
    }


    /**
     * This allows you get a paginated list of all transactions in a bulk transfer batch and their statuses.
     *
     * @param string $batchReference The unique reference for the entire batch of transactions being sent.
     * @param int $pageNo A number specifying what page of transfers to be retrieved. Minimum value is 0, and defaults to 0 if not specified.
     * @param int $pageSize A number specifying size of each transfer page. Minimum value is 1, and defaults to 10 if not specified.
     * @return object
     *
     * @throws MonnifyFailedRequestException
     * @link https://docs.teamapt.com/display/MON/Get+Bulk+Transfer+Transactions
     */
    public function getBulkTransferTransactions(string $batchReference, int $pageNo = 0, int $pageSize = 10)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}disbursements/bulk/$batchReference/transactions?pageNo=$pageNo&pageSize=$pageSize";
        $response = $this->monnify->withBasicAuth()->get($endpoint);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


    /**
     * This allows you get a paginated list of all transactions in a bulk transfer batch and their statuses.
     *
     * @param string $path
     * @param int $pageNo A number specifying what page of transfers to be retrieved. Minimum value is 0, and defaults to 0 if not specified.
     * @param int $pageSize A number specifying size of each transfer page. Minimum value is 1, and defaults to 10 if not specified.
     * @return object
     *
     * @throws MonnifyFailedRequestException
     * @link https://docs.teamapt.com/display/MON/List+All+Transfers
     */
    private function listAllTransfers(string $path, int $pageNo = 0, int $pageSize = 10)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}disbursements/$path/transactions?pageNo=$pageNo&pageSize=$pageSize";
        $response = $this->monnify->withBasicAuth()->get($endpoint);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


    /**
     * Single Disbursements
     * To get a list of all single transfers
     *
     * @param int $pageNo
     * @param int $pageSize
     * @return object
     *
     * @throws MonnifyFailedRequestException
     * @link https://docs.teamapt.com/display/MON/Get+Transfer+Details
     */
    public function getSingleTransferTransactions(int $pageNo = 0, int $pageSize = 10)
    {
        return $this->listAllTransfers('single', $pageNo, $pageSize);
    }


    /**
     * Bulk Disbursements
     * To get a list of all bulk transfers
     *
     * @param int $pageNo
     * @param int $pageSize
     * @return object
     *
     * @throws MonnifyFailedRequestException
     * @link https://docs.teamapt.com/display/MON/Get+Transfer+Details
     */
    public function getAllBulkTransferTransactions(int $pageNo = 0, int $pageSize = 10)
    {
        return $this->listAllTransfers('bulk', $pageNo, $pageSize);
    }


    /**
     * This allows you to get the available balance in your monnify wallet.
     *
     * @return mixed
     * @throws MonnifyFailedRequestException
     *
     * @link https://docs.teamapt.com/display/MON/Get+Wallet+Balance
     */
    public function getWalletBalance()
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}disbursements/wallet-balance?walletId={$this->config['wallet_id']}";
        $response = $this->monnify->withBasicAuth()->get($endpoint);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


    /**
     * This allows you to resend OTP for 2FA
     *
     * @param string $reference
     * @return mixed
     * @throws MonnifyFailedRequestException
     * @link https://docs.teamapt.com/display/MON/Resend+OTP
     */
    public function resendOTP(string $reference)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}disbursements/single/resend-otp";
        $response = $this->monnify->withBasicAuth()->post($endpoint, [
            'reference' => $reference
        ]);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


}
