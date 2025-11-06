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
                $table->id();
                $table->foreignId('payment_batch_id')->constrained()->cascadeOnDelete();
                $table->date('payment_date');
                $table->string('reference')->index();
                $table->string('customer_name')->nullable();
                $table->string('customer_email')->index();
                $table->string('currency', 10);
                $table->decimal('amount', 18, 6);
                $table->decimal('amount_usd', 18, 6)->nullable();
                $table->boolean('processed')->default(false)->index();
                $table->timestamp('processed_at')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->unique(['reference','customer_email']); // typical dedupe key
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
