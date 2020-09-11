# Laravel Monnify

[![Build Status](https://travis-ci.org/orobogenius/sansdaemon.svg?branch=master)](https://travis-ci.org/orobogenius/sansdaemon)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/henryejemuta/laravel-monnify.svg?style=flat-square)](https://packagist.org/packages/henryejemuta/laravel-monnify)
[![Latest Stable Version](https://poser.pugx.org/henryejemuta/laravel-monnify/v/stable)](https://packagist.org/packages/henryejemuta/laravel-monnify)
[![Total Downloads](https://poser.pugx.org/henryejemuta/laravel-monnify/downloads)](https://packagist.org/packages/henryejemuta/laravel-monnify)
[![License](https://poser.pugx.org/henryejemuta/laravel-monnify/license)](https://packagist.org/packages/henryejemuta/laravel-monnify)
[![Quality Score](https://img.shields.io/scrutinizer/g/henryejemuta/laravel-monnify.svg?style=flat-square)](https://scrutinizer-ci.com/g/henryejemuta/laravel-monnify)

A laravel package to seamlessly integrate monnify api within your laravel application

## What is Monnify
Monnify is a leading payment technology that powers seamless transactions for businesses through omnichannel platforms

Create a Monnify Account [Sign Up](https://app.monnify.com/create-account).

Look up Monnify API Documentation [API Documentation](https://docs.teamapt.com/display/MON/Monnify).

## Installation

You can install the package via composer:

```bash
composer require henryejemuta/laravel-monnify
```

Publish Monnify configuration file as well as set default details in .env file:

```bash
php artisan monnify:init
```

## Usage
> To use the monnify package you must import the Monnify Facades with the import statement below; Other Classes import is based on your specific usage and would be highlighted in their corresponding sections.
> You'll also need to import the MonnifyFailedRequestException and handle the exception as all failed request will throw this exception the with the corresponding monnify message and code [Learn More](https://docs.teamapt.com/display/MON/Transaction+Responses)
>
``` php
...
use HenryEjemuta\LaravelMonnify\Facades\Monnify;
use HenryEjemuta\LaravelMonnify\Exceptions\MonnifyFailedRequestException;
...

```

### Monnify Facades Overview
``` php


    /**
     * This enables you to retrieve all banks supported by Monnify for collections and disbursements.
     * @return array
     *
     * @throws MonnifyFailedRequestException
     */
    $responseBody = Monnify::getBanks();


    /**
     * This API enables you to retrieve all banks with valid USSD short code. This is useful if you'll like to display USSD short codes for your customers to dial.
     * For a full list of banks, use @return array
     *
     * @throws MonnifyFailedRequestException
     *
     * @see getBanks()
     *
     */
    $responseBody = Monnify::getBanksWithUSSDShortCode();


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
    $responseBody = Monnify::createSubAccount(string $bankCode, string $accountNumber, string $email, string $currencyCode = null, string $splitPercentage = null);


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
    $responseBody = Monnify::createSubAccounts(array $accounts);


    /**
     * Returns a list of sub accounts previously created by the merchant.
     * @return array
     *
     * @throws MonnifyFailedRequestException
     *
     */
    $responseBody = Monnify::getSubAccounts();


    /**
     * Deletes a merchant's sub account.
     * @param string $subAccountCode The unique reference for the sub account
     * @return array
     *
     * @throws MonnifyFailedRequestException
     */
    $responseBody = Monnify::deleteSubAccount(string $subAccountCode);


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
    $responseBody = Monnify::updateSubAccount(string $subAccountCode, string $bankCode, string $accountNumber, string $email, string $currencyCode = null, string $splitPercentage = null);


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
    $responseBody = Monnify::getAllTransactions(array $queryParams);


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
    $responseBody = Monnify::reserveAccount(string $accountReference, string $accountName, string $customerEmail, string $customerName = null, string $customerBvn = null, string $currencyCode = null, bool $restrictPaymentSource = false, MonnifyAllowedPaymentSources $allowedPaymentSources = null, MonnifyIncomeSplitConfig $incomeSplitConfig = null);


    /**
     * If you want to get the details of a reserved account, you can initiate a GET request to the endpoint below and we will return all the details attached to that account Reference.
     *
     * @param string $accountReference Your unique reference used to identify this reserved account
     * @return object
     *
     * @throws MonnifyFailedRequestException
     * @link https://docs.teamapt.com/display/MON/Get+Reserved+Account+Details
     */
    $responseBody = Monnify::getReservedAccountDetails(string $accountReference);


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
    $responseBody = Monnify::updateReservedAccountSplitConfig(string $accountReference, MonnifyIncomeSplitConfig $incomeSplitConfig);


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
    $responseBody = Monnify::getAllTransactionsForReservedAccount(string $accountReference, int $page = 0, int $size = 10);


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
    $responseBody = Monnify::deallocateReservedAccount(string $accountNumber);


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
    $responseBody = Monnify::sourceAccountRestriction(string $accountReference, MonnifyAllowedPaymentSources $allowedPaymentSources);


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
    $responseBody = Monnify::initializeTransaction(float $amount, string $customerName, string $customerEmail, string $paymentReference, string $paymentDescription, string $redirectUrl, MonnifyPaymentMethods $monnifyPaymentMethods, MonnifyIncomeSplitConfig $incomeSplitConfig, string $currencyCode = null);


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
    $responseBody = Monnify::calculateTransactionHash(string $paymentReference, float $amountPaid, string $paidOn, string $transactionReference);


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
    $responseBody = Monnify::getTransactionStatus(string $transactions);


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
    $responseBody = Monnify::payWithBankTransfer(string $transactionReference, string $bankCode);


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
    $responseBody = Monnify::initiateTransferSingle(float $amount, string $reference, string $narration, MonnifyBankAccount $bankAccount, string $currencyCode = null);


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
    $responseBody = Monnify::initiateTransferSingleWithMonnifyTransaction(MonnifyTransaction $monnifyTransaction);


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
    $responseBody = Monnify::initiateTransferBulk(string $title, string $batchReference, string $narration, MonnifyOnFailureValidate $onFailureValidate, int $notificationInterval, MonnifyTransactionList $transactionList);


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
    $responseBody = Monnify::authorizeSingleTransfer2FA(string $authorizationCode, string $reference);

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
    $responseBody = Monnify::authorizeBulkTransfer2FA(string $authorizationCode, string $reference);


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
    $responseBody = Monnify::getSingleTransferDetails(string $reference);


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
    $responseBody = Monnify::getBulkTransferDetails(string $batchReference);


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
    $responseBody = Monnify::getBulkTransferTransactions(string $batchReference, int $pageNo = 0, int $pageSize = 10);


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
    $responseBody = Monnify::getSingleTransferTransactions(int $pageNo = 0, int $pageSize = 10);


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
    $responseBody = Monnify::getAllBulkTransferTransactions(int $pageNo = 0, int $pageSize = 10);


    /**
     * This allows you check if an account number is a valid NUBAN, get the account name if valid.
     *
     * @param MonnifyBankAccount $bankAccount
     * @return mixed
     * @throws MonnifyFailedRequestException
     *
     * @link https://docs.teamapt.com/display/MON/Validate+Bank+Account
     */
    $responseBody = Monnify::validateBankAccount(MonnifyBankAccount $bankAccount);


    /**
     * This allows you to get the available balance in your monnify wallet.
     *
     * @return mixed
     * @throws MonnifyFailedRequestException
     *
     * @link https://docs.teamapt.com/display/MON/Get+Wallet+Balance
     */
    $responseBody = Monnify::getWalletBalance();


    /**
     * This allows you to resend OTP for 2FA
     *
     * @param string $reference
     * @return mixed
     * @throws MonnifyFailedRequestException
     * @link https://docs.teamapt.com/display/MON/Resend+OTP
     */
    $responseBody = Monnify::resendOTP(string $reference);




```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Bugs & Issues

If you notice any bug or issues with this package kindly create and issues here [ISSUES](https://github.com/henryejemuta/laravel-monnify/issues)

### Security

If you discover any security related issues, please email henry.ejemuta@gmail.com instead of using the issue tracker.

## Credits

- [Henry Ejemuta](https://github.com/henryejemuta)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
