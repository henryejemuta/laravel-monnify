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
