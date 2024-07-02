<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
      if ($request->isMethod('OPTIONS')) {
          $response = response('', 200);
          $response->header('Access-Control-Allow-Origin', '*');
          $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
          $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Authorization');
          return $response;
      }

        $response = $next($request);
        $response->headers->set('Access-control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Application');
        return $response;
    }
}
