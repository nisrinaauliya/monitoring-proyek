<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppsmb_detail_pengerjaans', function (Blueprint $table) {
            $table->boolean('is_done')->default(false)->after('adjustment_mandays');
        });

        Schema::table('ppsmbs', function (Blueprint $table) {
            $table->decimal('progress', 5, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('ppsmb_detail_pengerjaans', function (Blueprint $table) {
            $table->dropColumn('is_done');
        });

        Schema::table('ppsmbs', function (Blueprint $table) {
            $table->integer('progress')->default(0)->change();
        });
    }
};