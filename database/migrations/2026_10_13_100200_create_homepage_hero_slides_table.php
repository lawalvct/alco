<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_hero_slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_asset_id')->constrained()->restrictOnDelete();
            $table->foreignId('mobile_media_asset_id')->nullable()->constrained('media_assets')->restrictOnDelete();
            $table->unsignedTinyInteger('focal_x')->default(50);
            $table->unsignedTinyInteger('focal_y')->default(50);
            $table->unsignedTinyInteger('mobile_focal_x')->nullable();
            $table->unsignedTinyInteger('mobile_focal_y')->nullable();
            $table->boolean('is_decorative')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['is_visible', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_hero_slides');
    }
};
