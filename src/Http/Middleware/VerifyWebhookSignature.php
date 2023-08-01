<?php

namespace HenryEjemuta\LaravelMonnify\Http\Middleware;

use HenryEjemuta\LaravelMonnify\Classes\WebhookSignature;
use HenryEjemuta\LaravelMonnify\Exceptions\SignatureVerificationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VerifyWebhookSignature
{

    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return \Illuminate\Http\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function handle($request, Closure $next)
    {
        try {
            WebhookSignature::verifyHeader(
                $request->getContent(),
                config('monnify.secret_key'),
                $request->header('monnify-signature')
            );
        } catch (SignatureVerificationException $exception) {
            throw new AccessDeniedHttpException($exception->getMessage(), $exception);
        }

        return $next($request);
    }
}