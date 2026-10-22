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
        Schema::table('services', function (Blueprint $table) {
            $table->index(['is_active', 'sort_order'], 'services_active_sort_index');
        });

        Schema::table('portfolios', function (Blueprint $table) {
            $table->index(['service_id', 'is_published'], 'portfolios_service_published_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex('services_active_sort_index');
        });

        Schema::table('portfolios', function (Blueprint $table) {
            $table->dropIndex('portfolios_service_published_index');
        });
    }
};
