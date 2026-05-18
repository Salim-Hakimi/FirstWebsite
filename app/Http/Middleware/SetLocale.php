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
        $locale = Locale::DEFAULT;

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
