<?php
/**
 * Created By: Henry Ejemuta
 * PC: Enrico Systems
 * Project: laravel-monnify
 * Company: Stimolive Technologies Limited
 * Class Name: Banks.php
 * Date Created: 3/2/21
 * Time Created: 4:11 PM
 */

namespace HenryEjemuta\LaravelMonnify;


use HenryEjemuta\LaravelMonnify\Classes\MonnifyBankAccount;
use HenryEjemuta\LaravelMonnify\Exceptions\MonnifyFailedRequestException;

abstract class Banks
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
     * This enables you to retrieve all banks supported by Monnify for collections and disbursements.
     * @return array
     *
     * @throws MonnifyFailedRequestException
     */
    public function getBanks()
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}banks";
        $response = $this->monnify->withOAuth2()->get($endpoint);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


    /**
     * This API enables you to retrieve all banks with valid USSD short code. This is useful if you'll like to display USSD short codes for your customers to dial.
     * For a full list of banks, use @return array
     *
     * @throws MonnifyFailedRequestException
     *
     * @see getBanks()
     *
     */
    public function getBanksWithUSSDShortCode()
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}sdk/transactions/banks";

        $response = $this->monnify->withOAuth2()->get($endpoint);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


    /**
     * This allows you check if an account number is a valid NUBAN, get the account name if valid.
     *
     * @param MonnifyBankAccount $bankAccount
     * @return mixed
     * @throws MonnifyFailedRequestException
     *
     * @link https://docs.teamapt.com/display/MON/Validate+Bank+Account
     */
    public function validateBankAccount(MonnifyBankAccount $bankAccount)
    {

        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}disbursements/account/validate?accountNumber={$bankAccount->getAccountNumber()}&bankCode={$bankAccount->getBankCode()}";
        $response = $this->monnify->withBasicAuth()->get($endpoint);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


}
