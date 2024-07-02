<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contact;
use App\Models\Document;

class ContactSeeder extends Seeder
{
    public function run()
    {
        $documentsIds = Document::pluck('id')->toArray();

        if (!empty($documentsIds)) {
            for ($i = 0; $i < 4; $i++) {

                $documentId = $this->getUniqueRandomElement($documentsIds);

                Contact::factory()->create([
                    'document_id' => $documentId
                ]);
            }
        }
    }


    private function getUniqueRandomElement(array $array)
    {
        $maxAttempts = 2000;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $element = $array[array_rand($array)];
            if (!Contact::where('document_id', $element)) {
                return $element;
            }
        }

        return null;
    }
}
