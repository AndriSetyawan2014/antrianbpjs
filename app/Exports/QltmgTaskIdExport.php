<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class QltmgTaskIdExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return DB::table('qltmg_data_taskids')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Booking',
            'Waktu',
            'Task ID',
            'ID Pendaftaran',
            'Code',
            'Message',
            'Tanggal',
            'Jam',
            'Request',
            'Response',
            'Reupload',
        ];
    }
}
