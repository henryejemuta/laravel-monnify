<?php
/**
 * Created By: Henry Ejemuta
 * PC: Enrico Systems
 * Project: laravel-monnify
 * Company: Stimolive Technologies Limited
 * Class Name: SubAccounts.php
 * Date Created: 3/2/21
 * Time Created: 4:08 PM
 */

namespace HenryEjemuta\LaravelMonnify;


use HenryEjemuta\LaravelMonnify\Exceptions\MonnifyFailedRequestException;

abstract class SubAccounts
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
     * Creates a sub account for a merchant. Allowing the merchant split transaction settlement between the main account and one or more sub account(s)
     * For $bankCode, check returned object from getBanks() or  getBanksWithUSSDShortCode()
     * @param string $bankCode
     * @param string $accountNumber The account number that should be created as a sub account.
     * @param string $email The email tied to the sub account. This email will receive settlement reports for settlements into the sub account.
     * @param string|null $currencyCode
     * @param string|null $splitPercentage
     * @return array
     *
     * @throws MonnifyFailedRequestException
     *
     * Once the request is sent, a sub account code will be returned. This sub account code is the unique identifier for that sub account and will be used to reference the sub account in split payment requests.
     * <strong>Note: </strong> Currency code and Split Percentage will use the configured default in you .env file if not explicitly provided
     * Also, if bank account is not found within the provide bank code a MonnifyFailedRequestException will be thrown
     *
     */
    public function createSubAccount(string $bankCode, string $accountNumber, string $email, string $currencyCode = null, string $splitPercentage = null)
    {
        $currencyCode = $currencyCode ?? $this->config['default_currency_code'];
        $splitPercentage = $splitPercentage ?? $this->config['default_split_percentage'];

        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}sub-accounts";
        $response = $this->monnify->withBasicAuth()->post($endpoint, [[
            'currencyCode' => $currencyCode,
            'bankCode' => $bankCode,
            'accountNumber' => $accountNumber,
            'email' => $email,
            'defaultSplitPercentage' => $splitPercentage,
        ],]);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


    /**
     * Creates a sub accounts for a merchant. Allowing the merchant split transaction settlement between the main account and one or more sub account(s)
     * For $bankCode, check returned object from getBanks() or  getBanksWithUSSDShortCode()
     * @param array $accounts is an array of arrays, with each individual array containing the following keys 'currencyCode', 'bankCode', 'accountNumber', 'email', and 'defaultSplitPercentage'
     * Note that you can always get the set default currency code as well as default split percentage from the monnify config file with config('monnify.default_currency_code') and config('monnify.default_split_percentage') respectively
     * @return array
     *
     * @throws MonnifyFailedRequestException
     *
     * Once the request is sent, a sub account code will be returned. This sub account code is the unique identifier for that sub account and will be used to reference the sub account in split payment requests.
     * <strong>Note: </strong> If any of the provided details bank account is not found within the corresponding provide bank code a MonnifyFailedRequestException will be thrown
     *
     */
    public function createSubAccounts(array $accounts)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}sub-accounts";
        $response = $this->monnify->withBasicAuth()->post($endpoint, $accounts);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


    /**
     * Returns a list of sub accounts previously created by the merchant.
     * @return array
     *
     * @throws MonnifyFailedRequestException
     *
     */
    public function getSubAccounts()
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}sub-accounts";
        $response = $this->monnify->withBasicAuth()->get($endpoint);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


    /**
     * Deletes a merchant's sub account.
     * @param string $subAccountCode The unique reference for the sub account
     * @return object
     *
     * @throws MonnifyFailedRequestException
     */
    public function deleteSubAccount(string $subAccountCode)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}sub-accounts/$subAccountCode";
        $response = $this->monnify->withBasicAuth()->delete($endpoint);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject;
    }


    /**
     * Updates the information on an existing sub account for a merchant.
     *
     * @param string $subAccountCode The unique reference for the sub account
     * @param string $bankCode
     * @param string $accountNumber
     * @param string $email The email tied to the sub account. This email will receive settlement reports for settlements into the sub account.
     * @param string|null $currencyCode
     * @param string|null $splitPercentage
     * @return array
     *
     * @throws MonnifyFailedRequestException
     *
     */
    public function updateSubAccount(string $subAccountCode, string $bankCode, string $accountNumber, string $email, string $currencyCode = null, string $splitPercentage = null)
    {
        $currencyCode = $currencyCode ?? $this->config['default_currency_code'];
        $splitPercentage = $splitPercentage ?? $this->config['default_split_percentage'];

        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}sub-accounts";

        $response = $this->monnify->withBasicAuth()->put($endpoint, [
            'subAccountCode' => $subAccountCode,
            'currencyCode' => $currencyCode,
            'bankCode' => $bankCode,
            'accountNumber' => $accountNumber,
            'email' => $email,
            'defaultSplitPercentage' => $splitPercentage,
        ]);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }

}
