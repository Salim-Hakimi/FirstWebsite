<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class BlockSuspiciousRequests
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_if($this->isTooLarge($request), 413);
        abort_if($this->containsSuspiciousInput($request), 400);

        return $next($request);
    }

    private function isTooLarge(Request $request): bool
    {
        $limit = (int) config('security.requests.max_content_length', 25 * 1024 * 1024);
        $length = (int) $request->headers->get('CONTENT_LENGTH', 0);

        return $length > 0 && $length > $limit;
    }

    private function containsSuspiciousInput(Request $request): bool
    {
        $values = [
            $request->getRequestUri(),
            $request->path(),
            $request->query->all(),
            array_keys($request->request->all()),
            $request->files->keys(),
        ];

        return collect(Arr::flatten($values))
            ->filter(fn ($value): bool => is_scalar($value))
            ->map(fn ($value): string => urldecode((string) $value))
            ->contains(fn (string $value): bool => $this->matchesBlockedPattern($value));
    }

    private function matchesBlockedPattern(string $value): bool
    {
        foreach (config('security.requests.blocked_patterns', []) as $pattern) {
            if (preg_match($pattern, $value) === 1) {
                return true;
            }
        }

        return false;
    }
}
