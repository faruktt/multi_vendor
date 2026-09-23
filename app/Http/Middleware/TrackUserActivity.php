<?php

namespace App\Http\Middleware;

use App\Models\UserActivity;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    /**
     * Handle an incoming request and track page visits.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track successful GET requests for pages (HTML)
        if (!$request->isMethod('GET')) {
            return $response;
        }

        // Skip non-200 responses (errors, redirects, etc.)
        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        // Exclude admin panel, api, polling, and ajax background requests
        if (
            $request->is('admin*') ||
            $request->is('api*') ||
            $request->is('up') ||
            $request->is('*poll*') ||
            $request->is('*chat*') ||
            $request->ajax() ||
            $request->expectsJson()
        ) {
            return $response;
        }

        // Ensure response is HTML (avoid tracking binary downloads or json)
        $contentType = (string) $response->headers->get('Content-Type', '');
        if ($contentType !== '' && !str_contains(strtolower($contentType), 'text/html')) {
            return $response;
        }

        try {
            UserActivity::recordVisit($request);
        } catch (\Throwable $e) {
            Log::warning('UserActivity tracking error: ' . $e->getMessage());
        }

        return $response;
    }
}
