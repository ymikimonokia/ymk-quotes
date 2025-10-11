<?php
/**
 * Plugin Name: Quotes for WooCommerce
 * Description: This plugin allows you to convert your WooCommerce store into a quote only store. It will hide the prices for the products and not take any payment at Checkout. You can then setup prices for the items in the order and send a notification to the Customer.
 * Version: 2.5.1
 * Author: Mikel Marqués
 * Requires at least: 4.5
 * WC Requires at least: 3.0
 * WC tested up to: 6.5.1
 * Text Domain: quote-wc
 * Domain Path: /languages/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package Quotes For WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Quotes_WC' ) ) {
    include_once WP_PLUGIN_DIR . '/ymk-quotes/includes/qwc-functions.php';
    include_once WP_PLUGIN_DIR . '/ymk-quotes/includes/qwc-custom-order.php';
    include_once WP_PLUGIN_DIR . '/ymk-quotes/class-quotes-wc.php';
}

/**
 * Registrar el estado de pedido personalizado "Quoted"
 */
function registrar_estado_pedido_quoted() {
    register_post_status( 'wc-quoted', array(
        'label'                     => _x( 'Quoted', 'Order status', 'quote-wc' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Quoted <span class="count">(%s)</span>', 'Quoted <span class="count">(%s)</span>', 'quote-wc' )
    ) );
}
add_action( 'init', 'registrar_estado_pedido_quoted' );

/**
 * Permitir que el estado 'quoted' sea editable en el admin de WooCommerce
 */
function permitir_edicion_estado_quoted( $statuses ) {
    $statuses[] = 'wc-quoted';
    return $statuses;
}
add_filter( 'wc_order_is_editable', 'quoted_order_is_editable', 10, 2 );

function quoted_order_is_editable( $is_editable, $order ) {
    if ( $order->get_status() === 'quoted' ) {
        return true;
    }
    return $is_editable;
}

/**
 * Agregar el estado "Quoted" a la lista de estados de pedido de WooCommerce
 */
function agregar_estado_pedido_quoted( $order_statuses ) {
    $order_statuses['wc-quoted'] = _x( 'Quoted', 'Order status', 'quote-wc' );
    return $order_statuses;
}
add_filter( 'wc_order_statuses', 'agregar_estado_pedido_quoted' );




/**
 * Ahora en includes/class-quotes-payment-gateway.php (51) Process the payment gateway.
 * Establecer el estado del pedido como "quoted" cuando se usa el método de pago quotes-gateway
 */
function set_order_status_to_quoted( $order_id ) {
    if ( ! $order_id ) {
        return;
    }
    
    // Obtén el objeto del pedido
    $order = wc_get_order( $order_id );
    
    // Verifica si el pedido existe y si el método de pago es 'quotes-gateway'
    if ( $order && $order->get_payment_method() === 'quotes-gateway' ) {
        // Establece el estado del pedido como 'quoted'
        $order->update_status( 'quoted', __( 'Order awaiting quote.', 'quote-wc' ), true );
        
        // Establecer el meta de quote status
        update_post_meta( $order_id, '_quote_status', 'quote-pending' );
    }
}
add_action( 'woocommerce_checkout_order_processed', 'set_order_status_to_quoted', 10, 1 );




/**
 * FORZAR registro de acciones AJAX
 */
add_action( 'wp_ajax_qwc_update_status', 'qwc_ajax_update_status' );
add_action( 'wp_ajax_qwc_send_quote', 'qwc_ajax_send_quote' );

function qwc_ajax_update_status() {
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
    
    if ($order_id && $status) {
        update_post_meta($order_id, '_quote_status', $status);
        $order = wc_get_order($order_id);
        $order->add_order_note(__('Quote Complete.', 'quote-wc'));
    }
    wp_die();
}

function qwc_ajax_send_quote() {
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    
    if ($order_id) {
        WC_Emails::instance();
        do_action('qwc_send_quote_notification', $order_id);
        update_post_meta($order_id, '_quote_status', 'quote-sent');
        
        $order = wc_get_order($order_id);
        $order->add_order_note(__('Quote email sent to ', 'quote-wc') . $order->get_billing_email());
        
        echo 'quote-sent';
    }
    wp_die();
}



/**
 * SISTEMA DE FIRMA DE COTIZACIONES
 * Añadir al final de quotes-woocommerce.php
 */

/**
 * 1. REGISTRAR NUEVOS ESTADOS
 */
add_action( 'init', 'qwc_register_signature_statuses' );
function qwc_register_signature_statuses() {
    // Estado: Firmado
    register_post_status( 'wc-quote-signed', array(
        'label'                     => _x( 'Quote Signed', 'Order status', 'quote-wc' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Quote Signed <span class="count">(%s)</span>', 'Quote Signed <span class="count">(%s)</span>', 'quote-wc' )
    ) );
    
    // Estado: Pagado
    register_post_status( 'wc-quote-paid', array(
        'label'                     => _x( 'Quote Paid', 'Order status', 'quote-wc' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Quote Paid <span class="count">(%s)</span>', 'Quote Paid <span class="count">(%s)</span>', 'quote-wc' )
    ) );
}

add_filter( 'wc_order_statuses', 'qwc_add_signature_statuses' );
function qwc_add_signature_statuses( $order_statuses ) {
    $order_statuses['wc-quote-signed'] = _x( 'Quote Signed', 'Order status', 'quote-wc' );
    $order_statuses['wc-quote-paid'] = _x( 'Quote Paid', 'Order status', 'quote-wc' );
    return $order_statuses;
}

/**
 * 2. ENDPOINT PARA FIRMA
 */
add_action( 'init', 'qwc_add_sign_quote_endpoint' );
function qwc_add_sign_quote_endpoint() {
    add_rewrite_endpoint( 'sign-quote', EP_PAGES );
}

/**
 * 3. MODIFICAR TABLA DE COTIZACIONES - Botón "Firmar"
 */
add_action( 'woocommerce_account_quotes_endpoint', 'qwc_quotes_table_with_sign' );
function qwc_quotes_table_with_sign() {
    $current_user = wp_get_current_user();
    
    $args = array(
        'customer_id' => $current_user->ID,
        'status'      => array( 'quoted', 'quote-signed' ),
        'limit'       => -1,
        'orderby'     => 'date',
        'order'       => 'DESC',
    );
    
    $orders = wc_get_orders( $args );
    
    ?>
    <h2><?php esc_html_e( 'Mis Cotizaciones', 'quote-wc' ); ?></h2>
    
    <?php if ( $orders ) : ?>
        <table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Pedido', 'quote-wc' ); ?></th>
                    <th><?php esc_html_e( 'Fecha', 'quote-wc' ); ?></th>
                    <th><?php esc_html_e( 'Estado', 'quote-wc' ); ?></th>
                    <th><?php esc_html_e( 'Total', 'quote-wc' ); ?></th>
                    <th><?php esc_html_e( 'Acciones', 'quote-wc' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $orders as $order ) : 
                    $quote_status = get_post_meta( $order->get_id(), '_quote_status', true );
                    $order_status = $order->get_status();
                    $is_signed = get_post_meta( $order->get_id(), '_quote_signed', true );
                    
                    if ( $order_status === 'quote-signed' ) {
                        $status_label = __( 'Firmado - Pendiente de pago', 'quote-wc' );
                        $status_color = '#46b450';
                    } elseif ( $quote_status === 'quote-sent' ) {
                        $status_label = __( 'Cotización enviada - Pendiente firma', 'quote-wc' );
                        $status_color = '#ffb900';
                    } elseif ( $quote_status === 'quote-pending' ) {
                        $status_label = __( 'Pendiente cotización', 'quote-wc' );
                        $status_color = '#dc3232';
                    } else {
                        $status_label = __( 'En proceso', 'quote-wc' );
                        $status_color = '#999';
                    }
                ?>
                <tr>
                    <td data-title="<?php esc_attr_e( 'Pedido', 'quote-wc' ); ?>">
                        <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
                            #<?php echo $order->get_order_number(); ?>
                        </a>
                    </td>
                    <td data-title="<?php esc_attr_e( 'Fecha', 'quote-wc' ); ?>">
                        <?php echo esc_html( $order->get_date_created()->date_i18n( 'd/m/Y' ) ); ?>
                    </td>
                    <td data-title="<?php esc_attr_e( 'Estado', 'quote-wc' ); ?>">
                        <span style="color: <?php echo esc_attr( $status_color ); ?>; font-weight: bold;">
                            <?php echo esc_html( $status_label ); ?>
                        </span>
                    </td>
                    <td data-title="<?php esc_attr_e( 'Total', 'quote-wc' ); ?>">
                        <?php echo $order->get_formatted_order_total(); ?>
                    </td>
                    <td data-title="<?php esc_attr_e( 'Acciones', 'quote-wc' ); ?>">
                        <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="woocommerce-button button view">
                            <?php esc_html_e( 'Ver', 'woocommerce' ); ?>
                        </a>
                        <?php if ( $quote_status === 'quote-sent' && $order_status !== 'quote-signed' ) : ?>
                            <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'sign-quote' ) . $order->get_id() ); ?>" 
                               class="woocommerce-button button sign" 
                               style="background: #2ecc71; border-color: #27ae60;">
                                <?php esc_html_e( '✍️ Firmar Cotización', 'quote-wc' ); ?>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p><?php esc_html_e( 'No tienes cotizaciones pendientes.', 'quote-wc' ); ?></p>
    <?php endif; ?>
    <?php
}

/**
 * 4. FORMULARIO DE FIRMA
 */
add_action( 'woocommerce_account_sign-quote_endpoint', 'qwc_sign_quote_form' );
function qwc_sign_quote_form( $order_id ) {
    if ( ! $order_id ) {
        echo '<p>' . __( 'ID de pedido no válido.', 'quote-wc' ) . '</p>';
        return;
    }
    
    $order = wc_get_order( $order_id );
    
    if ( ! $order || $order->get_customer_id() !== get_current_user_id() ) {
        echo '<p>' . __( 'No tienes permiso para ver este pedido.', 'quote-wc' ) . '</p>';
        return;
    }
    
    $quote_status = get_post_meta( $order_id, '_quote_status', true );
    
    if ( $quote_status !== 'quote-sent' ) {
        echo '<p>' . __( 'Esta cotización no está lista para firmar.', 'quote-wc' ) . '</p>';
        return;
    }
    
    // Procesar formulario
    /*
    if ( isset( $_POST['qwc_sign_quote_nonce'] ) && wp_verify_nonce( $_POST['qwc_sign_quote_nonce'], 'qwc_sign_quote_' . $order_id ) ) {
        qwc_process_signature_form( $order_id );
    }*/

    if ( isset( $_POST['qwc_sign_quote_nonce'] ) && wp_verify_nonce( $_POST['qwc_sign_quote_nonce'], 'qwc_sign_quote_' . $order_id ) ) {
    qwc_process_signature_form( $order_id );
    return; // IMPORTANTE: para que no muestre el formulario después de procesar
}
    
    ?>
    <div class="qwc-sign-quote-wrapper">
        <h2><?php printf( __( 'Firmar Cotización #%s', 'quote-wc' ), $order->get_order_number() ); ?></h2>
        
        <div class="qwc-order-summary" style="background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 8px;">
            <h3><?php esc_html_e( 'Resumen del Pedido', 'quote-wc' ); ?></h3>
            <table class="shop_table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Producto', 'quote-wc' ); ?></th>
                        <th><?php esc_html_e( 'Cantidad', 'quote-wc' ); ?></th>
                        <th><?php esc_html_e( 'Precio', 'quote-wc' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $order->get_items() as $item ) : ?>
                    <tr>
                        <td><?php echo esc_html( $item->get_name() ); ?></td>
                        <td><?php echo esc_html( $item->get_quantity() ); ?></td>
                        <td><?php echo $order->get_formatted_line_subtotal( $item ); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2"><?php esc_html_e( 'TOTAL', 'quote-wc' ); ?></th>
                        <th><?php echo $order->get_formatted_order_total(); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <form method="post" enctype="multipart/form-data" class="qwc-signature-form" id="qwc-signature-form">
            <?php wp_nonce_field( 'qwc_sign_quote_' . $order_id, 'qwc_sign_quote_nonce' ); ?>
            
            <div class="qwc-form-section">
                <h3><?php esc_html_e( '📝 Datos Personales', 'quote-wc' ); ?></h3>
                
                <p class="form-row form-row-wide">
                    <label for="qwc_birth_date"><?php esc_html_e( 'Fecha de Nacimiento *', 'quote-wc' ); ?></label>
                    <input type="date" name="qwc_birth_date" id="qwc_birth_date" required 
                           max="<?php echo date( 'Y-m-d', strtotime( '-18 years' ) ); ?>" />
                    <small><?php esc_html_e( 'Debes ser mayor de 18 años', 'quote-wc' ); ?></small>
                </p>
                
                <p class="form-row form-row-wide">
                    <label for="qwc_iban"><?php esc_html_e( 'IBAN *', 'quote-wc' ); ?></label>
                    <!--input type="text" name="qwc_iban" id="qwc_iban" required 
                           placeholder="ES00 0000 0000 0000 0000 0000"
                           pattern="[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}"
                           maxlength="34" /-->

                    <input type="text" name="qwc_iban" id="qwc_iban" required placeholder="ES00 0000 0000 0000 0000 0000" maxlength="34" />

                    <small><?php esc_html_e( 'Formato: ES00 0000 0000 0000 0000 0000', 'quote-wc' ); ?></small>
                </p>
            </div>
            
            <div class="qwc-form-section">
                <h3><?php esc_html_e( '🆔 Documentación', 'quote-wc' ); ?></h3>
                
                <p class="form-row form-row-wide">
                    <label for="qwc_dni_front"><?php esc_html_e( 'DNI/NIE Frontal *', 'quote-wc' ); ?></label>
                    <input type="file" name="qwc_dni_front" id="qwc_dni_front" required 
                           accept="image/*,.pdf" />
                    <small><?php esc_html_e( 'Formatos: JPG, PNG, PDF (Max 5MB)', 'quote-wc' ); ?></small>
                </p>
                
                <p class="form-row form-row-wide">
                    <label for="qwc_dni_back"><?php esc_html_e( 'DNI/NIE Reverso *', 'quote-wc' ); ?></label>
                    <input type="file" name="qwc_dni_back" id="qwc_dni_back" required 
                           accept="image/*,.pdf" />
                    <small><?php esc_html_e( 'Formatos: JPG, PNG, PDF (Max 5MB)', 'quote-wc' ); ?></small>
                </p>
            </div>
            
            <div class="qwc-form-section">
                <h3><?php esc_html_e( '✍️ Firma', 'quote-wc' ); ?></h3>
                
                <div class="qwc-signature-pad-wrapper">
                    <canvas id="qwc-signature-pad" width="600" height="200" 
                            style="border: 2px solid #ddd; border-radius: 8px; cursor: crosshair; background: #fff; max-width: 100%;"></canvas>
                    <input type="hidden" name="qwc_signature" id="qwc_signature" required />
                </div>
                
                <p>
                    <button type="button" id="qwc-clear-signature" class="button" style="margin-top: 10px;">
                        <?php esc_html_e( '🗑️ Limpiar Firma', 'quote-wc' ); ?>
                    </button>
                </p>
            </div>
            
            <div class="qwc-form-section">
                <p class="form-row">
                    <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
                        <input type="checkbox" name="qwc_accept_terms" id="qwc_accept_terms" required />
                        <span><?php esc_html_e( 'Acepto los términos y condiciones de la cotización *', 'quote-wc' ); ?></span>
                    </label>
                </p>
            </div>
            
            <p class="form-row">
                <button type="submit" class="button alt" name="qwc_submit_signature" 
                        style="background: #2ecc71; border-color: #27ae60; font-size: 18px; padding: 15px 30px;">
                    <?php esc_html_e( '✅ Firmar y Enviar Cotización', 'quote-wc' ); ?>
                </button>
            </p>
        </form>
    </div>
    
    <style>
        .qwc-sign-quote-wrapper {
            max-width: 800px;
            margin: 0 auto;
        }
        .qwc-form-section {
            background: #fff;
            padding: 20px;
            margin: 20px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .qwc-form-section h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .qwc-signature-pad-wrapper {
            text-align: center;
        }
        #qwc-signature-pad {
            display: block;
            margin: 0 auto;
        }
        .form-row label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        .form-row small {
            color: #666;
            font-size: 12px;
        }
        .form-row input[type="text"],
        .form-row input[type="date"],
        .form-row input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        // Canvas de firma
        var canvas = document.getElementById('qwc-signature-pad');
        var ctx = canvas.getContext('2d');
        var drawing = false;
        var hasSignature = false;
        
        // Configurar canvas
        ctx.strokeStyle = '#000';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        
        // Eventos de dibujo
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);
        
        // Touch events para móviles
        canvas.addEventListener('touchstart', function(e) {
            e.preventDefault();
            var touch = e.touches[0];
            var mouseEvent = new MouseEvent('mousedown', {
                clientX: touch.clientX,
                clientY: touch.clientY
            });
            canvas.dispatchEvent(mouseEvent);
        });
        
        canvas.addEventListener('touchmove', function(e) {
            e.preventDefault();
            var touch = e.touches[0];
            var mouseEvent = new MouseEvent('mousemove', {
                clientX: touch.clientX,
                clientY: touch.clientY
            });
            canvas.dispatchEvent(mouseEvent);
        });
        
        canvas.addEventListener('touchend', function(e) {
            e.preventDefault();
            var mouseEvent = new MouseEvent('mouseup', {});
            canvas.dispatchEvent(mouseEvent);
        });
        
        function startDrawing(e) {
            drawing = true;
            hasSignature = true;
            var rect = canvas.getBoundingClientRect();
            ctx.beginPath();
            ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
        }
        
        function draw(e) {
            if (!drawing) return;
            var rect = canvas.getBoundingClientRect();
            ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
            ctx.stroke();
        }
        
        function stopDrawing() {
            if (drawing && hasSignature) {
                // Guardar firma como base64
                $('#qwc_signature').val(canvas.toDataURL());
            }
            drawing = false;
        }
        
        // Limpiar firma
        $('#qwc-clear-signature').on('click', function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            $('#qwc_signature').val('');
            hasSignature = false;
        });
        
        // Validar formato IBAN
        $('#qwc_iban').on('blur', function() {
            var iban = $(this).val().replace(/\s/g, '');
            if (iban.length > 0) {
                // Formatear IBAN con espacios
                var formatted = iban.match(/.{1,4}/g).join(' ');
                $(this).val(formatted.toUpperCase());
            }
        });
        
        // Validar formulario antes de enviar
        $('#qwc-signature-form').on('submit', function(e) {
            if (!hasSignature || $('#qwc_signature').val() === '') {
                e.preventDefault();
                alert('Por favor, firma en el recuadro antes de enviar.');
                return false;
            }
        });
    });
    </script>
    <?php
}

