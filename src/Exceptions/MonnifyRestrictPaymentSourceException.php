<?php
/**
 * Created By: Henry Ejemuta
 * Project: laravel-monnify
 * Class Name: MonnifyRestrictPaymentSourceException.php
 * Date Created: 7/14/20
 * Time Created: 5:14 PM
 */

namespace HenryEjemuta\LaravelMonnify\Exceptions;


use Throwable;

class MonnifyRestrictPaymentSourceException extends \Exception
{
    /**
     * MonnifyFailedRequestException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "Allowed payment source is required since restrictPayment source is true", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
