<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calculators', function (Blueprint $table): void {
            $table->string('seo_title', 160)->nullable();
            $table->string('seo_description', 300)->nullable();
            $table->text('guide_intro')->nullable();
            $table->text('guide_example')->nullable();
            $table->json('guide_steps')->nullable();
            $table->json('guide_faqs')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('calculators', function (Blueprint $table): void {
            $table->dropColumn(['seo_title', 'seo_description', 'guide_intro', 'guide_example', 'guide_steps', 'guide_faqs']);
        });
    }
};
