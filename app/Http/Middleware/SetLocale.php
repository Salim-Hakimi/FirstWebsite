<?php

namespace App\Http\Middleware;

use App\Support\Locale;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->routeIs('admin.*')
            || $request->routeIs('dorm.rooms.*')
            || $request->routeIs('dorm.students.*')
            || $request->routeIs('representative.*')
            || $request->routeIs('purchaser.*')
            || $request->routeIs('settings.*')
            ? 'en'
            : $request->session()->get('locale', Locale::DEFAULT);

        if (! in_array($locale, Locale::SUPPORTED, true)) {
            $locale = Locale::DEFAULT;
        }

        app()->setLocale($locale);

        $response = $next($request);

        if ($this->isHtmlResponse($response)) {
            $response->setContent(Locale::translateHtml((string) $response->getContent(), $locale));
        }

        return $response;
    }

    private function isHtmlResponse(Response $response): bool
    {
        return str_contains((string) $response->headers->get('Content-Type'), 'text/html');
    }
}
