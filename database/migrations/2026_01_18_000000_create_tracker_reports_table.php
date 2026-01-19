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
        Schema::create('tracker_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_id')->unique();
            $table->string('fingerprint')->nullable();
            $table->string('type');
            $table->string('level');
            $table->json('payload');
            $table->json('scope')->nullable();
            $table->json('metrics')->nullable();
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracker_reports');
    }
};
