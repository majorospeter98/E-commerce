<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * The locales the shop can be displayed in. The first one is the default.
     *
     * @var list<string>
     */
    public const SUPPORTED_LOCALES = ['hu', 'en'];

    /**
     * Apply the locale chosen in the `locale` cookie, falling back to Hungarian.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->cookie('locale');

        app()->setLocale(
            in_array($locale, self::SUPPORTED_LOCALES, true) ? $locale : self::SUPPORTED_LOCALES[0],
        );

        return $next($request);
    }
}
