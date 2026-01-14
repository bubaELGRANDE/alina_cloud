<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            html {
                font-family: sans-serif; 
                line-height: 1.15; 
                -webkit-text-size-adjust: 100%; 
                -ms-text-size-adjust: 100%; 
                -ms-overflow-style: scrollbar;
                -webkit-tap-highlight-color: transparent; 
                }
            body{
                margin: 0; 
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
                font-size: 1rem;
                font-weight: normal;
                line-height: 1.5;
                color: #212529;
                background: #8e9eab;
                padding: 5rem 0;
            }
            .contenido{background: #fff; width:60%; padding: 20px;}
            .contenido img{padding-right: 15px; vertical-align: -3px;}
            .footer{background: #004a87; color:#fff; width: 60%; text-align: center; padding: 20px; vertical-align:middle;}
            .footer img{vertical-align: -5px; height: 40px;}
            h1{color:#004a87;}
        </style>
    </head>
    <body>

        <table align='center' role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='margin: auto;'>
            <tbody>
            <tr>
                <td></td>
                    <th class='contenido'>
                        <h1><img src="cid:logo" alt="Cloud"> Alina Jewerly</h1>
                    </th>
                <td></td>
            </tr>
            </tbody>
        </table>
        <table align='center' role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='margin: auto;'>
            <tbody>
            <tr>
                <td></td>
                <td class='contenido'>
                    {{textoCorreo}}
                </td>
                <td></td>
            </tr>
            </tbody>
        </table>
        <table align='center' role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='margin: auto;'>
            <tbody>
            <tr>
                <td></td>
                <td class='footer'>
                    <img src="cid:logoBlanco" alt="Alina Jewerly" height="40px"> - <b>{{anio}}</b>
                </td>
                <td></td>
            </tr>
            </tbody>
        </table>
    </body>
</html>
