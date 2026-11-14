@include('asic.errors.error-layout', [
    'code' => 500,
    'title' => app()->getLocale() === 'id' ? 'Kesalahan Internal Server' : 'Internal Server Error',
    'message' => app()->getLocale() === 'id' ? 'Terjadi kesalahan sistem saat memproses permintaan Anda. Tim teknis sedang memperbaikinya.' : 'Something went wrong on our servers. Our technical team has been notified.',
    'icon' => 'dns'
])

