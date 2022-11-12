<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserClientTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('userClient')) {
            Schema::create('userClient', function (Blueprint $table) {
                $table->increments('userClientId');
                $table->char('userClientSignature', 100)->comment('alphanumeric')->nullable();
                $table->string('userClientPlatform')->nullable();
                $table->ipAddress('userClientIpAddress')->comment('ip address equivalent column.')->nullable();
                $table->enum('userClientStatus', ['reg', 'act', 'ina'])->comment('registered', 'active', 'inactive')->nullable();
                $table->timestampTz('userClientCreatedDate')->useCurrent();
                $table->timestampTz('userClientUpdatedDate')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
                $table->softDeletesTz('userClientDeletedDate')->nullable();
                $table->index([
                    'userClientSignature',
                    'userClientId'
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
        Schema::dropIfExists('userClient');
    }
}
