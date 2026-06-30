<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Per-merchant BayarCash credentials (secrets encrypted at rest via model casts).
            $table->text('bayarcash_pat')->nullable();
            $table->text('bayarcash_portal_key')->nullable();
            $table->text('bayarcash_api_secret')->nullable();
            $table->boolean('bayarcash_sandbox')->default(false);
            $table->boolean('bayarcash_active')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bayarcash_pat', 'bayarcash_portal_key', 'bayarcash_api_secret', 'bayarcash_sandbox', 'bayarcash_active']);
        });
    }
};
