<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Remembers how a visitor first arrived (campaign tags, referring site and
 * landing page) so an enquiry can show its source. Only whitelisted tags
 * and the referrer's host are kept, never full URLs or other query values.
 */
class CaptureAttribution
{
    public const SESSION_KEY = 'lead_attribution';

    private const UTM_KEYS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldCapture($request)) {
            $attribution = ['landing_page' => '/'.ltrim($request->path(), '/')];

            foreach (self::UTM_KEYS as $key) {
                $value = $request->query($key);

                if (is_string($value) && trim($value) !== '') {
                    $attribution[$key] = Str::limit(strip_tags(trim($value)), 100, '');
                }
            }

            $referrerHost = parse_url((string) $request->headers->get('referer'), PHP_URL_HOST);

            if (is_string($referrerHost) && $referrerHost !== $request->getHost()) {
                $attribution['referrer'] = Str::limit($referrerHost, 100, '');
            }

            $request->session()->put(self::SESSION_KEY, $attribution);
        }

        return $next($request);
    }

    private function shouldCapture(Request $request): bool
    {
        return $request->isMethod('GET')
            && $request->hasSession()
            && ! $request->session()->has(self::SESSION_KEY)
            && ! $request->is('admin', 'admin/*', 'livewire*', 'storage/*', 'build/*');
    }
}
