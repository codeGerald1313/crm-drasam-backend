<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClousureReason;

class ClousureReasonSeeder extends Seeder
{
    public function run(): void
    {
        ClousureReason::insert([
            ['name' => 'Venta'],
            ['name' => 'No interesado'],
            ['name' => 'Datos incorrectos'],
            ['name' => 'Sin medio de contacto'],
            ['name' => 'Bloqueo'],
            ['name' => 'Esperando respuesta'],
            ['name' => 'Inactivo']
        ]);
    }
}
