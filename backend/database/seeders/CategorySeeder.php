<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category; // Category modelini de eklemeyi unutmayın

class CategorySeeder extends Seeder {
  public function run(): void {
    $root = Category::factory()->create(['name'=>'Genel','slug'=>'genel']);
    Category::factory()->count(5)->create(['parent_id'=>$root->id]);
  }
}