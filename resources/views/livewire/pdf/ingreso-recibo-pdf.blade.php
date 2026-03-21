<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Ingreso - {{ $datosRecibo['nro_recibo'] }}</title>
    <style>
        /* Tipografía y márgenes súper comprimidos para hoja tamaño Carta */
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #111; margin: 0; padding: 20px 40px; line-height: 1.2; }
        table { width: 100%; border-collapse: collapse; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        
        /* Cabecera */
        .header-table { border-bottom: 2px dashed #999; padding-bottom: 5px; margin-bottom: 10px; }
        .logo { max-height: 45px; } 
        .title { font-size: 20px; font-weight: 900; letter-spacing: 1px; margin: 0; color: #000; text-transform: uppercase; }
        .subtitle { font-size: 11px; margin: 2px 0 0 0; color: #333; font-weight: bold; }
        .contact { font-size: 9px; color: #666; margin-top: 2px;}

        /* Título del Comprobante */
        .comprobante-header { margin-bottom: 8px; border-bottom: 1px solid #222; padding-bottom: 3px; }
        .comprobante-title { font-size: 14px; font-weight: bold; text-transform: uppercase; margin: 0; }

        /* Caja Gris de Datos */
        .info-box { background-color: #f9fafb; border: 1px solid #e5e7eb; padding: 6px; margin-bottom: 15px; border-radius: 4px; }
        .info-label { font-size: 9px; color: #6b7280; font-weight: bold; text-transform: uppercase; }
        .info-data { font-size: 11px; color: #111; font-weight: bold; }

        /* Concepto del Ingreso */
        .concepto-box { margin-bottom: 20px; }
        .concepto-title { font-size: 10px; font-weight: bold; color: #666; text-transform: uppercase; border-bottom: 1px solid #ddd; padding-bottom: 2px; margin-bottom: 5px; }
        .concepto-desc { font-size: 13px; font-weight: bold; color: #000; }
        .concepto-sub { font-size: 11px; color: #555; font-style: italic; margin-top: 3px; }

        /* Totales */
        .totals-container { width: 100%; margin-bottom: 30px; }
        .totals-table { width: 100%; }
        .tot-line-top td { border-top: 2px solid #222; border-bottom: 2px solid #222; padding: 6px 0; font-weight: 900; font-size: 14px; color: #000; background-color: #f9fafb; }

        /* Pie de página */
        .footer { text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 10px; margin-top: 30px;}
    </style>
</head>
<body>

    {{-- 1. Cabecera con Logo --}}
    <table class="header-table">
        <tr>
            <td width="25%" class="text-left" valign="middle">
                {{-- MAGIA DE DOMPDF: public_path --}}
                <img src="{{ public_path('img/LOGO_POTOSI_01.png') }}" class="logo" alt="Logo IGLA">
            </td>
            <td width="75%" class="text-right" valign="middle">
                <p class="title">IGLA POTOSÍ</p>
                <p class="subtitle">Instituto Técnico Gastronómico</p>
                <p class="contact">Telfs 74289575 | Calle Tarija #30, Potosí - Bolivia</p>
            </td>
        </tr>
    </table>

    {{-- 2. Título y Número --}}
    <table class="comprobante-header">
        <tr>
            <td class="text-left"><h2 class="comprobante-title">Comprobante de Ingreso</h2></td>
            <td class="text-right"><span style="font-size: 13px;">Nro: <strong style="font-size: 15px;">{{ $datosRecibo['nro_recibo'] }}</strong></span></td>
        </tr>
    </table>

    {{-- 3. Caja de Datos (Izquierda y Derecha) --}}
    <div class="info-box">
        <table>
            <tr>
                <td width="50%" valign="top">
                    <span class="info-label">Recibimos de:</span><br>
                    <span class="info-data" style="font-size: 13px;">{{ $datosRecibo['origen'] }}</span><br>
                    <span class="info-label">Ingresado a:</span> <span style="font-size: 11px; font-weight: normal;">{{ $datosRecibo['metodo_pago'] }}</span>
                </td>
                <td width="50%" class="text-right" valign="top">
                    <span class="info-label">Fecha y Hora:</span><br>
                    <span class="info-data">{{ $datosRecibo['fecha'] }}</span><br>
                    <span class="info-label">Cajero(a):</span> <span style="font-size: 12px; font-weight: normal;">{{ $datosRecibo['cajero'] }}</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- 4. Detalle del Ingreso --}}
    <div class="concepto-box">
        <div class="concepto-title">Por Concepto De</div>
        <div class="concepto-desc">{{ $datosRecibo['concepto'] }}</div>
        @if(isset($datosRecibo['descripcion']) && $datosRecibo['descripcion'])
            <div class="concepto-sub">{{ $datosRecibo['descripcion'] }}</div>
        @endif
    </div>

    {{-- 5. Totales --}}
    <table class="totals-container">
        <tr>
            {{-- Columna vacía para empujar a la derecha --}}
            <td width="50%"></td> 
            
            {{-- Columna de totales --}}
            <td width="50%">
                <table class="totals-table">
                    <tr class="tot-line-top">
                        <td class="text-left" style="padding-left: 10px;">IMPORTE TOTAL:</td>
                        <td class="text-right" style="padding-right: 10px;">{{ number_format($datosRecibo['monto'], 2) }} Bs</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- 6. Pie de página --}}
    <div class="footer">
        <strong style="color: #111; font-size: 11px;">Comprobante de ingreso válido para control interno.</strong>
    </div>

</body>
</html>