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
            $table->foreignId('loan_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('transaction_id')->unique();
            $table->string('mpesa_receipt_number')->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', ['mpesa', 'cash', 'bank_transfer', 'other'])->default('mpesa');
            $table->enum('status', ['pending', 'completed', 'failed', 'reversed'])->default('pending');
            $table->timestamp('payment_date');
            $table->string('phone_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
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
