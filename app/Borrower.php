<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\CanResetPassword;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class Borrower extends Model
{
    // use Notifiable;
    protected $primaryKey = 'brw_id';
    protected $table = 'brw_users';
    protected $guard = 'borrower';

    protected $fillable = [
        'username', 'email', 'password', 'email_verif','ref_number', 'status'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];
}
