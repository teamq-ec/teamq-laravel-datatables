<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authors', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id');
            $table->string('name');
            $table->string('email', 254);
            $table->unsignedTinyInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};
