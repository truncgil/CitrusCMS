<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        // Anasayfa
        Page::factory()->create([
            'title' => 'Anasayfa',
            'slug'  => 'anasayfa',
            'status'=> 'published',
            'is_homepage' => true,
        ]);

        // Statik sayfalar
        Page::factory()->create(['title'=>'Hakkımızda','slug'=>'hakkimizda','status'=>'published']);
        Page::factory()->create(['title'=>'İletişim','slug'=>'iletisim','status'=>'draft']);

        // Fazladan listeyi doldurmak için
        Page::factory(10)->create();
    }
}
