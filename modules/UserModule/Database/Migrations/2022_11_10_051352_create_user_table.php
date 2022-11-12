<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('user')) {
            Schema::create('user', function (Blueprint $table) {
                $table->increments('userId');
                $table->integer('userClientId')->nullable();
                $table->string('userFullName')->comment('is alphabet')->nullable();
                $table->string('userPassword')->comment('hash from lumen')->nullable();
                $table->char('userName', 100)->comment('is alphabet')->nullable();
                $table->char('userToken', 100)->comment('is alphabet')->nullable();
                $table->timestampTz('userCreatedDate')->useCurrent();
                $table->timestampTz('userUpdatedDate')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
                $table->softDeletesTz('userDeletedDate')->nullable();
                $table->index([
                    'userName',
                    'userId'
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
        Schema::dropIfExists('user');
    }
}
