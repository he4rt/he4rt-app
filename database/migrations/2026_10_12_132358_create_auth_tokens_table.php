<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Key/value store for App\Support\AuthTokenStore. Not the OS
        // keychain/keystore (that's the nativephp/mobile-secure-storage
        // plugin — paid add-on, not installed) — plain on-device SQLite,
        // same disk encryption as the rest of the app's data. Fine for
        // now; revisit before shipping anything that needs hardware-backed
        // storage.
        Schema::create('auth_tokens', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_tokens');
    }
};
