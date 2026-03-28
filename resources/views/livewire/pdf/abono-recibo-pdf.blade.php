<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Pago - {{ $datosRecibo['nro_recibo'] }}</title>
    <style>
        /* Tipografía y márgenes para hoja tamaño Carta/Media Carta */
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

        /* Concepto del Pago */
        .concepto-box { margin-bottom: 15px; }
        .concepto-title { font-size: 10px; font-weight: bold; color: #666; text-transform: uppercase; border-bottom: 1px solid #ddd; padding-bottom: 2px; margin-bottom: 5px; }
        .concepto-desc { font-size: 13px; font-weight: bold; color: #000; text-transform: uppercase;}
        .concepto-sub { font-size: 11px; color: #555; font-weight: bold; margin-top: 3px; }

        /* Contenedor de Totales */
        .totales-container { width: 100%; border-top: 2px solid #222; padding-top: 8px; margin-bottom: 20px; }
        .totales-table { width: 100%; }
        .totales-table td { padding: 3px 0; }
        
        .tot-label { font-size: 12px; font-weight: bold; color: #555; }
        .tot-val { font-size: 12px; font-weight: bold; text-align: right; }
        
        .tot-main-label { font-size: 14px; font-weight: 900; color: #000; }
        .tot-main-val { font-size: 14px; font-weight: 900; color: #000; text-align: right; }
        
        .deuda-line td { border-top: 1px dashed #ccc; padding-top: 6px; margin-top: 4px; }
        .text-red { color: #d32f2f; }
        .text-green { color: #2e7d32; }

        /* Pie de página */
        .footer { text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 10px; margin-top: 20px;}
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
            <td class="text-left"><h2 class="comprobante-title">Comprobante de Pago</h2></td>
            <td class="text-right"><span style="font-size: 13px;">Nro: <strong style="font-size: 15px;">{{ $datosRecibo['nro_recibo'] }}</strong></span></td>
        </tr>
    </table>

    {{-- 3. Caja de Datos (Izquierda y Derecha) --}}
    <div class="info-box">
        <table>
            <tr>
                <td width="50%" valign="top">
                    <span class="info-label">Estudiante:</span><br>
                    <span class="info-data" style="font-size: 12px;">{{ $datosRecibo['estudiante'] }}</span><br>
                    <span class="info-label">CI:</span> <span class="info-data">{{ $datosRecibo['ci'] }}</span>
                </td>
                <td width="50%" class="text-right" valign="top">
                    <span class="info-label">Fecha y Hora:</span><br>
                    <span class="info-data">{{ $datosRecibo['fecha'] }}</span><br>
                    <span class="info-label">Cajero(a):</span> <span class="info-data">{{ $datosRecibo['cajero'] }}</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- 4. Concepto del Pago --}}
    <div class="concepto-box">
        <div class="concepto-title">Por Concepto De</div>
        <div class="concepto-desc">{{ $datosRecibo['concepto'] }}</div>
        <div class="concepto-sub">Abonado vía: {{ $datosRecibo['metodo_pago'] }}</div>
    </div>

    {{-- 5. Totales y Deuda --}}
    <div class="totales-container">
        <table class="totales-table">
            <tr>
                <td class="tot-label">Costo Total Original:</td>
                <td class="tot-val">{{ number_format($datosRecibo['costo_total'], 2) }} Bs</td>
            </tr>
            <tr>
                <td class="tot-main-label" style="padding-top: 8px;">MONTO ABONADO:</td>
                <td class="tot-main-val" style="padding-top: 8px;">{{ number_format($datosRecibo['monto_abonado_hoy'], 2) }} Bs</td>
            </tr>
            
            @if(isset($datosRecibo['cambio']) && $datosRecibo['cambio'] > 0)
            <tr>
                <td class="tot-label" style="font-size: 10px; color: #888;">Cambio Devuelto:</td>
                <td class="tot-val" style="font-size: 10px; color: #888;">{{ number_format($datosRecibo['cambio'], 2) }} Bs</td>
            </tr>
            @endif

            {{-- Bloque Dinámico de Saldo --}}
            <tr class="deuda-line">
                @if($datosRecibo['saldo_pendiente'] > 0)
                    <td class="tot-label text-red">NUEVO SALDO DEUDOR:</td>
                    <td class="tot-val text-red">{{ number_format($datosRecibo['saldo_pendiente'], 2) }} Bs</td>
                @else
                    <td class="tot-label text-green">ESTADO DE DEUDA:</td>
                    <td class="tot-val text-green">PAGADO COMPLETO</td>
                @endif
            </tr>
        </table>
    </div>

    {{-- 6. Pie de página --}}
    <div class="footer">
        <p style="margin: 0; font-weight: bold; color: #111;">¡Gracias por ser parte de IGLA!</p>
        <p style="margin: 3px 0 0 0;">Conserve este comprobante para cualquier reclamo.</p>
    </div>

</body>
</html>