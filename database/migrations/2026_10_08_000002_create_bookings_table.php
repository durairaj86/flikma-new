<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /** Carrier bookings (sits between a quotation / job and the shipment). */
    public function up(): void
    {
        if (Schema::hasTable('bookings')) {
            return;
        }
        Schema::create('bookings', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('company_id')->nullable()->index();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('row_no', 50)->nullable();
            $t->unsignedInteger('unique_row_no')->nullable();
            $t->string('booking_ref', 100)->nullable();
            $t->string('shipment_mode', 20)->default('sea');
            $t->unsignedBigInteger('customer_id')->nullable()->index();
            $t->unsignedBigInteger('job_id')->nullable()->index();
            $t->unsignedBigInteger('carrier_id')->nullable()->index();
            $t->string('vessel_flight', 150)->nullable();
            $t->string('voyage_no', 100)->nullable();
            $t->string('pol', 150)->nullable();
            $t->string('pod', 150)->nullable();
            $t->date('booking_date')->nullable();
            $t->date('etd')->nullable();
            $t->date('eta')->nullable();
            $t->dateTime('cargo_cutoff')->nullable();
            $t->dateTime('doc_cutoff')->nullable();
            $t->string('container_type', 100)->nullable();
            $t->unsignedInteger('container_qty')->nullable();
            $t->decimal('gross_weight', 12, 2)->nullable();
            $t->decimal('volume', 12, 3)->nullable();
            $t->text('cargo_description')->nullable();
            $t->unsignedTinyInteger('status')->default(1);
            $t->text('remarks')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
