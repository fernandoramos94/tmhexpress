<?php

namespace App\Imports;

use App\Http\Controllers\OrderController;
use App\Models\Order;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OrderImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $ord = new OrderController();
        $from = $row["latitud_origen"].",".$row["longitud_origen"];
        $to = $row["latitud_destino"] . "," . $row["longitud_destino"];
        $data = $ord->calculate($from, $to);

        $km = $data->rows[0]->elements[0]->distance->value / 1000;

        $fecha = date('Y-m-d');

        if(trim(strtolower($row["tipo_entrega"])) != 'hoy' ){
            $fecha = date('Y-m-d', strtotime($fecha. ' + 1 days'));
        }

        return new Order([
            "contact" => $row["contacto"],
            "identification" => $row["identificacion"],
            "phone" => $row["telefono"],
            "email" => $row["correo"],
            "type_product" => $row["tipo_producto"],
            "origin_address" => $row["direccion_origen"],
            "destination_address" => $row["direccion_destino"],
            "lat_origin" => $row["latitud_origen"],
            "long_origin" => $row["longitud_origen"],
            "lat_destination" => $row["latitud_destino"],
            "long_destination" => $row["longitud_destino"],
            "volume" => $row["volumencm3"],
            "weight" => $row["pesokg"],
            "pieces" => $row["numero_piezas"],
            "containt" => $row["breve_descripcion"],
            "date_order" => $fecha,
            "km" => $km,
            "status_id" => 1,
            "moveType_id" => 1,
            "user_id" => 1,
            "guide" => $ord->generateUniqueCode()
        ]);
    }
}
