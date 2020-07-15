<?php
/**
 * Created By: Henry Ejemuta
 * Project: laravel-monnify
 * Class Name: Monnify.php
 * Date Created: 7/13/20
 * Time Created: 8:44 PM
 */

namespace HenryEjemuta\LaravelMonnify\Facades;

use Illuminate\Support\Facades\Facade;

class Monnify extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'monnify';
    }
}
