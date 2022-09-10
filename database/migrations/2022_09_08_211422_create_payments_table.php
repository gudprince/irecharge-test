<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->nullable();;
            $table->string('reference');
            $table->decimal('amount', 12, 2);
            $table->unsignedBigInteger('product_id');
            $table->string('payment_method');
            $table->string('currency');
            $table->string('customer_email');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('pending');
            $table->date('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
