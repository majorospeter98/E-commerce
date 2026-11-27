<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->unsignedInteger('price')->default(0);
        });

        /** Prices are in whole forints. ItemSeeder never updates existing rows, so fill them here. */
        DB::table('items')->where('image', 'short.webp')->update(['price' => 12990]);
        DB::table('items')->where('image', '!=', 'short.webp')->update(['price' => 29990]);
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
