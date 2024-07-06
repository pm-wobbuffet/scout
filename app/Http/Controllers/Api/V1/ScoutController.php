<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScoutRequest;
use App\Models\Scout;
use Illuminate\Http\Request;

class ScoutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreScoutRequest $request)
    {
        //dd($request->safe()->all());
        $s = Scout::create($request->safe()->all());
        if($s) {
            return response()->json([
                'slug'                  => $s->slug,
                'collaborator_password' => $s->collaborator_password,
                'readonly_url'          => route('scout.view', $s),
                'collaborate_url'       => route('scout.view', [$s, $s->collaborator_password]),
            ]);
        } else {
            return response()->json([
                'error' => 'An unknown error was created while trying to store this scouting report.',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
