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
        Schema::table('product_variants', function (Blueprint $table): void {
            if (! Schema::hasColumn('product_variants', 'color_id')) {
                $table->foreignId('color_id')->nullable()->constrained('colors')->nullOnDelete();
            }
            if (Schema::hasColumn('product_variants', 'color')) {
                $table->dropColumn('color');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table): void {
            if (! Schema::hasColumn('product_variants', 'color')) {
                $table->string('color')->nullable();
            }
            if (Schema::hasColumn('product_variants', 'color_id')) {
                $table->dropConstrainedForeignId('color_id');
            }
        });
    }
};
