<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('github_trending_monthly', function (Blueprint $table) {
            $table->id();
            $table->string('repo');
            $table->text('desc')->nullable();
            $table->string('language')->nullable();
            $table->unsignedInteger('stars');
            $table->unsignedInteger('forks');
            $table->unsignedInteger('added_stars')->default(0);
            $table->string('spoken_language_code')->nullable();
            $table->string('month');

            $table->timestamps();
            $table->unique(['repo', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('github_trending_monthly');
    }
};
