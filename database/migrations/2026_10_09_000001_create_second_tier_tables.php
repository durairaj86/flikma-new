<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /** Delivery orders, rate sheets and master B/Ls (+ the link from house bills to their master). Guarded: skipped when present. */
    public function up(): void
    {
        $mk = function ($name, $cb) { if (!Schema::hasTable($name)) { Schema::create($name, $cb); } };
        $mk('delivery_orders', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('company_id')->nullable()->index();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('row_no', 50)->nullable();
            $t->unsignedInteger('unique_row_no')->nullable();
            $t->unsignedBigInteger('customer_id')->nullable()->index();
            $t->unsignedBigInteger('job_id')->nullable()->index();
            $t->date('do_date')->nullable();
            $t->date('delivery_date')->nullable();          // planned
            $t->dateTime('delivered_at')->nullable();       // actual (set when marked delivered)
            $t->string('pickup_location', 255)->nullable();
            $t->text('delivery_address')->nullable();
            $t->string('consignee', 255)->nullable();
            $t->string('contact_person', 150)->nullable();
            $t->string('contact_phone', 50)->nullable();
            $t->string('transporter', 150)->nullable();
            $t->string('vehicle_no', 50)->nullable();
            $t->string('driver_name', 150)->nullable();
            $t->string('driver_phone', 50)->nullable();
            $t->string('container_no', 255)->nullable();
            $t->unsignedInteger('packages')->nullable();
            $t->decimal('weight', 12, 2)->nullable();
            $t->text('cargo_description')->nullable();
            $t->string('received_by', 150)->nullable();
            $t->text('pod_notes')->nullable();
            $t->unsignedTinyInteger('status')->default(1);
            $t->text('remarks')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        $mk('rate_sheets', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('company_id')->nullable()->index();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('row_no', 50)->nullable();
            $t->unsignedInteger('unique_row_no')->nullable();
            $t->string('shipment_mode', 20)->default('sea');
            $t->string('origin', 150);
            $t->string('destination', 150);
            $t->unsignedBigInteger('carrier_id')->nullable()->index();
            $t->string('container_type', 100)->nullable();
            $t->string('basis', 30)->default('per_container');
            $t->string('currency', 10)->default('USD');
            $t->decimal('buy_rate', 14, 2)->default(0);
            $t->decimal('sell_rate', 14, 2)->default(0);
            $t->decimal('min_charge', 14, 2)->nullable();
            $t->unsignedSmallInteger('transit_days')->nullable();
            $t->date('valid_from')->nullable();
            $t->date('valid_to')->nullable();
            $t->boolean('is_active')->default(true);
            $t->text('remarks')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        $mk('master_bls', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('company_id')->nullable()->index();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('row_no', 50)->nullable();
            $t->unsignedInteger('unique_row_no')->nullable();
            $t->string('mbl_no', 100)->nullable();
            $t->string('shipment_mode', 20)->default('sea');
            $t->unsignedBigInteger('carrier_id')->nullable()->index();
            $t->string('vessel_flight', 150)->nullable();
            $t->string('voyage_no', 100)->nullable();
            $t->string('pol', 150)->nullable();
            $t->string('pod', 150)->nullable();
            $t->date('issue_date')->nullable();
            $t->date('etd')->nullable();
            $t->date('eta')->nullable();
            $t->string('shipper', 255)->nullable();
            $t->string('consignee', 255)->nullable();
            $t->string('freight_terms', 20)->default('prepaid');
            $t->unsignedTinyInteger('status')->default(1);
            $t->text('remarks')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        foreach (['seaway_bills','airway_bills'] as $t) {
            if (!Schema::hasColumn($t,'master_bl_id')) { DB::statement("ALTER TABLE `$t` ADD COLUMN `master_bl_id` BIGINT UNSIGNED NULL AFTER `job_id`");  }
        }
    }

    public function down(): void
    {
        // additive only: nothing is dropped
    }
};
