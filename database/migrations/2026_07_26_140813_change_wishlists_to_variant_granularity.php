<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * D6: the wishlist moves from product-level to variant-level granularity.
     * There is no real user data to backfill at this point in the project
     * (pre-launch, only seeder/manual-testing rows exist), so the table is
     * truncated instead of guessing which variant an old row "meant" — see
     * design.md D-A7 for the full rationale.
     *
     * Column/index ops are ordered so the `user_id` foreign key always has a
     * supporting index: MySQL refuses to drop `[user_id, product_id]` while
     * it is the only index covering `user_id`, so the new
     * `[user_id, product_variant_id]` unique is created before the old one
     * is dropped. `product_id` is dropped via `dropConstrainedForeignId()`
     * (not a separate `dropColumn()`) because MySQL also refuses to drop a
     * column while its own foreign key constraint still references it.
     */
    public function up(): void
    {
        Schema::table('wishlists', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->after('user_id')->nullable()->constrained()->cascadeOnDelete();
        });

        DB::table('wishlists')->truncate();

        Schema::table('wishlists', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable(false)->change();
            $table->unique(['user_id', 'product_variant_id']);
        });

        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'product_id']);
        });

        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wishlists', function (Blueprint $table) {
            $table->foreignId('product_id')->after('user_id')->nullable()->constrained()->cascadeOnDelete();
        });

        DB::table('wishlists')->truncate();

        Schema::table('wishlists', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable(false)->change();
            $table->unique(['user_id', 'product_id']);
        });

        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'product_variant_id']);
        });

        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_variant_id');
        });
    }
};
