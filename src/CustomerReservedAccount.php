<?php
/**
 * Created By: Henry Ejemuta
 * PC: Enrico Systems
 * Project: laravel-monnify
 * Company: Stimolive Technologies Limited
 * Class Name: CustomerReservedAccount.php
 * Date Created: 3/2/21
 * Time Created: 4:07 PM
 */

namespace HenryEjemuta\LaravelMonnify;


use HenryEjemuta\LaravelMonnify\Classes\MonnifyAllowedPaymentSources;
use HenryEjemuta\LaravelMonnify\Classes\MonnifyAllowedPaymentSourcesForRegulatedBusiness;
use HenryEjemuta\LaravelMonnify\Classes\MonnifyIncomeSplitConfig;
use HenryEjemuta\LaravelMonnify\Exceptions\MonnifyFailedRequestException;
use HenryEjemuta\LaravelMonnify\Exceptions\MonnifyInvalidParameterException;

abstract class CustomerReservedAccount
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
     * Once an account number has been reserved for a customer, the customer can make payment by initiating a transfer to that account number at any time. Once the transfer hits the partner bank, we will notify you with the transfer details along with the accountReference you specified when reserving the account.
     *
     * @param string $accountReference Your unique reference used to identify this reserved account
     * @param string $accountName The name you want to be attached to the reserved account. This will be displayed during name enquiry
     * @param string $customerEmail Email address of the customer who the account is being reserved for. This is the unique identifier for each customer.
     * @param string $customerName Full name of the customer who the account is being reserved for
     * @param bool $getAllAvailableBanks If you want to reserve accounts across all partner banks for your customers, you will need to set the boolean value of "$getAllAvailableBanks" to true. Note that Wema bank accounts are the default virtual account.
     * @param string|null $customerBvn BVN of the customer the account is being reserved for. Although this field is not mandatory, it is advised that it is supplied. Please note that there could be low limits on the reserved account in future, if BVN is not supplied.
     * @param string|null $currencyCode
     * @param MonnifyIncomeSplitConfig|null $incomeSplitConfig
     * @param bool $restrictPaymentSource
     * @param MonnifyAllowedPaymentSources|MonnifyAllowedPaymentSourcesForRegulatedBusiness $allowedPaymentSources Object capturing bvns or account numbers or account names that are permitted to fund a reserved account. This is mandatory if restrictPaymentSource is set to true. Click here to learn more about source account restriction.
     * @return object
     *
     * @throws MonnifyInvalidParameterException
     * @throws MonnifyFailedRequestException
     * @link https://docs.teamapt.com/display/MON/Reserving+An+Account
     */
    public function reserveAccount(string $accountReference, string $accountName, string $customerEmail, string $customerName = null, bool $getAllAvailableBanks = false, string $customerBvn = null, string $currencyCode = null, bool $restrictPaymentSource = false, MonnifyAllowedPaymentSources $allowedPaymentSources = null, MonnifyIncomeSplitConfig $incomeSplitConfig = null)
    {
        if ($restrictPaymentSource && is_null($allowedPaymentSources))
            throw new MonnifyInvalidParameterException("Allowed Payment Sources can't be null if payment source is restricted");

        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}bank-transfer/reserved-accounts";
        $requestBody = [
            "accountReference" => $accountReference,
            "accountName" => $accountName,
            "currencyCode" => $currencyCode ?? $this->config['default_currency_code'],
            "contractCode" => $this->config['contract_code'],
            "customerEmail" => $customerEmail,
            "restrictPaymentSource" => $restrictPaymentSource,
            "getAllAvailableBanks" => $getAllAvailableBanks
        ];

        if ((!is_null($customerName)) && (!empty(trim($customerName))))
            $requestBody['customerName'] = $customerName;

        if ((!is_null($customerBvn)) && (!empty(trim($customerBvn))))
            $requestBody['customerBvn'] = $customerBvn;

        if (!is_null($allowedPaymentSources))
            $requestBody['allowedPaymentSources'] = $allowedPaymentSources->toArray();

        if (!is_null($incomeSplitConfig))
            $requestBody['incomeSplitConfig'] = $incomeSplitConfig->toArray();

        $response = $this->monnify->withOAuth2()->post($endpoint, $requestBody);


        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


    /**
     * Once an account number has been reserved for a customer, the customer can make payment by initiating a transfer to that account number at any time. Once the transfer hits the partner bank, we will notify you with the transfer details along with the accountReference you specified when reserving the account.
     *
     * @param string $accountReference Your unique reference used to identify this reserved account
     * @param string $accountName The name you want to be attached to the reserved account. This will be displayed during name enquiry
     * @param string $customerEmail Email address of the customer who the account is being reserved for. This is the unique identifier for each customer.
     * @param string $customerName Full name of the customer who the account is being reserved for
     * @param array $preferredBanksCodes The bank codes of the preferred banks in an array. Wema Bank => 035, and Rolez MFB => 50515
     * @param string|null $customerBvn BVN of the customer the account is being reserved for. Although this field is not mandatory, it is advised that it is supplied. Please note that there could be low limits on the reserved account in future, if BVN is not supplied.
     * @param string|null $currencyCode
     * @param MonnifyIncomeSplitConfig|null $incomeSplitConfig
     * @param bool $restrictPaymentSource
     * @param MonnifyAllowedPaymentSources|MonnifyAllowedPaymentSourcesForRegulatedBusiness $allowedPaymentSources Object capturing bvns or account numbers or account names that are permitted to fund a reserved account. This is mandatory if restrictPaymentSource is set to true. Click here to learn more about source account restriction.
     * @return object
     *
     * @throws MonnifyInvalidParameterException
     * @throws MonnifyFailedRequestException
     * @link https://docs.teamapt.com/display/MON/Reserving+An+Account
     */
    public function reserveAccountWithBankCodes(string $accountReference, string $accountName, string $customerEmail, string $customerName = null, array $preferredBanksCodes = ["035"], string $customerBvn = null, string $currencyCode = null, bool $restrictPaymentSource = false, MonnifyAllowedPaymentSources $allowedPaymentSources = null, MonnifyIncomeSplitConfig $incomeSplitConfig = null)
    {
        if ($restrictPaymentSource && is_null($allowedPaymentSources))
            throw new MonnifyInvalidParameterException("Allowed Payment Sources can't be null if payment source is restricted");

        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}bank-transfer/reserved-accounts";
        $requestBody = [
            "accountReference" => $accountReference,
            "accountName" => $accountName,
            "currencyCode" => $currencyCode ?? $this->config['default_currency_code'],
            "contractCode" => $this->config['contract_code'],
            "customerEmail" => $customerEmail,
            "restrictPaymentSource" => $restrictPaymentSource,
            "getAllAvailableBanks" => false,
            "preferredBanks" => ["035"]
        ];

        if ((!is_null($customerName)) && (!empty(trim($customerName))))
            $requestBody['customerName'] = $customerName;

        if (!is_null($allowedPaymentSources))
            $requestBody['allowedPaymentSources'] = $allowedPaymentSources->toArray();

        if (!is_null($incomeSplitConfig))
            $requestBody['incomeSplitConfig'] = $incomeSplitConfig->toArray();

        if ((!is_null($customerBvn)) && (!empty(trim($customerBvn))))
            $requestBody['customerBvn'] = $customerBvn;


        $response = $this->monnify->withOAuth2()->post($endpoint, $requestBody);


        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


    /**
     * If you want to get the details of a reserved account, you can initiate a GET request to the endpoint below and we will return all the details attached to that account Reference.
     *
     * @param string $accountReference Your unique reference used to identify this reserved account
     * @return object
     *
     * @throws MonnifyFailedRequestException
     * @link https://docs.teamapt.com/display/MON/Get+Reserved+Account+Details
     */
    public function getAccountDetails(string $accountReference)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}bank-transfer/reserved-accounts/$accountReference";
        $response = $this->monnify->withOAuth2()->get($endpoint);
        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


    /**
     * You can update income splitting config for a reserved account using the endpoint below.
     *
     * @param string $accountReference Your unique reference used to identify this reserved account
     * @param MonnifyIncomeSplitConfig $incomeSplitConfig
     * @return object
     *
     * @throws MonnifyFailedRequestException
     * @link https://docs.teamapt.com/display/MON/Updating+Split+Config+for+Reserved+Account
     */
    public function updateSplitConfig(string $accountReference, MonnifyIncomeSplitConfig $incomeSplitConfig)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}bank-transfer/reserved-accounts/update-income-split-config/$accountReference";
        $response = $this->monnify->withOAuth2()->put($endpoint, $incomeSplitConfig->toArray());


        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


    /**
     * You can get a paginated list of transactions processed to a reserved account by making a GET Request to the endpoint below and by specifying the accountReference as a query parameter. You can also specify the page number and size (number of transactions) you want returned per page.
     *
     * @param string $accountReference Your unique reference used to identify this reserved account
     * @param int $page The page of data you want returned by Monnify (Starts from 0)
     * @param int $size The number of records you want returned in a page.
     * @return object
     *
     * @throws MonnifyFailedRequestException
     * @link https://docs.teamapt.com/display/MON/Getting+all+transactions+on+a+reserved+account
     */
    public function getAllTransactions(string $accountReference, int $page = 0, int $size = 10)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}bank-transfer/reserved-accounts/transactions?accountReference=$accountReference&page=$page&size=$size";
        $response = $this->monnify->withOAuth2()->get($endpoint);


        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


    /**
     * You can delete a reserved account by initiating a DELETE request to the endpoint below. We will immediately deallocate the account.
     * Please note this action cannot be reversed!!
     *
     * @param string $accountNumber The virtual account number generated for the accountReference (Reserved account number)
     * @return object
     *
     * @throws MonnifyFailedRequestException
     * @link https://docs.teamapt.com/display/MON/Deallocating+a+reserved+account
     */
    public function deallocateAccount(string $accountNumber)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}bank-transfer/reserved-accounts/$accountNumber";
        $response = $this->monnify->withOAuth2()->delete($endpoint);


        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


    /**
     * This API enables you restrict accounts that can fund a reserved account. This most used for a wallet system where you want only the owner of a reserved account to fund the reserved account.
     *
     * You can explicitly specify account numbers, or specify one or more account names.
     *
     * <strong>How are specified rules applied?</strong>
     * If only account numbers are specified, funding of account will be restricted to specified account numbers.
     * If only account names are specified, funding of account will be restricted to specified account names.
     * If both account numbers and account names are specified, funding will be permitted when either of the two rules match, i.e. source account number matches specified account numbers or source account name matches specified account name.
     * Account Name Matching Rule
     *
     * Matching of source account name is dynamic, such that if CIROMA CHUKWUMA ADEKUNLE is the specified account name, funding of accounts will be permitted from accounts with name that has AT LEAST TWO words from the specified name, and in any order.
     *
     * @param string $accountReference Your unique reference used to identify this reserved account
     * @param MonnifyAllowedPaymentSources $allowedPaymentSources
     * @return object
     *
     * @throws MonnifyFailedRequestException
     * @link https://docs.teamapt.com/display/MON/Getting+all+transactions+on+a+reserved+account
     */
    public function restrictSourceAccount(string $accountReference, MonnifyAllowedPaymentSources $allowedPaymentSources)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}bank-transfer/reserved-accounts/update-payment-source-filter/$accountReference";
        $response = $this->monnify->withOAuth2()->put($endpoint, [
            "restrictPaymentSource" => true,
            "allowedPaymentSources" => $allowedPaymentSources->toArray()
        ]);


        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }

}
