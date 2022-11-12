<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('orderItem')) {
            Schema::create('orderItem', function (Blueprint $table) {
                $table->increments('orderItemId');
                $table->char('orderItemProductCode', 60)->comment('code from table product')->nullable();
                $table->integer('orderItemOrderId')->comment('id from table order')->nullable();
                $table->integer('orderItemUserId')->comment('id from table user')->nullable();
                $table->enum('orderItemPaymentMethod', ['cop', 'cod', 'pas', 'trf'])->comment('COP: cash on pickup, COD: cash on delievery, pas: pay at store, trf: transfer')->nullable();
                $table->integer('orderItemTotalQty')->comment('total Qty')->default(0);
                $table->integer('orderItemTotal')->comment('total: qty * harga')->default(0);
                $table->double('orderItemTotalWeight')->comment('total all item unit is g')->default(0);
                $table->enum('orderItemState', ['payment', 'waiting', 'onprogress', 'send', 'done', 'complain', 'return', 'cancel', 'failed', 'receive'])->comment('payment: waiting for payment, waiting: waiting confirmation')->default('payment');
                $table->timestampTz('orderItemCreatedDate')->useCurrent();
                $table->timestampTz('orderItemUpdatedDate')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
                $table->softDeletesTz('orderItemDeletedDate')->nullable();
                $table->index([
                    'orderItemId'
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
        Schema::dropIfExists('orderItem');
    }
}
