<?php

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
        Schema::table('ppsmbs', function (Blueprint $table) {
            $table->string('developer')->nullable()->after('secondary_ba');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ppsmbs', function (Blueprint $table) {
            $table->dropColumn('developer');
        });
    }
};
