<?php

namespace App\Http\Middleware;

use App\Support\Navigation;
use App\Support\SiteSettings;
use App\Support\StructuredData;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'site' => fn (): array => SiteSettings::publicPayload(),
            'navigation' => fn (): array => Navigation::menus(),
            'legalLinks' => fn (): array => Navigation::legalLinks(),
            'canonical' => fn (): string => $this->canonicalUrl($request),
        ];
    }

    /**
     * The one address search engines should index for this page: the configured
     * site address plus the path, without tracking or filter parameters. Only the
     * page number is kept, so paginated lists stay distinct.
     */
    private function canonicalUrl(Request $request): string
    {
        $url = StructuredData::absolute($request->path() === '/' ? '/' : $request->path());
        $page = (int) $request->query('page', 1);

        return $page > 1 ? "{$url}?page={$page}" : $url;
    }
}
