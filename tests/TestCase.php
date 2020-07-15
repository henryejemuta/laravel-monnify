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
        // perform environment setup
    }

}
