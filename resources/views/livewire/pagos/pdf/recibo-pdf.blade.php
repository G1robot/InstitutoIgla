<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Pago - {{ $datosRecibo['nro_recibo'] }}</title>
    <style>
        /* Tipografía y márgenes para hoja tamaño Carta */
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

        /* Caja Gris de Datos (Estudiante y Fecha) */
        .info-box { background-color: #f9fafb; border: 1px solid #e5e7eb; padding: 6px; margin-bottom: 10px; border-radius: 4px; }
        .info-label { font-size: 9px; color: #6b7280; font-weight: bold; text-transform: uppercase; }
        .info-data { font-size: 11px; color: #111; }

        /* Tabla de Items */
        .items-table { margin-bottom: 10px; margin-top: 10px; }
        .items-table th { border-bottom: 2px solid #222; padding: 4px 0; font-size: 11px; text-align: left; color: #444;}
        .items-table th.right { text-align: right; }
        .items-table td { border-bottom: 1px dashed #ccc; padding: 6px 0; vertical-align: top; }

        /* Totales */
        .totals-container { width: 100%; margin-bottom: 15px; }
        .totals-table { width: 100%; }
        .totals-table td { padding: 3px 0; font-size: 12px; }
        .tot-line-top td { border-top: 2px solid #222; padding-top: 4px; font-weight: 900; font-size: 14px; color: #000; }
        
        /* Pie de página */
        .footer { text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 6px; margin-top: 15px;}
    </style>
</head>
<body>

    {{-- 1. Cabecera con Logo --}}
    <table class="header-table">
        <tr>
            <td width="25%" class="text-left" valign="middle">
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
            <td class="text-left"><h2 class="comprobante-title">Comprobante de Pago</h2></td>
            <td class="text-right"><span style="font-size: 13px;">Nro: <strong style="font-size: 15px;">{{ $datosRecibo['nro_recibo'] }}</strong></span></td>
        </tr>
    </table>

    {{-- 3. Caja de Datos (Izquierda y Derecha) --}}
    <div class="info-box">
        <table>
            <tr>
                <td width="50%" valign="top">
                    <span class="info-label">Estudiante:</span>
                    <span class="info-data">{{ $datosRecibo['estudiante'] }}</span><br>
                    <span class="info-label">CI:</span> <span style="font-size: 13px;">{{ $datosRecibo['ci'] }}</span>
                </td>
                <td width="50%" class="text-right" valign="top">
                    <span class="info-label">Fecha de emisión:</span>
                    <span class="info-data">{{ $datosRecibo['fecha'] }}</span><br>
                    <span class="info-label">Cajero(a):</span> <span style="font-size: 13px;">{{ $datosRecibo['cajero'] }}</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- 4. Detalle Académico (Plan y Cuota en una sola línea) --}}
    <table>
        <tr>
            <td width="50%" class="text-left">
                <span class="info-label">Plan:</span> <span style="font-size: 13px;">{{ $datosRecibo['plan'] }}</span>
            </td>
            <td width="50%" class="text-right">
                <span class="info-label">Cuota a pagar:</span> <strong style="font-size: 14px; border-bottom: 1px solid #111; padding-bottom: 2px;">{{ $datosRecibo['cuota'] }}</strong>
            </td>
        </tr>
    </table>

    {{-- 5. Tabla de Importes --}}
    <table class="items-table">
        <thead>
            <tr>
                <th>Detalle de Transacción</th>
                <th class="right">Importe</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-left">
                    <strong style="font-size: 10px;">Abono a cuota</strong><br>
                    <span style="font-size: 11px; color: #666;">Vía: {{ $datosRecibo['metodos'] }}</span>
                </td>
                <td class="text-right" style="font-weight: bold; font-family: monospace; font-size: 16px;">
                    {{ number_format($datosRecibo['monto_pagado'], 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- 6. Totales (Empujados a la derecha usando columnas invisibles) --}}
    <table class="totals-container">
        <tr>
            {{-- Columna vacía para empujar a la derecha --}}
            <td width="50%"></td> 
            
            {{-- Columna de totales --}}
            <td width="50%">
                <table class="totals-table">
                    <tr class="tot-line-top">
                        <td class="text-left">TOTAL ABONADO Bs:</td>
                        <td class="text-right">{{ number_format($datosRecibo['monto_pagado'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-left" style="color: #555;">Efectivo/Ingresado:</td>
                        <td class="text-right" style="color: #555;">{{ number_format($datosRecibo['ingresado'] ?? $datosRecibo['monto_pagado'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-left" style="color: #555;">Cambio:</td>
                        <td class="text-right" style="color: #555;">{{ number_format($datosRecibo['cambio'] ?? 0, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- 7. Pie de página --}}
    <div class="footer">
        Conserve este comprobante para cualquier reclamo.<br>
        <strong style="color: #111; font-size: 12px;">¡Gracias por su pago!</strong>
    </div>

</body>
</html>