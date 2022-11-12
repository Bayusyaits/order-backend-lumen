<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('order')) {
            Schema::create('order', function (Blueprint $table) {
                $table->increments('orderId');
                $table->integer('orderUserId')->comment('id from table user')->nullable();
                $table->char('orderCustomerName', 50)->nullable();
                $table->char('orderNumber', 20)->comment('numeric: 89902010181')->nullable();
                $table->integer('orderTotalQty')->comment('total qty items')->default(0);
                $table->integer('orderTotalWeight')->comment('total weight items')->default(0);
                $table->integer('orderTotalCharge')->comment('total header: sum total items')->default(0);
                $table->timestampTz('orderTransactionDate')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
                $table->timestampTz('orderCreatedDate')->useCurrent();
                $table->timestampTz('orderUpdatedDate')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
                $table->softDeletesTz('orderDeletedDate')->nullable();
                $table->index([
                    'orderNumber',
                    'orderId'
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
        Schema::dropIfExists('order');
    }
}
