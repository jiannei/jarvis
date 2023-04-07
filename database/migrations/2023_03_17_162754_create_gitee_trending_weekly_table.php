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
        Schema::create('gitee_trending_weekly', function (Blueprint $table) {
            $table->id();
            $table->string('repo');
            $table->text('desc')->nullable();
            $table->string('language')->nullable();
            $table->unsignedInteger('stars');
            $table->string('week');

            $table->timestamps();
            $table->unique(['repo', 'week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gitee_trending_weekly');
    }
};
