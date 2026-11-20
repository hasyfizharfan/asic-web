<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListMyNotificationsRequest;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

/**
 * KF-30: notifikasi dalam situs milik pengguna yang sedang login. Setiap query dibatasi
 * pada $request->user()->notifications(), sehingga pengguna lain tidak dapat dibaca/ditandai.
 */
class MyNotificationController extends Controller
{
    #[OA\Get(
        path: '/api/me/notifications',
        summary: 'Daftar notifikasi saya',
        tags: ['Notifikasi'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'unread', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 50)),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1)),
        ],
        responses: [new OA\Response(response: 200, description: 'Daftar notifikasi (belum dibaca di atas)'), new OA\Response(response: 401, description: 'Belum login')]
    )]
    public function index(ListMyNotificationsRequest $request): JsonResponse
    {
        $page = $request->user()->notifications()
            ->when($request->boolean('unread'), fn ($q) => $q->whereNull('read_at'))
            ->reorder()
            ->orderByRaw('read_at IS NULL DESC')
            ->orderByDesc('created_at')
            ->paginate((int) $request->validated('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => NotificationResource::collection($page->getCollection())->resolve(),
            'pagination' => ['total' => $page->total(), 'per_page' => $page->perPage(), 'current_page' => $page->currentPage(), 'last_page' => $page->lastPage()],
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ], Response::HTTP_OK);
    }

    #[OA\Get(
        path: '/api/me/notifications/unread-count',
        summary: 'Jumlah notifikasi saya yang belum dibaca',
        tags: ['Notifikasi'],
        security: [['sanctumAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Jumlah belum dibaca'), new OA\Response(response: 401, description: 'Belum login')]
    )]
    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => ['unread_count' => $request->user()->unreadNotifications()->count()]], Response::HTTP_OK);
    }

    #[OA\Patch(
        path: '/api/me/notifications/{id}/read',
        summary: 'Tandai satu notifikasi saya sebagai dibaca',
        tags: ['Notifikasi'],
        security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        responses: [new OA\Response(response: 200, description: 'Ditandai dibaca'), new OA\Response(response: 404, description: 'Bukan milik pengguna atau tidak ada')]
    )]
    public function markRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->whereKey($id)->firstOrFail();
        $notification->markAsRead();

        return response()->json(['success' => true, 'data' => (new NotificationResource($notification))->resolve()], Response::HTTP_OK);
    }

    #[OA\Post(
        path: '/api/me/notifications/read-all',
        summary: 'Tandai semua notifikasi saya sebagai dibaca',
        tags: ['Notifikasi'],
        security: [['sanctumAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Jumlah notifikasi yang ditandai')]
    )]
    public function readAll(Request $request): JsonResponse
    {
        $updated = $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['success' => true, 'data' => ['updated' => $updated, 'unread_count' => 0]], Response::HTTP_OK);
    }
}
