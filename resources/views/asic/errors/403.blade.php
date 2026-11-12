@include('asic.errors.error-layout', [
    'code' => 403,
    'title' => app()->getLocale() === 'id' ? 'Akses Ditolak / Terlarang' : 'Access Forbidden',
    'message' => app()->getLocale() === 'id' ? 'Anda tidak memiliki hak akses untuk membuka halaman atau portal ini.' : 'You do not have permission to access this conference resource.',
    'icon' => 'gpp_bad'
])

