<?php

namespace App\Exports;

use App\Models\data_taskid;
use App\Models\QlkpTaskId;  // Ganti dengan model yang sesuai
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class QlkpTaskIdExport implements FromCollection, WithHeadings
{
    /**
     * Mengambil data dari model untuk ekspor ke Excel
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Ambil data sesuai dengan query atau model yang kamu inginkan
        return (new data_taskid([], 'QLKP'))->get();
    }

    /**
     * Menambahkan header pada file Excel
     *
     * @return array
     */
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
