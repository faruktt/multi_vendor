<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Access Denied | IP Blocked</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4 antialiased">
    <div class="max-w-md w-full bg-slate-800/80 border border-slate-700/80 backdrop-blur-xl rounded-3xl p-8 shadow-2xl text-center space-y-5">
        <div class="w-16 h-16 rounded-2xl bg-red-500/20 text-red-500 flex items-center justify-center mx-auto text-2xl border border-red-500/30 shadow-lg shadow-red-500/10">
            <i class="fas fa-ban"></i>
        </div>

        <div>
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest bg-red-500/15 text-red-400 border border-red-500/20 mb-2">
                HTTP 403 Forbidden
            </span>
            <h1 class="text-2xl font-black text-white">Access Denied</h1>
            <p class="text-sm text-slate-400 mt-1">Your IP address has been blocked by the system security administrator.</p>
        </div>

        <div class="bg-slate-900/80 border border-slate-700 rounded-2xl p-4 text-left space-y-2 text-xs">
            <div class="flex justify-between items-center">
                <span class="text-slate-400">Blocked IP:</span>
                <span class="font-mono font-bold text-red-400 bg-red-950/60 px-2 py-0.5 rounded border border-red-800/40">{{ $ip ?? request()->ip() }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-400">Reason:</span>
                <span class="text-slate-300 font-medium">Suspicious Activity / Security Policy</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-400">Time:</span>
                <span class="text-slate-300">{{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>

        <p class="text-xs text-slate-500 leading-relaxed">
            If you believe your IP was blocked by mistake, please contact the website administrator to request an unblock.
        </p>
    </div>
</body>
</html>

