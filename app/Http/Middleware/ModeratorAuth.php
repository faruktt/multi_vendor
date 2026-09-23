<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ModeratorAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth('moderator')->check()) {
            return redirect()->route('moderator.login')->with('error', 'দয়া করে লগইন করুন।');
        }

        $moderator = auth('moderator')->user();

        if (!$moderator->isActive()) {
            auth('moderator')->logout();
            return redirect()->route('moderator.login')->with('error', 'আপনার অ্যাকাউন্টটি নিষ্ক্রিয় (Inactive) করা হয়েছে। অ্যাডমিনের সাথে যোগাযোগ করুন।');
        }

        // Share moderator instance with all moderator views
        view()->share('moderator', $moderator);

        return $next($request);
    }
}
