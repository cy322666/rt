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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('type')->nullable();
            $table->integer('leads_count_last')->nullable();
            $table->integer(  'leads_count_new')->nullable();
            $table->integer('lead_id')->nullable();
            $table->string('contact_id')->nullable();
            $table->string('agreement')->nullable();
            $table->integer(  'part_sum')->nullable();
            $table->integer('all_sum')->nullable();
            $table->boolean('status')->nullable();
            $table->json('body')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
