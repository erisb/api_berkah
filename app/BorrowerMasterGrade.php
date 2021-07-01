<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\CanResetPassword;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class BorrowerMasterGrade extends Model
{
    // use Notifiable;
    //protected $primaryKey = 'brw_id';
    protected $table = 'brw_master_grade';
    protected $guard = 'borrower';

    protected $fillable = [
        'grade_interval', 'grade_nilai', 'grade_keterangan'
    ];

}
