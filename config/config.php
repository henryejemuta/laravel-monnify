<?php
/**
 * Created By: Henry Ejemuta
 * Email: henry.ejemuta@gmail.com
 * GitHub: https://github.com/henryejemuta
 * Project: laravel-monnify
 * Class Name: monnify.php
 * Date Created: 7/13/20
 * Time Created: 3:43 PM
 */

return [
    /*
     * ---------------------------------------------------------------
     * Base Url
     * ---------------------------------------------------------------
     *
     * The monnify base url upon which others is based, if not set it's going to use the sandbox version
     */
    'base_url' => env('MONNIFY_BASE_URL', 'https://sandbox.monnify.com'),


    /*
     * ---------------------------------------------------------------
     * API KEY
     * ---------------------------------------------------------------
     *
     * The API key is required for the basic authentication with  the OAuth2 is dependent on
     * This can be gotten from your monnify dashboard, if not set the sandbox version would be used
     */
    'api_key' => env('MONNIFY_API_KEY', 'MK_TEST_SAF7HR5F3F'),


    /*
     * ---------------------------------------------------------------
     * Client Secret KEY
     * ---------------------------------------------------------------
     *
     * The Client secret key is also required for the basic authentication with  the OAuth2 is dependent on
     * This can be gotten from your monnify dashboard, if not set the sandbox version would be used
     */
    'secret_key' => env('MONNIFY_SECRET_KEY', '4SY6TNL8CK3VPRSBTHTRG2N8XXEGC6NL'),

    /*
     * ---------------------------------------------------------------
     * Contract Code
     * ---------------------------------------------------------------
     *
     * This can be gotten from your monnify dashboard, if not set the sandbox version would be used
     */
    'contract_code' => env('MONNIFY_CONTRACT_CODE', '4934121686'),

    /*
     * ---------------------------------------------------------------
     * Default Split Percentage
     * ---------------------------------------------------------------
     *
     * The default percentage to be split into the sub account on any transaction. (Only applies if a specific amount is not passed during transaction initialization)
     */
    'default_split_percentage' => env('MONNIFY_DEFAULT_SPLIT_PERCENTAGE', 20),

    /*
     * ---------------------------------------------------------------
     * Default Currency Code
     * ---------------------------------------------------------------
     *
     * The default currency to be used for any request requiring currency code usage
     */
    'default_currency_code' => env('MONNIFY_DEFAULT_CURRENCY_CODE', 'NGN'),


    /*
     * ---------------------------------------------------------------
     * Default Payment Redirect URL
     * ---------------------------------------------------------------
     *
     * The default currency to be used for any request requiring currency code usage
     */
    'redirect_url' => env('MONNIFY_DEFAULT_PAYMENT_REDIRECT_URL', env('APP_URL')),


    /*
     * ---------------------------------------------------------------
     * Wallet ID
     * ---------------------------------------------------------------
     *
     * ID of business wallet from which transfer will initiated.
     */
    'wallet_id' => env('MONNIFY_WALLET_ID', '2A47114E88904626955A6BD333A6B164'),

];
