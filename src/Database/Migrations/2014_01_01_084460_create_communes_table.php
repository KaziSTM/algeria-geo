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
        Schema::create('communes', function (Blueprint $table) {
            $table->id();

            $table->string('post_code')->nullable();
            $table->string('name');
            $table->unsignedBigInteger('wilaya_id');
            $table->string('arabic_name');
            $table->decimal('latitude', 11, 8)->nullable();
            $table->decimal('longitude', 10, 8)->nullable();

            $table->foreign('wilaya_id')
                ->references('id')
                ->on('cities')
                ->onDelete('cascade');

            $table->index('name');
            $table->index('arabic_name');
            $table->index(['latitude', 'longitude']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('communes');
    }
};