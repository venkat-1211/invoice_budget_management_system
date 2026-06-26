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
        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->binary('uuid', 16)->unique();
            $table->string('payment_number', 50)->unique();
            $table->morphs('payable'); // sales_invoice or purchase_invoice
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 50); // cash, bank_transfer, credit_card, check, online
            $table->string('reference', 100)->nullable();
            $table->string('transaction_id', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'created_at'], 'idx_status_created');
            $table->index(['payable_type', 'payable_id'], 'idx_payable');
            $table->index(['payment_date', 'payment_method'], 'idx_date_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
