<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->string('type', 20);
            $table->string('name', 120);
            $table->string('email', 160)->nullable();
            $table->string('phone', 40)->nullable();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('service_title', 160)->nullable();
            // The package as the visitor saw it, so later edits cannot rewrite history.
            $table->foreignId('engagement_package_id')->nullable()->constrained()->nullOnDelete();
            $table->json('package_snapshot')->nullable();
            $table->boolean('wants_tailored')->default(false);
            $table->text('message')->nullable();
            $table->string('preferred_contact_method', 20)->nullable();
            $table->string('availability', 200)->nullable();
            $table->text('consent_text');
            $table->timestamp('consented_at');
            $table->string('status', 30)->default('new');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('next_action', 200)->nullable();
            $table->timestamp('follow_up_at')->nullable();
            $table->string('source_path')->nullable();
            $table->json('attribution')->nullable();
            $table->string('fingerprint', 64)->nullable()->index();
            $table->string('notification_status', 20)->default('pending');
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
            $table->index(['assigned_to', 'status']);
            $table->index('follow_up_at');
        });

        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 30);
            $table->text('body');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_activities');
        Schema::dropIfExists('leads');
    }
};
