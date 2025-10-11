<?php 

// Register a custom order status
function register_quoted_order_status() {
    register_post_status('wc-quoted', array(
        'label'                     => 'Quoted', // Nombre visible
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Quoted <span class="count">(%s)</span>', 'Quoted <span class="count">(%s)</span>'),
    ));
}
add_action('init', 'register_quoted_order_status');



// Añadir "Quoted" a la lista de estados de WooCommerce
function add_quoted_to_order_statuses($order_statuses) {
    $order_statuses['wc-quoted'] = 'Quoted';
    return $order_statuses;
}
add_filter('wc_order_statuses', 'add_quoted_to_order_statuses');



// Añadir CSS para el estado "Quoted" en el admin
function quoted_status_admin_css() {
    echo '<style>
        .order-status.status-quoted {
            background: #f8dda7;
            color: #94660c;
        }
    </style>';
}
add_action('admin_head', 'quoted_status_admin_css');



//////////////////////////////////////////////////




//add_filter('wc_order_statuses', 'ymk_add_custom_order_tradein');
function ymk_add_custom_order_tradein($order_statuses) {
    $new_order_statuses = array();

    // add new order status before processing
    foreach ($order_statuses as $key => $status) {
        $new_order_statuses[$key] = $status;
        if ('wc-processing' === $key) {
            $new_order_statuses['wc-quoted'] = __('Adquisición', 'Order status', 'woocommerce' );
        }
    }
    return $new_order_statuses;
}




// Adding custom status 'awaiting-delivery' to admin order list bulk dropdown
add_filter( 'bulk_actions-edit-shop_order', 'ymk_custom_dropdown_bulk_actions_tradein_order', 50, 1 );
function ymk_custom_dropdown_bulk_actions_tradein_order( $actions ) {
    $new_actions = array();
    // add new order status before processing
    foreach ($actions as $key => $action) {
        if ('mark_processing' === $key)
            $new_actions['mark_quoted'] = __( 'Change status to quoted', 'woocommerce' );
            $new_actions[$key] = $action;
    }
    return $new_actions;
}


// Add a custom order status action button
add_filter( 'woocommerce_admin_order_actions', 'ymk_add_tradein_order_status_actions_button', 100, 2 );
function ymk_add_tradein_order_status_actions_button( $actions, $order ) {
    // Display the button for all orders that have a 'processing', 'pending' or 'on-hold' status
    if ( $order->has_status( array( 'quoted' ) ) ) {

        // The key slug defined for your action button
        $action_slug = 'quoted';

        // Set the action button
        $actions[$action_slug] = array(
            'url'       => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status='.$action_slug.'&order_id='.$order->get_id() ), 'woocommerce-mark-order-status' ),
            'name'      => __( 'Adquisición', 'woocommerce' ),
            'action'    => $action_slug,
        );
    }

    if ( $order->has_status( array( 'quoted' ) ) ) {

        // The key slug defined for your action button
        $action_slug = 'completed';

        // Set the action button
        $actions[$action_slug] = array(
            'url'       => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status='.$action_slug.'&order_id='.$order->get_id() ), 'woocommerce-mark-order-status' ),
            'name'      => __( 'Complete', 'woocommerce' ),
            'action'    => 'complete',
        );
    }

    return $actions;
}


// Set styling for custom order status action button icon and List icon
add_action( 'admin_head', 'ymk_add_custom_tradein_order_status_actions_button_css' );
function ymk_add_custom_tradein_order_status_actions_button_css() {
    $action_slug = "quoted"; // The key slug defined for your action button
    ?>
    <style>
        .wc-action-button-<?php echo $action_slug; ?>::after {
            font-family: woocommerce !important; content: "\e029" !important;
        }
    </style>
    <?php
}


// Add customer note to order
// add_action( 'woocommerce_order_status_quoted', 'add_tradein_processing_note', 10, 2 );
function add_tradein_processing_note( $order_id, $order) {
    $order->add_order_note( "Pedido enviado", $is_customer_note = 1 );
}


// Add metabox to order part 1
add_action( 'add_meta_boxes', 'ymk_tradein_order_quoted_meta_box' );
function ymk_tradein_order_quoted_meta_box() {
    add_meta_box( 'custom_box', 'Nº Seguimiento', 'ymk_single_order_quoted_meta', 'shop_order', 'advanced', 'high' );
}


// Add metabox to order part 2
function ymk_single_order_quoted_meta( $order_id ) {
    $order = wc_get_order( $order_id );
    echo '<input name="quoted_number" id="quoted_number" type="text" size="20" value="' . get_post_meta( $order->get_id(), '_quoted_number', true ) . '">';
}


// Save custom field value
add_action( 'woocommerce_process_shop_order_meta', 'save_tradein_number_id_value', 20 );
function save_tradein_number_id_value( $order_id ) {
    $order = wc_get_order( $order_id ); // Get the WC_Order object
    
    if ( isset($_POST['quoted_number']) ) {
        $order->update_meta_data('_quoted_number', sanitize_text_field($_POST['quoted_number']));
        $order->save();
    }
}


// Cuando cambie el estado de pedido desde Processing a Adquisición enviamos el correo Customer_Completed_Order
// Con el Asunto y la cabecera cambiados
add_action( 'woocommerce_order_status_processing_to_quoted', 'ymk_processing_to_quoted_email', 20, 2 );
function ymk_processing_to_quoted_email( $order_id, $order ){ 
    $subject = 'Tu pedido está en camino';
    $heading = 'Tu pedido está en camino';

    $mailer = WC()->mailer()->get_emails();
    $mailer['WC_Email_Customer_Completed_Order']->settings['heading'] = $heading;
    $mailer['WC_Email_Customer_Completed_Order']->settings['subject'] = $subject;
    // Send the email with custom heading & subject
    $mailer['WC_Email_Customer_Completed_Order']->trigger( $order_id );
}


// Hemos comentado el sujeto del correo "customer-completed-order.php"
// Añadimos en su lugar un nuevo YMK Hook en el que añadimos la frase para enviados y completados
add_action( 'ymk_email_order_subject', 'ymk_email_tradein_order_subject', 20, 4 );
function ymk_email_tradein_order_subject( $order, $sent_to_admin, $plain_text, $email ) {

   if ( $order->get_status() == "quoted" ) {
      echo '<p class="email-upsell-p">Hemos aceptado tu solicidud de venta.</p>';
   }

   if ( $order->get_status() == "completed" ) {
      esc_html_e( 'We have finished processing your order.', 'woocommerce' );
   }

}


// En el caso de los enviados tomamos los datos y creamos texto personalizado
add_action( 'woocommerce_email_before_order_table', 'ymk_add_content_tradein_emaila', 20, 4 );
function ymk_add_content_tradein_emaila( $order, $sent_to_admin, $plain_text, $email ) {

    $order_id = $order->get_id();
    $edit_order_mail = $order->get_billing_email();
    $edit_order_name = $order->get_billing_first_name();
    $edit_quoted_number = get_post_meta( $order_id, '_quoted_number', true );

   if ( $order->get_status() == "quoted" ) {
      echo '<h2 class="email-upsell-title">Firma tu venta.</h2><p class="email-upsell-p">Puedes confirmar tu venta <a target="_blank" href="'.get_site_url().'/mi-cuenta/quotes">pulsando aquí</a>.' . $edit_quoted_number . '.</p>';
   }
}


