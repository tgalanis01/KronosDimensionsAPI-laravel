<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeAvailability extends Model
{
    protected $table = 'employee_availability';
    protected $fillable = ['employee_id', 'accepted', 'unavailable', 'refused', 'review_status'];
}
