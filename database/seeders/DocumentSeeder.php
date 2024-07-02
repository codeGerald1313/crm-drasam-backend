<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Document;

class DocumentSeeder extends Seeder
{

    public function run(): void
    {
        Document::create([
            'name' => 'DNI',
        ]);
        Document::create([
            'name' => 'CE',
        ]);
        Document::create([
            'name' => 'RUC',
        ]);
        Document::create([
            'name' => 'SIN DOCUMENTO',
        ]);
    }
}
