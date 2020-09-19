<?php
/**
 * Created By: Henry Ejemuta
 * PC: Enrico Systems
 * Project: laravel-monnify
 * Company: Stimolive Technologies Limited
 * Class Name: NewWebHookCallReceived.php
 * Date Created: 9/18/20
 * Time Created: 11:54 PM
 */

namespace HenryEjemuta\LaravelMonnify\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use HenryEjemuta\LaravelMonnify\Models\WebHookCall;

class NewWebHookCallReceived
{
    use Dispatchable, SerializesModels;

    public $webHookCall;

    public function __construct(WebHookCall $webHookCall) {
        $this->webHookCall = $webHookCall;
    }
}
