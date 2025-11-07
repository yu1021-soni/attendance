<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorrectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();

            // 修正前の打刻時間
            $table->datetime('old_work_start')->nullable();
            $table->datetime('old_work_end')->nullable();

            $table->datetime('new_work_start');
            $table->datetime('new_work_end');
            $table->text('user_comment');

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
        Schema::dropIfExists('corrections');
    }
}
