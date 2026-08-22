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
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->default('General')->index();
            $table->string('icon')->default('wrench');
            $table->string('component')->nullable();
            $table->string('badge')->nullable(); // e.g. HOT, NEW, PRO, BETA
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_maintenance')->default(false)->index();
            $table->text('maintenance_message')->nullable();
            $table->integer('sort_order')->default(0)->index();
            $table->unsignedBigInteger('total_usage_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};
