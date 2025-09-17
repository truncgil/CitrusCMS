<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->word();
        return [
             'name' => ucfirst($name),
             'slug' => \Illuminate\Support\Str::slug($name).'-'.\Illuminate\Support\Str::random(5),
             'description' => $this->faker->sentence(10),
             'parent_id' => null,
             'sort_order' => 0,
        ];
    }
}
