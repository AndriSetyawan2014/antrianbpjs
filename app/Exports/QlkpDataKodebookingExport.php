<?php

namespace App\Exports;

use App\Models\DataKodebooking;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class QlkpDataKodebookingExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return (new DataKodebooking([], 'QLKP'))->get();
    }

    public function headings(): array
    {
        return [
            'ID', 
            'No Pendaftaran', 
            'No RM', 
            'Kode Booking', 
            'Cara Bayar', 
            'No Antrian',
            'ID Jenis Kunjungan',
            'Tanggal Periksa',
            'Pasien Lama',
            'No JKN',
            'NIK',
            'No Telepon',
            'Nomor Referensi',
            'Quota JKN',
            'Quota JKN Sisa',
            'Quota Non-JKN',
            'Quota Non-JKN Sisa',
            'Estimasi Dilayani',
            'Kode Dokter BPJS',
            'Nama Dokter',
            'Kode Unit',
            'Nama Unit',
            'Jam Mulai',
            'Jam Akhir',
            'Code',
            'Message',
            'Request',
            'Response',
            'Reupload',
            'Created At',
            'Updated At',
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->idpendaftaran,
            $row->norm,
            $row->kodebooking,
            $row->carabayar,
            $row->noantrian,
            $row->idjeniskunjungan,
            $row->tanggalperiksa,
            $row->ispasienlama,
            $row->nojkn,
            $row->nik,
            $row->notelpon,
            $row->nomorreferensi,
            $row->quota_jkn,
            $row->quota_jkn_sisa,
            $row->quota_nonjkn,
            $row->quota_nonjkn_sisa,
            $row->estimasidilayani,
            $row->bpjs_kodedokter,
            $row->namadokter,
            $row->kodeunit,
            $row->namaunit,
            $row->jammulai,
            $row->jamakhir,
            $row->code,
            $row->message,
            $row->request,
            $row->response,
            $row->reupload,
            $row->created_at,
            $row->updated_at,
        ];
    }
}
