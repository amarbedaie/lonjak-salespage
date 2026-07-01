<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salespages', function (Blueprint $table) {
            $table->boolean('bump_enabled')->default(false)->after('offer_ends_at');
            $table->string('bump_title')->nullable()->after('bump_enabled');
            $table->string('bump_desc')->nullable()->after('bump_title');
            $table->decimal('bump_price', 10, 2)->nullable()->after('bump_desc');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('bump_title')->nullable()->after('discount');
            $table->decimal('bump_price', 10, 2)->default(0)->after('bump_title');
        });
    }

    public function down(): void
    {
        Schema::table('salespages', function (Blueprint $table) {
            $table->dropColumn(['bump_enabled', 'bump_title', 'bump_desc', 'bump_price']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['bump_title', 'bump_price']);
        });
    }
};
