<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\CanResetPassword;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class BorrowerLogScorring extends Model
{
    // use Notifiable;
    protected $primaryKey = 'scorring_log_id';
    protected $table = 'brw_log_scorring';
    // protected $guard = 'borrower';

    protected $fillable = [
        'scorring_personal_id', 'scorring_pendanaan_id', 'user_create'
    ];

}
