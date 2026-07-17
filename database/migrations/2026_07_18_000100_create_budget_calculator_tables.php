<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calc_factor_groups', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();       // jabodetabek, existing_building, target_building, style
            $table->string('name');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('calc_factor_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factor_group_id')->constrained('calc_factor_groups')->cascadeOnDelete();
            $table->string('label');
            $table->decimal('multiplier', 6, 4);   // 1.0000, 1.1500, 0.8000
            $table->string('note')->nullable();
            $table->boolean('is_default')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('calc_allocations', function (Blueprint $table) {
            $table->id();
            $table->string('category');            // pelaksanaan | perancangan | persiapan
            $table->string('label');
            $table->decimal('percentage', 6, 4);   // 0.1500 = 15%, 1.0000 = 100% (base)
            $table->boolean('is_base')->default(false);  // true only for "Bangunan"
            $table->boolean('is_default')->default(false);
            $table->string('note')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('calc_building_types', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();       // standar | optimal | premium
            $table->string('name');
            $table->bigInteger('price_per_m2');    // 5500000 ...
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('calc_components', function (Blueprint $table) {
            $table->id();
            $table->string('name');                // Struktur, Dinding, ...
            $table->string('standar');
            $table->string('optimal');
            $table->string('premium');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('calc_zonasi', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();      // R-1 ... R-5
            $table->string('name')->nullable();
            $table->decimal('kdb', 6, 4);          // 0.6000
            $table->decimal('klb', 6, 4);          // 1.2000 (ratio)
            $table->decimal('ktb', 6, 4);
            $table->decimal('rth', 6, 4);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('calc_size_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();       // minimum ... eksekutif
            $table->string('name');                // Minimum ... Eksekutif (matches JSON tier names)
            $table->string('description')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('calc_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('category');            // service | public | private | luxury
            $table->string('name');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('calc_room_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('calc_rooms')->cascadeOnDelete();
            $table->foreignId('size_tier_id')->constrained('calc_size_tiers')->cascadeOnDelete();
            $table->decimal('panjang', 6, 2);
            $table->decimal('lebar', 6, 2);
            $table->decimal('area', 8, 2);         // = panjang * lebar (full precision, NOT display-rounded)
            $table->timestamps();
            $table->unique(['room_id', 'size_tier_id']);
        });

        Schema::create('calc_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();       // dana_darurat_pct, sirkulasi_pct, toleransi_default
            $table->string('value');
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calc_room_areas');
        Schema::dropIfExists('calc_rooms');
        Schema::dropIfExists('calc_size_tiers');
        Schema::dropIfExists('calc_zonasi');
        Schema::dropIfExists('calc_components');
        Schema::dropIfExists('calc_building_types');
        Schema::dropIfExists('calc_allocations');
        Schema::dropIfExists('calc_factor_options');
        Schema::dropIfExists('calc_factor_groups');
        Schema::dropIfExists('calc_settings');
    }
};
