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
        Schema::create('gitee_explore', function (Blueprint $table) {
            $table->id();
            $table->string('repo');
            $table->text('desc')->nullable();
            $table->string('language')->nullable();
            $table->string('category')->nullable();
            $table->unsignedInteger('stars');
            $table->timestamp('latest_updated_at');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gitee_explore');
    }
};
