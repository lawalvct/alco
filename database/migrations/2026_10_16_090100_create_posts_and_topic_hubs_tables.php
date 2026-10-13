<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 160)->unique();
            $table->string('title', 180);
            $table->string('review_status', 30)->default('draft');
            $table->foreignId('cover_media_asset_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->json('draft_data');
            $table->json('published_data')->nullable();
            $table->string('publication_status', 30)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['publication_status', 'published_at']);
            $table->index('review_status');
        });

        Schema::create('article_category_post', function (Blueprint $table) {
            $table->foreignId('article_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->primary(['article_category_id', 'post_id']);
        });

        Schema::create('article_post_tag', function (Blueprint $table) {
            $table->foreignId('article_tag_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->primary(['article_tag_id', 'post_id']);
        });

        Schema::create('topic_hubs', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 100)->unique();
            $table->string('title', 180);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('draft_data');
            $table->json('published_data')->nullable();
            $table->string('publication_status', 30)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['publication_status', 'published_at', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topic_hubs');
        Schema::dropIfExists('article_post_tag');
        Schema::dropIfExists('article_category_post');
        Schema::dropIfExists('posts');
    }
};
