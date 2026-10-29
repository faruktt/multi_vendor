<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IP Address Blocked Successfully</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4 antialiased">
    <div class="max-w-md w-full bg-slate-800/90 border border-slate-700/80 backdrop-blur-xl rounded-3xl p-8 shadow-2xl text-center space-y-6">
        <div class="w-16 h-16 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto text-2xl border border-emerald-500/30 shadow-lg shadow-emerald-500/10">
            <i class="fas fa-shield-check"></i>
        </div>

        <div>
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest bg-emerald-500/15 text-emerald-400 border border-emerald-500/20 mb-2">
                Action Completed
            </span>
            <h1 class="text-2xl font-black text-white">IP Address Blocked!</h1>
            <p class="text-sm text-slate-400 mt-1">This IP has been permanently banned from accessing your system.</p>
        </div>

        <div class="bg-slate-900/80 border border-slate-700 rounded-2xl p-5 text-left space-y-2.5 text-xs">
            <div class="flex justify-between items-center">
                <span class="text-slate-400">Blocked IP:</span>
                <span class="font-mono font-bold text-red-400 bg-red-950/60 px-2 py-0.5 rounded border border-red-800/40 text-sm">{{ $ip }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-400">Location:</span>
                <span class="text-slate-300 font-medium">{{ $location ?: 'Unknown' }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-400">Status:</span>
                <span class="text-emerald-400 font-bold flex items-center gap-1"><i class="fas fa-check-circle text-[10px]"></i> Active Block</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-400">Blocked At:</span>
                <span class="text-slate-300">{{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>

        <div class="pt-2">
            <a href="{{ route('admin.ip-blocks.index') }}"
               class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition-all shadow-lg shadow-blue-600/30 text-sm">
                <i class="fas fa-arrow-right text-xs"></i>
                <span>Open IP Block in Admin Panel</span>
            </a>
        </div>
    </div>
</body>
</html>

