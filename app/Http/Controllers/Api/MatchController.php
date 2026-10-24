<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameMatch;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class MatchController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            GameMatch::with(['homeTeam', 'awayTeam'])
                ->orderByDesc('scheduled_at')
                ->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'home_team_id' => ['required', 'integer', 'exists:teams,id'],
            'away_team_id' => [
                'required', 'integer', 'different:home_team_id',
                'exists:teams,id'
            ],
            'scheduled_at' => ['required', 'date'],
            'venue' => ['required', 'string', 'max:150'],
            'home_score' => ['sometimes', 'integer', 'min:0'],
            'away_score' => ['sometimes', 'integer', 'min:0'],
            'status' => [
                'sometimes',
                Rule::in(['scheduled', 'in_progress', 'completed', 'cancelled'])
            ],
        ]);

        $match = GameMatch::create($data);

        return response()->json(
            $match->load(['homeTeam', 'awayTeam']),
            201
        );
    }

    public function show(GameMatch $match): JsonResponse
    {
        return response()->json(
            $match->load(['homeTeam', 'awayTeam'])
        );
    }

    public function update(Request $request, GameMatch $match): JsonResponse
    {
        $data = $request->validate([
            'home_team_id' => ['sometimes', 'required', 'integer', 'exists:teams,id'],
            'away_team_id' => ['sometimes', 'required', 'integer', 'exists:teams,id'],
            'scheduled_at' => ['sometimes', 'required', 'date'],
            'venue' => ['sometimes', 'required', 'string', 'max:150'],
            'home_score' => ['sometimes', 'integer', 'min:0'],
            'away_score' => ['sometimes', 'integer', 'min:0'],
            'status' => [
                'sometimes',
                Rule::in(['scheduled', 'in_progress', 'completed', 'cancelled'])
            ],
        ]);

        $homeId = $data['home_team_id'] ?? $match->home_team_id;
        $awayId = $data['away_team_id'] ?? $match->away_team_id;

        if ($homeId == $awayId) {
            return response()->json([
                'message' => 'Un equipo no puede jugar contra sí mismo.'
            ], 422);
        }

        $match->update($data);

        return response()->json(
            $match->load(['homeTeam', 'awayTeam'])
        );
    }

    public function destroy(GameMatch $match): JsonResponse
    {
        $match->delete();

        return response()->json([
            'message' => 'Partido eliminado correctamente.'
        ]);
    }
}
