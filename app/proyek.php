<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Interfaces\Investable;

class Proyek extends Model
{
    protected $table='proyek';




    protected $dates=['tgl_mulai', 'tgl_selesai'];
    public function pendanaanAktif() {
        return $this->hasMany('App\PendanaanAktif');
    }
    public function gambarProyek() {
        return $this->hasMany('App\GambarProyek');
    }
    public function pemilikPaket() {
        return $this->hasOne('App\PemilikPaket');
    }
    public function progressProyek() {
        return $this->hasMany('App\ProgressProyek');
    }
    public function log_payout_user() {
        return $this->hasOne('App\log_payout_user');
    }

    public function mutationProyek(){
        return $this->hasMany('App\ProjectMutation');
    }

    public function getInvestableIdentifier()
    {
        return $this->id;
    }

    public function getInvestableDescription()
    {
        return $this->nama;
    }

    public function getInvestablePrice()
    {
        return $this->harga_paket;
    }

    public $timestamps = true;
}
