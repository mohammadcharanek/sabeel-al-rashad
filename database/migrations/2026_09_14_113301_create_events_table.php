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
    Schema::create('events', function (Blueprint $table) {
        $table->id();

        $table->string('title');
        $table->string('slug')->unique();

        $table->text('description')->nullable();

        $table->dateTime('starts_at');
        $table->dateTime('ends_at')->nullable();

        $table->string('location')->nullable();
        $table->string('image')->nullable();

        $table->boolean('is_published')->default(false);
        $table->unsignedInteger('sort_order')->default(0);

        $table->timestamps();

        $table->index(['is_published', 'starts_at']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
