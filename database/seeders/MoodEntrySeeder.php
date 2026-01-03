<?php

namespace Database\Seeders;

use App\Models\MoodEntry;
use App\Models\User;
use Illuminate\Database\Seeder;

class MoodEntrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('is_admin', false)->get();
        $currentYear = date('Y');

        foreach ($users as $user) {
            $numberOfEntries = rand(50, 150);
            $createdDates = [];

            for ($i = 0; $i < $numberOfEntries; $i++) {
                $randomDay = rand(1, min(365, (new \DateTime())->format('z') + 1));
                $date = (new \DateTime("$currentYear-01-01"))->modify("+$randomDay days");
                $dateString = $date->format('Y-m-d');

                if (in_array($dateString, $createdDates)) {
                    continue;
                }

                $createdDates[] = $dateString;
                $score = $this->generateRealisticMoodScore();

                MoodEntry::create([
                    'user_id' => $user->id,
                    'date' => $dateString,
                    'score' => $score,
                    'comment' => $this->generateComment($score),
                ]);
            }
        }
    }

    /**
     * Generate a realistic mood score with tendency towards middle-high values
     */
    private function generateRealisticMoodScore(): int
    {
        $rand = rand(1, 100);

        return match (true) {
            $rand <= 5 => rand(1, 2),      
            $rand <= 15 => rand(3, 4),     
            $rand <= 40 => rand(5, 6),     
            $rand <= 80 => rand(7, 8),     
            default => rand(9, 10),        
        };
    }

    /**
     * Generate a comment based on mood score
     */
    private function generateComment(int $score): ?string
    {
        if (rand(1, 10) <= 3) {
            return null;
        }

        // generated from chatgpt, not real comments, just for seeding purposes
        $comments = [
            1 => ['Really struggling today', 'Tough day', 'Not good at all'],
            2 => ['Having a rough time', 'Feeling down', 'Could be better'],
            3 => ['Bit of a low day', 'Not great but okay', 'Struggling a bit'],
            4 => ['Below average', 'Could use improvement', 'Feeling meh'],
            5 => ['Okay day', 'Nothing special', 'Average'],
            6 => ['Pretty decent', 'Good enough', 'Solid day'],
            7 => ['Feeling good today', 'Nice day overall', 'Pretty happy'],
            8 => ['Great day!', 'Feeling really good', 'Things are going well'],
            9 => ['Excellent day!', 'Feeling amazing', 'Everything is great'],
            10 => ['Perfect day!', 'Couldn\'t be better', 'Absolutely wonderful'],
        ];

        $options = $comments[$score] ?? [''];
        return $options[array_rand($options)];
    }
}
