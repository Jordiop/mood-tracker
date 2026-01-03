<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MoodEntry;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Get all users with their basic stats
     */
    public function users()
    {
        $users = User::withCount('moodEntries')
            ->with(['moodEntries' => function ($query) {
                $query->selectRaw('user_id, AVG(score) as avg_score, COUNT(*) as total_entries')
                    ->groupBy('user_id');
            }])
            ->where('is_admin', false)
            ->get()
            ->map(function ($user) {
                $currentYear = date('Y');
                $totalDaysInYear = (new \DateTime("$currentYear-12-31"))->format('z') + 1;

                $yearEntries = MoodEntry::where('user_id', $user->id)
                    ->whereYear('date', $currentYear)
                    ->get();

                $avgScore = $yearEntries->avg('score') ?? 0;
                $daysTracked = $yearEntries->count();
                $trackingRate = $totalDaysInYear > 0 ? ($daysTracked / $totalDaysInYear) * 100 : 0;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'stats' => [
                        'average' => round($avgScore, 1),
                        'days_tracked' => $daysTracked,
                        'tracking_rate' => round($trackingRate, 1),
                    ]
                ];
            });

        return response()->json($users);
    }

    /**
     * Get a specific user's moods for a given year
     */
    public function userMoods(Request $request, User $user)
    {
        $year = $request->get('year', date('Y'));

        $moods = MoodEntry::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->orderBy('date')
            ->get();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'moods' => $moods,
        ]);
    }
}
