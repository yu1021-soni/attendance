<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorrectionRestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('correction_rests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('correction_id')->constrained()->cascadeOnDelete();
            $table->datetime('new_rest_start_1');
            $table->datetime('new_rest_end_1');
            $table->datetime('new_rest_start_2');
            $table->datetime('new_rest_end_2');
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
        Schema::dropIfExists('correction_rests');
    }
}
