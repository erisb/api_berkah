<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class gambarProyek extends Model
{
    protected $table = 'gambar_proyek';
    protected $fillable = [
        'proyek_id', 'gambar'
    ];

}
