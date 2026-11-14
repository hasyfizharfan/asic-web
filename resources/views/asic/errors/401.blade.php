@include('asic.errors.error-layout', [
    'code' => 401,
    'title' => app()->getLocale() === 'id' ? 'Sesi Tidak Sah / Autentikasi Diperlukan' : 'Unauthorized Access',
    'message' => app()->getLocale() === 'id' ? 'Anda belum masuk atau sesi Anda telah berakhir. Silakan masuk terlebih dahulu untuk mengakses halaman ini.' : 'You must log in to view this conference portal page.',
    'icon' => 'lock'
])

