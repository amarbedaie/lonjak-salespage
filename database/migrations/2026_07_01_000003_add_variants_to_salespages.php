<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salespages', function (Blueprint $table) {
            $table->json('variants')->nullable()->after('blocks');       // all generated variants
            $table->unsignedTinyInteger('variant_index')->default(0)->after('variants'); // which one is active
        });
    }

    public function down(): void
    {
        Schema::table('salespages', function (Blueprint $table) {
            $table->dropColumn(['variants', 'variant_index']);
        });
    }
};
