<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provisioning_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('system', 40);                      // config('provisioning.systems') key
            $table->string('idempotency_key');                 // stable per order+system, replayable
            $table->string('status', 20)->default('pending');  // pending | done | failed
            $table->string('external_user_id')->nullable();
            $table->text('magic_link')->nullable();
            $table->string('login_url')->nullable();
            $table->json('response')->nullable();
            $table->text('error')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamps();
            $table->unique(['order_id', 'system']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provisioning_calls');
    }
};
