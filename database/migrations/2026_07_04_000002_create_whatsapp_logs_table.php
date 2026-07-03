<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);            // paid_customer | paid_merchant | shipped
            $table->string('to', 40)->nullable();
            $table->string('session', 60)->nullable();
            $table->string('status', 20)->default('queued'); // queued | sent | failed
            $table->string('waha_message_id')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
            $table->unique(['order_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_logs');
    }
};
