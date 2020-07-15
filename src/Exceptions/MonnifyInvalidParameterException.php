<?php
/**
 * Created By: Henry Ejemuta
 * Project: laravel-monnify
 * Class Name: MonnifyInvalidParameterException.php
 * Date Created: 7/14/20
 * Time Created: 5:28 PM
 */

namespace HenryEjemuta\LaravelMonnify\Exceptions;


use Throwable;

class MonnifyInvalidParameterException extends \Exception
{
    /**
     * MonnifyFailedRequestException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message, int $code = -1, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
