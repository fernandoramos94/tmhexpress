<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class cancellation_reason extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cancellation_reason')->insert([[
            'reason' => 'Nadie en casa'
        ], [
            'reason' => 'Direcion equivocada'
        ], [
            'reason' => 'Otras razones'
        ]]);
    }
}
