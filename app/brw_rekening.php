<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Brw_rekening extends Model
{
    protected $table = 'brw_rekening';
    protected $fillable = [
        'brw_id', 'va_number','total_plafon', 'total_terpakai', 'total_sisa'
    ];

}
