<?php
/**
 * Script de Verificación Rápida para Quotes for WooCommerce
 * 
 * INSTRUCCIONES:
 * 1. Crea un archivo llamado 'qwc-verify.php' en la raíz de WordPress
 * 2. Copia este código en el archivo
 * 3. Accede a: tu-sitio.com/qwc-verify.php?order_id=123 (reemplaza 123 con un Order ID real)
 * 4. Lee los resultados
 * 5. ELIMINA este archivo después de usarlo (por seguridad)
 */

// Cargar WordPress
require_once('wp-load.php');

// Solo admins pueden ver esto
if (!current_user_can('manage_woocommerce')) {
    die('Acceso denegado. Debes ser administrador.');
}

// Obtener Order ID de la URL
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id === 0) {
    die('Especifica un Order ID: ?order_id=123');
}

// Obtener el pedido
$order = wc_get_order($order_id);

if (!$order) {
    die('Pedido no encontrado con ID: ' . $order_id);
}

// Función para mostrar resultado con color
function show_result($label, $value, $expected = null, $is_critical = false) {
    $color = 'black';
    $status = '';
    
    if ($expected !== null) {
        if ($value == $expected) {
            $color = 'green';
            $status = ' ✓';
        } else {
            $color = $is_critical ? 'red' : 'orange';
            $status = ' ✗';
        }
    }
    
    echo "<tr>";
    echo "<td style='font-weight: bold; padding: 8px; border: 1px solid #ddd;'>{$label}{$status}</td>";
    echo "<td style='padding: 8px; border: 1px solid #ddd; color: {$color};'>" . esc_html($value) . "</td>";
    if ($expected !== null) {
        echo "<td style='padding: 8px; border: 1px solid #ddd; color: #666;'>" . esc_html($expected) . "</td>";
    } else {
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>-</td>";
    }
    echo "</tr>";
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Verificación QWC - Pedido #<?php echo $order_id; ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
        }
        h2 {
            color: #34495e;
            margin-top: 30px;
            border-left: 4px solid #3498db;
            padding-left: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background: #34495e;
            color: white;
            padding: 12px;
            text-align: left;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verificación de Quotes for WooCommerce</h1>
        <p><strong>Pedido ID:</strong> <?php echo $order_id; ?></p>
        <p><strong>Fecha:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>

        <h2>📋 Información Básica del Pedido</h2>
        <table>
            <thead>
                <tr>
                    <th>Campo</th>
                    <th>Valor Actual</th>
                    <th>Valor Esperado</th>
                </tr>
            </thead>
            <tbody>
                <?php
                show_result('Order Status', $order->get_status(), 'quoted', true);
                show_result('Payment Method', $order->get_payment_method(), 'quotes-gateway', true);
                show_result('Order Total', wc_price($order->get_total()));
                show_result('Customer Email', $order->get_billing_email());
                show_result('Is Editable', wc_order_is_editable($order) ? 'YES' : 'NO', 'YES', true);
                ?>
            </tbody>
        </table>

        <h2>🔧 Meta Data del Plugin</h2>
        <table>
            <thead>
                <tr>
                    <th>Meta Key</th>
                    <th>Valor Actual</th>
                    <th>Valor Esperado</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $quote_status = get_post_meta($order_id, '_quote_status', true);
                $qwc_quote = get_post_meta($order_id, '_qwc_quote', true);
                
                show_result('_quote_status', $quote_status ? $quote_status : 'NO ESTABLECIDO', 'quote-pending', true);
                show_result('_qwc_quote', $qwc_quote ? $qwc_quote : 'NO ESTABLECIDO', '1', true);
                ?>
            </tbody>
        </table>

        <h2>🎯 Verificación de Hooks</h2>
        <?php
        global $wp_filter;
        
        $hooks_to_check = array(
            'woocommerce_admin_order_data_after_order_details' => 'qwc_add_buttons_alternative',
            'woocommerce_checkout_order_processed' => 'set_order_status_to_quoted',
            'wc_order_is_editable' => 'qwc_make_quoted_orders_editable',
            'wc_order_statuses' => 'agregar_estado_pedido_quoted',
        );
        
        echo '<table>';
        echo '<thead><tr><th>Hook</th><th>Función</th><th>Estado</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($hooks_to_check as $hook => $function) {
            $found = false;
            if (isset($wp_filter[$hook])) {
                foreach ($wp_filter[$hook]->callbacks as $priority => $callbacks) {
                    foreach ($callbacks as $callback) {
                        if (is_string($callback['function']) && $callback['function'] === $function) {
                            $found = true;
                            break 2;
                        }
                    }
                }
            }
            
            echo '<tr>';
            echo '<td style="padding: 8px; border: 1px solid #ddd;"><code>' . $hook . '</code></td>';
            echo '<td style="padding: 8px; border: 1px solid #ddd;"><code>' . $function . '</code></td>';
            echo '<td style="padding: 8px; border: 1px solid #ddd; color: ' . ($found ? 'green' : 'red') . ';">';
            echo $found ? '✓ Registrado' : '✗ NO Registrado';
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
        ?>

        <h2>📁 Verificación de Archivos</h2>
        <table>
            <thead>
                <tr>
                    <th>Archivo</th>
                    <th>Estado</th>
                    <th>Tamaño</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $files_to_check = array(
                    WP_PLUGIN_DIR . '/ymk-quotes/quotes-woocommerce.php',
                    WP_PLUGIN_DIR . '/ymk-quotes/class-quotes-wc.php',
                    WP_PLUGIN_DIR . '/ymk-quotes/includes/class-quotes-payment-gateway.php',
                    WP_PLUGIN_DIR . '/ymk-quotes/assets/js/qwc-admin.js',
                );
                
                foreach ($files_to_check as $file) {
                    $exists = file_exists($file);
                    $size = $exists ? filesize($file) : 0;
                    
                    echo '<tr>';
                    echo '<td style="padding: 8px; border: 1px solid #ddd;"><code>' . basename($file) . '</code></td>';
                    echo '<td style="padding: 8px; border: 1px solid #ddd; color: ' . ($exists ? 'green' : 'red') . ';">';
                    echo $exists ? '✓ Existe' : '✗ NO Existe';
                    echo '</td>';
                    echo '<td style="padding: 8px; border: 1px solid #ddd;">' . ($exists ? number_format($size) . ' bytes' : '-') . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>

        <h2>💊 Diagnóstico y Recomendaciones</h2>
        <?php
        $errors = array();
        $warnings = array();
        $success = true;

        // Verificar estado del pedido
        if ($order->get_status() !== 'quoted') {
            $errors[] = "El estado del pedido es '<strong>" . $order->get_status() . "</strong>' pero debería ser '<strong>quoted</strong>'.";
            $success = false;
        }

        // Verificar método de pago
        if ($order->get_payment_method() !== 'quotes-gateway') {
            $errors[] = "El método de pago es '<strong>" . $order->get_payment_method() . "</strong>' pero debería ser '<strong>quotes-gateway</strong>'.";
            $success = false;
        }

        // Verificar quote_status
        if (empty($quote_status)) {
            $errors[] = "El meta '<strong>_quote_status</strong>' no está establecido.";
            $success = false;
        }

        // Verificar qwc_quote
        if ($qwc_quote !== '1') {
            $warnings[] = "El meta '<strong>_qwc_quote</strong>' no está establecido correctamente.";
        }

        // Verificar si es editable
        if (!wc_order_is_editable($order)) {
            $errors[] = "El pedido NO es editable. Los botones no aparecerán.";
            $success = false;
        }

        // Verificar JavaScript
        if (!file_exists(WP_PLUGIN_DIR . '/ymk-quotes/assets/js/qwc-admin.js')) {
            $errors[] = "El archivo JavaScript '<strong>qwc-admin.js</strong>' no existe. Los botones no funcionarán.";
            $success = false;
        }

        if ($success && empty($warnings)) {
            echo '<div class="success">';
            echo '<strong>✓ ¡Todo está correcto!</strong><br>';
            echo 'El pedido está configurado correctamente. Los botones deberían aparecer en la página de edición del pedido.';
            echo '</div>';
        } else {
            if (!empty($errors)) {
                echo '<div class="error">';
                echo '<strong>✗ Errores Críticos Encontrados:</strong><ul>';
                foreach ($errors as $error) {
                    echo '<li>' . $error . '</li>';
                }
                echo '</ul></div>';
            }

            if (!empty($warnings)) {
                echo '<div class="warning">';
                echo '<strong>⚠ Advertencias:</strong><ul>';
                foreach ($warnings as $warning) {
                    echo '<li>' . $warning . '</li>';
                }
                echo '</ul></div>';
            }
        }
        ?>

        <h2>🔧 Soluciones Rápidas</h2>
        
        <?php if ($order->get_status() !== 'quoted'): ?>
        <div class="info">
            <strong>Corregir Estado del Pedido:</strong><br>
            Ejecuta esta consulta SQL en phpMyAdmin:
            <pre><code>UPDATE <?php echo $GLOBALS['wpdb']->posts; ?> 
SET post_status = 'wc-quoted' 
WHERE ID = <?php echo $order_id; ?> AND post_type = 'shop_order';</code></pre>
        </div>
        <?php endif; ?>

        <?php if (empty($quote_status)): ?>
        <div class="info">
            <strong>Establecer Quote Status:</strong><br>
            Ejecuta esta consulta SQL en phpMyAdmin:
            <pre><code>INSERT INTO <?php echo $GLOBALS['wpdb']->postmeta; ?> (post_id, meta_key, meta_value) 
VALUES (<?php echo $order_id; ?>, '_quote_status', 'quote-pending')
ON DUPLICATE KEY UPDATE meta_value = 'quote-pending';</code></pre>
        </div>
        <?php endif; ?>

        <div class="info">
            <strong>Enlace Directo al Pedido:</strong><br>
            <a href="<?php echo admin_url('post.php?post=' . $order_id . '&action=edit'); ?>" target="_blank">
                Editar Pedido #<?php echo $order_id; ?>
            </a>
        </div>

        <p style="margin-top: 40px; padding-top: 20px; border-top: 2px solid #ddd; color: #999; font-size: 12px;">
            <strong>⚠️ IMPORTANTE:</strong> Elimina este archivo (<code>qwc-verify.php</code>) después de usarlo por seguridad.
        </p>
    </div>
</body>
</html>