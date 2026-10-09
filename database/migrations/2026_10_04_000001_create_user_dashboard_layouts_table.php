<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('user_dashboard_layouts')) {
            return;
        }

        Schema::create('user_dashboard_layouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->json('layout');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_dashboard_layouts');
    }
};
