<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPiket extends Model
{
    use HasFactory;

    protected $append = ['det_karyawan'];

    function getDetKaryawanAttribute(){
        return User::where('id','=',$this->id_karyawan)->first();
    }
}
