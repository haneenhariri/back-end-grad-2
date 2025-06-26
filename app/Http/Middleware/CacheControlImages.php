<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CacheControlImages
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // فقط إذا كان الطلب لملف صورة داخل storage
        if ($request->is('storage/course-cover/*')) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        }

        return $response;
    }
}
