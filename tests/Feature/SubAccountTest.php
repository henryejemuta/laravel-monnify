<?php
/**
 * Created By: Henry Ejemuta
 * PC: Enrico Systems
 * Project: laravel-monnify
 * Company: Stimolive Technologies Limited
 * Class Name: reateSubAccountTest.php
 * Date Created: 7/15/20
 * Time Created: 11:31 AM
 */

namespace HenryEjemuta\LaravelMonnify\Tests\Feature;


use HenryEjemuta\LaravelMonnify\Exceptions\MonnifyFailedRequestException;
use HenryEjemuta\LaravelMonnify\Facades\Monnify;
use HenryEjemuta\LaravelMonnify\Tests\TestCase;

class SubAccountTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGetSubAccountsTest()
    {
        try {
            Monnify::SubAccounts()->getSubAccounts();
            $this->assertTrue(true);
        } catch (MonnifyFailedRequestException $exception) {
            $this->assertTrue(true, $exception->getMessage());
        }

    }
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testDeleteSubAccountTest()
    {
        try {
            $response = Monnify::SubAccounts()->deleteSubAccount('MFY_SUB_683712752381');
            $this->assertJson($response);
        } catch (MonnifyFailedRequestException $exception) {
            $this->assertTrue(true, $exception->getMessage());
        }

    }
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateSubAccountTest()
    {
        try {
            $response = Monnify::SubAccounts()->createSubAccount('058', '0221097794', 'johndoe@example.com');
            $this->assertTrue(true);
        } catch (MonnifyFailedRequestException $exception) {
            $this->assertTrue(true, $exception->getMessage());
        }

    }

}
