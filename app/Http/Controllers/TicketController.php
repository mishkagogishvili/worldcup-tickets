<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($request->user()->cannot('viewAny', Ticket::class)) {
            return response()->json(['message' => 'Unauthorized action'], 403);
        }
        $tickets = Ticket::with(['match', 'category', 'reservation'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $tickets,
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $ticket = Ticket::with(['match', 'category', 'reservation'])->find($id);
        if (!$ticket) {
            return response()->json([
                'message' => 'Ticket not found.',
            ], Response::HTTP_NOT_FOUND);
        }
        if ($request->user()->cannot('view', $ticket)) {
            return response()->json(['message' => 'Unauthorized action'], 403);
        }

        return response()->json([
            'data' => $ticket,
        ]);
    }
}
