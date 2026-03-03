<?php

namespace App\Exports;

use App\Models\data_taskid;
use App\Models\TaskId; // Ganti dengan model yang sesuai
use Maatwebsite\Excel\Concerns\FromCollection;

class TaskIdExport implements FromCollection
{
    public function collection()
    {
        return data_taskid::all(); // Ambil semua data dari model
    }
}
