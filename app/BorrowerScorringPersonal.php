<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\CanResetPassword;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class BorrowerScorringPersonal extends Model
{
    // use Notifiable;
    protected $primaryKey = 'scorring_personal_id';
    protected $table = 'brw_scorring_personal';
    // protected $guard = 'borrower';

    protected $fillable = [
        'brw_id', 'nilai'
    ];

}
