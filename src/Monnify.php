<?php
/**
 * Created By: Henry Ejemuta
 * Project: laravel-monnify
 * Class Name: Monnify.php
 * Date Created: 7/13/20
 * Time Created: 7:55 PM
 */

namespace HenryEjemuta\LaravelMonnify;

use HenryEjemuta\LaravelMonnify\Exceptions\MonnifyFailedRequestException;
use Illuminate\Http\Client\PendingRequest;
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
    public $baseUrl;
    public $v1 = "/api/v1/";
    public $v2 = "/api/v2/";

    /**
     * the cart session key
     *
     * @var
     */
    private $instanceName;

    /**
     * Flexible handle to the Monnify Configuration
     *
     * @var
     */
    private $config;

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


    public function withBasicAuth()
    {
        $this->httpClient = Http::withBasicAuth($this->config['api_key'], $this->config['secret_key'])->asJson();
        return $this->httpClient;
    }


    /**
     * @return $this
     * @throws MonnifyFailedRequestException
     */
    private function getToken()
    {
        $endpoint = "{$this->baseUrl}{$this->v1}auth/login";
        $response = $this->withBasicAuth()->post($endpoint);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        $this->oAuth2Token = $responseObject->responseBody->accessToken;
        $this->oAuth2TokenExpires = ((time() + $responseObject->responseBody->expiresIn) - 60);//Just make sure the token would not expire in 60 secs

        return $this;
    }

    /**
     * When Monnify sends a notification, a hash of the request body is computed and set in the request header with the key <strong>monnify-signature</strong>. We expect you to try to recreate the hash and only accept or honor the notification if your computed hash matches what’s sent by Monnify.
     *
     * To calculate the hash value, you will have to hash the whole object with your unique client secret as key. This allows you to pass data to be hashed as a string alongside the client secret.
     *
     * @param string $stringifiedData Stringifer version of the whole request body object
     * @return string Hash of successful transaction
     *
     * @link https://teamapt.atlassian.net/wiki/spaces/MON/pages/212008918/Computing+Request+Validation+Hash
     */
    public function computeRequestValidationHash(string $stringifiedData)
    {
        $clientSK = $this->config['secret_key'];
        return hash_hmac('sha512', $stringifiedData, $clientSK);
    }

    public function computeRequestValidationHashTest(string $stringifiedData)
    {
        $DEFAULT_MERCHANT_CLIENT_SECRET = '91MUDL9N6U3BQRXBQ2PJ9M0PW4J22M1Y';
        $data = '{"eventData":{"product":{"reference":"111222333","type":"OFFLINE_PAYMENT_AGENT"},"transactionReference":"MNFY|76|20211117154810|000001","paymentReference":"0.01462001097368737","paidOn":"17/11/2021 3:48:10 PM","paymentDescription":"Mockaroo Jesse","metaData":{},"destinationAccountInformation":{},"paymentSourceInformation":{},"amountPaid":78000,"totalPayable":78000,"offlineProductInformation":{"code":"41470","type":"DYNAMIC"},"cardDetails":{},"paymentMethod":"CASH","currency":"NGN","settlementAmount":77600,"paymentStatus":"PAID","customer":{"name":"Mockaroo Jesse","email":"111222333@ZZAMZ4WT4Y3E.monnify"}},"eventType":"SUCCESSFUL_TRANSACTION"}';
        $hash = hash_hmac('sha512', $stringifiedData, $DEFAULT_MERCHANT_CLIENT_SECRET);
        $clientSK = $this->config['secret_key'];
        $dataHash = hash_hmac('sha512', $data, $clientSK);
        return "$hash\n\r$dataHash\n\r" . hash_hmac('sha512', $data, $DEFAULT_MERCHANT_CLIENT_SECRET);
    }

    /**
     * @return PendingRequest|Http
     * @throws MonnifyFailedRequestException
     */
    public function withOAuth2()
    {
        if (time() >= $this->oAuth2TokenExpires) {
            $this->getToken();
            $this->httpClient = Http::withToken($this->oAuth2Token);
        }

        return $this->httpClient;
    }

    private $banks;

    public function Banks(): Banks
    {
        if (is_null($this->banks))
            $this->banks = new class($this, $this->config) extends Banks {
            };
        return $this->banks;
    }

    /**
     * @var CustomerReservedAccount
     */
    private $reservedAccounts;

    public function ReservedAccounts(): CustomerReservedAccount
    {
        if (is_null($this->reservedAccounts))
            $this->reservedAccounts = new class($this, $this->config) extends CustomerReservedAccount {
            };
        return $this->reservedAccounts;
    }


    private $disbursements;

    public function Disbursements(): Disbursements
    {
        if (is_null($this->disbursements))
            $this->disbursements = new class($this, $this->config) extends Disbursements {
            };
        return $this->disbursements;
    }

    private $invoice;

    public function Invoice(): Invoice
    {
        if (is_null($this->invoice))
            $this->invoice = new class($this, $this->config) extends Invoice {
            };
        return $this->invoice;
    }

    private $subAccounts;

    public function SubAccounts(): SubAccounts
    {
        if (is_null($this->subAccounts))
            $this->subAccounts = new class($this, $this->config) extends SubAccounts {
            };
        return $this->subAccounts;
    }

    private $transactions;

    public function Transactions(): Transactions
    {
        if (is_null($this->transactions))
            $this->transactions = new class($this, $this->config) extends Transactions {
            };
        return $this->transactions;
    }
}
