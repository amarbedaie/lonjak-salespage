<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salespages', function (Blueprint $table) {
            $table->string('fb_pixel')->nullable()->after('video_url');      // Facebook/Meta Pixel ID
            $table->string('tiktok_pixel')->nullable()->after('fb_pixel');   // TikTok Pixel ID
            $table->string('ga_id')->nullable()->after('tiktok_pixel');      // Google Analytics / GTM ID
            $table->timestamp('offer_ends_at')->nullable()->after('ga_id');  // countdown deadline
        });
    }

    public function down(): void
    {
        Schema::table('salespages', function (Blueprint $table) {
            $table->dropColumn(['fb_pixel', 'tiktok_pixel', 'ga_id', 'offer_ends_at']);
        });
    }
};
