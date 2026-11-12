@include('asic.errors.error-layout', [
    'code' => 419,
    'title' => app()->getLocale() === 'id' ? 'Sesi Halaman Kadaluarsa' : 'Page Expired',
    'message' => app()->getLocale() === 'id' ? 'Token sesi keamanan Anda telah kadaluarsa karena tidak ada aktivitas. Silakan muat ulang halaman.' : 'Your session token has expired. Please refresh the page and try again.',
    'icon' => 'hourglass_disabled'
])

