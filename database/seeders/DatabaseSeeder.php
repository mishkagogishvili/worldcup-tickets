<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Matches;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@worldcup.ge',
            'password' => Hash::make('admin_password'),
            'role' => 'admin',
        ]);

        $fanPassword = Hash::make('fan_password');
        for ($i = 1; $i <= 5; $i++) {
            User::factory()->create([
                'name' => "Fan $i",
                'email' => "fan$i@worldcup.ge",
                'password' => $fanPassword,
                'role' => 'fan',
            ]);
        }

        //matches
        $matches = [
            [
                'home_team' => ['en' => 'Mexico', 'ka' => 'მექსიკა'],
                'away_team' => ['en' => 'South Africa', 'ka' => 'სამხრეთ აფრიკა'],
                'stadium' => ['en' => 'Estadio Azteca', 'ka' => 'აცტეკას სტადიონი'],
                'match_date' => '2026-06-11 15:00:00',
                'capacity' => 83000
            ],
            [
                'home_team' => ['en' => 'Canada', 'ka' => 'კანადა'],
                'away_team' => ['en' => 'Italy', 'ka' => 'იტალია'],
                'stadium' => ['en' => 'BMO Field', 'ka' => 'ბი-ემ-ო ფილდი'],
                'match_date' => '2026-06-12 19:00:00',
                'capacity' => 45000
            ],
            [
                'home_team' => ['en' => 'USA', 'ka' => 'აშშ'],
                'away_team' => ['en' => 'Paraguay', 'ka' => 'პარაგვაი'],
                'stadium' => ['en' => 'SoFi Stadium', 'ka' => 'სო-ფაი სტადიონი'],
                'match_date' => '2026-06-12 21:00:00',
                'capacity' => 70000
            ],
            [
                'home_team' => ['en' => 'Brazil', 'ka' => 'ბრაზილია'],
                'away_team' => ['en' => 'Morocco', 'ka' => 'მაროკო'],
                'stadium' => ['en' => 'MetLife Stadium', 'ka' => 'მეტლაიფ სტადიონი'],
                'match_date' => '2026-06-13 22:00:00',
                'capacity' => 82500
            ],
            [
                'home_team' => ['en' => 'Germany', 'ka' => 'გერმანია'],
                'away_team' => ['en' => 'Curaçao', 'ka' => 'კურასაო'],
                'stadium' => ['en' => 'NRG Stadium', 'ka' => 'ენ-არ-ჯი სტადიონი'],
                'match_date' => '2026-06-14 17:00:00',
                'capacity' => 72000
            ],
            [
                'home_team' => ['en' => 'Spain', 'ka' => 'ესპანეთი'],
                'away_team' => ['en' => 'Cape Verde', 'ka' => 'კაბო-ვერდე'],
                'stadium' => ['en' => 'Mercedes-Benz Stadium', 'ka' => 'მერსედეს-ბენც სტადიონი'],
                'match_date' => '2026-06-15 16:00:00',
                'capacity' => 75000
            ],
            [
                'home_team' => ['en' => 'France', 'ka' => 'საფრანგეთი'],
                'away_team' => ['en' => 'Senegal', 'ka' => 'სენეგალი'],
                'stadium' => ['en' => 'MetLife Stadium', 'ka' => 'მეტლაიფ სტადიონი'],
                'match_date' => '2026-06-16 19:00:00',
                'capacity' => 82500
            ],
            [
                'home_team' => ['en' => 'Argentina', 'ka' => 'არგენტინა'],
                'away_team' => ['en' => 'Algeria', 'ka' => 'ალჟირი'],
                'stadium' => ['en' => 'Arrowhead Stadium', 'ka' => 'ეროუჰედის სტადიონი'],
                'match_date' => '2026-06-16 21:00:00',
                'capacity' => 73000
            ],
        ];

        foreach ($matches as $match) {
            Matches::factory()->create($match);
        }
        $match = Matches::first();
        if ($match) {
            $match->ticketCategories()->first()->update(['available_count' => 2]);
        }
    }
}
