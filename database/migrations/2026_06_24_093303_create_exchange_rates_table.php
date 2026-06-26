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
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->binary('uuid', 16)->unique();
            $table->string('base_currency', 3);
            $table->string('target_currency', 3);
            $table->decimal('rate', 15, 6);
            $table->date('rate_date');
            $table->string('source', 50)->default('api'); // api, manual
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();
            $table->index(['status', 'created_at'], 'idx_status_created');
            $table->index(['base_currency', 'target_currency', 'rate_date'], 'idx_currency_date');
            $table->unique(['base_currency', 'target_currency', 'rate_date'], 'uniq_currency_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
