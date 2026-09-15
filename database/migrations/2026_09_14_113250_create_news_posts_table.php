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
    Schema::create('news_posts', function (Blueprint $table) {
        $table->id();

        $table->string('title');
        $table->string('slug')->unique();

        $table->text('excerpt')->nullable();
        $table->longText('body')->nullable();

        $table->string('category')->nullable();
        $table->string('featured_image')->nullable();

        $table->timestamp('published_at')->nullable();

        $table->boolean('is_published')->default(false);
        $table->unsignedInteger('sort_order')->default(0);

        $table->timestamps();

        $table->index(['is_published', 'published_at']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_posts');
    }
};
