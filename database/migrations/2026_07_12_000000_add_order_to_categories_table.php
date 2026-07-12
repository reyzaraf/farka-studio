<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->integer('order')->default(0)->after('slug');
        });

        // Give existing categories a stable, distinct starting order.
        foreach (DB::table('categories')->orderBy('id')->pluck('id') as $position => $id) {
            DB::table('categories')->where('id', $id)->update(['order' => $position + 1]);
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
};
