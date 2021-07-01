<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class deskripsiProyek extends Model
{
    protected $table = 'deskripsi_proyeks';
    protected $fillable = [
        'deskripsi'
    ];

}
