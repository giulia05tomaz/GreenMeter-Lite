<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('ts');
            $table->string('metric', 32);
            $table->decimal('value', 12, 3);
            $table->string('unit', 16);
            $table->string('source', 32)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'ts']);
            $table->index(['user_id', 'metric']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('readings');
    }
};
