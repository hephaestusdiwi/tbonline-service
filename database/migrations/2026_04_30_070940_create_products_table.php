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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('alternative_name')->nullable();
            $table->unsignedBigInteger('classification_id')->nullable();
            $table->string('category')->nullable();
            $table->string('variant_label')->nullable();
            $table->string('variant_names')->nullable();
            $table->string('alternative_variant_names')->nullable();
            $table->string('collections')->nullable();
            $table->string('brand')->nullable();
            $table->string('condition_id')->default('N');
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->decimal('buy_price', 15, 2)->nullable();
            $table->decimal('market_price', 15, 2)->nullable();
            $table->decimal('sell_price', 15, 2)->default(0);
            $table->decimal('pos_sell_price', 15, 2)->nullable();
            $table->tinyInteger('pos_sell_price_dynamic')->default(0);
            $table->decimal('comission', 15, 2)->default(0);
            $table->tinyInteger('track_inventory')->default(1);
            $table->integer('stock_qty')->default(0);
            $table->integer('hold_qty')->default(0);
            $table->integer('low_stock_alert')->default(2);
            $table->string('uom')->nullable();
            $table->integer('qty_fast_moving')->default(0);
            $table->decimal('weight_kg', 10, 3)->nullable();
            $table->integer('loyalty_points')->default(0);
            $table->tinyInteger('published')->default(1);
            $table->tinyInteger('pos_hidden')->default(0);
            $table->text('description')->nullable();
            $table->text('photo_1')->nullable();
            $table->text('photo_2')->nullable();
            $table->text('photo_3')->nullable();
            $table->text('photo_4')->nullable();
            $table->text('photo_5')->nullable();
            $table->text('photo_6')->nullable();
            $table->text('photo_7')->nullable();
            $table->text('photo_8')->nullable();
            $table->text('photo_9')->nullable();
            $table->text('photo_10')->nullable();
            $table->text('notes')->nullable();
            $table->string('tax_free_item')->default('No');
            $table->timestamps();
 
            $table->index('name');
            $table->index('category');
            $table->index('sku');
            $table->index('published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
