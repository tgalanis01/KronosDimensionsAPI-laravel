<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimeOffSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('time_off_schedules', function (Blueprint $table) {
            $table->integer('schedule_id')->primary();
            $table->integer('kronos_id');
            $table->integer('employee_number');
            $table->date('start_date');
            $table->time('start_time');
            $table->date('end_date');
            $table->time('end_time');
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->boolean('locked');
            $table->boolean('posted');
            $table->boolean('generated');
            $table->boolean('deleted');
            $table->integer('pay_code_id');
            $table->string('pay_code_name');
            $table->boolean('oulook_updated')->default(false);
            $table->timestamps();

            $table->index(['employee_number','start_datetime','end_datetime']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('time_off_schedules');
    }
}
