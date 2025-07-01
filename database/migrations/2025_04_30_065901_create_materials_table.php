<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\Medium;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained('topics')->cascadeOnDelete();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->string('poster')->nullable();
            $table->json('title')->nullable();
            $table->json('author')->nullable();
            $table->json('short_description')->nullable();
            $table->string('image')->nullable();
            $table->integer('start_year');
            $table->integer('end_year');
            $table->json('tags')->nullable();
            $table->geometry('location', subtype: 'point')->nullable();
            $table->geometry('author_location', subtype: 'point')->nullable();
            $table->enum('medium', array_column(Medium::cases(), 'value'))->default(Medium::Paper->value);
            $table->json('full_text')->nullable();
            $table->text('book_url');
            $table->text('video');
            $table->text('source_url');
            $table->json('source');
            $table->text('author_url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
