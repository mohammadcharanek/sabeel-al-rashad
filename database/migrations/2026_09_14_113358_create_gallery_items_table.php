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
    Schema::create('gallery_items', function (Blueprint $table) {
        $table->id();

        $table->string('title')->nullable();

        $table->string('image');
        $table->string('alt_text')->nullable();

        $table->text('caption')->nullable();
        $table->string('category')->nullable();

        $table->unsignedInteger('sort_order')->default(0);

        $table->boolean('is_active')->default(true);
        $table->boolean('is_featured')->default(false);

        $table->timestamps();

        $table->index(['is_active', 'sort_order']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery_items');
    }
};
