<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('moveType_id');
            $table->string("contact");
            $table->string("identification");
            $table->string("phone");
            $table->string("email");
            $table->text("destination_address");
            $table->text("origin_address");
            $table->string("type_product");
            $table->date("date_order");
            $table->string("long_destination");
            $table->string("lat_destination");
            $table->string("long_origin");
            $table->string("lat_origin");
            $table->integer("volume")->default(0);
            $table->integer("weight")->default(0);
            $table->integer("total")->default(0);
            $table->string("guide");
            $table->text("containt")->nullable();
            $table->integer("km")->nullable();
            $table->integer("pieces")->nullable();
            
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('status_id')->references('id')->on('status');
            $table->foreign('moveType_id')->references('id')->on('move_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
