@include('asic.errors.error-layout', [
    'code' => 429,
    'title' => app()->getLocale() === 'id' ? 'Terlalu Banyak Permintaan' : 'Too Many Requests',
    'message' => app()->getLocale() === 'id' ? 'Anda mengirim terlalu banyak permintaan dalam waktu singkat. Harap tunggu sejenak sebelum mencoba lagi.' : 'Rate limit exceeded. Please wait a moment before trying again.',
    'icon' => 'speed'
])

