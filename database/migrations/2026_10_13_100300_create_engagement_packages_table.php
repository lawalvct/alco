<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pricing, billing cadence and contract terms are deliberately absent
     * until ALCO approves them (development plan §5).
     */
    public function up(): void
    {
        Schema::create('engagement_packages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->foreignId('parent_id')->nullable()->constrained('engagement_packages')->nullOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_selectable')->default(true);
            $table->json('draft_data');
            $table->json('published_data')->nullable();
            $table->string('publication_status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['publication_status', 'published_at', 'sort_order'], 'engagement_packages_publication_index');
            $table->index('is_selectable');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engagement_packages');
    }
};
