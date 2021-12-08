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
use HenryEjemuta\LaravelMonnify\Facades\Monnify;
use HenryEjemuta\LaravelMonnify\Models\WebHookCall;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

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
        $webHookCall = new WebHookCall($request->all());

        $calculatedHash = Monnify::Transactions()->calculateHash($validatedPayload['paymentReference'], $validatedPayload['amountPaid'], $validatedPayload['paidOn'], $validatedPayload['transactionReference']);

        event(new NewWebHookCallReceived($webHookCall, $calculatedHash == $validatedPayload['transactionHash']));

    }


    /**
     * Receive a Transaction completion webhook call from monnify and validate the transaction hash, then dispatch an event if the hash is valid else just ignore
     * @param Request $request
     */
    public function txnCompletion(Request $request): void
    {
        $stringifiedData = json_encode($request->all());
        Log::info("headers: " . print_r($request->header(), true));
        Log::info("txnCompletion body: " . $stringifiedData);
        $transactionHash = $request->header('monnify-signature');
        Log::info("transactionHash: $transactionHash");

        $payload = $request->input('eventData');
        $webHookCall = new WebHookCall($payload);
        $webHookCall->transactionHash = $request->header('monnify-signature');
        $webHookCall->stringifiedData = $stringifiedData;

        $calculatedHash1 = Monnify::computeRequestValidationHash($stringifiedData);
        $calculatedHash = Monnify::computeRequestValidationHash($webHookCall->stringifiedData);
        Log::info("$transactionHash\n\r{$webHookCall->stringifiedData}\n\rcalculatedHash1: $calculatedHash1\n\rcalculatedHash: $calculatedHash");
        Log::info(print_r($webHookCall, true));
//        $request->validate([
//            'eventData.transactionReference' => 'required',
//            'eventData.paymentReference' => 'required',
//            'eventData.amountPaid' => 'required',
//            'eventData.totalPayable' => 'required',
//            'eventData.paidOn' => 'required',
//            'eventData.paymentStatus' => 'required',
//            'eventData.paymentDescription' => 'required',
//            'eventData.currency' => 'required',
//            'eventData.paymentMethod' => 'required',
//        ]);
//        $payload = $request->input('eventData');
//
//        $webHookCall = new WebHookCall($payload);
//        $webHookCall->transactionHash = $request->header('monnify-signature');
//        $webHookCall->stringifiedData = $stringifiedData;
//
//        $calculatedHash = Monnify::computeRequestValidationHash($webHookCall->stringifiedData);
//        Log::info("$transactionHash\n\r{$webHookCall->stringifiedData}\n\r$calculatedHash");
//
//        event(new NewWebHookCallReceived($webHookCall, $calculatedHash == $transactionHash));

    }

    /**
     * Receive a Refund completion webhook call from monnify and validate the transaction hash, then dispatch an event if the hash is valid else just ignore
     * @param Request $request
     */
    public function refundCompletion(Request $request): void
    {
        $stringifiedData = json_encode($request->all());
        Log::info("headers: " . print_r($request->header(), true));
        Log::info("refundCompletion: $stringifiedData");
//        $validatedPayload = $request->validate([
//            'transactionReference' => 'required',
//            'paymentReference' => 'required',
//            'amountPaid' => 'required',
//            'totalPayable' => 'required',
//            'paidOn' => 'required',
//            'paymentStatus' => 'required',
//            'paymentDescription' => 'required',
//            'transactionHash' => 'required',
//            'currency' => 'required',
//            'paymentMethod' => 'required',
//        ]);
//
////        Log::info(print_r($validatedPayload, true));
//        $webHookCall = new WebHookCall($request->all());
//
//        $calculatedHash = Monnify::Transactions()->calculateHash($validatedPayload['paymentReference'], $validatedPayload['amountPaid'], $validatedPayload['paidOn'], $validatedPayload['transactionReference']);
//
//        event(new NewWebHookCallReceived($webHookCall, $calculatedHash == $validatedPayload['transactionHash']));

    }

    /**
     * Receive a Refund completion webhook call from monnify and validate the transaction hash, then dispatch an event if the hash is valid else just ignore
     * @param Request $request
     */
    public function disbursement(Request $request): void
    {
        $stringifiedData = json_encode($request->all());
        Log::info("headers: " . print_r($request->header(), true));
        Log::info("disbursement: $stringifiedData");
//        $validatedPayload = $request->validate([
//            'transactionReference' => 'required',
//            'paymentReference' => 'required',
//            'amountPaid' => 'required',
//            'totalPayable' => 'required',
//            'paidOn' => 'required',
//            'paymentStatus' => 'required',
//            'paymentDescription' => 'required',
//            'transactionHash' => 'required',
//            'currency' => 'required',
//            'paymentMethod' => 'required',
//        ]);
//
////        Log::info(print_r($validatedPayload, true));
//        $webHookCall = new WebHookCall($request->all());
//
//        $calculatedHash = Monnify::Transactions()->calculateHash($validatedPayload['paymentReference'], $validatedPayload['amountPaid'], $validatedPayload['paidOn'], $validatedPayload['transactionReference']);
//
//        event(new NewWebHookCallReceived($webHookCall, $calculatedHash == $validatedPayload['transactionHash']));

    }

    /**
     * Receive a Refund completion webhook call from monnify and validate the transaction hash, then dispatch an event if the hash is valid else just ignore
     * @param Request $request
     */
    public function settlement(Request $request): void
    {

        $stringifiedData = json_encode($request->all());
        Log::info("headers: " . print_r($request->header(), true));
        Log::info("settlement: $stringifiedData");
//        $validatedPayload = $request->validate([
//            'transactionReference' => 'required',
//            'paymentReference' => 'required',
//            'amountPaid' => 'required',
//            'totalPayable' => 'required',
//            'paidOn' => 'required',
//            'paymentStatus' => 'required',
//            'paymentDescription' => 'required',
//            'transactionHash' => 'required',
//            'currency' => 'required',
//            'paymentMethod' => 'required',
//        ]);
//
////        Log::info(print_r($validatedPayload, true));
//        $webHookCall = new WebHookCall($request->all());
//
//        $calculatedHash = Monnify::Transactions()->calculateHash($validatedPayload['paymentReference'], $validatedPayload['amountPaid'], $validatedPayload['paidOn'], $validatedPayload['transactionReference']);
//
//        event(new NewWebHookCallReceived($webHookCall, $calculatedHash == $validatedPayload['transactionHash']));

    }
}
