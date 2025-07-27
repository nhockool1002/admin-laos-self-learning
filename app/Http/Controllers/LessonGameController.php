<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SupabaseService;

class LessonGameController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    // ========== LESSON GAMES ==========
    public function index()
    {
        $games = $this->supabase->getLessonGames();
        return response()->json($games);
    }

    public function show($id)
    {
        $game = $this->supabase->getLessonGameById($id);
        return response()->json($game);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $game = $this->supabase->createLessonGame($data);
        return response()->json($game);
    }

    public function update($id, Request $request)
    {
        $data = $request->all();
        $game = $this->supabase->updateLessonGame($id, $data);
        return response()->json($game);
    }

    public function destroy($id)
    {
        $result = $this->supabase->deleteLessonGame($id);
        return response()->json(['success' => $result]);
    }

    // ========== LESSON GAME GROUPS ==========
    public function listGroups()
    {
        $groups = $this->supabase->getLessonGameGroups();
        return response()->json($groups);
    }

    public function showGroup($id)
    {
        $group = $this->supabase->getLessonGameGroupById($id);
        return response()->json($group);
    }

    public function createGroup(Request $request)
    {
        $data = $request->all();
        $group = $this->supabase->createLessonGameGroup($data);
        return response()->json($group);
    }

    public function updateGroup($id, Request $request)
    {
        $data = $request->all();
        $group = $this->supabase->updateLessonGameGroup($id, $data);
        return response()->json($group);
    }

    public function deleteGroup($id)
    {
        $result = $this->supabase->deleteLessonGameGroup($id);
        return response()->json(['success' => $result]);
    }
}