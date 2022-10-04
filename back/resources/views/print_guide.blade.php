<html>

<head>

    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />

    <style type="text/css">
        
        * {
            margin: 0;
            padding: 0;
            font-family: 'Lato', sans-serif;
        }

        a.comment-indicator:hover+comment {
            background: #ffd;
            position: absolute;
            display: block;
            border: 1px solid black;
            padding: 0.5em;
        }

        a.comment-indicator {
            background: red;
            display: inline-block;
            border: 1px solid black;
            width: 0.5em;
            height: 0.5em;
        }

        comment {
            display: none;
        }
    </style>

</head>

<body>
    <table cellspacing="0" border="0" style="border-collapse: collapse; width: 100%; padding: 40px" >
    
        <tr>
            <td style="border: 1px solid; width: 50%; " 
                rowspan=2 align="center" valign=middle >
                <img src="./img/tmhexpress-negro.jpg" style="width: 70%;">
            </td>
            <td style="border: 1px solid; padding-left: 15px;"
                align="left" valign=middle bgcolor="#FFFFFF">
                <h2>ORIGEN:</h2>
                <span>{{$data->origin_address}}</span>
            </td>
        </tr>
        <tr>
            <td style="border: 1px solid; vertical-align: middle; " align="center" valign=center bgcolor="#FFFFFF">

            @if($data->date_order > date('Y-m-d', strtotime($data->created_at)))
                <h1>NEXT DAY</h1>
            @else 
                <h1>SAME DAY</h1>
            @endif
                
            </td>
        </tr>
        <tr>
            <td style="border: 1px solid; padding-left: 15px;"
                colspan=2 height="55" align="let" valign=middle bgcolor="#FFFFFF">
                <h2>DESTINO:</h2>
                <span>{{$data->destination_address}}</span>
            </td>
        </tr>
        <tr>
            <td style="border: 1px solid; padding-left: 15px;"
                rowspan=2 height="100" align="let" valign=middle bgcolor="#FFFFFF">
                <h3>ORDEN NR: {{$data->guide}}</h3>
                {!! DNS1D::getBarcodeHTML($data->guide, 'C39') !!}
            </td>
            <td style="border: 1px solid; padding-left: 15px;"
                align="left" valign=middle bgcolor="#FFFFFF">
                <h3>ORDEN NR: {{$data->guide}}</h3>
            </td>
        </tr>
        <tr>
            <td style="border: 1px solid; padding-left: 15px;"
                align="left" valign=middle bgcolor="#FFFFFF">
                <h3>FECHA: {{$data->created_at}}</h3>
            </td>
        </tr>
        <tr>
            <td style="border: 1px solid; padding-left: 15px;"
                height="50" align="left" valign=middle bgcolor="#FFFFFF">
                <h3>ORDEN NR: {{$data->guide}}</h3>
            </td>
            <td style="border: 1px solid; padding-left: 15px;" align="left" valign=middle bgcolor="#FFFFFF">
                <h3>PESO: {{$data->weight}} Kg</h3>
            </td>
        </tr>
        <tr >
            <td style="border: 1px solid; border-right: none;" height="100" align="center" valign=middle>
                <img src="./img/fragile.jpg" width="20%" height="auto">
                    <img src="./img/arrows.jpg" width="20%" height="auto">
            </td>
            <td style="border: 1px solid; border-left: none; text-align: left;" align="left" valign=middle>
                {!! DNS1D::getBarcodeHTML($data->guide, 'C39') !!}
            </td>
        </tr>
    </table>
    <!-- ************************************************************************** -->
</body>

</html>