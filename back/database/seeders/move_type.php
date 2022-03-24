<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class move_type extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('move_type')->insert([
            [
                'moving' => 'Recoger'
            ], [
                'moving' => 'Entrega'
            ]
        ]);
    }
}
