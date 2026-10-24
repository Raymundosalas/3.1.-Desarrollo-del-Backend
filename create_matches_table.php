<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('home_team_id')
                ->constrained('teams')
                ->restrictOnDelete();

            $table->foreignId('away_team_id')
                ->constrained('teams')
                ->restrictOnDelete();

            $table->dateTime('scheduled_at');
            $table->string('venue', 150);
            $table->unsignedSmallInteger('home_score')->default(0);
            $table->unsignedSmallInteger('away_score')->default(0);

            $table->string('status', 20)->default('scheduled');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
