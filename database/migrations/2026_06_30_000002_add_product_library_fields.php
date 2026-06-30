<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Rich product-library fields — also feed the salespage AI builder.
            $table->decimal('compare_price', 10, 2)->nullable()->after('price');
            $table->string('category')->nullable()->after('compare_price');
            $table->text('description')->nullable()->after('category');
            $table->string('audience')->nullable()->after('description');
            $table->text('problem')->nullable()->after('audience');
            $table->text('benefits')->nullable()->after('problem');
            $table->string('tone')->default('santai')->after('benefits');
            $table->json('images')->nullable()->after('tone');     // stored image paths
            $table->string('video_url')->nullable()->after('images');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['compare_price', 'category', 'description', 'audience', 'problem', 'benefits', 'tone', 'images', 'video_url']);
        });
    }
};
