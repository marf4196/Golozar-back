<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tooth_service_relationships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tooth_id');
            $table->unsignedBigInteger('service_id');
            $table->timestamps();


            $table->foreign('tooth_id')
                ->references('id')
                ->on('teeth')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('service_id')
                ->references('id')
                ->on('dental_services')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tooth_service_relationships');
    }
};
