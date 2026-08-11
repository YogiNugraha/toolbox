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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedInteger('subtotal')->default(0)->after('amount');
            $table->unsignedInteger('discount')->default(0)->after('subtotal');
            $table->unsignedInteger('service_fee')->default(0)->after('discount');
            $table->unsignedInteger('tax')->default(0)->after('service_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'discount', 'service_fee', 'tax']);
        });
    }
};
