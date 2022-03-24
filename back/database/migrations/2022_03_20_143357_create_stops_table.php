<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stops', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('moveType_id');
            $table->unsignedBigInteger('driver_id');
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('cancellationReason_id')->nullable();
            $table->dateTime("date_order");
            $table->integer("index");
            $table->text("address");
            $table->string("lat");
            $table->string("long");
            $table->text("comment_cancellation")->nullable();
            $table->text("comment_delivery")->nullable();
            $table->text("comment_pickup")->nullable();
            $table->text("photo_cancellation")->nullable();
            $table->text("photo_delivery")->nullable();
            $table->text("photo_pickup")->nullable();
            $table->text("signature_delivery")->nullable();
            $table->text("signature_pickup")->nullable();
            
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders');
            $table->foreign('moveType_id')->references('id')->on('move_type');
            $table->foreign('driver_id')->references('id')->on('drivers');
            $table->foreign('status_id')->references('id')->on('status');
            $table->foreign('cancellationReason_id')->references('id')->on('cancellation_reason');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stops');
    }
}
