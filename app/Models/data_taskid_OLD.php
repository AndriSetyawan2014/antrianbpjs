<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class data_taskid extends Model
{
    use HasFactory;

    protected $table = 'data_taskids';

    protected $fillable = [
        'kodebooking',
        'waktu',
        'taskid',
        'idpendaftaran',
        'tanggal',
        'jam',
        'code',
        'message',
        'request',
        'response',
        'reupload',
    ];

    public $timestamps = true;
}
