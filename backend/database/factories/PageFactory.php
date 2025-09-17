<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PageFactory extends Factory
{
    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(3);
        $status = $this->faker->randomElement(['draft','published','archived']);

        return [
            'title'          => $title,
            'slug'           => Str::slug($title) . '-' . Str::random(5),
            'content'        => $this->faker->paragraphs(4, true),
            'excerpt'        => $this->faker->sentence(12),
            'meta_title'     => $title,
            'meta_description'=> $this->faker->sentence(18),
            'meta_keywords'  => implode(',', $this->faker->words(6)),
            'status'         => $status,
            'published_at'   => $status === 'published' ? now() : null,
            'author_id'      => null,   // istersen burada User::factory() da kullanabilirsin
            'parent_id'      => null,
            'template'       => 'default',
            'sort_order'     => 0,
            'is_homepage'    => false,
        ];
    }
}

