<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Team::with('players')->orderBy('name')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:teams,name'],
            'city' => ['required', 'string', 'max:100'],
            'coach' => ['nullable', 'string', 'max:100'],
            'category' => ['required', 'string', 'max:50'],
        ]);

        $team = Team::create($data);

        return response()->json($team, 201);
    }

    public function show(Team $team): JsonResponse
    {
        return response()->json($team->load('players'));
    }

    public function update(Request $request, Team $team): JsonResponse
    {
        $data = $request->validate([
            'name' => [
                'sometimes', 'required', 'string', 'max:100',
                Rule::unique('teams', 'name')->ignore($team->id)
            ],
            'city' => ['sometimes', 'required', 'string', 'max:100'],
            'coach' => ['sometimes', 'nullable', 'string', 'max:100'],
            'category' => ['sometimes', 'required', 'string', 'max:50'],
        ]);

        $team->update($data);

        return response()->json($team);
    }

    public function destroy(Team $team): JsonResponse
    {
        if ($team->players()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar el equipo porque tiene jugadores asociados.'
            ], 409);
        }

        $hasMatches = DB::table('matches')
            ->where('home_team_id', $team->id)
            ->orWhere('away_team_id', $team->id)
            ->exists();

        if ($hasMatches) {
            return response()->json([
                'message' => 'No se puede eliminar el equipo porque aparece en partidos registrados.'
            ], 409);
        }

        $team->delete();

        return response()->json([
            'message' => 'Equipo eliminado correctamente.'
        ]);
    }
}
