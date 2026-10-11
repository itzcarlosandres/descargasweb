<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RemoveIndexPhp
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $uri = $request->server('REQUEST_URI');

        if (is_string($uri) && str_starts_with($uri, '/index.php')) {
            $cleanUri = preg_replace('#^/index\.php(?:/|(?=\?)|$)#', '/', $uri);
            if ($cleanUri === '' || ! str_starts_with($cleanUri, '/')) {
                $cleanUri = '/'.$cleanUri;
            }

            return redirect($cleanUri, 301);
        }

        return $next($request);
    }
}
