<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Invoice number unik: INV20260502001
            $table->string('invoice_number')->unique();

            // Customer
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();

            // Alamat
            $table->text('address');
            $table->string('subdistrict');
            $table->string('district');
            $table->string('city');
            $table->string('province');
            $table->string('postal_code')->nullable();
            $table->unsignedBigInteger('destination_id')->nullable();

            // Pengiriman
            $table->string('shipping_courier');
            $table->string('shipping_service');
            $table->string('shipping_name');
            $table->unsignedInteger('shipping_cost');
            $table->string('shipping_etd')->nullable();

            // Harga
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('total_price');

            // Catatan
            $table->text('notes')->nullable();

            // Status
            $table->enum('status', ['pending', 'success', 'cancelled'])->default('pending');
            $table->text('cancel_reason')->nullable();

            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name');
            $table->string('variant_label')->nullable();
            $table->string('variant_names')->nullable();
            $table->unsignedInteger('qty');
            $table->unsignedBigInteger('sell_price');
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};