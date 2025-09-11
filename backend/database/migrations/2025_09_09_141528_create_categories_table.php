<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $t) { // $table yerine $t kullandık 
    $t->id();
    $t->string('name');
    $t->string('slug')->unique();
    $t->text('description')->nullable();
    $t->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
    $t->integer('sort_order')->default(0);
    $t->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }

    
};
