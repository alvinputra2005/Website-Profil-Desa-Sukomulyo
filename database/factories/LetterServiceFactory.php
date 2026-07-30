<?php

namespace Database\Factories;

use App\Models\LetterService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<LetterService> */
class LetterServiceFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return ['name' => ucwords($name), 'slug' => Str::slug($name), 'code' => Str::upper(fake()->unique()->lexify('???')), 'description' => fake()->sentence(), 'requirements_json' => [['key' => 'ktp', 'label' => 'KTP asli', 'required' => true]], 'processing_days' => 3, 'fee_information' => 'Gratis', 'is_active' => true];
    }
}
