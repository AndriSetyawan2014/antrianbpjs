<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
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
        return DB::table('qlkp_data_taskids')
            ->leftJoin('qlkp_data_kodebooking', 'qlkp_data_taskids.kodebooking', '=', 'qlkp_data_kodebooking.kodebooking')
            ->select(
                'qlkp_data_taskids.id',
                'qlkp_data_taskids.kodebooking',
                'qlkp_data_taskids.waktu',
                'qlkp_data_taskids.taskid',
                'qlkp_data_taskids.idpendaftaran',
                'qlkp_data_kodebooking.namadokter',
                'qlkp_data_kodebooking.namaunit as namapoli',
                DB::raw("CONCAT(COALESCE(qlkp_data_kodebooking.jammulai, ''), ' - ', COALESCE(qlkp_data_kodebooking.jamakhir, '')) as jadwal_rs"),
                'qlkp_data_taskids.code',
                'qlkp_data_taskids.message',
                'qlkp_data_taskids.tanggal',
                'qlkp_data_taskids.jam',
                'qlkp_data_taskids.request',
                'qlkp_data_taskids.response',
                'qlkp_data_taskids.reupload'
            )
            ->get();
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
            'Nama Dokter',
            'Nama Poli',
            'Jadwal Dokter (RS)',
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
