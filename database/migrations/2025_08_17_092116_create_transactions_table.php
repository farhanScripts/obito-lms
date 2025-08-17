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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('booking_trx_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pricing_id')->constrained('pricings')->cascadeOnDelete();
            $table->integer('sub_total_amount'); // Subtotal amount for the transaction
            $table->integer('grand_total_amount'); // Grand total amount after any discounts or fees
            $table->integer('total_tax_amount'); // Total tax amount applied to the transaction
            $table->boolean('is_paid'); // Indicates if the transaction has been paid
            $table->string('payment_type');
            $table->string('proof')->nullable(); // Optional proof of payment, if applicable
            $table->date('started_at'); // Date when the transaction started, if applicable
            $table->date('ended_at');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
