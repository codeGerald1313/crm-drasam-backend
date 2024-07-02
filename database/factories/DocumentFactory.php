<?php

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {

        static $uniqueNames = ['DNI', 'Sin Doc', 'RUC', 'CE'];

        return [
            'name' => $this->faker->unique()->randomElement($uniqueNames)
        ];
    }
}
