<!DOCTYPE html>
<html>

<head>
    <style>
        #customers {
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        #customers td,
        #customers th {
            border: 1px solid #ddd;
            padding: 8px;
        }

        #customers tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        #customers tr:hover {
            background-color: #ddd;
        }

        #customers th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            background-color: #04AA6D;
            color: white;
        }
    </style>
</head>

<body>

    <h1>Listado de ordenes</h1>

    <table id="customers">
        <tr>
            <th>Guia</th>
            <th>Cliente</th>
            <th>Identificacion del cliente</th>
            <th>Fecha crecion</th>
        </tr>
        @foreach($data as $info)
        <tr>
            <td>{{$info->guide}}</td>
            <td>{{$info->client_name}} {{$info->client_lastName}}</td>
            <td>{{$info->identification_number}}</td>
            <td>{{$info->created_at}}</td>
        </tr>
        @endforeach
    </table>

</body>

</html>