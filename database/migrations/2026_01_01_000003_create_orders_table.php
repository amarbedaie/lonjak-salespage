<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('salespage_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('state')->nullable();
            $table->string('product_name')->nullable();
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('status')->default('baru');    // baru|diproses|dihantar|selesai|batal
            $table->string('courier')->nullable();
            $table->string('awb')->nullable();
            $table->string('payment_status')->default('belum'); // belum|dibayar|gagal
            $table->string('payment_ref')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
