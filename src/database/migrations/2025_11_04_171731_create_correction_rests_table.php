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
    
            $table->datetime('old_rest_start')->nullable();;
            $table->datetime('old_rest_end')->nullable();

            $table->datetime('new_rest_start');
            $table->datetime('new_rest_end');

            $table->tinyInteger('status')->default(1)->comment('1:申請中、2:承認済み');

            //承認者
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();

            //承認した日付
            $table->datetime('approved_at')->nullable();

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
