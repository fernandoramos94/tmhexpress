<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class status extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('status')->insert([
            [
                'status' => 'Pendiente'
            ], [
                'status' => 'Asignado'
            ], [
                'status' => 'Pedido recolectado'
            ], [
                'status' => 'Pedido entregado'
            ], [
                'status' => 'Pendiente por entregar'
            ], [
                'status' => 'Pedido cancelado por el conductor'
            ],[
                'status' => 'Pedido cancelado por el administrador'
            ]
        ]);
    }
}
