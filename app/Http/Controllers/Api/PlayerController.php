<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PlayerController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Player::with('team')->orderBy('last_name')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'team_id' => ['required', 'integer', 'exists:teams,id'],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'jersey_number' => [
                'required', 'integer', 'min:0', 'max:99',
                function ($attribute, $value, $fail) use ($request) {
                    $exists = Player::where('team_id', $request->team_id)
                        ->where('jersey_number', $value)
                        ->exists();

                    if ($exists) {
                        $fail('Ese número de camiseta ya está asignado en el equipo.');
                    }
                }
            ],
            'position' => ['required', 'string', 'max:50'],
        ]);

        $player = Player::create($data);

        return response()->json($player->load('team'), 201);
    }

    public function show(Player $player): JsonResponse
    {
        return response()->json($player->load('team'));
    }

    public function update(Request $request, Player $player): JsonResponse
    {
        $data = $request->validate([
            'team_id' => ['sometimes', 'required', 'integer', 'exists:teams,id'],
            'first_name' => ['sometimes', 'required', 'string', 'max:80'],
            'last_name' => ['sometimes', 'required', 'string', 'max:80'],
            'jersey_number' => ['sometimes', 'required', 'integer', 'min:0', 'max:99'],
            'position' => ['sometimes', 'required', 'string', 'max:50'],
        ]);

        $teamId = $data['team_id'] ?? $player->team_id;
        $number = $data['jersey_number'] ?? $player->jersey_number;

        $duplicate = Player::where('team_id', $teamId)
            ->where('jersey_number', $number)
            ->where('id', '!=', $player->id)
            ->exists();

        if ($duplicate) {
            return response()->json([
                'message' => 'Ese número de camiseta ya está asignado en el equipo.'
            ], 422);
        }

        $player->update($data);

        return response()->json($player->load('team'));
    }

    public function destroy(Player $player): JsonResponse
    {
        $player->delete();

        return response()->json([
            'message' => 'Jugador eliminado correctamente.'
        ]);
    }
}