/**
 * 5. PROCESAR FIRMA Y DOCUMENTOS
 */
function qwc_process_signature_form( $order_id ) {
    // Validar campos
    if ( empty( $_POST['qwc_birth_date'] ) || empty( $_POST['qwc_iban'] ) || 
         empty( $_POST['qwc_signature'] ) || empty( $_POST['qwc_accept_terms'] ) ) {
        wc_add_notice( __( 'Por favor, completa todos los campos requeridos.', 'quote-wc' ), 'error' );
        return;
    }
    
    // Validar edad mínima (18 años)
    $birth_date = strtotime( $_POST['qwc_birth_date'] );
    $min_age_date = strtotime( '-18 years' );
    if ( $birth_date > $min_age_date ) {
        wc_add_notice( __( 'Debes ser mayor de 18 años.', 'quote-wc' ), 'error' );
        return;
    }
    
    // Validar IBAN
    $iban = sanitize_text_field( str_replace( ' ', '', $_POST['qwc_iban'] ) );
    if ( ! preg_match( '/^[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}$/', $iban ) ) {
        wc_add_notice( __( 'El formato del IBAN no es válido.', 'quote-wc' ), 'error' );
        return;
    }
    
    // Subir archivos
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/image.php' );
    require_once( ABSPATH . 'wp-admin/includes/media.php' );
    
    $upload_overrides = array( 'test_form' => false );
    
    // DNI Frontal
    $dni_front = null;
    if ( isset( $_FILES['qwc_dni_front'] ) && $_FILES['qwc_dni_front']['size'] > 0 ) {
        $dni_front = wp_handle_upload( $_FILES['qwc_dni_front'], $upload_overrides );
        if ( isset( $dni_front['error'] ) ) {
            wc_add_notice( __( 'Error al subir DNI frontal: ', 'quote-wc' ) . $dni_front['error'], 'error' );
            return;
        }
    }
    
    // DNI Reverso
    $dni_back = null;
    if ( isset( $_FILES['qwc_dni_back'] ) && $_FILES['qwc_dni_back']['size'] > 0 ) {
        $dni_back = wp_handle_upload( $_FILES['qwc_dni_back'], $upload_overrides );
        if ( isset( $dni_back['error'] ) ) {
            wc_add_notice( __( 'Error al subir DNI reverso: ', 'quote-wc' ) . $dni_back['error'], 'error' );
            return;
        }
    }
    
    // Guardar firma como imagen
    $signature_data = $_POST['qwc_signature'];
    $signature_data = str_replace( 'data:image/png;base64,', '', $signature_data );
    $signature_data = str_replace( ' ', '+', $signature_data );
    $signature_decoded = base64_decode( $signature_data );
    
    $upload_dir = wp_upload_dir();
    $signature_filename = 'signature-order-' . $order_id . '-' . time() . '.png';
    $signature_filepath = $upload_dir['path'] . '/' . $signature_filename;
    file_put_contents( $signature_filepath, $signature_decoded );
    
    // Guardar todo en post meta
    update_post_meta( $order_id, '_qwc_birth_date', sanitize_text_field( $_POST['qwc_birth_date'] ) );
    update_post_meta( $order_id, '_qwc_iban', $iban );
    update_post_meta( $order_id, '_qwc_dni_front', $dni_front['url'] );
    update_post_meta( $order_id, '_qwc_dni_back', $dni_back['url'] );
    update_post_meta( $order_id, '_qwc_signature', $upload_dir['url'] . '/' . $signature_filename );
    update_post_meta( $order_id, '_quote_signed', 'yes' );
    update_post_meta( $order_id, '_quote_signed_date', current_time( 'mysql' ) );
    
    // Cambiar estado del pedido
    $order = wc_get_order( $order_id );
    $order->update_status( 'quote-signed', __( 'Cliente ha firmado la cotización.', 'quote-wc' ) );
   /* 
    // Notificación
    wc_add_notice( __( '✅ Cotización firmada correctamente. Recibirás el pago en los próximos días.', 'quote-wc' ), 'success' );
    
    // Redireccionar
    wp_safe_redirect( wc_get_account_endpoint_url( 'quotes' ) );
    exit;*/


    // Notificación para la próxima página
    wc_add_notice( __( '✅ Cotización firmada correctamente. Recibirás el pago en los próximos días.', 'quote-wc' ), 'success' );

    // Redireccionar (intentar PHP primero, luego JS como backup)
    if ( ! headers_sent() ) {
        wp_safe_redirect( wc_get_account_endpoint_url( 'quotes' ) );
        exit;
    } else {
        // Si los headers ya se enviaron, usar JavaScript
        echo '<script>window.location.href = "' . esc_url( wc_get_account_endpoint_url( 'quotes' ) ) . '";</script>';
        exit;
    }
}



