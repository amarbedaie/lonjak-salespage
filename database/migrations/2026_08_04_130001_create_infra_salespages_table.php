<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('infra_salespages', function (Blueprint $table) {
            $table->id();
            $table->string('command_uuid')->unique();
            $table->json('produk')->nullable();
            $table->json('salespage')->nullable();
            $table->json('utm')->nullable();
            $table->string('status')->default('draf'); // draf: menunggu pemetaan ke Salespage sebenar
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infra_salespages');
    }
};
