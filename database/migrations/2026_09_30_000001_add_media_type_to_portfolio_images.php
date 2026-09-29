<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('portfolio_images', function (Blueprint $table) {
            $table->string('media_type', 10)->default('image')->after('cloudinary_public_id');
        });
    }

    public function down(): void
    {
        Schema::table('portfolio_images', fn (Blueprint $table) => $table->dropColumn('media_type'));
    }
};
