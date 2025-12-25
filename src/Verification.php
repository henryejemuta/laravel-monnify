<?php
/**
 * Created By: Henry Ejemuta
 * Project: laravel-monnify
 * Class Name: Verification.php
 * Date Created: 25/12/2025
 */

namespace HenryEjemuta\LaravelMonnify;

use HenryEjemuta\LaravelMonnify\Exceptions\MonnifyFailedRequestException;

abstract class Verification
{
    private $monnify;
    private $config;

    public function __construct(Monnify $monnify, $config)
    {
        $this->config = $config;
        $this->monnify = $monnify;
    }

    /**
     * Verify BVN details match
     * @param string $bvn
     * @param string $name
     * @param string $dob
     * @param string $mobileNo
     * @return mixed
     * @throws MonnifyFailedRequestException
     */
    public function validateBVN(string $bvn, string $name, string $dob, string $mobileNo)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}vas/bvn-details-match";
        $response = $this->monnify->withOAuth2()->post($endpoint, [
            'bvn' => $bvn,
            'name' => $name,
            'dateOfBirth' => $dob,
            'mobileNo' => $mobileNo
        ]);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }

    /**
     * Verify BVN and Account Number match
     * @param string $bvn
     * @param string $accountNumber
     * @param string $bankCode
     * @return mixed
     * @throws MonnifyFailedRequestException
     */
    public function validateBVNAccountInvalidation(string $bvn, string $accountNumber, string $bankCode)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}vas/bvn-account-match";
        $response = $this->monnify->withOAuth2()->post($endpoint, [
            'bvn' => $bvn,
            'accountNumber' => $accountNumber,
            'bankCode' => $bankCode
        ]);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }

    /**
     * Verify NIN details with NIN number
     * @param string $nin
     * @return mixed
     * @throws MonnifyFailedRequestException
     */
    public function validateNIN(string $nin)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}vas/nin-details";
        $response = $this->monnify->withOAuth2()->post($endpoint, [
            'nin' => $nin
        ]);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }
}
