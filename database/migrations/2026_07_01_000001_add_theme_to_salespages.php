<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salespages', function (Blueprint $table) {
            $table->string('theme')->default('default')->after('category'); // visual color scheme
        });
    }

    public function down(): void
    {
        Schema::table('salespages', function (Blueprint $table) {
            $table->dropColumn('theme');
        });
    }
};
