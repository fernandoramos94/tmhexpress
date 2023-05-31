<?php

namespace App\Imports;

use App\Http\Controllers\ZipCodeController;
use App\Models\ZipCode;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ZipCodeImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        $zip_code = new ZipCodeController();

        $exists = $zip_code->getId($row["code"]);

        if($exists == 0){
            return new ZipCode([
                "code" => $row["code"],
            ]);
        }
    }
}
