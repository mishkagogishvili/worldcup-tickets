<?php

namespace App\Http\Controllers;

use App\Models\Matches;
use App\Models\TicketCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MatchesController extends Controller
{
    public function index(): JsonResponse
    {
        $matches = Matches::withSum('ticketCategories as availability', 'available_count')
            ->where('match_date', '>=', now())
            ->orderBy('match_date')
            ->get();

        return response()->json([
            'data' => $matches,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        if (!$request->user()->can('admin')) {
            return response()->json(['message' => 'Unauthorized action'], 403);
        }

        $validated = $request->validate([
            'home_team' => ['required', 'array'],
            'away_team' => ['required', 'array'],
            'stadium' => ['required', 'array'],
            'match_date' => ['required', 'date'],
            'capacity' => ['required', 'integer'],
        ]);

        $match = Matches::create($validated);

        return response()->json([
            'message' => 'Match created successfully.',
            'data' => $match,
        ], Response::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $match = Matches::with('ticketCategories')->find($id);

        if (!$match) {
            return response()->json([
                'message' => 'Match not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => $match,
        ], 200);
    }

    public function report(Request $request, int $id): JsonResponse
    {
        if (!$request->user()->can('admin')) {
            return response()->json(['message' => 'Unauthorized action'], 403);
        }

        $categories = TicketCategory::where('match_id', $id)
            ->withSum([
                'reservations as sold' => function ($query) {
                    $query->where('status', 'confirmed');
                }
            ], 'quantity')
            ->withSum([
                'reservations as total_revenue' => function ($query) {
                    $query->where('status', 'confirmed');
                }
            ], 'total_price')
            ->with('tickets')
            ->get();

        if ($categories->isEmpty()) {
            return response()->json(['message' => 'Match or categories not found'], 404);
        }
        $reportData = $categories->map(function ($category) {
            return [
                'category_name' => $category->name,
                'available_count' => $category->seat_count - $category->sold,
                'tickets' => $category->tickets,
            ];
        });

        return response()->json([
            'match_id' => $id,
            'total_sold' => $categories->sum('sold'),
            'total_revenue' => $categories->sum('total_revenue'),
            'details' => $reportData,
        ]);
    }
}
