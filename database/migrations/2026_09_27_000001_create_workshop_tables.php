<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['SUPER_ADMIN', 'ADMIN'])->default('ADMIN');
            $table->boolean('is_active')->default(true);
        });
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->unsignedBigInteger('min_price')->nullable();
            $table->unsignedBigInteger('max_price')->nullable();
            $table->json('features');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('vehicle_type', ['CAR', 'MOTOR']);
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
        });
        Schema::create('portfolio_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained()->cascadeOnDelete();
            $table->text('image_url');
            $table->string('cloudinary_public_id')->unique();
            $table->enum('stage', ['BEFORE', 'PROCESS', 'AFTER']);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('portfolio_images');
        Schema::dropIfExists('portfolios');
        Schema::dropIfExists('services');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['role', 'is_active']));
    }
};
