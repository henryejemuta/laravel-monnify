<?php
/**
 * Created By: Henry Ejemuta
 * Project: laravel-monnify
 * Class Name: Monnify.php
 * Date Created: 7/13/20
 * Time Created: 7:55 PM
 */

namespace HenryEjemuta\LaravelMonnify;

use HenryEjemuta\LaravelMonnify\Classes\MonnifyAllowedPaymentSources;
use HenryEjemuta\LaravelMonnify\Classes\MonnifyBankAccount;
use HenryEjemuta\LaravelMonnify\Classes\MonnifyIncomeSplitConfig;
use HenryEjemuta\LaravelMonnify\Classes\MonnifyOnFailureValidate;
use HenryEjemuta\LaravelMonnify\Classes\MonnifyPaymentMethods;
use HenryEjemuta\LaravelMonnify\Classes\MonnifyTransaction;
use HenryEjemuta\LaravelMonnify\Classes\MonnifyTransactionList;
use HenryEjemuta\LaravelMonnify\Exceptions\MonnifyFailedRequestException;
use HenryEjemuta\LaravelMonnify\Exceptions\MonnifyInvalidParameterException;
use Illuminate\Support\Facades\Http;

/**
 * Class Monnify
 * @package HenryEjemuta\LaravelMonnify
 *
 * This is a Laravel wrapper around the Monnify API hence all failed request will throw MonnifyFailedRequestException with the appropriate message from the Monnify API as well as error code
 *
 * @link https://docs.teamapt.com/display/MON/Monnify+API+Docs for any error details as well as status code to gracefully handle them within your application
 *
 */
class Monnify
{
    /**
     * base url
     *
     * @var
     */
    private $baseUrl;
    private $v1 = "/api/v1/";
    private $v2 = "/api/v2/";

    /**
     * the cart session key
     *
     * @var
     */
    protected $instanceName;

    /**
     * Flexible handle to the Monnify Configuration
     *
     * @var
     */
    protected $config;

    /**
     * Http Client for remote request handling
     * @var Http
     */
    private $httpClient;


    private $oAuth2Token = '';
    private $oAuth2TokenExpires = '';

    public function __construct($baseUrl, $instanceName, $config)
    {
        $this->baseUrl = $baseUrl;
        $this->instanceName = $instanceName;
        $this->config = $config;
    }


    /**
     * get instance name of the cart
     *
     * @return string
     */
    public function getInstanceName()
    {
        return $this->instanceName;
    }


    private function withBasicAuth()
    {
        $this->httpClient = Http::withBasicAuth($this->config['api_key'], $this->config['secret_key'])->asJson();
        return $this;
    }


    /**
     * @return $this
     * @throws MonnifyFailedRequestException
     */
    private function getToken()
    {
        $endpoint = "{$this->baseUrl}{$this->v1}auth/login";
        $this->withBasicAuth();
        $response = $this->httpClient->post($endpoint);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        $this->oAuth2Token = $responseObject->responseBody->accessToken;
        $this->oAuth2TokenExpires = ((time() + $responseObject->responseBody->expiresIn) - 60);//Just make sure the token would not expire in 60 secs

        return $this;
    }


    private function withOAuth2()
    {
        if (time() >= $this->oAuth2TokenExpires) {
            $this->getToken();
            $this->httpClient = Http::withToken($this->oAuth2Token);
        }

        return $this;
    }


