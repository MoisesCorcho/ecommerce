<?php

declare(strict_types=1);

use App\Enums\Blog\PostStatusEnum;
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
        Schema::create('posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_category_id')->nullable()->constrained('post_categories')->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('title');
            $table->string('slug', 255)->unique();
            $table->json('excerpt')->nullable();
            $table->json('content');
            $table->string('cover_image_path', 2048)->nullable();
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->string('status', 32)->default(PostStatusEnum::Draft->value)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
