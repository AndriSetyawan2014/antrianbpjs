<?php

namespace App\Exports;

use App\Models\DataKodebooking;
use Maatwebsite\Excel\Concerns\FromCollection;

class DataKodeBookingExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return DataKodebooking::all();
    }
}