/**
 * BOTÓN ADMIN: MARCAR COMO PAGADO
 * Añadir al final de quotes-woocommerce.php
 */

/**
 * Mostrar datos de firma y botón "Marcar como Pagado" en admin
 */
add_action( 'woocommerce_admin_order_data_after_order_details', 'qwc_show_signature_data_admin', 20, 1 );
function qwc_show_signature_data_admin( $order ) {
    
    $order_id = $order->get_id();
    $order_status = $order->get_status();
    
    // Solo mostrar para pedidos firmados
    $is_signed = get_post_meta( $order_id, '_quote_signed', true );
    if ( $is_signed !== 'yes' ) {
        return;
    }
    
    // Obtener datos
    $birth_date = get_post_meta( $order_id, '_qwc_birth_date', true );
    $iban = get_post_meta( $order_id, '_qwc_iban', true );
    $dni_front = get_post_meta( $order_id, '_qwc_dni_front', true );
    $dni_back = get_post_meta( $order_id, '_qwc_dni_back', true );
    $signature = get_post_meta( $order_id, '_qwc_signature', true );
    $signed_date = get_post_meta( $order_id, '_quote_signed_date', true );
    
    ?>
    <br class="clear" />
    <div class="order_data_column" style="clear:both; width: 100%;">
        <h3 style="color: #2ecc71;">✍️ <?php esc_html_e( 'Datos de Firma del Cliente', 'quote-wc' ); ?></h3>
        <div class="qwc-signature-data" style="padding: 15px; background: #f0fff4; border: 2px solid #2ecc71; border-radius: 8px;">
            
            <p><strong><?php esc_html_e( 'Fecha de Firma:', 'quote-wc' ); ?></strong> 
                <?php echo esc_html( date_i18n( 'd/m/Y H:i', strtotime( $signed_date ) ) ); ?>
            </p>
            
            <p><strong><?php esc_html_e( 'Fecha de Nacimiento:', 'quote-wc' ); ?></strong> 
                <?php echo esc_html( date_i18n( 'd/m/Y', strtotime( $birth_date ) ) ); ?>
                (<?php echo esc_html( floor( ( time() - strtotime( $birth_date ) ) / 31556926 ) ); ?> años)
            </p>
            
            <p><strong><?php esc_html_e( 'IBAN:', 'quote-wc' ); ?></strong> 
                <code style="background: #fff; padding: 5px 10px; border-radius: 4px; font-size: 14px;">
                    <?php echo esc_html( $iban ); ?>
                </code>
                <button type="button" onclick="navigator.clipboard.writeText('<?php echo esc_js( $iban ); ?>')" 
                        class="button button-small" style="margin-left: 10px;">
                    📋 Copiar
                </button>
            </p>
            
            <p><strong><?php esc_html_e( 'Firma Digital:', 'quote-wc' ); ?></strong></p>
            <div style="background: #fff; padding: 10px; border-radius: 4px; text-align: center;">
                <img src="<?php echo esc_url( $signature ); ?>" alt="Firma" style="max-width: 400px; border: 1px solid #ddd;" />
            </div>
            
            <p style="margin-top: 15px;"><strong><?php esc_html_e( 'Documentación:', 'quote-wc' ); ?></strong></p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <p style="margin: 0 0 5px 0; font-weight: bold;">DNI/NIE Frontal:</p>
                    <?php if ( strpos( $dni_front, '.pdf' ) !== false ) : ?>
                        <a href="<?php echo esc_url( $dni_front ); ?>" target="_blank" class="button">
                            📄 Ver PDF
                        </a>
                    <?php else : ?>
                        <a href="<?php echo esc_url( $dni_front ); ?>" target="_blank">
                            <img src="<?php echo esc_url( $dni_front ); ?>" alt="DNI Frontal" 
                                 style="max-width: 100%; border: 1px solid #ddd; border-radius: 4px;" />
                        </a>
                    <?php endif; ?>
                </div>
                <div>
                    <p style="margin: 0 0 5px 0; font-weight: bold;">DNI/NIE Reverso:</p>
                    <?php if ( strpos( $dni_back, '.pdf' ) !== false ) : ?>
                        <a href="<?php echo esc_url( $dni_back ); ?>" target="_blank" class="button">
                            📄 Ver PDF
                        </a>
                    <?php else : ?>
                        <a href="<?php echo esc_url( $dni_back ); ?>" target="_blank">
                            <img src="<?php echo esc_url( $dni_back ); ?>" alt="DNI Reverso" 
                                 style="max-width: 100%; border: 1px solid #ddd; border-radius: 4px;" />
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ( $order_status === 'quote-signed' ) : ?>
                <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #ddd;">
                    <button id='qwc_mark_as_paid' type="button" class="button button-primary button-large" 
                            style="background: #27ae60; border-color: #229954; font-size: 16px; padding: 10px 20px;">
                        💰 <?php esc_html_e( 'Marcar como PAGADO', 'quote-wc' ); ?>
                    </button>
                    <p class="description" style="margin-top: 10px;">
                        <?php esc_html_e( 'Haz clic aquí una vez hayas realizado la transferencia al IBAN del cliente.', 'quote-wc' ); ?>
                    </p>
                    <div id='qwc_paid_msg' style='margin-top: 10px; font-weight: bold; font-size: 14px; color: #27ae60; display: none;'></div>
                </div>
            <?php elseif ( $order_status === 'quote-paid' ) : ?>
                <div style="margin-top: 20px; padding: 15px; background: #d4edda; border: 2px solid #28a745; border-radius: 4px;">
                    <p style="margin: 0; font-size: 16px; font-weight: bold; color: #155724;">
                        ✅ <?php esc_html_e( 'PAGO REALIZADO', 'quote-wc' ); ?>
                    </p>
                    <?php 
                    $paid_date = get_post_meta( $order_id, '_quote_paid_date', true );
                    if ( $paid_date ) : ?>
                        <p style="margin: 5px 0 0 0; color: #155724;">
                            <?php printf( __( 'Fecha: %s', 'quote-wc' ), date_i18n( 'd/m/Y H:i', strtotime( $paid_date ) ) ); ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#qwc_mark_as_paid').on('click', function() {
            if ( ! confirm('<?php esc_html_e( '¿Confirmas que has realizado el pago al cliente?', 'quote-wc' ); ?>') ) {
                return;
            }
            
            var button = $(this);
            button.prop('disabled', true).text('⏳ Procesando...');
            
            var data = {
                order_id: <?php echo $order_id; ?>,
                action: 'qwc_mark_quote_paid'
            };
            
            $.post(ajaxurl, data, function(response) {
                if (response === 'success') {
                    $('#qwc_paid_msg').html('✅ Pedido marcado como PAGADO').show();
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    alert('Error al marcar como pagado');
                    button.prop('disabled', false).text('💰 Marcar como PAGADO');
                }
            });
        });
    });
    </script>
    <?php
}

