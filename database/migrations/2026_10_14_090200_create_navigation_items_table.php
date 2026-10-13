<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('navigation_items', function (Blueprint $table) {
            $table->id();
            $table->string('location', 20);
            $table->string('label', 60);
            $table->string('url');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->boolean('opens_in_new_tab')->default(false);
            $table->timestamps();
            $table->index(['location', 'is_visible', 'sort_order']);
        });

        // Start from the menus the site already shows, so staff edit real data.
        $header = ['Home' => '/', 'About' => '/about', 'Services' => '/services', 'Insights' => '/insights', 'Tools' => '/tools', 'Contact' => '/contact'];
        $footer = ['Home' => '/', 'About' => '/about', 'Services' => '/services', 'Insights' => '/insights', 'Tools' => '/tools', 'FAQ' => '/faq', 'Contact' => '/contact'];
        $rows = [];

        foreach (['header' => $header, 'footer' => $footer] as $location => $links) {
            $position = 0;

            foreach ($links as $label => $url) {
                $rows[] = [
                    'location' => $location,
                    'label' => $label,
                    'url' => $url,
                    'sort_order' => ++$position,
                    'is_visible' => true,
                    'opens_in_new_tab' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('navigation_items')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('navigation_items');
    }
};
