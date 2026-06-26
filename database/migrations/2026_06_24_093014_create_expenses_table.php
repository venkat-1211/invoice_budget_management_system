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
        Schema::create('expenses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->binary('uuid', 16)->unique();
            $table->string('expense_number', 50)->unique();
            $table->foreignId('budget_id')->nullable()->constrained('budgets')->onDelete('set null');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->date('expense_date');
            $table->string('category', 100);
            $table->string('payment_method', 50)->default('cash'); // cash, bank_transfer, credit_card, check
            $table->string('reference', 100)->nullable();
            $table->decimal('amount', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->text('description')->nullable();
            $table->json('attachments')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'created_at'], 'idx_status_created');
            $table->index(['budget_id', 'expense_date'], 'idx_budget_date');
            $table->index(['category', 'expense_date'], 'idx_category_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
