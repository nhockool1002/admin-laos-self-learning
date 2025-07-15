<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SupabaseService;

class GameController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    // ========== FLASH GAMES ==========
    public function index()
    {
        $games = $this->supabase->getFlashGames();
        return response()->json($games);
    }

    public function show($id)
    {
        $game = $this->supabase->getFlashGameById($id);
        return response()->json($game);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $game = $this->supabase->createFlashGame($data);
        return response()->json($game);
    }

    public function update($id, Request $request)
    {
        $data = $request->all();
        $game = $this->supabase->updateFlashGame($id, $data);
        return response()->json($game);
    }

    public function destroy($id)
    {
        $result = $this->supabase->deleteFlashGame($id);
        return response()->json(['success' => $result]);
    }

    // ========== GAME GROUPS ==========
    public function listGroups()
    {
        $groups = $this->supabase->getGameGroups();
        return response()->json($groups);
    }

    public function showGroup($id)
    {
        $group = $this->supabase->getGameGroupById($id);
        return response()->json($group);
    }

    public function createGroup(Request $request)
    {
        $data = $request->all();
        $group = $this->supabase->createGameGroup($data);
        return response()->json($group);
    }

    public function updateGroup($id, Request $request)
    {
        $data = $request->all();
        $group = $this->supabase->updateGameGroup($id, $data);
        return response()->json($group);
    }

    public function deleteGroup($id)
    {
        $result = $this->supabase->deleteGameGroup($id);
        return response()->json(['success' => $result]);
    }
} 