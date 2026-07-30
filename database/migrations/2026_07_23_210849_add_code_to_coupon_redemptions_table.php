<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Snapshot of the coupon code at redemption time (D41).
     */
    public function up(): void
    {
        Schema::table('coupon_redemptions', function (Blueprint $table) {
            $table->string('code', 32)->after('coupon_id');
        });
    }

    public function down(): void
    {
        Schema::table('coupon_redemptions', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