/**
 * AJAX: Marcar cotización como pagada
 */
add_action( 'wp_ajax_qwc_mark_quote_paid', 'qwc_ajax_mark_quote_paid' );
function qwc_ajax_mark_quote_paid() {
    $order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;
    
    if ( ! $order_id || ! current_user_can( 'manage_woocommerce' ) ) {
        echo 'error';
        wp_die();
    }
    
    $order = wc_get_order( $order_id );
    
    if ( ! $order ) {
        echo 'error';
        wp_die();
    }
    
    // Marcar como pagado
    update_post_meta( $order_id, '_quote_paid_date', current_time( 'mysql' ) );
    $order->update_status( 'quote-paid', __( 'Pago realizado al cliente.', 'quote-wc' ) );
    
    // Añadir nota con IBAN para referencia
    $iban = get_post_meta( $order_id, '_qwc_iban', true );
    $order->add_order_note( sprintf( __( 'Pago transferido al IBAN: %s', 'quote-wc' ), $iban ) );
    
    echo 'success';
    wp_die();
}

/**
 * Hacer editables los pedidos quote-signed y quote-paid
 */
add_filter( 'wc_order_is_editable', 'qwc_make_signed_paid_editable', 10, 2 );
function qwc_make_signed_paid_editable( $is_editable, $order ) {
    if ( in_array( $order->get_status(), array( 'quote-signed', 'quote-paid' ) ) ) {
        return true;
    }
    return $is_editable;
}

