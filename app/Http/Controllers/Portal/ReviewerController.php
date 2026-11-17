<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AbstractSubmission;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewerController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $base = AbstractSubmission::query();

        return view('reviewer.dashboard', [
            'stats' => [
                'pending' => (clone $base)->where('status_abstrak', 'Diajukan')->count(),
                'reviewed_by_me' => (clone $base)->whereHas('reviews', fn ($q) => $q->where('id_reviewer', $user->id_user))->distinct('id_abstract')->count('id_abstract'),
                'accepted' => (clone $base)->where('status_abstrak', 'Diterima')->count(),
                'rejected' => (clone $base)->where('status_abstrak', 'Ditolak')->count(),
            ],
            'total' => (clone $base)->count(),
            'byScope' => (clone $base)->selectRaw('scope, COUNT(*) as total')->groupBy('scope')->pluck('total', 'scope'),
            'latestSubmissions' => (clone $base)->orderByRaw("CASE WHEN status_abstrak = 'Diajukan' THEN 0 ELSE 1 END")
                ->orderByDesc('tanggal_submit')->limit(6)
                ->get(['id_abstract', 'judul_abstrak', 'scope', 'metode_presentasi', 'status_abstrak', 'tanggal_submit']),
        ]);
    }

    public function queue(): View
    {
        return view('reviewer.queue', ['scopes' => config('abstract-scopes', []), 'blindReview' => config('asic.blind_review_active', false)]);
    }

    public function show(int $id): View
    {
        $file = AbstractSubmission::whereKey($id)->value('file_abstrak');

        return view('reviewer.detail', ['id' => $id, 'blindReview' => config('asic.blind_review_active', false),
            'abstractIsPdf' => $file && strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'pdf']);
    }

    public function profile(): View
    {
        return view('reviewer.profile');
    }
}
