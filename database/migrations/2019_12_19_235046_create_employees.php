<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('PEOPLE_PERSON_ID')->nullable()->index();
            $table->integer('PEOPLE_PERSON_NUMBER')->nullable()->index();
            $table->integer('PEOPLE_BADGE_NUMBER')->nullable();
            $table->string('PEOPLE_FIRST_NAME')->nullable();
            $table->string('PEOPLE_LAST_NAME')->nullable();
            $table->string('EMP_COMMON_FULL_NAME')->nullable();
            $table->string('PEOPLE_MANAGER_NAME')->nullable();
            $table->string('PEOPLE_EMAIL')->nullable();
            $table->string('PEOPLE_PHONE_NUMBER')->nullable();
            $table->string('EMP_COMMON_PRIMARY_ORG')->nullable();
            $table->string('EMP_COMMON_PRIMARY_JOB_TITLE')->nullable();
            $table->string('EMP_COMMON_PRIMARY_JOB_DESCRIPTION')->nullable();
            $table->date('PEOPLE_HIRE_DATE')->nullable();
            $table->date('PEOPLE_SENIORITY_DATE')->nullable();

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
        Schema::dropIfExists('employees');
    }
}
