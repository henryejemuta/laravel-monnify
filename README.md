# Laravel Monnify

[![Build Status](https://travis-ci.org/henryejemuta/laravel-monnify.svg?branch=master)](https://travis-ci.org/henryejemuta/laravel-monnify)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/henryejemuta/laravel-monnify.svg?style=flat-square)](https://packagist.org/packages/henryejemuta/laravel-monnify)
[![Latest Stable Version](https://poser.pugx.org/henryejemuta/laravel-monnify/v/stable)](https://packagist.org/packages/henryejemuta/laravel-monnify)
[![Total Downloads](https://poser.pugx.org/henryejemuta/laravel-monnify/downloads)](https://packagist.org/packages/henryejemuta/laravel-monnify)
[![License](https://poser.pugx.org/henryejemuta/laravel-monnify/license)](https://packagist.org/packages/henryejemuta/laravel-monnify)
[![Quality Score](https://img.shields.io/scrutinizer/g/henryejemuta/laravel-monnify.svg?style=flat-square)](https://scrutinizer-ci.com/g/henryejemuta/laravel-monnify)

A laravel package to seamlessly integrate monnify api within your laravel application

## What is Monnify
Monnify is a leading payment technology that powers seamless transactions for businesses through omnichannel platforms

Create a Monnify Account [Sign Up](https://app.monnify.com/create-account).

Look up Monnify API Documentation [API Documentation](https://teamapt.atlassian.net/wiki/spaces/MON/overview).

## Installation

You can install the package via composer:

```bash
composer require henryejemuta/laravel-monnify
```

Publish Monnify configuration file, migrations as well as set default details in .env file:

```bash
php artisan monnify:init
```


## Laravel Monnify Webhook Event
To handle Monnify Webhook notification the Laravel Monnify already include the webhook endpoint `YOUR_DOMAIN/laravel-monnify/webhook`, replace `YOUR_DOMAIN` with your server url

- Log on to your [Monnify Dashboard Setting](https://app.monnify.com/settings), select **API Keys & Webhooks** and set your webhook to `YOUR_DOMAIN/laravel-monnify/webhook`


`php artisan make:listener MonnifyNotificationListener -e NewWebHookCallReceived`
The NewWebHookCallReceived has two properties:
- `WebHookCall webHookCall` => This is an unguarded Model with property dump from the webhook call `$event->webHookCall->transactionReference` gives you the transactionReference from the webhook call, learn more about webhookcall properties on Monnify API Docs [Here](https://docs.teamapt.com/display/MON/Webhook+Notifications) 
- `bool isValidTransactionHash` => This does the transaction hash calculation for you ahead of time, if you prefer doing it yourself; `Monnify::Transactions()->calculateHash($event->webHookCall->paymentReference, $event->webHookCall->amountPaid, $event->webHookCall->paidOn, $event->webHookCall->transactionReference);`
Laravel Monnify Webhook Event

```bash
php artisan monnify:init
```
/laravel-monnify/webhook


## Usage
> To use the monnify package you must import the Monnify Facades with the import statement below; Other Classes import is based on your specific usage and would be highlighted in their corresponding sections.
> You'll also need to import the MonnifyFailedRequestException and handle the exception as all failed request will throw this exception the with the corresponding monnify message and code [Learn More](https://docs.teamapt.com/display/MON/Transaction+Responses)
>
```php
    //...
    use HenryEjemuta\LaravelMonnify\Facades\Monnify;
    use HenryEjemuta\LaravelMonnify\Exceptions\MonnifyFailedRequestException;
//...

```

# Important Notice!!!
### Migrating from Previous Version of Laravel Monnify
This new changes reflect my concern for modular code base, I'm certain you should not have any issues migrating and refactoring your codebase, but if you do, kindly contact me or use the issues tab, and I will make sure your concerns are all attended to.
The Monnify class has been broken down grouping all actions into five(5) classes, Banks, CustomerReservedAccounts, Disbursements, SubAccounts, and Transactions
see example usage below: 
```php

    //...
    use HenryEjemuta\LaravelMonnify\Facades\Monnify;
    //...

    $responseBody = Monnify::Transactions()->initializeTransaction(float $amount, string $customerName, string $customerEmail, string $paymentReference, string $paymentDescription, string $redirectUrl, MonnifyPaymentMethods $monnifyPaymentMethods, MonnifyIncomeSplitConfig $incomeSplitConfig = null, string $currencyCode = null);
    $responseBody = Monnify::Transactions()->getAllTransactions(array $queryParams);
    $responseBody = Monnify::Transactions()->calculateHash(string $paymentReference, $amountPaid, string $paidOn, string $transactionReference);
    $responseBody = Monnify::Transactions()->getTransactionStatus(string $transactions);
    $responseBody = Monnify::Transactions()->payWithBankTransfer(string $transactionReference, string $bankCode);

```
## Before
```php
    //...
    use HenryEjemuta\LaravelMonnify\Facades\Monnify;
    use HenryEjemuta\LaravelMonnify\Classes\MonnifyPaymentMethod;
    use HenryEjemuta\LaravelMonnify\Classes\MonnifyPaymentMethods;
    //...
    
    Monnify::initializeTransaction(
                        15000, "Customer Name", "customer@example.com", "transaction_ref", "Transaction Description",
                        "https://youdomain.com/afterpaymentendpoint", new MonnifyPaymentMethods(MonnifyPaymentMethod::CARD(), MonnifyPaymentMethod::ACCOUNT_TRANSFER()));
```
## Now

```php
    //...
    use HenryEjemuta\LaravelMonnify\Facades\Monnify;
    use HenryEjemuta\LaravelMonnify\Classes\MonnifyPaymentMethod;
    use HenryEjemuta\LaravelMonnify\Classes\MonnifyPaymentMethods;
    //...

    Monnify::Transactions()->initializeTransaction(
                        15000, "Customer Name", "customer@example.com", "transaction_ref", "Transaction Description",
                        "https://youdomain.com/afterpaymentendpoint", new MonnifyPaymentMethods(MonnifyPaymentMethod::CARD(), MonnifyPaymentMethod::ACCOUNT_TRANSFER()));

```

Similar implementation applies to other sections (i.e. Banks, CustomerReservedAccounts, Disbursements, and SubAccounts)

```php
    //...
    use HenryEjemuta\LaravelMonnify\Facades\Monnify;
    //...
    $responseBody = Monnify::Banks()->getBanks();
    $responseBody = Monnify::Banks()->getBanksWithUSSDShortCode();
    $responseBody = Monnify::Banks()->validateBankAccount(MonnifyBankAccount $bankAccount);

    $responseBody = Monnify::Disbursements()->initiateTransferSingle(float $amount, string $reference, string $narration, MonnifyBankAccount $bankAccount, string $currencyCode = null);
    $responseBody = Monnify::Disbursements()->initiateTransferSingleWithMonnifyTransaction(MonnifyTransaction $monnifyTransaction);
    $responseBody = Monnify::Disbursements()->initiateTransferBulk(string $title, string $batchReference, string $narration, MonnifyOnFailureValidate $onFailureValidate, int $notificationInterval, MonnifyTransactionList $transactionList);
    $responseBody = Monnify::Disbursements()->authorizeTransfer2FA(string $authorizationCode, string $reference, string $path);


    $responseBody = Monnify::SubAccounts()->createSubAccount(string $bankCode, string $accountNumber, string $email, string $currencyCode = null, string $splitPercentage = null);
    $responseBody = Monnify::SubAccounts()->createSubAccounts(array $accounts);
    $responseBody = Monnify::SubAccounts()->getSubAccounts();
    $responseBody = Monnify::SubAccounts()->deleteSubAccount(string $subAccountCode);

    
    $responseBody = Monnify::ReservedAccounts()->getAllTransactions(array $queryParams);
    $responseBody = Monnify::ReservedAccounts()->reserveAccount(string $accountReference, string $accountName, string $customerEmail, string $customerName = null, string $customerBvn = null, string $currencyCode = null, bool $restrictPaymentSource = false, MonnifyAllowedPaymentSources $allowedPaymentSources = null, MonnifyIncomeSplitConfig $incomeSplitConfig = null);
    $responseBody = Monnify::ReservedAccounts()->getAccountDetails(string $accountReference);
    $responseBody = Monnify::ReservedAccounts()->updateSplitConfig(string $accountReference, MonnifyIncomeSplitConfig $incomeSplitConfig);


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