/**
 * Mostrar columna de estado en listado de pedidos admin
 */
add_filter( 'manage_edit-shop_order_columns', 'qwc_add_quote_status_column', 20 );
function qwc_add_quote_status_column( $columns ) {
    $new_columns = array();
    foreach ( $columns as $key => $column ) {
        $new_columns[ $key ] = $column;
        if ( $key === 'order_status' ) {
            $new_columns['quote_signature_status'] = __( 'Estado Firma', 'quote-wc' );
        }
    }
    return $new_columns;
}

add_action( 'manage_shop_order_posts_custom_column', 'qwc_quote_status_column_content', 20, 2 );
function qwc_quote_status_column_content( $column, $post_id ) {
    if ( $column === 'quote_signature_status' ) {
        $order = wc_get_order( $post_id );
        
        if ( $order->get_payment_method() !== 'quotes-gateway' ) {
            echo '—';
            return;
        }
        
        $is_signed = get_post_meta( $post_id, '_quote_signed', true );
        $order_status = $order->get_status();
        
        if ( $order_status === 'quote-paid' ) {
            echo '<span style="color: #27ae60; font-weight: bold;">💰 PAGADO</span>';
        } elseif ( $order_status === 'quote-signed' ) {
            echo '<span style="color: #f39c12; font-weight: bold;">✍️ FIRMADO</span>';
        } elseif ( $is_signed === 'yes' ) {
            echo '<span style="color: #3498db;">✅ Firmado</span>';
        } else {
            $quote_status = get_post_meta( $post_id, '_quote_status', true );
            if ( $quote_status === 'quote-sent' ) {
                echo '<span style="color: #e74c3c;">⏳ Pendiente Firma</span>';
            } else {
                echo '<span style="color: #95a5a6;">—</span>';
            }
        }
    }
}


/**
 * PESTAÑA MIS COTIZACIONES EN MY ACCOUNT
 */
// 1. Añadir la pestaña al menú
add_filter( 'woocommerce_account_menu_items', 'qwc_add_quotes_tab_my_account', 40 );
function qwc_add_quotes_tab_my_account( $items ) {
    $new_items = array();
    foreach ( $items as $key => $value ) {
        $new_items[$key] = $value;
        if ( $key === 'orders' ) {
            $new_items['quotes'] = __( 'Mis Cotizaciones', 'quote-wc' );
        }
    }
    return $new_items;
}

// 2. Registrar el endpoint
add_action( 'init', 'qwc_register_quotes_endpoint' );
function qwc_register_quotes_endpoint() {
    add_rewrite_endpoint( 'quotes', EP_PAGES );
}

// 3. Contenido de la pestaña
add_action( 'woocommerce_account_quotes_endpoint', 'qwc_show_quotes_page' );
function qwc_show_quotes_page() {
    echo '<h2>Mis Cotizaciones</h2>';
    echo '<p>Aquí van tus cotizaciones</p>';
}