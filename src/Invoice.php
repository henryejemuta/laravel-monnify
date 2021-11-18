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


use HenryEjemuta\LaravelMonnify\Classes\MonnifyIncomeSplitConfig;
use HenryEjemuta\LaravelMonnify\Classes\MonnifyPaymentMethods;
use HenryEjemuta\LaravelMonnify\Exceptions\MonnifyFailedRequestException;

abstract class Invoice
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
     * You can get details of all your invoices by using this handle
     *  Monnify will return a paginated list of all your invoices. Here's a sample response
     * @return object
     *
     * @throws MonnifyFailedRequestException
     * @see https://teamapt.atlassian.net/wiki/spaces/MON/pages/212008979/Get+All+Invoices
     *
     */
    public function getAllInvoice()
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}invoice/all";
        $response = $this->monnify->withOAuth2()->get($endpoint);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


    /**
     * Allows you create an invoice on Monnify. When the request is sent, an account number will be returned.
     * You should include that account number (and bank) on the invoice being sent to your customer. We also return a checkout URL which can be included on your invoices. This way customers who want to pay using their debit cards can simply click on the link and pay using the Monnify payment interface.
     *
     * @param float $amount The amount to be paid by the customer
     * @param string $expiryDateTime Invoice Expiry date in this format "2019-10-30 12:00:00"
     * @param string $customerName Full name of the customer
     * @param string $customerEmail Email address of the customer
     * @param string $invoiceReference Merchant's Unique reference for the invoice.
     * @param string $invoiceDescription Description for the invoce. Will be returned as part of the account name on name enquiry for transfer payments.
     * @param string $redirectUrl A URL which user will be redirected to, on completion of the payment.
     * @param MonnifyPaymentMethods $monnifyPaymentMethods
     * @param MonnifyIncomeSplitConfig $incomeSplitConfig
     * @param string|null $currencyCode
     * @return array
     *
     * @throws MonnifyFailedRequestException
     * @link https://teamapt.atlassian.net/wiki/spaces/MON/pages/212008946/Create+an+Invoice
     */
    public function createAnInvoice(float $amount, $expiryDateTime, string $customerName, string $customerEmail, string $invoiceReference, string $invoiceDescription, string $redirectUrl, MonnifyPaymentMethods $monnifyPaymentMethods, MonnifyIncomeSplitConfig $incomeSplitConfig = null, string $currencyCode = null)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}invoice/create";

        $formData = [
            "amount" => $amount,
            "expiryDate" => trim($expiryDateTime),
            "customerName" => trim($customerName),
            "customerEmail" => $customerEmail,
            "invoiceReference" => $invoiceReference,
            "description" => trim($invoiceDescription),
            "currencyCode" => $currencyCode ?? $this->config['default_currency_code'],
            "contractCode" => $this->config['contract_code'],
            "redirectUrl" => trim($redirectUrl),
            "paymentMethods" => $monnifyPaymentMethods->toArray(),
        ];
        if ($incomeSplitConfig !== null)
            $formData["incomeSplitConfig"] = $incomeSplitConfig->toArray();
        $response = $this->monnify->withBasicAuth()->post($endpoint, $formData);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


    /**
     * To view the details of an invoice you need to use this handle
     * Once you send the request, Monnify will return all the details attached to the invoice
     *
     * @param string $invoiceReference Unique invoice reference provide while creating invoice
     * @return object
     *
     * @throws MonnifyFailedRequestException
     * @link https://teamapt.atlassian.net/wiki/spaces/MON/pages/212008971/View+Invoice+Details
     */
    public function viewInvoiceDetails(string $invoiceReference)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}invoice/$invoiceReference/details";

        $response = $this->monnify->withOAuth2()->get($endpoint);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }

    /**
     * To cancel an invoice you need to use this handle
     * Once the invoice is canceled, the customer can no longer pay for that invoice. For the customer reserved invoice, the customer's account number becomes inactive.
     *
     * @param string $invoiceReference Unique invoice reference provide while creating invoice
     * @return object
     *
     * @throws MonnifyFailedRequestException
     * @link https://teamapt.atlassian.net/wiki/spaces/MON/pages/213909772/Cancel+an+Invoice
     */
    public function cancelAnInvoice(string $invoiceReference)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}invoice/$invoiceReference/cancel";

        $response = $this->monnify->withOAuth2()->delete($endpoint);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


    /**
     * Monnify allows you Reserve an Account for your customers who you send invoices to.
     * You can then attach these accounts to invoices being generated so the customer always receives the same account number for any invoice he receives.
     * These accounts are slightly different from regular customer reserved accounts as customers cannot pay into these accounts until they are attached to an invoice.
     * Basically, the account number is only active when attached to an invoice.
     *
     * <strong>NB</strong>
     * <em>Only one invoice can be attached to a Reserved Account at a time.</em>
     *
     * @param string $accountName Reserved Account Name
     * @param string $customerName Full name of the customer
     * @param string $customerEmail Email address of the customer
     * @param string $accountReference Merchant's Unique reference for the Account.
     * @param string|null $currencyCode
     *
     * @return object
     *
     * @throws MonnifyFailedRequestException
     * @link https://teamapt.atlassian.net/wiki/spaces/MON/pages/212008993/Reserved+Account+Invoicing
     */
    public function reservedAccountInvoicing(string $accountName, string $customerName, string $customerEmail, string $accountReference, string $currencyCode = null)
    {
        $endpoint = "{$this->monnify->baseUrl}{$this->monnify->v1}bank-transfer/reserved-accounts";

        $response = $this->monnify->withBasicAuth()->post($endpoint, [
            "contractCode" => $this->config['contract_code'],
            "accountName" => trim($accountName),
            "currencyCode" => $currencyCode ?? $this->config['default_currency_code'],
            "accountReference" => $accountReference,
            "customerEmail" => $customerEmail,
            "customerName" => trim($customerName),
            "reservedAccountType" => "INVOICE",
        ]);

        $responseObject = json_decode($response->body());
        if (!$response->successful())
            throw new MonnifyFailedRequestException($responseObject->responseMessage ?? "Path '{$responseObject->path}' {$responseObject->error}", $responseObject->responseCode ?? $responseObject->status);

        return $responseObject->responseBody;
    }


}
