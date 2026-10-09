<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /** Job containers selected on a waybill / airway bill / seaway bill (ids from job_containers). */
    public function up(): void
    {
        foreach (['waybills', 'airway_bills', 'seaway_bills'] as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'container_ids')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->json('container_ids')->nullable()->after('special_instructions');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['waybills', 'airway_bills', 'seaway_bills'] as $table) {
            if (Schema::hasColumn($table, 'container_ids')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('container_ids');
                });
            }
        }
    }
};
