<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $code }} - {{ $title }} | ASIC 2027</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Carlito:ital,wght@0,400;0,700;1,400;1,700&family=JetBrains+Mono:wght@400;500;600&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/asic/logo.svg') }}">
    <style>::-webkit-scrollbar{display:none !important;width:0px !important;}*{scrollbar-width:none !important;-ms-overflow-style:none !important;}</style>
    @vite(['resources/css/asic.css', 'resources/js/asic.js'])
</head>
<body class="bg-[#F1F5EC] text-[#17261D] min-h-screen flex flex-col justify-between font-sans antialiased">
    <!-- Top Nav Minimal -->
    <header class="bg-[#0A2B19] text-white py-4 px-6 sm:px-12 flex items-center justify-between border-b border-white/10">
        <a href="{{ route('fe.home') }}" class="flex items-center gap-3">
            <img src="{{ asset('images/asic/logo.svg') }}" alt="ASIC Logo" class="w-8 h-8 rounded-md bg-white p-0.5 shadow-sm">
            <div>
                <span class="block text-base font-bold tracking-tight text-white font-display">ASIC 2027</span>
                <span class="block text-[10px] text-[#a7f4b6] font-semibold uppercase tracking-wider">Universitas Andalas</span>
            </div>
        </a>
        <a href="{{ route('fe.home') }}" class="text-xs font-semibold text-white/80 hover:text-white transition">
            {{ app()->getLocale() === 'id' ? '← Beranda' : '← Home' }}
        </a>
    </header>

    <!-- Error Content Center -->
    <main class="flex-1 flex items-center justify-center p-6">
        <div class="max-w-lg w-full bg-white rounded-3xl p-8 sm:p-10 shadow-xl border border-black/5 text-center space-y-6">
            <div class="w-20 h-20 mx-auto rounded-2xl bg-[#005226]/10 text-[#005226] flex items-center justify-center">
                <span class="material-symbols-outlined text-4xl">{{ $icon ?? 'error' }}</span>
            </div>
            
            <div class="space-y-2">
                <div class="text-5xl sm:text-6xl font-bold font-display text-[#005226] tracking-tight">{{ $code }}</div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 font-display">{{ $title }}</h1>
                <p class="text-sm text-gray-600 leading-relaxed max-w-sm mx-auto">
                    {{ $message }}
                </p>
            </div>

            <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('fe.home') }}" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-[#005226] hover:bg-[#1f6b3a] text-white font-bold text-sm shadow transition active:scale-95">
                    {{ app()->getLocale() === 'id' ? 'Kembali ke Beranda' : 'Back to Home' }}
                </a>
                <button onclick="history.back()" class="w-full sm:w-auto px-6 py-2.5 rounded-xl border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold text-sm transition">
                    {{ app()->getLocale() === 'id' ? 'Kembali Sebelumnya' : 'Go Back' }}
                </button>
            </div>
        </div>
    </main>

    <!-- Footer Minimal -->
    <footer class="py-6 text-center text-xs text-gray-500 border-t border-gray-200">
        &copy; {{ date('Y') }} ASIC 2027 • Faculty of Agriculture, Universitas Andalas
    </footer>
</body>
</html>

