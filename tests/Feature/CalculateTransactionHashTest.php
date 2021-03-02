<?php
/**
 * Created By: David Ogunejimite
 * Project: laravel-monnify
 * Class Name: CalculateTransactionHashTest.php
 * Date Created: 9/06/20
 * Time Created: 03:00 AM
 */

namespace HenryEjemuta\LaravelMonnify\Tests\Feature;


use HenryEjemuta\LaravelMonnify\Monnify;
use HenryEjemuta\LaravelMonnify\Tests\TestCase;

class CalculateTransactionHashTest extends TestCase
{
    /**
     * A sample payload that was posted to the webhook, it must exist on Monnify !Really
     * @var string
     */
    public $payload = '{
    "transactionReference": "MNFY|20200903225338|000442",
    "paymentReference": "MNFY|20200903225338|000442",
    "amountPaid": "150000.00",
    "totalPayable": "150000.00",
    "settlementAmount": "149990.00",
    "paidOn": "03/09/2020 10:53:39 PM",
    "paymentStatus": "PAID",
    "paymentDescription": "David TESTER",
    "transactionHash": "396a405ad973a5f5b2327ba06c920a1cc26b3b5c2573db29fbc9d2aaae68b0c1ebeda4461a188d9dd9db014653493a022bdd5efb363ebcd68a74d769297c8a1d",
    "currency": "NGN",
    "paymentMethod": "ACCOUNT_TRANSFER",
    "product": {
        "type": "RESERVED_ACCOUNT",
        "reference": "eb6a3cae-bc80-418e-86d7-1be28365d387"
    },
    "cardDetails": null,
    "accountDetails": {
        "accountName": "Monnify Limited",
        "accountNumber": "******2190",
        "bankCode": "001",
        "amountPaid": "150000.00"
    },
    "accountPayments": [
        {
            "accountName": "Monnify Limited",
            "accountNumber": "******2190",
            "bankCode": "001",
            "amountPaid": "150000.00"
        }
    ],
    "customer": {
        "email": "Ogkingd95@gmail.com",
        "name": "David TESTER"
    },
    "metaData": {}
}';
    public $config = [
        'base_url' => "https://sandbox.monnify.com",
        'api_key' => "MK_TEST_SAF7HR5F3F",
        'secret_key' => "4SY6TNL8CK3VPRSBTHTRG2N8XXEGC6NL",
        'contract_code' => "4934121686",
        'default_split_percentage' => "",
        'default_currency_code' => "",
        'redirect_url' => "",
        'wallet_id' => "",
    ];

    /**
     * A mock of the Monnify class, i'm just so lazy to load an actual project
     * @return Monnify
     */
    public function fakeMonnifyClass(): Monnify
    {
        return new Monnify($this->config['base_url'], 'monnify', $this->config);

    }


    /**
     *Run the test against the default calculateTransactionHash implemented in the Monnify Class, THIS TEST WOULD FAIL!
     */
    public function testCalculateTransactionHash(): void
    {
        $payload = json_decode($this->payload, false);
        self::assertIsObject($payload);
        $payloadHash = $payload->transactionHash;
        $computedHash = $this->fakeMonnifyClass()->Transactions()->calculateHash($payload->paymentReference, $payload->amountPaid, $payload->paidOn, $payload->transactionReference);
        self::assertSame($payloadHash, $computedHash);
    }

}
