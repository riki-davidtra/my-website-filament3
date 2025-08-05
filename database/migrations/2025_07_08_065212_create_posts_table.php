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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users', 'id')->nullOnDelete();
            $table->foreignId('category_id')->constrained('categories', 'id')->nullOnDelete();
            $table->string('image')->nullable();
            $table->string('title', 255);
            $table->enum('status', ['draft', 'publish'])->default('draft');
            $table->integer('view_total')->default(0);
            $table->text('content')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
