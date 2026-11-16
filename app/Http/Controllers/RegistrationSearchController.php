<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRegistrationRequest;
use App\Http\Resources\RegistrationSearchResource;
use App\Models\Registration;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class RegistrationSearchController extends Controller
{
    #[OA\Get(
        path: '/api/registrations/search',
        summary: 'Cari pendaftaran untuk registrasi ulang',
        description: 'Mencari nomor pendaftaran, nama, atau email peserta. Hanya staf dengan izin manage_registrations.',
        tags: ['Pendaftaran'],
        security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'q', in: 'query', required: true, description: 'Minimal dua karakter; ID registrasi numerik dapat satu digit.', schema: new OA\Schema(type: 'string', minLength: 1, maxLength: 100))],
        responses: [new OA\Response(response: 200, description: 'Hasil pencarian'), new OA\Response(response: 403, description: 'Tidak memiliki izin'), new OA\Response(response: 422, description: 'Kata pencarian tidak valid')]
    )]
    public function __invoke(SearchRegistrationRequest $request): AnonymousResourceCollection
    {
        $term = trim($request->validated('q'));
        $registrations = Registration::query()
            ->with(['participant.userAccount', 'event', 'invoice', 'abstractSubmission'])
            ->where(function ($query) use ($term) {
                if (ctype_digit($term)) {
                    $query->where('id_registration', (int) $term);
                }
                $query->orWhereHas('participant', function ($participant) use ($term) {
                    $participant->where('first_name', 'like', '%'.$term.'%')
                        ->orWhere('last_name', 'like', '%'.$term.'%')
                        ->orWhere('institution', 'like', '%'.$term.'%')
                        ->orWhereHas('userAccount', fn ($account) => $account->where('email', 'like', '%'.$term.'%'));
                });
            })
            ->orderByDesc('id_registration')
            ->paginate(15);

        return RegistrationSearchResource::collection($registrations);
    }
}
