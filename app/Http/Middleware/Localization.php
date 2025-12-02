<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Foundation\Application;

class Localization
{
    public const ERROR_MESSAGE = 'هذه اللغة غير مدعومة';
    public Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $lang = $request->header('Accept-Language');
        if (empty($lang) || (is_string($lang) && strlen($lang) !== 2)) {
            $lang = config('app.fallback_locale');
        }

        if (!$this->isSupportedLanguage($lang)) {
            return response()->json(['message' => self::ERROR_MESSAGE], 403);
        }

        return $next($request);
    }

    private function isSupportedLanguage(string $lang): bool
    {
        $supportedLanguages = config('app.supported_languages');

        return isset($supportedLanguages[$lang]);
    }
}
