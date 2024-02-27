<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeTimeOff extends Model
{
    protected $fillable = ['EMP_COMMON_FULL_NAME','SCH_PCE_START_DATE','SCH_PCE_START_TIME','SCH_PCE_END_DATE','SCH_PCE_END_TIME','PEOPLE_ACCRUAL_PROFILE_NAME','SCH_PCE_START_DATETIME','SCH_PCE_END_DATETIME','CALENDAR_EVENT_ID'];
}
