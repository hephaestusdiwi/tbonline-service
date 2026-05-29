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
        Schema::create('product_option_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('position')->default(0);
            $table->timestamps();

            $table->index('product_id');
        });

        Schema::create('product_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_option_type_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->string('value');
            $table->unsignedTinyInteger('position')->default(0);
            $table->timestamps();
 
            $table->index('product_option_type_id');
        });

        // ─── 3. Buat tabel product_variants ──────────────────
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->string('sku')->nullable()->unique();
            $table->string('barcode')->nullable();
            $table->decimal('buy_price', 15, 2)->nullable();
            $table->decimal('sell_price', 15, 2)->nullable();
            $table->decimal('pos_sell_price', 15, 2)->nullable();
            $table->decimal('market_price', 15, 2)->nullable();
            $table->integer('stock_qty')->default(0);
            $table->integer('hold_qty')->default(0);
            $table->integer('low_stock_alert')->default(2);
            $table->integer('qty_fast_moving')->default(0);
            $table->decimal('weight_kg', 10, 3)->nullable();
            $table->text('photo')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
 
            $table->index('product_id');
            $table->index('sku');
        });

        Schema::create('product_variant_option_values', function (Blueprint $table) {
            $table->foreignId('product_variant_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('product_option_value_id')
                  ->constrained()
                  ->cascadeOnDelete();
 
            $table->primary(
                ['product_variant_id', 'product_option_value_id'],
                'variant_option_value_primary'
            );
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'variant_label',
                'variant_names',
                'alternative_variant_names',
                'stock_qty',
                'hold_qty',
                'low_stock_alert',
                'qty_fast_moving',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: kembalikan kolom lama ke products
        Schema::table('products', function (Blueprint $table) {
            $table->string('variant_label')->nullable();
            $table->string('variant_names')->nullable();
            $table->string('alternative_variant_names')->nullable();
            $table->integer('stock_qty')->default(0);
            $table->integer('hold_qty')->default(0);
            $table->integer('low_stock_alert')->default(2);
            $table->integer('qty_fast_moving')->default(0);
        });
 
        // Drop tabel baru (urutan terbalik karena foreign key)
        Schema::dropIfExists('product_variant_option_values');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_option_values');
        Schema::dropIfExists('product_option_types');
    }
};
