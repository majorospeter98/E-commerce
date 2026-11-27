<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /** @var list<string> */
    public const SUPPORTED_LOCALES = ['hu', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('X-Locale');

        app()->setLocale(
            in_array($locale, self::SUPPORTED_LOCALES, true) ? $locale : self::SUPPORTED_LOCALES[0],
        );

        $response = $next($request);

        if ($request->cookies->has('locale')) {
            $response->headers->clearCookie('locale');
        }

        return $response;
    }
}
