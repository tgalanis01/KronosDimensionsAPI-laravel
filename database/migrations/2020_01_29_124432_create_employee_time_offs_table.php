<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeTimeOffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_time_offs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('EMP_COMMON_FULL_NAME');
            $table->date('SCH_PCE_START_DATE');
            $table->time('SCH_PCE_START_TIME');
            $table->date('SCH_PCE_END_DATE');
            $table->time('SCH_PCE_END_TIME');
            $table->string('PEOPLE_ACCRUAL_PROFILE_NAME');
            $table->dateTime('SCH_PCE_START_DATETIME');
            $table->dateTime('SCH_PCE_END_DATETIME');
            $table->boolean('OUTLOOK_UPDATED')->default(false);
            $table->timestamps();


            $table->index(['EMP_COMMON_FULL_NAME','SCH_PCE_START_DATETIME','SCH_PCE_END_DATETIME']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_time_offs');
    }
}
