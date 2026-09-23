<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="h-full bg-slate-100 flex items-center justify-center p-6">
    <div class="text-center max-w-sm">
        <div class="w-20 h-20 rounded-3xl bg-red-100 flex items-center justify-center mx-auto mb-5">
            <i class="fas fa-lock text-red-500 text-3xl"></i>
        </div>
        <h1 class="text-2xl font-bold text-slate-800 mb-2">Access Denied</h1>
        <p class="text-slate-500 text-sm mb-6 leading-relaxed">
            You don't have permission to access this page.<br>
            Contact your branch owner to request access.
        </p>
        <div class="flex flex-col gap-2.5">
            <button onclick="history.back()"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl text-sm font-bold transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Go Back
            </button>
            @auth
            @php
                $user = auth()->user();
                $backUrl = $user->hasRole('super-admin')
                    ? route('admin.index')
                    : route('branch.dashboard', $user->vendor_id);
            @endphp
            <a href="{{ $backUrl }}"
               class="w-full border border-slate-200 text-slate-600 hover:bg-slate-50 py-3 rounded-xl text-sm font-semibold transition-colors block">
                <i class="fas fa-house mr-2"></i> Go to Dashboard
            </a>
            @endauth
        </div>
        <p class="text-slate-400 text-xs mt-5">Error 403 — Forbidden</p>
    </div>
</body>
</html>
