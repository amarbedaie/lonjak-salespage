<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('easyparcel_api_key')->nullable()->after('bayarcash_active'); // encrypted
            $table->boolean('easyparcel_sandbox')->default(true)->after('easyparcel_api_key');
            // Pickup / sender address (required by EasyParcel booking)
            $table->string('ship_name')->nullable()->after('easyparcel_sandbox');
            $table->string('ship_phone')->nullable()->after('ship_name');
            $table->string('ship_addr1')->nullable()->after('ship_phone');
            $table->string('ship_addr2')->nullable()->after('ship_addr1');
            $table->string('ship_city')->nullable()->after('ship_addr2');
            $table->string('ship_state')->nullable()->after('ship_city');
            $table->string('ship_postcode', 10)->nullable()->after('ship_state');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('ep_order_no')->nullable()->after('awb');     // EasyParcel order_number
            $table->string('awb_link', 500)->nullable()->after('ep_order_no'); // PDF label URL
            $table->string('tracking_url', 500)->nullable()->after('awb_link');
            $table->decimal('ship_price', 10, 2)->nullable()->after('tracking_url');
            $table->decimal('ship_weight', 8, 2)->nullable()->after('ship_price');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['easyparcel_api_key', 'easyparcel_sandbox', 'ship_name', 'ship_phone', 'ship_addr1', 'ship_addr2', 'ship_city', 'ship_state', 'ship_postcode']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['ep_order_no', 'awb_link', 'tracking_url', 'ship_price', 'ship_weight']);
        });
    }
};
