<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;

class Localization
{

    public $app;

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

        $lang = app('request')->header('Accept-Language');

        if ($lang == null) {

            return abort(403, 'هذه اللغة غير مدعومة');
        } else {

            // check the languages defined is supported
            if (!array_key_exists($lang, $this->app->config->get('app.supported_languages'))) {

                // respond with error
                return abort(403, 'هذه اللغة غير مدعومة');
            } else {

                \App::setLocale($lang);

                return $next($request);
            }
        }
    }
}
