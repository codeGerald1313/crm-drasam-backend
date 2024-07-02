<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{

    protected $model = Contact::class;

    public function definition(): array
    {
        $documentId = Document::factory()->create()->id;

        return [
            'name' => $this->faker->firstName,
            'document' => $this->faker->unique()->numerify('#########'),
            'document_id' => $documentId,
            'birthdate' => $this->faker->date,
            'email' => $this->faker->unique()->safeEmail,
            'country_code' => '+1',
            'num_phone' => $this->faker->phoneNumber,
        ];
    }
}
