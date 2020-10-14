<?php
/**
 * Created By: Henry Ejemuta
 * PC: Enrico Systems
 * Project: laravel-monnify
 * Company: Stimolive Technologies Limited
 * Class Name: MonnifyController.php
 * Date Created: 9/18/20
 * Time Created: 8:07 PM
 */

namespace HenryEjemuta\LaravelMonnify\Http\Controllers;


use HenryEjemuta\LaravelMonnify\Events\NewWebHookCallReceived;
use HenryEjemuta\LaravelMonnify\Models\WebHookCall;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MonnifyController extends Controller
{

    /**
     * Receive a webhook call from monnify and validate the transaction hash, then dispatch an event if the hash is valid else just ignore
     * @param Request $request
     */
    public function webhook(Request $request): void
    {
        $validatedPayload = $request->validate([
            'transactionReference' => 'required',
            'paymentReference' => 'required',
            'amountPaid' => 'required',
            'totalPayable' => 'required',
            'paidOn' => 'required',
            'paymentStatus' => 'required',
            'paymentDescription' => 'required',
            'transactionHash' => 'required',
            'currency' => 'required',
            'paymentMethod' => 'required',
        ]);

//        Log::info(print_r($validatedPayload, true));
        $webHookCall = new WebHookCall($request->all());

        event(new NewWebHookCallReceived($webHookCall));
//        $calculatedHash = Monnify::calculateTransactionHash($validatedPayload['paymentReference'], $validatedPayload['amountPaid'], $validatedPayload['paidOn'], $validatedPayload['transactionReference']);
//        if ($calculatedHash == $validatedPayload['transactionHash']) {
//            $webHookCall = new WebHookCall($validatedPayload);
//
//            event(new NewWebHookCallReceived($webHookCall));
//
////            $transaction = Monnify::getTransactionStatus($validatedPayload['transactionReference']);
//
//        }
    }
}
