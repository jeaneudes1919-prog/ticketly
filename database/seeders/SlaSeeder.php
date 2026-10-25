<?php

namespace Database\Seeders;

use App\Models\SlaPolicy;
use Illuminate\Database\Seeder;

class SlaSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['urgent' => [30, 240], 'high' => [120, 480], 'medium' => [480, 2880], 'low' => [1440, 7200]] as $p => [$fr, $res]) {
            SlaPolicy::updateOrCreate(['priority' => $p], ['first_response_minutes' => $fr, 'resolution_minutes' => $res]);
        }
    }
}
