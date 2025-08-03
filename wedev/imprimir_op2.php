<?php
// imprimir_op.php
session_start();

if (!isset($_SESSION['usuario'])) {
    die('No autorizado - Sesión no iniciada');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id']) || $_GET['id'] < 1) {
    die('ID de Orden de Pago inválido');
}

require_once 'funciones_op.php';
$conn = conectarDB();

try {
    $id = (int)$_GET['id'];
    
    $stmt = $conn->prepare("SELECT * FROM detallepagoxcpbte WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        die('No se encontró la Orden de Pago');
    }
    
    $orden = $result->fetch_assoc();
    
} catch (Exception $e) {
    die('Error al obtener los datos: ' . $e->getMessage());
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}

// Función para formatear montos
function formatearMonto($monto) {
    return '$' . number_format($monto, 2, ',', '.');
}

// Función para formatear fechas
function formatearFecha($fecha) {
    if (empty($fecha) || $fecha === '0000-00-00') return '-';
    return date('d/m/Y', strtotime($fecha));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Pago N° <?php echo htmlspecialchars($orden['nro_comprobante']); ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1cm 1.5cm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            line-height: 1.2;
            color: #333;
            background: white;
            height: 100vh;
            max-height: 27cm;
            overflow: hidden;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
        }
        
        .header h1 {
            font-size: 20px;
            color: #2c3e50;
            font-weight: bold;
            margin-bottom: 3px;
            letter-spacing: 1px;
        }
        
        .header .subtitle {
            font-size: 12px;
            color: #7f8c8d;
            font-weight: normal;
        }
        
        .documento-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 4px;
            border-left: 3px solid #3498db;
        }
        
        .documento-info .left,
        .documento-info .right {
            flex: 1;
        }
        
        .documento-info .right {
            text-align: right;
        }
        
        .info-item {
            margin-bottom: 3px;
        }
        
        .info-label {
            font-weight: bold;
            color: #2c3e50;
            display: inline-block;
            min-width: 90px;
            font-size: 9px;
        }
        
        .info-value {
            color: #555;
            font-weight: normal;
            font-size: 9px;
        }
        
        .section {
            margin-bottom: 12px;
        }
        
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 6px;
            padding-bottom: 3px;
            border-bottom: 1px solid #ecf0f1;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .two-columns {
            display: flex;
            gap: 15px;
        }
        
        .column {
            flex: 1;
        }
        
        .field-group {
            margin-bottom: 8px;
            padding: 6px 8px;
            background: #fafbfc;
            border-radius: 3px;
            border: 1px solid #e9ecef;
        }
        
        .field-label {
            font-weight: bold;
            color: #2c3e50;
            font-size: 8px;
            text-transform: uppercase;
            margin-bottom: 2px;
            letter-spacing: 0.2px;
        }
        
        .field-value {
            color: #333;
            font-size: 9px;
            min-height: 12px;
            word-wrap: break-word;
        }
        
        .monto-destacado {
            background: linear-gradient(135deg, #e8f5e8, #d4edda);
            border: 1px solid #28a745;
            padding: 8px;
            border-radius: 4px;
            text-align: center;
            margin: 8px 0;
        }
        
        .monto-destacado .label {
            font-size: 9px;
            font-weight: bold;
            color: #155724;
            margin-bottom: 3px;
        }
        
        .monto-destacado .valor {
            font-size: 14px;
            font-weight: bold;
            color: #28a745;
            font-family: 'Courier New', monospace;
        }
        
        .estado-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .estado-cancelado {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .estado-pendiente {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .footer {
            position: absolute;
            bottom: 1cm;
            left: 1.5cm;
            right: 1.5cm;
            padding-top: 8px;
            border-top: 1px solid #ecf0f1;
            text-align: center;
            color: #7f8c8d;
            font-size: 8px;
        }
        
        .signature-section {
            margin-top: 15px;
            margin-bottom: 60px;
            display: flex;
            justify-content: space-around;
        }
        
        .signature-box {
            text-align: center;
            width: 150px;
        }
        
        .signature-line {
            border-top: 1px solid #2c3e50;
            margin-bottom: 5px;
            height: 25px;
            position: relative;
        }
        
        .signature-label {
            font-size: 8px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
        }
        
        .blank-lines {
            margin-bottom: 40px;
        }
        
        .blank-line {
            border-bottom: 1px solid #ccc;
            margin-bottom: 15px;
            height: 20px;
        }
        
        @media print {
            body {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .section {
                page-break-inside: avoid;
                break-inside: avoid;
            }
            
            .header {
                page-break-after: avoid;
                break-after: avoid;
            }
            
            .footer {
                position: fixed;
                bottom: 0;
            }
        }
        
        .highlight-box {
            background: #fff9e6;
            border: 1px solid #ffc107;
            padding: 6px 8px;
            border-radius: 3px;
            margin: 5px 0;
        }
        
        .compact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
            margin-bottom: 10px;
        }
        
        .compact-item {
            padding: 5px 8px;
            background: #fafbfc;
            border-radius: 3px;
            border: 1px solid #e9ecef;
        }
        
        .observaciones-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 8px 10px;
            border-radius: 4px;
            margin: 8px 0;
            border-left: 4px solid #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ORDEN DE PAGO</h1>
        <div class="subtitle">Sistema de Gestión Administrativa</div>
    </div>
    
    <div class="documento-info">
        <div class="left">
            <div class="info-item">
                <span class="info-label">N° Comprobante:</span>
                <span class="info-value"><?php echo htmlspecialchars($orden['nro_comprobante']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Año:</span>
                <span class="info-value"><?php echo htmlspecialchars($orden['anio']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Expediente:</span>
                <span class="info-value"><?php echo htmlspecialchars($orden['expediente']); ?></span>
            </div>
        </div>
        <div class="right">
            <div class="info-item">
                <span class="info-label">Fecha Ordenado:</span>
                <span class="info-value"><?php echo formatearFecha($orden['fecha_ordenado']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Estado:</span>
                <span class="estado-badge <?php echo $orden['cancelado'] ? 'estado-cancelado' : 'estado-pendiente'; ?>">
                    <?php echo $orden['cancelado'] ? 'Cancelado' : 'Pendiente'; ?>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">TC:</span>
                <span class="info-value"><?php echo htmlspecialchars($orden['tc']); ?></span>
            </div>
        </div>
    </div>
    
    <div class="section">
        <div class="section-title">Descripción de la Orden</div>
        <div class="highlight-box">
            <div class="field-value" style="font-size: 10px; font-weight: 500; line-height: 1.3;">
                <?php echo nl2br(htmlspecialchars($orden['op_descrip'])); ?>
            </div>
        </div>
    </div>
    
    <div class="section">
        <div class="section-title">Información del Beneficiario</div>
        <div class="compact-grid">
            <div class="compact-item">
                <div class="field-label">Beneficiario</div>
                <div class="field-value"><?php echo htmlspecialchars($orden['beneficiario'] ?: '-'); ?></div>
            </div>
            <div class="compact-item">
                <div class="field-label">CUIT Beneficiario</div>
                <div class="field-value"><?php echo htmlspecialchars($orden['cuit_benef'] ?: '-'); ?></div>
            </div>
            <div class="compact-item">
                <div class="field-label">Cuenta de Pago</div>
                <div class="field-value"><?php echo htmlspecialchars($orden['cuenta_pago'] ?: '-'); ?></div>
            </div>
        </div>
        <div class="field-group">
            <div class="field-label">Nombre Cuenta de Pago</div>
            <div class="field-value"><?php echo htmlspecialchars($orden['nombre_cuenta_pago'] ?: '-'); ?></div>
        </div>
    </div>
    
    <div class="section">
        <div class="section-title">Información Financiera</div>
        <div class="two-columns">
            <div class="column">
                <div class="monto-destacado">
                    <div class="label">Monto Total</div>
                    <div class="valor" style="font-size: 16px;"><?php echo formatearMonto($orden['monto_total']); ?></div>
                </div>
            </div>
            <div class="column">
                <div class="monto-destacado" style="background: linear-gradient(135deg, #e3f2fd, #bbdefb); border-color: #2196f3;">
                    <div class="label" style="color: #0d47a1;">Monto Pagado</div>
                    <div class="valor" style="color: #1976d2; font-size: 16px;"><?php echo formatearMonto($orden['monto_pagado_total']); ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="section">
        <div class="section-title">Información de Pago</div>
        <div class="compact-grid">
            <div class="compact-item">
                <div class="field-label">Fecha de Pago</div>
                <div class="field-value"><?php echo formatearFecha($orden['fecha_pago']); ?></div>
            </div>
            <div class="compact-item">
                <div class="field-label">Tipo de Pago (TP)</div>
                <div class="field-value"><?php echo htmlspecialchars($orden['tp'] ?: '-'); ?></div>
            </div>
            <div class="compact-item">
                <div class="field-label">Pago Electrónico</div>
                <div class="field-value"><?php echo htmlspecialchars($orden['pago_e'] ?: '-'); ?></div>
            </div>
        </div>
        <div class="field-group">
            <div class="field-label">Tipo de Comprobante</div>
            <div class="field-value"><?php echo htmlspecialchars($orden['tipo_comprobante'] ?: '-'); ?></div>
        </div>
    </div>
    
    <div class="section">
        <div class="section-title">Observaciones</div>
        <div class="observaciones-box">
            <div class="field-value" style="font-size: 10px; line-height: 1.4; min-height: 30px;">
                <?php 
                if (!empty($orden['descrip_carga'])) {
                    echo nl2br(htmlspecialchars($orden['descrip_carga']));
                } else {
                    echo '-';
                }
                ?>
            </div>
        </div>
    </div>
    
    <!-- Líneas en blanco antes de las firmas -->
    <div class="blank-lines">
        <div class="blank-line"></div>
        <div class="blank-line"></div>
    </div>
    
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Autorizado por</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Recibido por</div>
        </div>
    </div>
    
    <div class="footer">
        <p>Documento generado el <?php echo date('d/m/Y \a \l\a\s H:i'); ?> por <?php echo htmlspecialchars($_SESSION['usuario']); ?></p>
        <p>Sistema de Gestión de Órdenes de Pago</p>
    </div>
    
    <script>
        // Auto-imprimir al cargar la página
        window.onload = function() {
            window.print();
        };
        
        // Cerrar ventana después de imprimir o cancelar
        window.onafterprint = function() {
            window.close();
        };
    </script>
</body>
</html>