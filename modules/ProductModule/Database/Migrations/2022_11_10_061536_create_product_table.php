<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('product')) {
            Schema::create('product', function (Blueprint $table) {
                $table->bigIncrements('productId');
                $table->integer('productUserClientId')->comment('id from table user client')->nullable();
                $table->string('productName')->comment('name: Indomie Rebus')->nullable();
                $table->char('productCode', 60)->comment('sku code')->nullable();
                $table->integer('productPrice')->comment('retailer price')->nullable();
                $table->smallInteger('productMinOrder')->comment('min order')->default(1);
                $table->integer('productStock')->comment('numeric')->nullable();
                $table->smallInteger('productWeight')->comment('unit gram')->nullable();
                $table->timestampTz('productCreatedDate')->useCurrent();
                $table->timestampTz('productUpdatedDate')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
                $table->softDeletesTz('productDeletedDate')->nullable();
                $table->index([
                    'productCode',
                    'productId'
                ]);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product');
    }
}
