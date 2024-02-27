<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TimeOffSchedule extends Model
{

    protected $primaryKey = 'schedule_id';
    protected $fillable = ['schedule_id','kronos_id','employee_number','start_date','start_time','end_date','end_time','start_datetime','end_datetime','locked','posted','generated','deleted','pay_code_id','pay_code_name'];

}
