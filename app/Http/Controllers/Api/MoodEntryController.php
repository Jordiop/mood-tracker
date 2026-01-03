<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MoodEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MoodEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $year = $request->get('year', date('Y'));

        $moods = MoodEntry::where('user_id', auth()->id())
            ->whereYear('date', $year)
            ->orderBy('date')
            ->get();

        return response()->json($moods);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'score' => 'required|integer|min:1|max:10',
            'comment' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();

        // Upsert: Update if exists, create if not
        $moodEntry = MoodEntry::updateOrCreate(
            [
                'user_id' => $validated['user_id'],
                'date' => $validated['date']
            ],
            $validated
        );

        return response()->json($moodEntry, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $moodEntry)
    {
        $moodEntry = MoodEntry::find($moodEntry);

        if ((int)$moodEntry->user_id !== (int)auth()->id()) {
            abort(403, 'Unauthorized to update this mood entry. MoodEntry user: ' . $moodEntry->user_id . ', Auth user: ' . auth()->id());
        }

        $validated = $request->validate([
            'score' => 'required|integer|min:1|max:10',
            'comment' => 'nullable|string',
        ]);

        $moodEntry->update($validated);

        return response()->json($moodEntry);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $moodEntry)
    {
        $mood = MoodEntry::find($moodEntry);

        if (!$mood || (int)$mood->user_id !== (int)auth()->id()) {
            abort(403, 'Unauthorized to delete this mood entry.');
        }

        $mood->delete();

        return response()->json(null, 204);
    }
}
