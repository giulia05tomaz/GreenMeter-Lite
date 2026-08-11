<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emission_factors', function (Blueprint $table) {
            $table->id();
            $table->string('metric', 32)->unique();
            $table->decimal('factor', 14, 8);
            $table->string('unit_in', 16);
            $table->string('unit_out', 16);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emission_factors');
    }
};
