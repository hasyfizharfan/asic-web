@include('asic.errors.error-layout', [
    'code' => 404,
    'title' => app()->getLocale() === 'id' ? 'Halaman Tidak Ditemukan' : 'Page Not Found',
    'message' => app()->getLocale() === 'id' ? 'Halaman yang Anda tuju tidak ditemukan atau telah dipindahkan ke tautan lain.' : 'The requested URL was not found on the ASIC 2027 server.',
    'icon' => 'search_off'
])