    /**
     * This enables you to retrieve all banks supported by Monnify for collections and disbursements.
     * @return array
     *
     * @throws MonnifyFailedRequestException
     */
    public function getBanks()
    {
        $endpoint = "{$this->baseUrl}{$this->v1}banks";

        $this->withOAuth2();
        $response = $this->httpClient->get($endpoint);

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
        $endpoint = "{$this->baseUrl}{$this->v1}sdk/transactions/banks";

        $this->withOAuth2();
        $response = $this->httpClient->get($endpoint);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
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

        $endpoint = "{$this->baseUrl}{$this->v1}sub-accounts";

        $this->withBasicAuth();
        $response = $this->httpClient->post($endpoint, [[
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
        $endpoint = "{$this->baseUrl}{$this->v1}sub-accounts";

        $this->withBasicAuth();
        $response = $this->httpClient->post($endpoint, $accounts);

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
        $endpoint = "{$this->baseUrl}{$this->v1}sub-accounts";

        $this->withBasicAuth();
        $response = $this->httpClient->get($endpoint);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


    /**
     * Deletes a merchant's sub account.
     * @param string $subAccountCode The unique reference for the sub account
     * @return array
     *
     * @throws MonnifyFailedRequestException
     */
    public function deleteSubAccount(string $subAccountCode)
    {
        $endpoint = "{$this->baseUrl}{$this->v1}sub-accounts/$subAccountCode";

        $this->withBasicAuth();
        $response = $this->httpClient->delete($endpoint);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseMessage;
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

        $endpoint = "{$this->baseUrl}{$this->v1}sub-accounts";

        $this->withBasicAuth();
        $response = $this->httpClient->put($endpoint, [
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


    /**
     * This returns all transactions done by a merchant.
     *
     * @param array $queryParams
     * @return object
     *
     * @throws MonnifyFailedRequestException
     *
     * Kindly check here for query parameters keys
     * @link https://docs.teamapt.com/display/MON/Get+All+Transactions
     */
    public function getAllTransactions(array $queryParams)
    {
        $endpoint = "{$this->baseUrl}{$this->v1}transactions/search?" . http_build_query($queryParams, '', '&amp;');

        $this->withOAuth2();
        $response = $this->httpClient->get($endpoint);

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
     * @param string|null $customerBvn BVN of the customer the account is being reserved for. Although this field is not mandated, it is advised that it is supplied. Please note that there could be low limits on the reserved account in future, if BVN is not supplied.
     * @param string|null $currencyCode
     * @param MonnifyIncomeSplitConfig|null $incomeSplitConfig
     * @param bool $restrictPaymentSource
     * @param MonnifyAllowedPaymentSources $allowedPaymentSources
     * @return object
     *
     * @throws MonnifyInvalidParameterException
     * @throws MonnifyFailedRequestException
     * @link https://docs.teamapt.com/display/MON/Reserving+An+Account
     */
    public function reserveAccount(string $accountReference, string $accountName, string $customerEmail, string $customerName = null, string $customerBvn = null, string $currencyCode = null, bool $restrictPaymentSource = false, MonnifyAllowedPaymentSources $allowedPaymentSources = null, MonnifyIncomeSplitConfig $incomeSplitConfig = null)
    {
        if ($restrictPaymentSource && is_null($allowedPaymentSources))
            throw new MonnifyInvalidParameterException("Allowed Payment Sources can't be null if payment source is restricted");

        $endpoint = "{$this->baseUrl}{$this->v1}bank-transfer/reserved-accounts";
        $requestBody = [
            "accountReference" => $accountReference,
            "accountName" => $accountName,
            "currencyCode" => $currencyCode ?? $this->config['default_currency_code'],
            "contractCode" => $this->config['contract_code'],
            "customerEmail" => $customerEmail,
            "restrictPaymentSource" => $restrictPaymentSource,
        ];

        if ((!is_null($customerName)) && (!empty(trim($customerName))))
            $requestBody['customerName'] = $customerName;

        if ((!is_null($customerBvn)) && (!empty(trim($customerBvn))))
            $requestBody['customerBvn'] = $customerBvn;

        if (!is_null($allowedPaymentSources))
            $requestBody['allowedPaymentSources'] = $allowedPaymentSources->toArray();

        if (!is_null($incomeSplitConfig))
            $requestBody['incomeSplitConfig'] = $incomeSplitConfig->toArray();

        $this->withOAuth2();
        $response = $this->httpClient->post($endpoint, $requestBody);


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
    public function getReservedAccountDetails(string $accountReference)
    {
        $endpoint = "{$this->baseUrl}{$this->v1}bank-transfer/reserved-accounts/$accountReference";

        $this->withOAuth2();
        $response = $this->httpClient->get($endpoint);


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
    public function updateReservedAccountSplitConfig(string $accountReference, MonnifyIncomeSplitConfig $incomeSplitConfig)
    {
        $endpoint = "{$this->baseUrl}{$this->v1}bank-transfer/reserved-accounts/update-income-split-config/$accountReference";

        $this->withOAuth2();
        $response = $this->httpClient->put($endpoint, $incomeSplitConfig->toArray());


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
    public function getAllTransactionsForReservedAccount(string $accountReference, int $page = 0, int $size = 10)
    {
        $endpoint = "{$this->baseUrl}{$this->v1}bank-transfer/reserved-accounts/transactions?accountReference=$accountReference&page=$page&size=$size";

        $this->withOAuth2();
        $response = $this->httpClient->get($endpoint);


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
    public function deallocateReservedAccount(string $accountNumber)
    {
        $endpoint = "{$this->baseUrl}{$this->v1}bank-transfer/reserved-accounts/$accountNumber";

        $this->withOAuth2();
        $response = $this->httpClient->delete($endpoint);


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
    public function sourceAccountRestriction(string $accountReference, MonnifyAllowedPaymentSources $allowedPaymentSources)
    {
        $endpoint = "{$this->baseUrl}{$this->v1}bank-transfer/reserved-accounts/update-payment-source-filter/$accountReference";

        $this->withOAuth2();
        $response = $this->httpClient->put($endpoint, [
            "restrictPaymentSource" => true,
            "allowedPaymentSources" => $allowedPaymentSources->toArray()
        ]);


        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


    /**
     * Allows you initialize a transaction on Monnify and returns a checkout URL which you can load within a browser to display the payment form to your customer.
     *
     * @param float $amount The amount to be paid by the customer
     * @param string $customerName Full name of the customer
     * @param string $customerEmail Email address of the customer
     * @param string $paymentReference Merchant's Unique reference for the transaction.
     * @param string $paymentDescription Description for the transaction. Will be returned as part of the account name on name enquiry for transfer payments.
     * @param string $redirectUrl A URL which user will be redirected to, on completion of the payment.
     * @param MonnifyPaymentMethods $monnifyPaymentMethods
     * @param MonnifyIncomeSplitConfig $incomeSplitConfig
     * @param string|null $currencyCode
     * @return array
     *
     * @throws MonnifyFailedRequestException
     * @link https://docs.teamapt.com/display/MON/Initialize+Transaction
     */
    public function initializeTransaction(float $amount, string $customerName, string $customerEmail, string $paymentReference, string $paymentDescription, string $redirectUrl, MonnifyPaymentMethods $monnifyPaymentMethods, MonnifyIncomeSplitConfig $incomeSplitConfig, string $currencyCode = null)
    {
        $endpoint = "{$this->baseUrl}{$this->v1}merchant/transactions/init-transaction";

        $this->withBasicAuth();
        $response = $this->httpClient->post($endpoint, [
            "amount" => $amount,
            "customerName" => trim($customerName),
            "customerEmail" => $customerEmail,
            "paymentReference" => $paymentReference,
            "paymentDescription" => trim($paymentDescription),
            "currencyCode" => $currencyCode ?? $this->config['default_currency_code'],
            "contractCode" => $this->config['contract_code'],
            "redirectUrl" => trim($redirectUrl),
            "paymentMethods" => $monnifyPaymentMethods->toArray(),
            "incomeSplitConfig" => $incomeSplitConfig->toArray()
        ]);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


    /**
     * When Monnify sends transaction notifications, we add a transaction hash for security reasons. We expect you to try to recreate the transaction hash and only honor the notification if it matches.
     *
     * To calculate the hash value, concatenate the following parameters in the request body and generate a hash using the SHA512 algorithm:
     *
     * @param string $paymentReference Unique reference generated by the merchant for each transaction. However, will be the same as transactionReference for reserved accounts.
     * @param float $amountPaid The amount that was paid by the customer
     * @param string $paidOn Date and Time when payment happened in the format dd/mm/yyyy hh:mm:ss
     * @param string $transactionReference Unique transaction reference generated by Monnify for each transaction
     * @return string Hash of successful transaction
     *
     * @link https://docs.teamapt.com/display/MON/Calculating+the+Transaction+Hash
     */
    public function calculateTransactionHash(string $paymentReference, float $amountPaid, string $paidOn, string $transactionReference)
    {
        $clientSK = $this->config['secret_key'];
        return hash('sha512', "$clientSK|$paymentReference|$amountPaid|$paidOn|$transactionReference");
    }

    public function calculateTransactionHashFix(string $paymentReference, $amountPaid, string $paidOn, string $transactionReference)
    {
        $clientSK = $this->config['secret_key'];
        return hash('sha512', "$clientSK|$paymentReference|$amountPaid|$paidOn|$transactionReference");
    }


    /**
     * We highly recommend that when you receive a notification from us, even after checking to ensure the hash values match,
     * you should initiate a get transaction status request to us with the transactionReference to confirm the actual status of that transaction before updating the records on your database.
     *
     * @param string $transactions Unique transaction reference generated by Monnify for each transaction
     * @return object
     *
     * @throws MonnifyFailedRequestException
     * @link https://docs.teamapt.com/display/MON/Get+Transaction+Status
     */
    public function getTransactionStatus(string $transactions)
    {
        $endpoint = "{$this->baseUrl}{$this->v2}transactions/$transactions/";

        $this->withOAuth2();
        $response = $this->httpClient->get($endpoint);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


    /**
     * Allows you get virtual account details for a transaction using the transactionReference of an initialized transaction.
     * This is useful if you want to control the payment interface.
     * There are a lot of UX considerations to keep in mind if you choose to do this so we recommend you read this @link https://docs.teamapt.com/display/MON/Optimizing+Your+User+Experience.
     *
     * @param string $transactionReference
     * @param string $bankCode
     * @return array
     *
     * @throws MonnifyFailedRequestException
     * @link https://docs.teamapt.com/display/MON/Pay+with+Bank+Transfer
     */
    public function payWithBankTransfer(string $transactionReference, string $bankCode)
    {
        $endpoint = "{$this->baseUrl}{$this->v1}merchant/bank-transfer/init-payment";

        $this->withBasicAuth();
        $response = $this->httpClient->post($endpoint, [
            "transactionReference" => $transactionReference,
            "bankCode" => trim($bankCode),
        ]);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
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
        $endpoint = "{$this->baseUrl}{$this->v1}disbursements/single";

        $this->withBasicAuth();
        $response = $this->httpClient->post($endpoint, [
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
     * @param MonnifyTransaction $monnifyTransaction Transaction Object
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
        $endpoint = "{$this->baseUrl}{$this->v1}disbursements/batch";

        $this->withBasicAuth();
        $response = $this->httpClient->post($endpoint, [
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
        $endpoint = "{$this->baseUrl}{$this->v1}disbursements/$path/validate-otp";

        $this->withBasicAuth();
        $response = $this->httpClient->post($endpoint, [
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
        $endpoint = "{$this->baseUrl}{$this->v1}disbursements/$path/summary?reference=$reference";

        $this->withBasicAuth();
        $response = $this->httpClient->get($endpoint);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


    /**
     * Single Transfers
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
     * Bulk Transfers
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
        $endpoint = "{$this->baseUrl}{$this->v1}disbursements/bulk/$batchReference/transactions?pageNo=$pageNo&pageSize=$pageSize";
        $this->withBasicAuth();
        $response = $this->httpClient->get($endpoint);

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
        $endpoint = "{$this->baseUrl}{$this->v1}disbursements/$path/transactions?pageNo=$pageNo&pageSize=$pageSize";

        $this->withBasicAuth();
        $response = $this->httpClient->get($endpoint);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


    /**
     * Single Transfers
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
     * Bulk Transfers
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
     * This allows you check if an account number is a valid NUBAN, get the account name if valid.
     *
     * @param MonnifyBankAccount $bankAccount
     * @return mixed
     * @throws MonnifyFailedRequestException
     *
     * @link https://docs.teamapt.com/display/MON/Validate+Bank+Account
     */
    public function validateBankAccount(MonnifyBankAccount $bankAccount){

        $endpoint = "{$this->baseUrl}{$this->v1}disbursements/account/validate?accountNumber={$bankAccount->getAccountNumber()}&bankCode={$bankAccount->getBankCode()}";
        $this->withBasicAuth();
        $response = $this->httpClient->get($endpoint);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


    /**
     * This allows you to get the available balance in your monnify wallet.
     *
     * @return mixed
     * @throws MonnifyFailedRequestException
     *
     * @link https://docs.teamapt.com/display/MON/Get+Wallet+Balance
     */
    public function getWalletBalance(){
        $endpoint = "{$this->baseUrl}{$this->v1}disbursements/wallet-balance?walletId={$this->config['wallet_id']}";
        $this->withBasicAuth();
        $response = $this->httpClient->get($endpoint);

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
    public function resendOTP(string $reference){
        $endpoint = "{$this->baseUrl}{$this->v1}disbursements/single/resend-otp";
        $this->withBasicAuth();
        $response = $this->httpClient->post($endpoint, [
            'reference' => $reference
        ]);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


}
