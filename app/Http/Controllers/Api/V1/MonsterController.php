<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Monster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonsterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Monster::class);

        $user = Auth::user();
        return Monster::whereNull('user_id')->orWhere('user_id', $user->id)->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Monster::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'ac' => 'required|integer',
            'strength' => 'required|integer',
            'dexterity' => 'required|integer',
            'constitution' => 'required|integer',
            'intelligence' => 'required|integer',
            'wisdom' => 'required|integer',
            'charisma' => 'required|integer',
            'max_health' => 'required|integer',
            'data' => 'nullable|json',
            'is_shared' => 'nullable|boolean',
        ]);

        $data = $validated;
        if (isset($validated['is_shared']) && $validated['is_shared']) {
            $data['user_id'] = null;
        } else {
            $data['user_id'] = Auth::id();
        }
        unset($data['is_shared']);

        if (isset($data['data'])) {
            $data['data'] = json_decode($data['data'], true);
        }

        $monster = Monster::create($data);

        return response()->json($monster, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Monster $monster)
    {
        $this->authorize('view', $monster);
        return $monster;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Monster $monster)
    {
        $this->authorize('update', $monster);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'ac' => 'sometimes|required|integer',
            'strength' => 'sometimes|required|integer',
            'dexterity' => 'sometimes|required|integer',
            'constitution' => 'sometimes|required|integer',
            'intelligence' => 'sometimes|required|integer',
            'wisdom' => 'sometimes|required|integer',
            'charisma' => 'sometimes|required|integer',
            'max_health' => 'sometimes|required|integer',
            'data' => 'nullable|json',
        ]);

        if (isset($validated['data'])) {
            $validated['data'] = json_decode($validated['data'], true);
        }

        $monster->update($validated);

        return response()->json($monster);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Monster $monster)
    {
        $this->authorize('delete', $monster);
        $monster->delete();
        return response()->json(null, 204);
    }
}
