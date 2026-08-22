<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salespages', function (Blueprint $table) {
            // Which BeDaie system a paid order on this page should provision access to.
            // Null = physical product / no system access (the default).
            $table->string('provision_system', 40)->nullable()->after('gateway');
        });
    }

    public function down(): void
    {
        Schema::table('salespages', function (Blueprint $table) {
            $table->dropColumn('provision_system');
        });
    }
};
