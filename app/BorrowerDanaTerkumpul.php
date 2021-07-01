<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BorrowerDanaTerkumpul extends Model
{
    protected $table = 'pendanaan_aktif';
    protected $primaryKey = 'id';

    protected $fillable = [
        'investor_id', 'proyek_id', 'total_dana', 'nominal_awal','tanggal_invest', 'status', 'last_pay'
    ];
}
