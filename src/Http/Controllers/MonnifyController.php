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
use HenryEjemuta\LaravelMonnify\Http\Middleware\VerifyWebhookSignature;
use HenryEjemuta\LaravelMonnify\Models\WebHookCall;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class MonnifyController extends Controller
{


    /**
     * Create a new MonnifyController instance.
     *
     * @return void
     */
    public function __construct()
    {
        if (!empty(config('monnify.secret_key'))) {
            $this->middleware(VerifyWebhookSignature::class);
        }
    }


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
        $webHookCall->content = $request->getContent();

        event(new NewWebHookCallReceived($webHookCall));

    }


    /**
     * Receive a Transaction completion webhook call from monnify and validate the transaction hash, then dispatch an event if the hash is valid else just ignore
     * @param Request $request
     */
    public function txnCompletion(Request $request): void
    {
        $request->validate([
            'eventData.transactionReference' => 'required',
            'eventData.paymentReference' => 'required',
            'eventData.amountPaid' => 'required',
            'eventData.totalPayable' => 'required',
            'eventData.paidOn' => 'required',
            'eventData.paymentStatus' => 'required',
            'eventData.paymentDescription' => 'required',
            'eventData.currency' => 'required',
            'eventData.paymentMethod' => 'required',
        ]);

        
        $webHookCall = $this->initRequest($request);
        event(new NewWebHookCallReceived($webHookCall, NewWebHookCallReceived::WEB_HOOK_EVENT_TXN_COMPLETION_CALL));
    }

    /**
     * Receive a Refund completion webhook call from monnify and validate the transaction hash, then dispatch an event if the hash is valid else just ignore
     * @param Request $request
     */
    public function refundCompletion(Request $request): void
    {
        $request->validate([
            'eventData.transactionReference' => 'required',
            'eventData.paymentReference' => 'required',
            'eventData.amountPaid' => 'required',
            'eventData.totalPayable' => 'required',
            'eventData.paidOn' => 'required',
            'eventData.paymentStatus' => 'required',
            'eventData.paymentDescription' => 'required',
            'eventData.currency' => 'required',
            'eventData.paymentMethod' => 'required',
        ]);

        
        $webHookCall = $this->initRequest($request);
        event(new NewWebHookCallReceived($webHookCall, NewWebHookCallReceived::WEB_HOOK_EVENT_REFUND_COMPLETION_CALL));

    }

    /**
     * Receive a Refund completion webhook call from monnify and validate the transaction hash, then dispatch an event if the hash is valid else just ignore
     * @param Request $request
     */
    public function disbursement(Request $request): void
    {

        $request->validate([
            'eventData.transactionReference' => 'required',
            'eventData.paymentReference' => 'required',
            'eventData.amountPaid' => 'required',
            'eventData.totalPayable' => 'required',
            'eventData.paidOn' => 'required',
            'eventData.paymentStatus' => 'required',
            'eventData.paymentDescription' => 'required',
            'eventData.currency' => 'required',
            'eventData.paymentMethod' => 'required',
        ]);

        
        $webHookCall = $this->initRequest($request);
        event(new NewWebHookCallReceived($webHookCall, NewWebHookCallReceived::WEB_HOOK_EVENT_DISBURSEMENT_CALL));

    }

    /**
     * Receive a Refund completion webhook call from monnify and validate the transaction hash, then dispatch an event if the hash is valid else just ignore
     * @param Request $request
     */
    public function settlement(Request $request): void
    {

        $request->validate([
            'eventData.transactionReference' => 'required',
            'eventData.destinationAccountNumber' => 'required',
            'eventData.amount' => 'required',
            'eventData.reference' => 'required',
            'eventData.completedOn' => 'required',
            'eventData.status' => 'required',
            'eventData.narration' => 'required',
            'eventData.currency' => 'required',
            'eventData.destinationBankName' => 'required',
        ]);
        
        $webHookCall = $this->initRequest($request);
        event(new NewWebHookCallReceived($webHookCall, NewWebHookCallReceived::WEB_HOOK_EVENT_SETTLEMENT_CALL));
    }

    private function initRequest($request)
    {
        $monnifySignature = $request->header('monnify-signature');

        $stringifiedData = $request->getContent();
        $payload = $request->input('eventData');
      
        $webHookCall = new WebHookCall($payload);
        $webHookCall->transactionHash = $monnifySignature;
        $webHookCall->stringifiedData = $stringifiedData;
        return $webHookCall;
    }
}
