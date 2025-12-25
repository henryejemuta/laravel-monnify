<?php
/**
 * Created By: Henry Ejemuta
 * Project: laravel-monnify
 * Class Name: TestCase.php
 * Date Created: 7/13/20
 * Time Created: 6:52 PM
 */

namespace HenryEjemuta\LaravelMonnify\Tests;


use HenryEjemuta\LaravelMonnify\LaravelMonnifyServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // additional setup
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelMonnifyServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('monnify.base_url', 'https://sandbox.monnify.com');
        $app['config']->set('monnify.api_key', 'test_api_key');
        $app['config']->set('monnify.secret_key', 'test_secret_key');
        $app['config']->set('monnify.contract_code', '1234567890');
    }

}
